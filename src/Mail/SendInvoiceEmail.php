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

        //Set default subject
        $this->subject = $this->invoice->emailSubject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this
            ->markdown('invoices::mail.invoice_email', [
                'invoice' => $this->invoice,
                'message' => $this->message,
                'settings' => $this->invoice->subject,
            ]);

        if ( $name = $this->invoice->subject?->name ) {
            $mail->from(config('mail.from.address'), $name);
        }

        if ( $replyTo = $this->invoice->subject->email ) {
            $mail->replyTo($replyTo);
        }

        if ( $pdf = $this->invoice->getPdf() ) {
            $mail->attachData($pdf->get(), $pdf->filename);
        }

        return $mail;
    }
}
