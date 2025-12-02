<?php

namespace Gogol\Invoices\Model;

use Admin\Fields\Group;
use Admin\Eloquent\AdminModel;
use Gogol\Invoices\Model\InvoicesSetting;

class InvoicesAccount extends AdminModel
{
    /*
     * Model created date, for ordering tables in database and in user interface
     */
    protected $migration_date = '2025-10-26 20:01:47';

    /*
     * Template name
     */
    protected $name = 'Bankové účty';

    /*
     * Template title
     * Default ''
     */
    protected $title = '';

    protected $group = 'invoices';

    protected $icon = 'fa-bank';

    protected $reversed = true;

    /*
     * Automatic form and database generation
     * @name - field name
     * @placeholder - field placeholder
     * @type - field type | string/text/editor/select/integer/decimal/file/password/date/datetime/time/checkbox/radio
     * ... other validation methods from laravel
     */
    public function fields()
    {
        return [
            'Bankove údaje' => Group::fields([
                'name' => 'name:Názov účtu|placeholder:Zadajte názov účtu|max:90',
                'number' => 'name:Č. účtu|placeholder:0123456789/0000|max:90|required',
                'iban' => 'name:IBAN|max:90|required',
                'swift' => 'name:Swift|max:90|required',
            ]),
        ];
    }

    public function onTableCreate($a, $b, $c)
    {
        $c->registerAfterAllMigrations($this, function() {
            InvoicesSetting::get()->each(function($setting) {
                $account = $this->create([
                    'name' => $setting->name,
                    'number' => $setting->account,
                    'iban' => $setting->iban,
                    'swift' => $setting->swift,
                ]);

                $setting->accounts()->attach($account->getKey());
            });
        });
    }
}