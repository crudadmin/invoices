<?php

namespace Gogol\Invoices\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $invoice;
    private $message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($row, $message = null)
    {
        $this->invoice = $row;
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('invoices::mail.invoice_email', [
                        'invoice' => $this->invoice,
                        'message' => $this->message,
                    ])
                    ->subject($this->invoice->typeName . ' ' . $this->invoice->number)
                    ->attach($this->invoice->getPdf()->path, [
                        'as' => $this->invoice->getPdf()->filename
                    ]);
    }
}
