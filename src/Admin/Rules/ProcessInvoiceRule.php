<?php

namespace Gogol\Invoices\Admin\Rules;

use Admin;
use Ajax;
use Carbon\Carbon;
use Gogol\Admin\Models\AdminRule;
use Gogol\Admin\Models\Model as AdminModel;

class ProcessInvoiceRule extends AdminRule
{
    /*
     * Firing callback on create row
     */
    public function fire(AdminModel $row)
    {
        $row->setPaymentDate();
    }

    public function creating(AdminModel $row)
    {
        $row->setInvoiceNumber();
    }
}