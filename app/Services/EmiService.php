<?php

namespace App\Services;

use App\Models\EmiContract;
use App\Models\EmiSchedule;
use App\Models\EmiReceipt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmiService
{
    /**
     * Generate amortization schedule for an EMI contract.
     */
    public function generateSchedule(EmiContract $contract): void
    {
        $plan = $contract->plan;
        $tenor = $plan->tenor_months;
        $financedAmount = $contract->financed_amount;
        $interestRate = $plan->interest_rate; // Annual rate (%)
        $interestType = $plan->interest_type;
        $startDate = Carbon::parse($contract->start_date);

        if ($interestType === 'flat') {
            $this->generateFlatSchedule($contract, $tenor, $financedAmount, $interestRate, $startDate);
        } else {
            $this->generateDecliningSchedule($contract, $tenor, $financedAmount, $interestRate, $startDate);
        }
    }

    private function generateFlatSchedule($contract, $tenor, $financedAmount, $interestRate, $startDate)
    {
        $totalInterest = ($financedAmount * ($interestRate / 100) * ($tenor / 12));
        $totalAmount = $financedAmount + $totalInterest;
        $monthlyPayment = $totalAmount / $tenor;
        $monthlyPrincipal = $financedAmount / $tenor;
        $monthlyInterest = $totalInterest / $tenor;

        DB::transaction(function () use ($contract, $tenor, $monthlyPrincipal, $monthlyInterest, $monthlyPayment, $startDate, $totalInterest, $totalAmount) {
            for ($i = 1; $i <= $tenor; $i++) {
                EmiSchedule::create([
                    'emi_contract_id' => $contract->id,
                    'installment_no' => $i,
                    'due_date' => $startDate->copy()->addMonths($i),
                    'principal_due' => $monthlyPrincipal,
                    'interest_due' => $monthlyInterest,
                    'total_due' => $monthlyPayment,
                    'status' => 'pending',
                ]);
            }
            
            $contract->update([
                'interest_amount' => $totalInterest,
                'total_amount' => $totalAmount,
            ]);
        });
    }

    private function generateDecliningSchedule($contract, $tenor, $financedAmount, $interestRate, $startDate)
    {
        if ($interestRate <= 0) {
            $this->generateFlatSchedule($contract, $tenor, $financedAmount, 0, $startDate);
            return;
        }

        $monthlyRate = ($interestRate / 100) / 12;
        // EMI = [P x R x (1+R)^N]/[(1+R)^N-1]
        $pow = pow(1 + $monthlyRate, $tenor);
        $emi = $financedAmount * $monthlyRate * $pow / ($pow - 1);

        $remainingPrincipal = $financedAmount;
        $totalInterestCalculated = 0;

        DB::transaction(function () use ($contract, $tenor, $emi, $monthlyRate, $startDate, &$remainingPrincipal, &$totalInterestCalculated) {
            for ($i = 1; $i <= $tenor; $i++) {
                $interestDue = $remainingPrincipal * $monthlyRate;
                $principalDue = $emi - $interestDue;
                
                // Adjustment for last month
                if ($i === $tenor) {
                    $principalDue = $remainingPrincipal;
                    $emi = $principalDue + $interestDue;
                }

                EmiSchedule::create([
                    'emi_contract_id' => $contract->id,
                    'installment_no' => $i,
                    'due_date' => $startDate->copy()->addMonths($i),
                    'principal_due' => $principalDue,
                    'interest_due' => $interestDue,
                    'total_due' => $emi,
                    'status' => 'pending',
                ]);

                $remainingPrincipal -= $principalDue;
                $totalInterestCalculated += $interestDue;
            }

            $contract->update([
                'interest_amount' => $totalInterestCalculated,
                'total_amount' => $contract->financed_amount + $totalInterestCalculated,
            ]);
        });
    }

    /**
     * Process an EMI payment receipt.
     */
    public function processPayment(EmiContract $contract, float $amount, $paymentMethodId, $referenceNo = null, $note = null): void
    {
        DB::transaction(function () use ($contract, $amount, $paymentMethodId, $referenceNo, $note) {
            // Create receipt
            EmiReceipt::create([
                'branch_id' => $contract->branch_id,
                'emi_contract_id' => $contract->id,
                'payment_method_id' => $paymentMethodId,
                'amount' => $amount,
                'payment_date' => now(),
                'reference_no' => $referenceNo,
                'note' => $note,
                'created_by' => auth()->id() ?? User::where('is_super_admin', true)->first()->id, // Fallback if no auth (e.g. CLI)
            ]);

            // Allocate to schedules
            $remaining = $amount;
            $schedules = $contract->schedules()
                ->whereIn('status', ['pending', 'partially_paid', 'overdue'])
                ->orderBy('due_date')
                ->get();

            foreach ($schedules as $schedule) {
                if ($remaining <= 0) break;

                $totalDue = $schedule->total_due + $schedule->penalty_amount - $schedule->paid_amount;
                $payment = min($remaining, $totalDue);

                $schedule->paid_amount += $payment;
                if (round($schedule->paid_amount, 2) >= round($schedule->total_due + $schedule->penalty_amount, 2)) {
                    $schedule->status = 'paid';
                    $schedule->paid_at = now();
                } else {
                    $schedule->status = 'partially_paid';
                }
                $schedule->save();

                $remaining -= $payment;
            }

            // Check if contract is completed
            if ($contract->schedules()->where('status', '!=', 'paid')->count() === 0) {
                $contract->status = 'completed';
                $contract->save();
            }
        });
    }
}
