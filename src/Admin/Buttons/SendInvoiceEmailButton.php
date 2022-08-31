<?php

namespace Gogol\Invoices\Admin\Buttons;

use Admin\Helpers\Button;
use Admin\Eloquent\AdminModel;
use Validator;

class SendInvoiceEmailButton extends Button
{
    /*
     * Here is your place for binding button properties for each row
     */
    public function __construct(AdminModel $row)
    {
        //Name of button on hover
        $this->name = _('Odoslať doklad na email');

        //Button classes
        $this->class = 'btn-default';

        //Button Icon
        $this->icon = 'fa-envelope-o';
    }

    /*
     * Ask question with form before action
     */
    public function question($row)
    {
        if ( $row->items->count() == 0 )
            return $this->error(_('Doklad neobsahuje žiadné položky k vygenerovaniu PDF.'));

        return $this->title(_('Naozaj si prajete odoslať doklad na email?'))
                    ->component('AskForSendInvoice', [
                        'email' => $row->email,
                    ]);
    }

    /*
     * Firing callback on press button
     */
    public function fire(AdminModel $row)
    {
        if ( Validator::make(request()->all(), ['email' => 'email|required'])->fails() )
            return $this->error(_('Nezadali ste platnú emailovú adresu.'));

        $row->sendEmail(request('email'), request('message'));

        return $this->message(_('Email bol úspešne odoslaný.'));
    }
}