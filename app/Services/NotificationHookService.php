<?php

namespace App\Services;

use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\EmiSchedule;
use App\Models\User;

class NotificationHookService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Trigger low stock alert.
     */
    public function triggerLowStockAlert(ProductStock $stock)
    {
        $data = [
            'product_name' => $stock->product->name,
            'warehouse_name' => $stock->warehouse->name,
            'current_stock' => $stock->quantity,
            'threshold' => $stock->alert_quantity,
        ];

        // Notify branch managers or inventory admins
        $recipients = User::where('branch_id', $stock->branch_id)
            ->role(['Branch Manager', 'Inventory Admin'])
            ->get();

        foreach ($recipients as $recipient) {
            $this->notificationService->send('LOW_STOCK_ALERT', $recipient, $data, $stock->branch_id);
        }
    }

    /**
     * Trigger sale due reminder.
     */
    public function triggerSaleDueReminder(Sale $sale)
    {
        if (!$sale->customer) return;

        $data = [
            'customer_name' => $sale->customer->name,
            'invoice_number' => $sale->invoice_number,
            'due_amount' => $sale->total_amount - $sale->paid_amount,
            'due_date' => $sale->sold_at->addDays(30)->toDateString(), // Example logic
        ];

        $this->notificationService->send('SALE_DUE_REMINDER', $sale->customer, $data, $sale->branch_id);
    }
}
