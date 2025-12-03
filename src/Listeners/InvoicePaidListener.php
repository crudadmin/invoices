<?php

namespace Gogol\Invoices\Listeners;

use Gogol\Invoices\Events\InvoicePaid;

class InvoicePaidListener
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(InvoicePaid $event)
    {
        $invoice = $event->getInvoice();
        $transactions = $event->getTransactions();

        $invoice->update([
            'paid_at' => $transactions->last()['date'],
            'paid_amount' => $transactions->sum('amount'),
        ]);

        if ( $invoice->isProform ) {
            $proform = $invoice->fresh();

            // Invoice generated already
            if ( $proform->proformInvoice()->count() > 0 ) {
                return;
            }

            // Generate final invoice
            $finalInvoice = $proform->createInvoice();

            // Send email to client
            if ( config('invoices.mail.auto_mail_after_payment', false) && $finalInvoice->email ) {
                $finalInvoice->sendEmail($finalInvoice->email, $finalInvoice->paidMessage);
            }
        }
    }
}
