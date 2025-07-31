<?php

namespace Gogol\Invoices\Admin\Rules;

use Admin\Eloquent\AdminModel;
use Admin\Eloquent\AdminRule;

class ProcessInvoiceItemRule extends AdminRule
{
    /*
     * Allow events also in frontend
     */
    public $frontend = true;

    //On all events
    public function fire(AdminModel $row)
    {
        $this->setDefaultVat($row);
        $this->reloadItemPrice($row);
    }

    private function setDefaultVat($row)
    {
        if ( !($row->vat == -1 || !$row->vat) ){
            return;
        }

        $invoice = $row->invoice;
        $subject = $invoice->subject;

        // If country of invoice is different from country of subject and company has VAT, return VAT 0
        if ( ($invoice->country_id != $subject->country_id) && $invoice->company_vat_id ) {
            $row->vat = 0;
        }

        // Default VAT for chosen subject
        else if ( $subject->vat_default_id ) {
            $row->vat = $subject->vat_default->vat;
        }

        // Set zero vat if no setting has been found
        else {
            $row->vat = 0;
        }
    }

    private function reloadItemPrice($row)
    {
        if ( ! $row->price )
            $row->price = calculateWithoutVat($row->price_vat, $row->vat);

        if ( ! $row->price_vat )
            $row->price_vat = calculateWithVat($row->price, $row->vat);
    }
}