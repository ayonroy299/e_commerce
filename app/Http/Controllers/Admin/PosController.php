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

        return Inertia::render('Admin/Pos/Terminal', [
            'session' => $session,
            'emi_plans' => \App\Models\EmiPlan::where('is_active', true)->get(),
            'customers' => \App\Models\Customer::select('id', 'name', 'phone')->get(),
            'categories' => \App\Models\Category::where('is_active', true)->get(),
            'initial_products' => Product::active()
                ->where('branch_id', auth()->user()->branch_id)
                ->with(['media', 'variations'])
                ->latest()
                ->limit(24)
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => $p->getCurrentPrice(),
                    'image' => $p->getFirstMediaUrl('thumbnail'),
                    'stock' => $p->stocks->sum('quantity'),
                    'has_variations' => $p->hasVariations(),
                ]),
        ]);
    }

    public function store(Request $request)
    {
        // This is the Checkout action
        $validated = $request->validate([
            'customer_id' => 'required_if:payment_method,emi|nullable|exists:customers,id',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.variation_id' => 'nullable|exists:product_variations,id',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|string', // cash, card, emi
            'paid_amount' => 'required|numeric|min:0',
            'emi_plan_id' => 'required_if:payment_method,emi|nullable|exists:emi_plans,id',
        ]);
        
        return DB::transaction(function () use ($validated) {
            $user = auth()->user();
            
            // 1. Calculate Totals
            $totalAmount = 0;
            foreach ($validated['lines'] as $line) {
                $totalAmount += $line['quantity'] * $line['unit_price'];
            }
            
            $isEmi = $validated['payment_method'] === 'emi';
            $isOnline = $validated['payment_method'] === 'online';
            $paidAmount = $validated['paid_amount'];
            
            if ($isEmi) {
                $plan = \App\Models\EmiPlan::findOrFail($validated['emi_plan_id']);
                $minDownPayment = ($totalAmount * $plan->down_payment_percentage / 100);
                if ($paidAmount < $minDownPayment) {
                     return response()->json(['success' => false, 'message' => "Down payment must be at least {$minDownPayment}"], 422);
                }
            }

            $changeAmount = (!$isEmi && !$isOnline && $paidAmount > $totalAmount) ? ($paidAmount - $totalAmount) : 0;
            
            // 2. Create Sale
            $sale = Sale::create([
                'branch_id' => $user->branch_id,
                'customer_id' => $validated['customer_id'],
                'user_id' => $user->id,
                'status' => $isOnline ? 'pending_payment' : 'completed',
                'payment_status' => $isOnline ? 'pending' : ($isEmi ? 'partial' : ($paidAmount >= $totalAmount ? 'paid' : 'partial')),
                'invoice_number' => 'INV-' . strtoupper(uniqid()),
                'total_amount' => $totalAmount,
                'paid_amount' => $isOnline ? 0 : $paidAmount,
                'change_amount' => $changeAmount,
                'sold_at' => now(),
            ]);
            
            // 3. Create Sale Lines and Deduct Stock
            foreach ($validated['lines'] as $lineData) {
                SaleLine::create([
                    'sale_id' => $sale->id,
                    'product_id' => $lineData['product_id'],
                    'variation_id' => $lineData['variation_id'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'subtotal' => $lineData['quantity'] * $lineData['unit_price'],
                ]);

                // We deduct stock even for pending online sales in POS to reserve items
                $this->ledgerService->addEntry(
                    $user->branch_id,
                    $user->warehouse_id ?? 'default_warehouse_id_placeholder',
                    $lineData['product_id'],
                    $lineData['variation_id'] ?? null,
                    -$lineData['quantity'],
                    'sale',
                    $sale,
                    "POS Sale #{$sale->invoice_number}"
                );
            }
            
            // 4. Record Payment / EMI Contract
            $redirectUrl = null;
            if ($isEmi) {
                $contract = \App\Models\EmiContract::create([
                    'branch_id' => $user->branch_id,
                    'sale_id' => $sale->id,
                    'emi_plan_id' => $validated['emi_plan_id'],
                    'principal_amount' => $totalAmount,
                    'down_payment' => $paidAmount,
                    'financed_amount' => $totalAmount - $paidAmount,
                    'interest_amount' => 0,
                    'total_amount' => $totalAmount - $paidAmount,
                    'start_date' => now(),
                    'status' => 'pending', // Pending until down payment confirmed
                    'created_by' => $user->id,
                    'auto_debit' => $isOnline, // Set auto-debit if paid online
                ]);

                app(\App\Services\EmiService::class)->generateSchedule($contract);

                if (!$isOnline) {
                    $sale->payments()->create([
                         'branch_id' => $user->branch_id,
                         'amount' => $paidAmount,
                         'payment_method' => 'cash',
                         'note' => 'Down payment for EMI Contract',
                    ]);
                    $contract->update(['status' => 'active']);
                }
            } elseif (!$isOnline) {
                $sale->payments()->create([
                    'branch_id' => $user->branch_id,
                    'amount' => min($totalAmount, $paidAmount),
                    'payment_method' => $validated['payment_method'],
                ]);
            }

            if ($isOnline) {
                $customer = Customer::find($validated['customer_id']);
                $paymentResponse = app(\App\Services\SSLCommerzService::class)->initiatePayment($sale, [
                    'name' => $customer ? $customer->name : 'Walk-in Customer',
                    'phone' => $customer ? $customer->phone : '01700000000',
                    'email' => $customer ? $customer->email : 'customer@example.com',
                ]);

                if ($paymentResponse['status'] === 'success') {
                    $redirectUrl = $paymentResponse['gateway_url'];
                } else {
                    return response()->json(['success' => false, 'message' => $paymentResponse['message']], 422);
                }
            }
            
            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'contract_id' => $isEmi ? $contract->id : null,
                'redirect_url' => $redirectUrl,
            ]);
        });
    }
}
