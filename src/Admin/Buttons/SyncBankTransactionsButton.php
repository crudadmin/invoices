<?php

namespace Gogol\Invoices\Admin\Buttons;

use Admin\Helpers\Button;
use Admin\Eloquent\AdminModel;

class SyncBankTransactionsButton extends Button
{
    /*
     * Here is your place for binding button properties for each row
     */
    public function __construct(AdminModel $row)
    {
        //Name of button on hover
        $this->name = _('Synchronizovať platby');

        //Button classes
        $this->class = 'btn-primary';

        //Button Icon
        $this->icon = 'fa-sync';

        $this->active = $row->token ? true : false;
    }

    /*
     * Ask question with form before action
     */
    public function question($row)
    {
        return $this->title(_('Naozaj si prajete synchronizovať platby?'))
                    ->component('AskForSyncBank');
    }

    /*
     * Firing callback on press button
     */
    public function fire(AdminModel $row)
    {
        $canSyncAll = request('sync_all') == 1 ? true : false;

        $importer = $row->syncAccount(null, $canSyncAll);

        if ( count($importer->errors) > 0 ) {
            return $this->title(_('Pri synchronizácii došlo k chybe'))->error(__('Skúste neskôr prosím. Alebo preverte nastavenia banky.') . '<br><br>' . implode('<br>', $importer->errors));
        }

        return $this->message(_('Platby boli úspešne synchronizované.'));
    }
}