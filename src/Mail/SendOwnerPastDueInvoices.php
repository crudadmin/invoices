<?php

namespace Gogol\Invoices\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOwnerPastDueInvoices extends Mailable
{
    use Queueable, SerializesModels;

    private $invoices;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoices)
    {
        $this->invoices = $invoices;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this
            ->subject(_('Faktúry po splatnosti'))
            ->markdown('invoices::mail.past_due_invoices_owner', [
                'invoices' => $this->invoices,
                'settings' => $this->invoices[0]->subject,
            ]);

        return $mail;
    }
}
