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
        if ( $name = $this->getName() ) {
            return _('S pozdravom').', <br>'.$name;
        }

        return _('S pozdravom');
    }
}