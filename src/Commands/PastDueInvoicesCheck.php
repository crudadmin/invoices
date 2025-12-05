<?php

namespace Gogol\Invoices\Commands;

use Admin;
use Exception;
use Illuminate\Console\Command;
use Gogol\Invoices\Mail\SendOwnerPastDueInvoices;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
        $this->notifyOwnersWithPastDueInvoices();

        $this->line('Checking for past due invoices completed');
    }

    protected function log($message)
    {
        $this->info($message);
        Log::channel('invoices')->info($message);
    }

    /**
     * Send emails to invoice owners to notify them about past due invoices
     *
     * @return void
     */
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

        $this->log('Sending past due email to ' . $invoices->count() . ' clients');

        foreach ($invoices as $invoice) {
            try {
                $invoice->sendPastDueEmail($invoice->email);
            } catch (Exception $e) {
                report($e);

                $this->error('Error sending past due email for invoice ' . $invoice->number . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Send emails to invoice owners to notify them about past due invoices
     *
     * @return void
     */
    protected function notifyOwnersWithPastDueInvoices()
    {
        $invoices = $this->getInvoicesWithPastDueQuery(-1)
            ->whereNull('notifications_at->past_due_owner')
            ->whereHas('subject', function($query) {
                $query->where('email_past_due_owner', true)->whereNotNull('email');
            })
            ->get();

        $this->log('Sending ' . $invoices->count() . ' past due invoices to ' . $invoices->pluck('subject_id')->unique()->count() . ' owners');

        foreach ($invoices->groupBy('subject_id') as $subjectId => $invoices) {

            try {
                $invoices[0]->withInvoiceMail(function() use ($invoices) {
                    $subject = $invoices[0]->subject;

                    Mail::to($subject->email)->send(new SendOwnerPastDueInvoices($invoices));

                    // Save that email has been sent
                    foreach ($invoices as $invoice) {
                        $invoice->setNotified('past_due_owner');
                    }
                });

            } catch (Exception $e) {
                report($e);

                $this->error('Error sending past due email to owner ' . $subject->email . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Returns invoices with past due query
     *
     * @param  mixed $daysBefore
     * @return void
     */
    private function getInvoicesWithPastDueQuery($daysBefore)
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
