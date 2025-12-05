<?php

namespace Gogol\Invoices\Listeners;

use Gogol\Invoices\Events\InvoicePaidWrongly;
use Illuminate\Support\Facades\Mail;
use Gogol\Invoices\Mail\SendWrongPaymentEmail;

class InvoicePaidWronglyListener
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(InvoicePaidWrongly $event)
    {
        $invoice = $event->getInvoice();

        // It was already set as paid wrongly, skip email notificaiton.
        if ( $invoice->paid_amount ) {
            return;
        }

        if ( $invoice->subject->email && $invoice->created_at->isAfter(now()->subYear()) ) {
            $invoice->withInvoiceMail(function() use ($invoice, $event) {
                try {
                    Mail::to($invoice->subject->email)->send(
                        new SendWrongPaymentEmail($invoice, $event->expectedAmount, $event->paidAmount)
                    );
                } catch (Exception $e) {
                    report($e);

                    $this->error('Error sending wrong payment email to ' . $invoice->subject->email . ': ' . $e->getMessage());
                }
            });
        }

        $invoice->update([
            'paid_amount' => $event->paidAmount,
        ]);
    }
}
