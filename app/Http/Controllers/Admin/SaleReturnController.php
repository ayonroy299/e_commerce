<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnLine;
use App\Services\Inventory\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SaleReturnController extends Controller
{
    protected $ledgerService;

    public function __construct(StockLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function create(Sale $sale)
    {
        $sale->load('lines.product', 'lines.variation');
        return Inertia::render('Admin/Returns/Create', [
            'sale' => $sale,
        ]);
    }

    public function store(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'lines' => 'required|array|min:1',
            'lines.*.sale_line_id' => 'required|exists:sale_lines,id',
            'lines.*.quantity_returned' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($validated, $sale) {
            
            // 1. Calculate Refund Total
            $totalRefund = 0;
            $returnLinesData = [];
            
            foreach ($validated['lines'] as $lineData) {
                // Fetch original line to get price
                $origLine = $sale->lines()->find($lineData['sale_line_id']);
                if (!$origLine) continue;

                $refundAmount = $origLine->unit_price * $lineData['quantity_returned'];
                $totalRefund += $refundAmount;
                
                $returnLinesData[] = [
                    'line_data' => $lineData,
                    'orig_line' => $origLine,
                    'refund_amount' => $refundAmount
                ];
            }

            // 2. Create Sale Return
            $return = SaleReturn::create([
                'branch_id' => auth()->user()->branch_id,
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'reason' => $validated['reason'],
                'status' => 'approved', // Auto-approve for now
                'refund_status' => 'refunded', // Assuming immediate cash refund
                'total_amount' => $totalRefund,
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // 3. Create Lines & Update Stock
            foreach ($returnLinesData as $data) {
                SaleReturnLine::create([
                    'sale_return_id' => $return->id,
                    'sale_line_id' => $data['orig_line']->id,
                    'product_id' => $data['orig_line']->product_id,
                    'variation_id' => $data['orig_line']->variation_id,
                    'quantity_returned' => $data['line_data']['quantity_returned'],
                    'refund_amount' => $data['refund_amount'],
                ]);

                // RESTOCK
                $this->ledgerService->addEntry(
                    $return->branch_id,
                    auth()->user()->warehouse_id ?? 'default_warehouse_placeholder', 
                    $data['orig_line']->product_id,
                    $data['orig_line']->variation_id,
                    $data['line_data']['quantity_returned'], // Positive to add back
                    'return',
                    $return,
                    "Return for Sale #{$sale->invoice_number}"
                );
            }

            // 4. Record Refund Payment (Negative)
            $return->refunds()->create([
                'branch_id' => $return->branch_id,
                'amount' => -$totalRefund,
                'payment_method' => 'cash', // Default to cash refund
                'details' => ['reason' => 'Customer Return'],
            ]);
            
            $sale->update(['status' => 'returned']); // Or partially returned logic
        });

        return redirect()->route('sales.index')->with('success', 'Return processed successfully.');
    }
}
