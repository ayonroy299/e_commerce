<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Create a journal entry with double-entry validation.
     * 
     * $lines = [
     *   ['account_id' => '...', 'debit' => 100, 'credit' => 0],
     *   ['account_id' => '...', 'debit' => 0, 'credit' => 100],
     * ]
     */
    public function postJournal(array $data, array $lines)
    {
        return DB::transaction(function () use ($data, $lines) {
            $totalDebit = collect($lines)->sum('debit');
            $totalCredit = collect($lines)->sum('credit');

            if (abs($totalDebit - $totalCredit) > 0.001) {
                throw new \Exception("Journal entry is not balanced. Total Debit: {$totalDebit}, Total Credit: {$totalCredit}");
            }

            $journal = Journal::create([
                'branch_id' => $data['branch_id'] ?? auth()->user()->branch_id,
                'date' => $data['date'] ?? now()->toDateString(),
                'journal_no' => $data['journal_no'] ?? 'JRN-' . strtoupper(uniqid()),
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($lines as $line) {
                $journal->lines()->create([
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                ]);
            }

            return $journal;
        });
    }

    /**
     * Get or create a system account by code.
     */
    public function getSystemAccount(string $code, string $name, string $type, string $branchId)
    {
        return Account::firstOrCreate(
            ['code' => $code, 'branch_id' => $branchId],
            ['name' => $name, 'type' => $type, 'is_system' => true]
        );
    }

    /**
     * Example: Record a Sale payment
     */
    public function recordSalePayment($sale, $payment)
    {
        $branchId = $sale->branch_id;
        
        $cashAccount = $this->getSystemAccount('1001', 'Cash', 'asset', $branchId);
        $receivableAccount = $this->getSystemAccount('1101', 'Accounts Receivable', 'asset', $branchId);

        $lines = [
            ['account_id' => $cashAccount->id, 'debit' => $payment->amount, 'credit' => 0],
            ['account_id' => $receivableAccount->id, 'debit' => 0, 'credit' => $payment->amount],
        ];

        return $this->postJournal([
            'branch_id' => $branchId,
            'reference_type' => 'Sale',
            'reference_id' => $sale->id,
            'notes' => "Payment received for Sale #{$sale->invoice_no}",
        ], $lines);
    }
}
