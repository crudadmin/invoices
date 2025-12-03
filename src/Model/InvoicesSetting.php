<?php

namespace Gogol\Invoices\Model;

use Admin\Fields\Group;
use Admin\Eloquent\AdminModel;
use Gogol\Invoices\Model\InvoicesAccount;
use Gogol\Invoices\Model\InvoicesSettingsAccountPivot;

class InvoicesSetting extends AdminModel
{
    /*
     * Model created date, for ordering tables in database and in user interface
     */
    protected $migration_date = '2019-03-26 20:02:47';

    /*
     * Template name
     */
    protected $name = 'Nastavenia fakturácie';

    protected $group = 'settings';

    protected $icon = 'fa-file-invoice';

    protected $reversed = true;

    protected $sortable = false;

    protected $publishable = false;

    protected $settings = [
        'buttons.create' => 'Nový subjekt',
        'title.create' => 'Nový fakturačný subjekt',
        'title.update' => 'Upravujete fakturačný subjekt :name',
    ];

    public function single()
    {
        return config('invoices.multi_subjects', false) == false;
    }

    public function active()
    {
        return config('invoices.enabled', true);
    }

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
            'Firemné údaje' => Group::half([
                'name' => 'name:Meno a priezivsko / Firma|placeholder:Zadajte názov odoberateľa|required|max:90',
                'company_id' => 'name:IČO|required|placeholder:Zadajte IČO',
                'tax_id' => 'name:DIČ|required|placeholder:Zadajte DIČ',
                'vat_id' => 'name:IČ DPH|placeholder:Zadajte IČ DPH|title:V prípade, že je subjekt platcom DPH',
            ]),
            'Fakturačná adresa' => Group::half([
                'city' => 'name:Mesto|placeholder:Zadajte mesto|required|max:90',
                'zipcode' => 'name:PSČ|placeholder:Zadajte PSČ|required|max:90',
                'street' => 'name:Ulica|placeholder:Zadajte ulicu|required|max:90',
                'country' => 'name:Štát|belongsTo:countries,name|defaultByOption:default,1|hidden|required',
            ]),
            Group::fields([
                'Bankove údaje' => Group::half([
                    'accounts' => 'name:Bankové účty|belongsToMany:invoices_accounts,:name - :iban|canAdd|canList',
                ]),
                'Kontaktné údaje' => Group::half([
                    'email' => 'name:Email|email',
                    'phone' => 'name:Tel. číslo',
                ]),
            ])->add('hidden'),
            'Nastavenia faktúry' => Group::half([
                'input' => 'name:Číslo vložky|required',
                'register' => 'name:Registrácia|required',
                'sign' => 'name:Doklad vystavil|required',
                'payment_term' => 'name:Splatnosť faktúr|type:integer|min:0|default:30',
                'invoice_color' => 'name:RGB ramčeka faktúry|type:color|max:11',
                Group::fields([
                    'vat_default' => 'name:Predvolená DPH|belongsTo:vats,:name (:vat%)|canAdd',
                ])->if(config('invoices.multi_subjects')),
                'vat_payer_from' => 'name:Platca DPH od|type:date',
                Group::half([
                    'logo' => 'name:Logo|type:file|image',
                    'logo_height' => 'name:Výška loga (px)|type:integer|required|default:60',
                ]),
                Group::half([
                    'signature' => 'name:Podpis|type:file|image',
                    'signature_height' => 'name:Výška podpisu (px)|type:integer|required|default:180',
                ]),
            ])->add('hidden'),
            'Nastavenia emailu' => Group::half([
                'email_greeting' => 'name:Pozdrav v pätičke emailu',
            ])->add('hidden'),
        ];
    }

    public function getAccountNumberAttribute()
    {
        $account = explode('/', str_replace(' ', '', preg_replace('/\-|\||\.|\_/', '/', $this->account?->number ?: '')));

        //Sort by value length
        usort($account, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        return [
            'account' => isset($account[0]) ? $account[0] : null,
            'code' => isset($account[1]) ? $account[1] : null,
        ];
    }

    public function getInvoiceColorAttribute($value)
    {
        return '#'.str_replace('#', '', $value);
    }

    public function getHasVatAttribute()
    {
        return $this->vat_id ? true : false;
    }

    public function hasVat($invoice)
    {
        if ( $this->vat_payer_from && $this->vat_payer_from >= $invoice->delivery_at ){
            return false;
        }

        return $this->hasVat;
    }

    public function account()
    {
        return $this->hasOneThrough(InvoicesAccount::class, InvoicesSettingsAccountPivot::class, 'invoices_setting_id', 'id', 'id', 'invoices_account_id');
    }
}