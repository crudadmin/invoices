<?php

namespace Gogol\Invoices\Admin\Layouts;

use \Admin\Helpers\Layout;

class InvoiceComponent extends Layout
{
    /*
     * Layout position
     * top/bottom/form-top/form-bottom/form-header/form-footer/table-header/table-footer
     */
    public $position = 'top';

    /*
     * On build blade layour
     */
    public function build()
    {
        return $this->renderVueJs('invoiceComponent.vue');
    }
}