<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentLine;
use App\Models\Warehouse;
use App\Services\Inventory\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockAdjustmentController extends Controller
{
    protected $ledgerService;

    public function __construct(StockLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function index()
    {
        $adjustments = StockAdjustment::with(['branch', 'lines'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Stock/Adjustments/Index', [
            'adjustments' => $adjustments,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Stock/Adjustments/Create', [
            'warehouses' => Warehouse::all(),
            'reasons' => ['damage', 'loss', 'audit', 'opening_balance'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason' => 'required|string',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.variation_id' => 'nullable|exists:product_variations,id',
            'lines.*.quantity_adjusted' => 'required|numeric|not_in:0',
        ]);

        DB::transaction(function () use ($validated) {
            $adjustment = StockAdjustment::create([
                'branch_id' => auth()->user()->branch_id, // Assuming user has branch_id
                'warehouse_id' => $validated['warehouse_id'],
                'reason' => $validated['reason'],
                'date' => $validated['date'],
                'notes' => $validated['notes'],
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['lines'] as $line) {
                StockAdjustmentLine::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $line['product_id'],
                    'variation_id' => $line['variation_id'] ?? null,
                    'quantity_adjusted' => $line['quantity_adjusted'],
                ]);
            }
        });

        return redirect()->route('adjustments.index')->with('success', 'Adjustment draft created.');
    }

    public function show(StockAdjustment $adjustment)
    {
        $adjustment->load(['lines.product', 'lines.variation', 'warehouse']);
        return Inertia::render('Admin/Stock/Adjustments/Show', [
            'adjustment' => $adjustment,
        ]);
    }

    public function approve(StockAdjustment $adjustment)
    {
        if ($adjustment->status === 'approved') {
            return back()->with('error', 'Already approved.');
        }

        DB::transaction(function () use ($adjustment) {
            $adjustment->load('lines');

            foreach ($adjustment->lines as $line) {
                $this->ledgerService->addEntry(
                    $adjustment->branch_id,
                    $adjustment->warehouse_id,
                    $line->product_id,
                    $line->variation_id,
                    $line->quantity_adjusted,
                    'adjustment',
                    $adjustment,
                    $adjustment->reason
                );
            }

            $adjustment->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return back()->with('success', 'Adjustment approved and stock updated.');
    }
}
