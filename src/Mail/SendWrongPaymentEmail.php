<?php

namespace Gogol\Invoices\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWrongPaymentEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $invoice;
    private $expectedAmount;
    private $paidAmount;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($row, $expectedAmount, $paidAmount)
    {
        $this->invoice = $row;
        $this->expectedAmount = $expectedAmount;
        $this->paidAmount = $paidAmount;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this
            ->subject(sprintf(_('Nesprávna úhrada dokladu č. %s'), $this->invoice->number))
            ->markdown('invoices::mail.wrong_payment_email', [
                'invoice' => $this->invoice,
                'expectedAmount' => $this->expectedAmount,
                'paidAmount' => $this->paidAmount,
            ]);

        if ( $invoiceMail = $this->invoice->email ) {
            $mail->replyTo($invoiceMail);
        }

        return $mail;
    }
}
