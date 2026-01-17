<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\StockTransferLine;
use App\Models\StockLedgerEntry;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    protected $ledgerService;

    public function __construct(StockLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Mark a transfer as "Sent".
     * Deducts stock from the source warehouse.
     */
    public function send(StockTransfer $transfer)
    {
        return DB::transaction(function () use ($transfer) {
            if ($transfer->status !== 'pending' && $transfer->status !== 'draft') {
                throw new \Exception("Transfer cannot be sent from current status: {$transfer->status}");
            }

            foreach ($transfer->lines as $line) {
                // Deduct from source
                $this->ledgerService->addEntry(
                    $transfer->from_branch_id,
                    $transfer->from_warehouse_id,
                    $line->product_id,
                    $line->variation_id,
                    -$line->quantity,
                    'transfer_out',
                    $transfer,
                    "Stock Transfer #{$transfer->transfer_no} to {$transfer->toBranch->name}"
                );
            }

            $transfer->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return $transfer;
        });
    }

    /**
     * Mark a transfer as "Received".
     * Adds stock to the destination warehouse.
     */
    public function receive(StockTransfer $transfer, array $quantities)
    {
        return DB::transaction(function () use ($transfer, $quantities) {
            if ($transfer->status !== 'sent') {
                throw new \Exception("Transfer cannot be received if it hasn't been sent.");
            }

            foreach ($transfer->lines as $line) {
                $receivedQty = $quantities[$line->id] ?? $line->quantity;
                
                // Add to destination
                $this->ledgerService->addEntry(
                    $transfer->to_branch_id,
                    $transfer->to_warehouse_id,
                    $line->product_id,
                    $line->variation_id,
                    $receivedQty,
                    'transfer_in',
                    $transfer,
                    "Stock Transfer #{$transfer->transfer_no} from {$transfer->fromBranch->name}"
                );

                $line->update(['received_quantity' => $receivedQty]);
            }

            $transfer->update([
                'status' => 'received',
                'received_at' => now(),
            ]);

            return $transfer;
        });
    }
}
