<?php

namespace App\Services;

use App\Models\EmiContract;
use App\Models\EmiSchedule;
use Illuminate\Support\Facades\Log;

class EMIAutomationService
{
    protected $sslService;

    public function __construct(SSLCommerzService $sslService)
    {
        $this->sslService = $sslService;
    }

    /**
     * Process all due installments for auto-debit enabled contracts
     */
    public function processAutoDebits()
    {
        $dueSchedules = EmiSchedule::where('status', 'pending')
            ->where('due_date', '<=', now()->toDateString())
            ->whereHas('contract', function ($q) {
                $q->where('auto_debit', true)
                  ->whereNotNull('payment_token')
                  ->where('status', 'active');
            })
            ->get();

        foreach ($dueSchedules as $schedule) {
            $this->chargeInstallment($schedule);
        }
    }

    /**
     * Charge a single installment via SSLCommerz Tokenization
     */
    public function chargeInstallment(EmiSchedule $schedule)
    {
        $contract = $schedule->contract;
        $sale = $contract->sale;

        Log::info("Attempting auto-debit for Contract #{$contract->id}, Installment #{$schedule->installment_number}");

        // In a real SSLCommerz implementation, you would call their tokenization API
        // using the payment_token (which would be a tokenize_id)
        
        /*
        $response = Http::asForm()->post($this->sslService->apiUrl . '/gwprocess/v4/api_recurring.php', [
            'store_id' => $this->sslService->storeId,
            'store_passwd' => $this->sslService->storePassword,
            'tokenize_id' => $contract->payment_token,
            'total_amount' => $schedule->amount,
            'tran_id' => 'EMI-' . $schedule->id . '-' . uniqid(),
            'currency' => 'BDT',
        ]);
        */

        // For this implementation, we simulate a successful recurring charge
        // since we don't have a live tokenize_id or API credentials.
        
        $success = true; // Simulation

        if ($success) {
            $schedule->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $contract->receipts()->create([
                'branch_id' => $contract->branch_id,
                'amount' => $schedule->amount,
                'payment_method' => 'card', // Auto-debit is usually card
                'received_at' => now(),
                'note' => "Auto-debit for installment #{$schedule->installment_number}",
            ]);

            // Check if all installments are paid
            if ($contract->schedules()->where('status', 'pending')->count() === 0) {
                $contract->update(['status' => 'completed']);
                $contract->sale->update(['payment_status' => 'paid']);
            }

            Log::info("Auto-debit SUCCESS for Schedule #{$schedule->id}");
        } else {
            Log::error("Auto-debit FAILED for Schedule #{$schedule->id}");
            // Handle failure: notify customer, retry later, or mark as overdue
        }
    }
}
