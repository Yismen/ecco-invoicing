<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateInvoicesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-invoices-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \App\Models\Invoice::query()
            ->where('status', '!=', \App\Enums\InvoiceStatuses::Paid)
            ->with(['cancellation'])
            ->get()
            ->each
            ->touch();

        $this->info('Invoices status updated successfully.');
    }
}
