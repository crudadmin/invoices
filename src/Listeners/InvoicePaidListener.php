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
            if ( $finalInvoice->email ) {
                $message = sprintf(_('Prijali sme platbu vo výške %s k proforme %s, v prílohe zasielame ostrú faktúru.'), priceFormat($invoice->paid_amount).' €', $invoice->number);

                $finalInvoice->sendEmail($finalInvoice->email, $message);
            }
        }
    }
}
