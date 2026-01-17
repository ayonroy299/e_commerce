<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\Supplier;
use App\Services\Inventory\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with(['supplier'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Purchasing/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Purchasing/Orders/Create', [
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.variation_id' => 'nullable|exists:product_variations,id',
            'lines.*.quantity_ordered' => 'required|numeric|min:0.01',
            'lines.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            foreach ($validated['lines'] as $line) {
                $totalAmount += $line['quantity_ordered'] * $line['unit_cost'];
            }

            $po = PurchaseOrder::create([
                'branch_id' => auth()->user()->branch_id,
                'supplier_id' => $validated['supplier_id'],
                'date' => $validated['date'],
                'expected_date' => $validated['expected_date'],
                'notes' => $validated['notes'],
                'status' => 'draft',
                'created_by' => auth()->id(),
                'total_amount' => $totalAmount
            ]);

            foreach ($validated['lines'] as $line) {
                PurchaseOrderLine::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $line['product_id'],
                    'variation_id' => $line['variation_id'] ?? null,
                    'quantity_ordered' => $line['quantity_ordered'],
                    'unit_cost' => $line['unit_cost'],
                    'subtotal' => $line['quantity_ordered'] * $line['unit_cost'],
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order created.');
    }

    public function show(PurchaseOrder $order)
    {
        $order->load(['supplier', 'lines.product', 'lines.variation', 'grns']);
        return Inertia::render('Admin/Purchasing/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function approve(PurchaseOrder $order)
    {
        if ($order->status !== 'draft') {
            return back()->with('error', 'Only draft orders can be approved.');
        }

        $order->update([
            'status' => 'ordered',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Purchase Order approved.');
    }
}
