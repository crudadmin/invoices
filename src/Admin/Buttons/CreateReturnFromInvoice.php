<?php

namespace Gogol\Invoices\Admin\Buttons;

use Admin\Helpers\Button;
use Admin\Eloquent\AdminModel;

class CreateReturnFromInvoice extends Button
{
    /*
     * Here is your place for binding button properties for each row
     */
    public function __construct(AdminModel $row)
    {
        //Name of button on hover
        $this->name = _('Vystaviť dobropis');

        //Button classes
        $this->class = 'btn-default';

        //Button Icon
        $this->icon = 'fa-undo';

        $this->active = $row->type == 'invoice';
    }

    /*
     * Ask question with form before action
     */
    public function question($row)
    {
        if ( $row->items->count() == 0 )
            return $this->error(_('Faktúra neobsahuje žiadne položky k vygenerovaniu dobropisu.'));

        //If invoice for this proform exists already
        if ( $row->returnInvoice )
            return $this->title(sprintf(_('Dobropis č. %s už existuje!'), $row->returnInvoice->number))
                        ->error($this->getDownloadResponse($row->returnInvoice));

        return $this->title(_('Upozornenie'))->warning(_('Naozaj si prajete vygenerovať dobropis?'));
    }

    /*
     * Firing callback on press button
     */
    public function fire(AdminModel $row)
    {
        $invoice = $row->createReturn();

        return $this->title(_('Dobropis bol úspešne vygenerovaný!'))
                    ->message($this->getDownloadResponse($invoice));
    }

    private function getDownloadResponse($invoice)
    {
        return _('Stiahnuť si ho môžete na tomto odkaze:').'<br><a target="_blank" href="'.$invoice->getPdf().'">Faktúra č. '.$invoice->number.'</a>';
    }
}