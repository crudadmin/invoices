<?php

namespace Gogol\Invoices\Admin\Rules;

use Gogol\Admin\Models\Model as AdminModel;
use Gogol\Admin\Models\AdminRule;
use Admin;
use Ajax;

class ProcessInvoiceItemRule extends AdminRule
{
    /*
     * Allow events also in frontend
     */
    public $frontend = true;

    //On all events
    public function fire(AdminModel $row)
    {
        $this->reloadItemPrice($row);
    }

    //When item will be successfully saved in db, then refresh invoice price
    public function fired(AdminModel $row)
    {
        $invoice = $row->invoice;

        $invoice->reloadInvoicePrice();
        $invoice->save();
    }

    private function reloadItemPrice($row)
    {
        if ( ! $row->price )
            $row->price = $row->price_vat / (1 + ($row->vat / 100));

        $row->price_vat = $row->price * (1 + ($row->vat / 100));

    }
}