<?php

namespace Gogol\Invoices\Admin\Buttons;

use Gogol\Admin\Helpers\Button;
use Gogol\Admin\Models\Model as AdminModel;
use Validator;

class SendInvoiceEmailButton extends Button
{
    /*
     * Here is your place for binding button properties for each row
     */
    public function __construct(AdminModel $row)
    {
        //Name of button on hover
        $this->name = 'Odoslať doklad na email';

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
            return $this->error('Doklad neobsahuje žiadné položky k vygenerovaniu PDF.');

        return $this->title('Naozaj si prajete odoslať doklad na email?')
                    ->component('AskForSendInvoice');
    }

    /*
     * Firing callback on press button
     */
    public function fire(AdminModel $row)
    {
        if ( Validator::make(request()->all(), ['email' => 'email|required'])->fails() )
            return $this->error('Nezadali ste platnú emailovú adresu.');

        $row->sendEmail(request('email'), request('message'));

        return $this->message('Email bol úspešne odoslaný.');
    }
}