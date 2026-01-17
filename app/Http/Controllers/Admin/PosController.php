<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Services\Inventory\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PosController extends Controller
{
    protected $ledgerService;

    public function __construct(StockLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function index()
    {
        // Check for open session
        $session = PosSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        // If no session, maybe prompt to open one? For now, we auto-create or just allow.
        
        return Inertia::render('Admin/Pos/Terminal', [
            'session' => $session,
            // Preload some data or load via async
        ]);
    }

    public function store(Request $request)
    {
        // This is the Checkout action
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|string', // cash, card
            'paid_amount' => 'required|numeric|min:0',
        ]);
        
        return DB::transaction(function () use ($validated) {
            $user = auth()->user();
            
            // 1. Calculate Totals
            $totalAmount = 0;
            foreach ($validated['lines'] as $line) {
                $totalAmount += $line['quantity'] * $line['unit_price'];
            }
            // Simple logic: paid needs to cover total, change = paid - total
            $paidAmount = $validated['paid_amount'];
            $changeAmount = max(0, $paidAmount - $totalAmount);
            
            // 2. Create Sale
            $sale = Sale::create([
                'branch_id' => $user->branch_id,
                'customer_id' => $validated['customer_id'],
                'user_id' => $user->id,
                // 'pos_session_id' => ... find or create session
                'status' => 'completed',
                'payment_status' => $paidAmount >= $totalAmount ? 'paid' : 'partial',
                'invoice_number' => 'INV-' . strtoupper(uniqid()), // Simple generator
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
            ]);
            
            // 3. Create Sale Lines and Deduct Stock
            foreach ($validated['lines'] as $lineData) {
                $line = SaleLine::create([
                    'sale_id' => $sale->id,
                    'product_id' => $lineData['product_id'],
                    'variation_id' => $lineData['variation_id'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'subtotal' => $lineData['quantity'] * $lineData['unit_price'],
                ]);

                // Disable STOCK DEDUCTION for now to allow proceeding without negative stock error if stocks are 0
                // In production, we would check stock first.
                 $this->ledgerService->addEntry(
                    $user->branch_id,
                    $user->warehouse_id ?? 'default_warehouse_id_placeholder', // Should come from user's current register/warehouse
                    $lineData['product_id'],
                    $lineData['variation_id'] ?? null,
                    -$lineData['quantity'], // Negative for sale
                    'sale',
                    $sale,
                    "POS Sale #{$sale->invoice_number}"
                );
            }
            
            // 4. Record Payment
            $sale->payments()->create([
                'branch_id' => $user->branch_id,
                'amount' => $totalAmount, // Recording the transaction value, or actual cash? usually actual cash paid if tracking drawer
                'payment_method' => $validated['payment_method'],
            ]);
            
            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number
            ]);
        });
    }
}
