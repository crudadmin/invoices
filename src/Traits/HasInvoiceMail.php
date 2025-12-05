<?php

namespace Gogol\Invoices\Traits;

use Gogol\Invoices\Mail\SendInvoiceEmail;
use Illuminate\Support\Facades\Mail;

trait HasInvoiceMail
{
    /**
     * Callback before every invoice email is being sent
     *
     * @return void
     */
    public function withInvoiceMail($callback)
    {
        $this->withLocale(function() use ($callback) {
            if ( is_callable($callback) ) {
                $callback($this);
            }
        });

        return $this;
    }

    /**
     * Send email with invoice on given/saved email adress
     *
     * @param  mixed $email
     * @param  mixed $message
     * @return void
     */
    public function sendEmail($email = null, $message = null)
    {
        $this->withInvoiceMail(function() use ($email, $message) {
            $mail = new SendInvoiceEmail($this, $message);

            Mail::to($email ?: $this->email)->send($mail);

            //Save that email has been sent
            $this->setNotified();
        });
    }

    public function sendPastDueEmail($email = null, $message = null)
    {
        $this->withInvoiceMail(function() use ($email, $message) {
            $message = $message ?: $this->pastDueMessage;

            $mail = new SendInvoiceEmail($this, $message);
            $mail = $mail->subject($this->pastDueEmailSubject);

            Mail::to($email ?: $this->email)->send($mail);

            //Save that email has been sent
            $this->setNotified('past_due');
        });
    }

    /*
     * Check given email as sent
     */
    public function setNotified($key = 'notification')
    {
        if ( !$this->notified_at ) {
            $this->notified_at = now();
        }

        $notificationsAt = $this->notifications_at ?? [];

        // Mark notification type
        if ( !isset($notificationsAt[$key]) ) {
            $notificationsAt[$key] = now();
        }

        $this->notifications_at = $notificationsAt;

        $this->save();
    }

    /**
     * Email subject
     *
     * @return void
     */
    public function getEmailSubjectAttribute()
    {
        return sprintf(_('%s č. %s'), $this->typeName, $this->number);
    }

    /**
     * Message when payment is received
     *
     * @return void
     */
    public function getPaidMessageAttribute()
    {
        return sprintf(_('Ďakujeme, práve sme prijali platbu vo výške %s. V prílohe Vám zasielame ostrú faktúru.'), priceFormat($this->paid_amount ?: $this->price_vat).' €', $this->number);
    }

    /**
     * Subject when invoice is past due
     *
     * @return void
     */
    public function getPastDueEmailSubjectAttribute()
    {
        return sprintf(_('Pripomienka k úhrade faktúry č. %s'), $this->number);
    }

    /**
     * Message when invoice is past due
     *
     * @return void
     */
    public function getPastDueMessageAttribute()
    {
        return sprintf(_('Radi by sme Vám pripomenuli splatnosť faktúry č. %s, ktorá je %s.'), $this->number, '<strong>'.$this->pastDueDaysMessage.'</strong>');
    }

    /**
     * How many days is past due
     * >= 1: X days to pay
     * < 0: is past due -X days
     *
     * @return void
     */
    protected function getPastDueDaysAttribute()
    {
        return now()->startOfDay()->diffInDays($this->payment_date->startOfDay());
    }

    /**
     * Message when invoice is past due
     *
     * @return void
     */
    public function getPastDueDaysMessageAttribute()
    {
        $pastDueDays = $this->past_due_days;

        if ( $pastDueDays == 0 ) {
            return _('splatná do dnes');
        } elseif ( $pastDueDays == 1 ) {
            return _('splatná do zajtra');
        } else if ( $pastDueDays > 1 ) {
            return sprintf(_('splatná o %s dni'), $pastDueDays);
        } else if ( $pastDueDays < 0 ) {
            return sprintf(ngettext('po splatnosti %s deň', 'po splatnosti %s dni', abs($pastDueDays)), abs($pastDueDays));
        }

        return _('na úhradu');
    }
}