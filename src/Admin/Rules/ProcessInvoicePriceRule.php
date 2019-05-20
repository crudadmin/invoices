<?php

namespace Gogol\Invoices\Admin\Rules;

use Gogol\Admin\Models\Model as AdminModel;
use Gogol\Admin\Models\AdminRule;
use Admin;
use Ajax;

class ProcessInvoicePriceRule extends AdminRule
{
    //When item will be successfully saved in db, then refresh invoice price
    public function fired(AdminModel $row)
    {
        $invoice = $row->invoice;

        $invoice->reloadInvoicePrice();
        $invoice->save();
    }
}