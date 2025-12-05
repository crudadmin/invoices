<?php

namespace Gogol\Invoices\Commands;

use Admin;
use Exception;
use Illuminate\Console\Command;

class PastDueInvoicesCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:past-due-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for past due invoices';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Checking for past due invoices started.');

        $this->notifyClientsWithPastDueInvoices();

        $this->line('Checking for past due invoices completed');
    }

    protected function notifyClientsWithPastDueInvoices()
    {
        $daysBefore = config('invoices.mail.past_due_invoice.days_before', 1);

        $invoices = $this->getInvoicesWithPastDueQuery($daysBefore)
            ->whereNotNull('email')
            ->whereNull('notifications_at->past_due')
            ->whereHas('subject', function($query) {
                $query->where('email_past_due_client', true);
            })
            ->get();

        foreach ($invoices as $invoice) {
            try {
                $invoice->sendPastDueEmail($invoice->email);
            } catch (Exception $e) {
                report($e);

                $this->error('Error sending past due email for invoice ' . $invoice->number . ': ' . $e->getMessage());
            }
        }
    }

    protected function getInvoicesWithPastDueQuery($daysBefore)
    {
        return Admin::getModel('Invoice')
                ->whereIn('type', ['invoice', 'proform'])
                ->whereNull('paid_at')
                ->with([ 'subject' ])
                // Check invoices only from last month
                ->where('payment_date', '>=', now()->subMonth())
                // Check invoices only before days before payment date
                ->where('payment_date', '<=', now()->addDays($daysBefore)->startOfDay());
    }
}
