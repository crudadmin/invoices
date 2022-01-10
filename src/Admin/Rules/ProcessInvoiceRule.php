<?php

namespace Gogol\Invoices\Admin\Rules;

use Admin;
use Ajax;
use Carbon\Carbon;
use Admin\Eloquent\AdminRule;
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

        if ( ! $row->vs ) {
            $row->setNewVs();
        }
    }

    public function updating(AdminModel $row)
    {
        $this->checkCreationYearChange($row);
    }

    private function checkCreationYearChange($row)
    {
        $previousDate = new Carbon($row->getRawOriginal('created_at'));

        if ( $row->created_at->format('Y') != $previousDate->format('Y') ){
            Ajax::warning(_('Zmenili ste rok zaučtovania faktúry. Skontrolujte poradové číslo faktúry tak, aby patrilo do správneho účtovneho obdobia.'));
        }
    }
}