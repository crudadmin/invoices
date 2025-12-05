<?php

namespace Gogol\Invoices\Helpers;

use Gogol\Invoices\Model\InvoicesSetting;

class InvoiceOptions
{
    public $subject;

    /**
     * Set invoices subject
     *
     * @param  mixed $subject
     * @return void
     */
    public function setSubject(InvoicesSetting $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Returns subject name
     *
     * @return void
     */
    public static function getSubject()
    {
        return $this->subject;
    }

    /**
     * Returns subject name
     *
     * @return void
     */
    public function getName()
    {
        return $this->subject?->name;
    }

    /**
     * Returns mail greeting
     *
     * @return void
     */
    public function getGreeting()
    {
        if ( $this->subject ) {
            return _('S pozdravom').', <br>'.$this->subject->name;
        }

        return _('S pozdravom');
    }
}