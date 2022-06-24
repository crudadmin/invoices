<?php

namespace Gogol\Invoices\Model;

use Admin\Fields\Group;
use Admin\Eloquent\AdminModel;
use Gogol\Invoices\Admin\Rules\ProcessInvoiceItemRule;
use Gogol\Invoices\Admin\Rules\ProcessInvoicePriceRule;

class InvoicesItem extends AdminModel
{
    /*
     * Model created date, for ordering tables in database and in user interface
     */
    protected $migration_date = '2019-03-26 20:45:34';

    /*
     * Template name
     */
    protected $name = 'Položky';

    /*
     * Model Parent
     * Eg. Article::class,
     */
    protected $belongsToModel = Invoice::class;

    protected $publishable = false;

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
            'name' => 'name:Názov položky|placeholder:Zadajte názov položky|limit:50|required',
            'quantity' => 'name:Množstvo|type:integer|required|default:1|min:1',
            Group::fields([
                'price' => 'name:Cena/ks bez DPH|type:decimal|component:SetVatPrice|required_without:price_vat',
                'vat' => 'name:DPH %|type:select|options:0,20|default:'.(app()->runningInConsole() ? 0 : getDefaultVatValue()).'|required',
                'price_vat' => 'name:Cena/ks s DPH|type:decimal|component:SetVatPrice|required_without:price',
            ])->inline(),
        ];
    }

    public function options()
    {
        return [
            'vat' => array_combine(getVatValues(), getVatValues()),
        ];
    }

    protected $settings = [
        'title.insert' => 'Nová položka',
        'title.update' => 'Upravujete položku :name',
        'buttons.insert' => 'Nová položka',
    ];

    protected $rules = [
        ProcessInvoiceItemRule::class,
        ProcessInvoicePriceRule::class,
    ];

    public function getTotalPriceWithTaxAttribute()
    {
        return canRoundSummary()
            ? $this->price_vat * $this->quantity
            : calculateWithVat($this->price * $this->quantity, $this->vat);
    }

    public function canShowInSummary()
    {
        return true;
    }
}