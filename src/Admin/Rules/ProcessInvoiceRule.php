<?php

namespace Gogol\Invoices\Admin\Rules;

use Admin;
use Ajax;
use Carbon\Carbon;
use Admin\Models\AdminRule;
use Admin\Eloquent\AdminModel;

class ProcessInvoiceRule extends AdminRule
{
    /*
     * Allow events also in frontend
     */
    public $frontend = true;

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

        if ( ! $row->vs )
            $row->setNewVs();
    }
}