<?php

namespace Gogol\Invoices\Traits;

use Gogol\Invoices\Mail\SendInvoiceEmail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

trait HasInvoiceMail
{
    /**
     * Send email with invoice on given/saved email adress
     *
     * @param  mixed $email
     * @param  mixed $message
     * @return void
     */
    public function sendEmail($email = null, $message = null)
    {
        Mail::to($email ?: $this->email)->send(new SendInvoiceEmail($this, $message));

        //Save that email has been sent
        $this->setNotified();
    }

    /*
     * Check given email as sent
     */
    public function setNotified()
    {
        $this->update([
            'notified_at' => Carbon::now(),
        ]);
    }

    /*
     * Return typename with number
     */
    public function getEmailSubjectAttribute()
    {
        return sprintf(_('%s č. %s'), config('invoices.invoice_types.'.$this->type.'.name', ''), $this->number);
    }

    public function getPaidMessageAttribute()
    {
        return sprintf(_('Prijali sme platbu vo výške %s k dokladu č. %s, v prílohe zasielame ostrú faktúru.'), priceFormat($this->paid_amount).' €', $this->number);
    }
}