<?php

namespace Gogol\Invoices\Model;

use Admin\Eloquent\AdminModel;
use Admin\Fields\Group;

class InvoicesSetting extends AdminModel
{
    /*
     * Model created date, for ordering tables in database and in user interface
     */
    protected $migration_date = '2019-03-26 20:26:47';

    /*
     * Template name
     */
    protected $name = 'Nastavenia fakturácie';

    protected $group = 'settings';

    protected $icon = 'fa-file-invoice';

    protected $reversed = true;

    protected $sortable = false;

    protected $publishable = false;

    public function single()
    {
        return config('invoices.multi_subjects', false) == false;
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
                'zipcode' => 'name:PSČ|placeholder:Zadajte PSČ|default:080 01|required|max:90',
                'street' => 'name:Ulica|placeholder:Zadajte ulicu|required|max:90',
                'country' => 'name:Štát|default:Slovenská republika|required|max:90',
            ]),
            Group::fields([
                'Bankove údaje' => Group::half([
                    'account' => 'name:Č. účtu|placeholder:0123456789/0000|max:90|required',
                    'iban' => 'name:IBAN|max:90|required',
                    'swift' => 'name:Swift|max:90|required',
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
                'email_message' => 'name:Správa v emaili',
                'email_greeting' => 'name:Pozdrav|required',
            ])->add('hidden'),
        ];
    }

    public function getAccountNumberAttribute()
    {
        $account = explode('/', str_replace(' ', '', preg_replace('/\-|\||\.|\_/', '/', $this->account)));

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
        return $this->company_vat_id ? true : false;
    }
}