<?php

namespace Gogol\Invoices\Admin\Buttons;

use Admin\Helpers\Button;
use Admin\Eloquent\AdminModel;

class DownloadInvoiceButton extends Button
{
    /*
     * Button type
     * button|action|multiple
     */
    public $type = 'action';

    /*
     * Here is your place for binding button properties for each row
     */
    public function __construct()
    {
        //Name of button on hover
        $this->name = 'Stiahnuť doklad';

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
        $url = action('\Gogol\Invoices\Controllers\InvoiceController@generateInvoicePdf', $row->getKey());

        return $this->open($url);
    }
}