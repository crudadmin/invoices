<?php

namespace Gogol\Invoices\Admin\Buttons;

use Admin\Helpers\Button;
use Admin\Eloquent\AdminModel;

class SetInvoicePaidButton extends Button
{
    /*
     * Here is your place for binding button properties for each row
     */
    public function __construct($row = null)
    {
        //Name of button on hover
        $this->name = _('Označiť ako zaplatené');

        //Button classes
        $this->class = 'btn-default';

        //Button Icon
        $this->icon = 'fa-check';

        //Button type
        $this->type = 'action';

        $this->active = $row && $row->paid_at ? false : true;

        $this->reloadAll = true;
    }

    /*
     * Ask question with form before action
     */
    public function question($row)
    {
        return $this->message(_('Naozaj si prajete označiť doklad ako zaplatený?'));
    }

    /*
     * Firing callback on press button
     */
    public function fire(AdminModel $row)
    {
        //TODO: test single
        return $this->fireMultiple(collect([$row]));
    }

    public function fireMultiple($rows)
    {
        $rows->each(function($row) {
            if ( $row->paid_at ) {
                return;
            }

            $row->update([ 'paid_at' => now() ]);
        });

        return $this->message(_('Doklady boli úspešne označené ako zaplatené.'));
    }
}