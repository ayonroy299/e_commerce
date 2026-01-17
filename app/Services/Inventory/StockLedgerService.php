<?php

namespace App\Services\Inventory;

use App\Models\ProductStock;
use App\Models\StockLedgerEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockLedgerService
{
    /**
     * Add a stock ledger entry and update product_stocks.
     *
     * @param string $branchId
     * @param string $warehouseId
     * @param string $productId
     * @param string|null $variationId
     * @param float $qtyChange Positive for increase, negative for decrease
     * @param string $type sale, purchase, adjustment, transfer, return
     * @param Model|null $reference Polymorphic reference (e.g. Sale, PO)
     * @param string|null $remarks
     * @return StockLedgerEntry
     */
    public function addEntry(
        string $branchId,
        string $warehouseId,
        string $productId,
        ?string $variationId,
        float $qtyChange,
        string $type,
        ?Model $reference = null,
        ?string $remarks = null
    ): StockLedgerEntry {
        return DB::transaction(function () use (
            $branchId,
            $warehouseId,
            $productId,
            $variationId,
            $qtyChange,
            $type,
            $reference,
            $remarks
        ) {
            // 1. Get current stock or create if not exists
            $stock = ProductStock::firstOrCreate(
                [
                    'branch_id' => $branchId,
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'variation_id' => $variationId,
                ],
                [
                    'quantity' => 0,
                    'alert_quantity' => null // Optional, could fetch from default settings
                ]
            );

            // 2. Lock for update to prevent race conditions
            $stock = ProductStock::lockForUpdate()->find($stock->id);

            // 3. Calculate new quantity
            $oldQty = $stock->quantity;
            $newQty = $oldQty + $qtyChange;

            // 4. Update product_stock
            $stock->update(['quantity' => $newQty]);

            // 5. Create Ledger Entry
            return StockLedgerEntry::create([
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'variation_id' => $variationId,
                'qty_change' => $qtyChange,
                'new_qty' => $newQty,
                'type' => $type,
                'reference_type' => $reference ? $reference->getMorphClass() : null,
                'reference_id' => $reference ? $reference->getKey() : null,
                'remarks' => $remarks,
            ]);
        });
    }
}
