<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessEmiAutoDebits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emi:process-auto-debits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all due EMI installments for auto-debit contracts';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\EMIAutomationService $automationService)
    {
        $this->info('Starting EMI Auto-Debit process...');
        $automationService->processAutoDebits();
        $this->info('EMI Auto-Debit process completed.');
    }
}
