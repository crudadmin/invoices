<?php

namespace Gogol\Invoices\Admin\Buttons;

use Admin\Helpers\Button;
use Admin\Eloquent\AdminModel;

class ExportInvoicesButton extends Button
{
    /*
     * Here is your place for binding button properties for each row
     */
    public function __construct(AdminModel $row)
    {
        //Name of button on hover
        $this->name = 'Stiahnuť';

        //Button classes
        $this->class = 'btn-success';

        //Button Icon
        $this->icon = 'fa-download';
    }

    /*
     * Firing callback on press button
     */
    public function fire(AdminModel $row)
    {
        $url = action('\Gogol\Invoices\Controllers\InvoiceController@downloadExport', $row->getKey());

        return $this->success('XML Export si môžete stiahnuť na tejto adrese:<br><a target="_blank" href="'.$url.'">'.$url.'</a>');
    }
}