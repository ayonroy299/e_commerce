<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grn;
use App\Models\GrnLine;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Services\Inventory\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GrnController extends Controller
{
    protected $ledgerService;

    public function __construct(StockLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function create(PurchaseOrder $order)
    {
        // Only allow receiving for appropriate statuses
        if (!in_array($order->status, ['ordered', 'partially_received'])) {
             return redirect()->route('purchase-orders.show', $order->id)->with('error', 'Cannot receive against this order.');
        }

        $order->load(['lines.product', 'lines.variation']);
        
        return Inertia::render('Admin/Purchasing/GRNs/Create', [
            'order' => $order,
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function store(Request $request, PurchaseOrder $order)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'received_date' => 'required|date',
            'notes' => 'nullable|string',
            'lines' => 'required|array',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity_received' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $order) {
            
            // 1. Create GRN
            $grn = Grn::create([
                'branch_id' => auth()->user()->branch_id,
                'warehouse_id' => $validated['warehouse_id'],
                'purchase_order_id' => $order->id,
                'status' => 'approved', // Auto-approve for simplicity in this sprint, or use draft flow if needed.
                'received_date' => $validated['received_date'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // 2. Create Lines and Update PO Lines
            foreach ($validated['lines'] as $lineData) {
                if ($lineData['quantity_received'] > 0) {
                    GrnLine::create([
                        'grn_id' => $grn->id,
                        'product_id' => $lineData['product_id'],
                        'variation_id' => $lineData['variation_id'] ?? null,
                        'quantity_received' => $lineData['quantity_received'],
                    ]);

                    // Update PO Line received qty
                    // Finding the correct PO line can be tricky if products duplicate, assuming unique product/variant pair per PO line
                    $poLine = $order->lines()
                        ->where('product_id', $lineData['product_id'])
                        ->where('variation_id', $lineData['variation_id'] ?? null)
                        ->first();
                        
                    if ($poLine) {
                        $poLine->increment('quantity_received', $lineData['quantity_received']);
                    }

                    // 3. Update Stock Ledger
                    $this->ledgerService->addEntry(
                        $grn->branch_id,
                        $grn->warehouse_id,
                        $lineData['product_id'],
                        $lineData['variation_id'] ?? null,
                        $lineData['quantity_received'],
                        'purchase',
                        $grn, // Reference GRN not PO for stock movement
                        "Received via GRN #{$grn->id} (PO #{$order->id})"
                    );
                }
            }
            
            // 4. Update PO Status
            $order->refresh();
            $allReceived = $order->lines->every(function ($line) {
                 return $line->quantity_received >= $line->quantity_ordered;
            });
            
            $anyReceived = $order->lines->some(function ($line) {
                 return $line->quantity_received > 0;
            });
            
            if ($allReceived) {
                $order->update(['status' => 'received']);
            } elseif ($anyReceived) {
                 $order->update(['status' => 'partially_received']);
            }
        });

        return redirect()->route('purchase-orders.show', $order->id)->with('success', 'Goods Received successfully.');
    }
}
