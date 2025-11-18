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

    protected $rules = [
        ProcessInvoiceItemRule::class,
        ProcessInvoicePriceRule::class,
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
            'name' => 'name:Názov položky|placeholder:Zadajte názov položky|limit:50|required',
            'quantity' => 'name:Množstvo|type:integer|required|default:1|min:1',
            Group::fields([
                'price' => 'name:Cena/ks bez DPH|type:decimal|component:SetVatPrice|required_without:price_vat',
                'vat' => 'name:DPH %|type:select|sub_component:SelectDefaultVat|required',
                'price_vat' => 'name:Cena/ks s DPH|type:decimal|component:SetVatPrice|required_without:price',
            ])->inline(),
        ];
    }

    public function settings()
    {
        return [
            'title.insert' => 'Nová položka',
            'title.update' => 'Upravujete položku :name',
            'buttons.insert' => 'Nová položka',
            'defaultVat' => getDefaultVatValue(),
        ];
    }

    public function options()
    {
        return [
            'vat' => $this->getVatValues(),
        ];
    }

    public function getTotalPriceWithVatAttribute()
    {
        return roundInvoicePrice($this->price_vat * $this->quantity);
    }

    public function getTotalPriceWithoutVatAttribute()
    {
        if ( hasVatPriority() ) {
            $price = calculateWithoutVat($this->totalPriceWithVat, $this->vat);
        } else {
            $price = $this->price * $this->quantity;
        }

        return roundInvoicePrice($price);
    }

    private function getVatValues()
    {
        $vats = getVatValues();

        return array_combine($vats, $vats);
    }

    public function canShowInSummary()
    {
        return true;
    }
}