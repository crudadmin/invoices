<?php

namespace Gogol\Invoices\Admin\Buttons;

use Carbon\Carbon;
use Admin\Helpers\Button;
use Admin\Eloquent\AdminModel;
use Validator;

class CreateInvoiceFromProform extends Button
{
    /*
     * Here is your place for binding button properties for each row
     */
    public function __construct(AdminModel $row)
    {
        //Name of button on hover
        $this->name = $row->proformInvoice ? 'Zobraziť faktúru k proforme' : 'Vygenerovať faktúru k proforme';

        //Button classes
        $this->class = $row->proformInvoice ? 'btn-default' : 'btn-primary';

        //Button Icon
        $this->icon = 'fa-file-text-o';

        $this->active = $row->type == 'proform';
    }

    /*
     * Ask question with form before action
     */
    public function question($row)
    {
        if ( $row->items->count() == 0 )
            return $this->error('Proforma neobsahuje žiadne položky k vygenerovaniu faktúry.');

        //If invoice for this proform exists already
        if ( $row->proformInvoice )
            return $this->title('Faktúra č. '.$row->proformInvoice->number)
                        ->success($this->getDownloadResponse($row->proformInvoice));

        return $this->title('Naozaj si prajete vygenerovať faktúru?')
                    ->component('AskForCreateInvoice')
                    ->type('warning');
    }

    /*
     * Firing callback on press button
     */
    public function fire(AdminModel $row)
    {
        //If we want send email, first check email validation
        if ( $this->canSendEmail() && Validator::make(request()->all(), ['email' => 'email|required'])->fails() )
            return $this->title('Faktúra nebola vygenerovaná!')->error('Nezadali ste platnú emailovú adresu.');

        //Replicate row and reset previous saved relations
        $invoice = $row->createInvoice();

        //Send invoice on email
        if ( $this->canSendEmail() )
            $invoice->sendEmail(request('email'), request('message'));

        return $this->title('Faktúra bola úspešne vygenerovaná'.($this->canSendEmail() ? ' a odoslaná na email' : '').'!')
                    ->message($this->getDownloadResponse($invoice));
    }

    private function getDownloadResponse($invoice)
    {
        return 'Stiahnuť si ju môžete na tomto odkaze:<br><a target="_blank" href="'.$invoice->pdf.'">Faktúra č. '.$invoice->number.'</a>';
    }

    private function canSendEmail()
    {
        return request('send') == 1;
    }
}