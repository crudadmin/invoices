<?php

namespace Gogol\Invoices\Model;

use Admin\Fields\Group;
use Admin\Eloquent\AdminModel;
use Gogol\Invoices\Model\InvoicesSetting;
use Gogol\Invoices\Admin\Buttons\SyncBankTransactionsButton;

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

    protected $publishable = false;

    protected $buttons = [
        SyncBankTransactionsButton::class,
    ];

    protected $settings = [
        'grid.default' => 'full',
    ];

    protected $options = [
        'bank' => [
            'fio' => 'FIO Banka',
        ],
    ];

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
            'Automatická synchronizácia platieb' => Group::fields([
                'bank' => 'name:Banka|type:select',
                'token' => 'name:Token|encrypted|type:password',
                'last_sync_at' => 'name:Posledná synchronizácia|type:datetime|column_visible',
            ])->add('hidden'),
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

    /**
     * Run account synchronization for unpaid invoices
     *
     * @param  mixed $cmd
     * @param  bool $syncAll
     * @return void
     */
    public function syncAccount($cmd = null, $syncAll = false)
    {
        $bank = $this->bank;

        if ( !($classname = config('invoices.banks.' . $bank.'.import')) ){
            return;
        }

        $importer = new $classname($this);

        if ( $cmd ) {
            $importer->setCommand($cmd);
        }

        // Set sync all transactions flag
        $importer->setSyncAllTransactions($syncAll);

        // Run synchronization
        $importer->sync();

        return $importer;
    }
}