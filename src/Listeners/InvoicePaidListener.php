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
    }
}
