<?php

namespace Gogol\Invoices\Model;

use Gogol\Invoices\Admin\Buttons\CreateInvoiceFromProform;
use Gogol\Invoices\Admin\Buttons\CreateReturnFromInvoice;
use Gogol\Invoices\Admin\Buttons\SendInvoiceEmailButton;
use Gogol\Invoices\Admin\Layouts\InvoiceComponent;
use Gogol\Invoices\Admin\Rules\ProcessInvoiceRule;
use Gogol\Invoices\Traits\InvoiceProcessTrait;
use Gogol\Admin\Fields\Group;
use Gogol\Admin\Models\Model as AdminModel;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Invoice extends AdminModel
{
    use InvoiceProcessTrait;

    /*
     * Model created date, for ordering tables in database and in user interface
     */
    protected $migration_date = '2019-03-26 20:10:10';

    /*
     * Template name
     */
    protected $name = 'Faktúry';

    protected $publishable = false;
    protected $sortable = false;

    protected $group = 'invoices';

    /*
     * Automatic form and database generation
     * @name - field name
     * @placeholder - field placeholder
     * @type - field type | string/text/editor/select/integer/decimal/file/password/date/datetime/time/checkbox/radio
     * ... other validation methods from laravel
     */
    public function fields($row)
    {
        return [
            'Nastavenia dokladu' => Group::fields([
                'type' => 'name:Typ dokladu|type:select|'.($row ? '' : 'required').'|max:20',
                'number' => 'name:Č. dokladu|removeFromForm|index|max:30',
                'return' => 'name:Dobropis k faktúre|belongsTo:invoices,'.config('invoices.invoice_types.invoice.prefix').':number|exists:invoices,id,type,invoice|component:setReturnField|required_if:type,return|hidden',
                'proform' => 'name:Proforma|belongsTo:invoices,id|invisible',
                'vs' => [ 'name' => 'Variabilný symbol', 'digits_between' => '0,10', 'max' => 10, 'index' => true, 'placeholder' => 'Zadajte variabilný symbol', 'required' => true, $this->vsRuleUnique($row) ],
                'payment_method' => 'name:Spôsob platby|type:select|default:sepa',
                Group::fields([
                    'payment_date' => 'name:Dátum splatnosti|type:date|format:d.m.Y|title:Vypočítava sa automatický od dátumu vytvorenia +('.getSettings('payment_term').' dní)',
                    'paid_at' => 'name:Zaplatené dňa|type:date|format:d.m.Y|title:Zadajte dátum zaplatenia faktúry',
                    'created_at' => 'name:Vystavené dňa|type:datetime|format:d.m.Y H:i:s|required|default:CURRENT_TIMESTAMP',
                ])->inline(),
                Group::fields([
                    'note' => 'name:Poznámka|type:text|hidden',
                    Group::fields([
                        'price' => 'name:Cena bez DPH (€)|type:decimal|required|default:0',
                        'price_vat' => 'name:Cena s DPH (€)|type:decimal|required|default:0',
                    ])->add('removeFromForm')->inline()
                ]),
                'pdf' => 'name:Doklad|type:file|extension:pdf|removeFromForm',
            ]),
            'Fakturačné údaje' => Group::fields([
                'client' => 'name:Klient|'.(config('invoices.clients', false) ? 'belongsTo:clients,company_name' : 'type:imaginary').'|hidden|canAdd',
                'Firemné údaje' => Group::half([
                    Group::fields([
                        'company_name' => 'name:Meno a priezivsko / Firma|fillBy:client|placeholder:Zadajte názov odoberateľa|required|max:90',
                        'email' => 'name:Email|fillBy:client|placeholder:Slúži pre odoslanie faktúry na email|email',
                    ])->inline(),
                    'company_id' => 'name:IČO|type:string|fillBy:client|placeholder:Zadajte IČO|hidden',
                    'tax_id' => 'name:DIČ|type:string|fillBy:client|placeholder:Zadajte DIČ|hidden',
                    'vat_id' => 'name:IČ DPH|type:string|fillBy:client|placeholder:Zadajte IČ DPH|hidden',
                ]),
                'Fakturačná adresa' => Group::half([
                    'city' => 'name:Mesto|fillBy:client|placeholder:Zadajte mesto|required|hidden|max:90',
                    'zipcode' => 'name:PSČ|fillBy:client|placeholder:Zadajte PSČ|default:080 01|required|hidden|max:90',
                    'street' => 'name:Ulica|fillBy:client|placeholder:Zadajte ulicu|required|hidden|max:90',
                    'country' => 'name:Štát|fillBy:client|type:select|default:'.getDefaultInvoiceLanguage().'|hidden|required|max:90',
                ]),
            ]),
            'email_sent' => 'name:Notifikácia|type:json|removeFromForm',
            'snapshot_sha' => 'name:SHA Dát fakúry|max:50|invisible',
            'guid' => 'name:GUID|max:50|invisible',
        ];
    }

    public function options()
    {
        return [
            'type' => array_map(function($item){
                return $item['name'];
            }, config('invoices.invoice_types', [])),
            'payment_method' => config('invoices.payment_methods'),
            'country' => config('invoices.countries'),
        ];
    }

    protected $settings = [
        'increments' => false,
        'autoreset' => false,
        'refresh_interval' => 3000,
        'buttons.insert' => 'Nový doklad',
        'title' => [
            'insert' => 'Nový doklad',
            'update' => 'Upravujete doklad č. :number',
        ],
        'grid' => [
            'default' => 'full',
            'disabled' => true,
        ],
        'columns' => [
            'number.before' => 'type',
            'company_name.name' => 'Odberateľ',
            'company_name.after' => 'vs',
            'vs.name' => 'VS.',
            'email.before' => 'payment_method',
            'email_sent.before' => 'pdf',
            'email_sent.encode' => false,
            'pdf.encode' => false,
        ],
    ];

    protected $rules = [
        ProcessInvoiceRule::class,
    ];

    protected $layouts = [
        InvoiceComponent::class,
    ];

    protected $buttons = [
        CreateInvoiceFromProform::class,
        CreateReturnFromInvoice::class,
        SendInvoiceEmailButton::class,
    ];

    public function scopeAdminRows($query)
    {
        $query->with('proformInvoice:id,proform_id,number,pdf,type');
    }

    public function setAdminAttributes($attributes)
    {
        $attributes['number'] = $this->number;

        $attributes['email_sent'] = '<i style="color: '.($this->isEmailChecked() ? 'green' : 'red').'" class="fa fa-'.($this->isEmailChecked() ? 'check' : 'times').'"></i>';

        $attributes['return_number'] = $this->return_id && $this->return ? $this->return->number : null;

        $attributes['pdf'] = '<a href="'.action('\Gogol\Invoices\Controllers\InvoiceController@generateInvoicePdf', $this->getKey()).'" target="_blank">Zobraziť doklad</a>';

        return $attributes;
    }

    /*
     * Return type of invoice
     */
    public function getTypeNameAttribute()
    {
        return config('invoices.invoice_types.'.$this->type.'.name', '') . ' č.';
    }

    /*
     * Return prefix of invoice number
     */
    public function getNumberPrefixAttribute()
    {
        return config('invoices.invoice_types.'.$this->type.'.prefix', '');
    }

    /*
     * Return full invoice number
     */
    public function getNumberAttribute($value)
    {
        return $this->numberPrefix . $value;
    }

    /*
     * Return payment method in text value
     */
    public function getPaymentMethodNameAttribute()
    {
        return config('invoices.payment_methods.'.$this->payment_method, '-');
    }

    /*
     * Return country name in text value
     */
    public function getCountryNameAttribute()
    {
        return config('invoices.countries.'.$this->country, '-');
    }

    /*
     * Check if is proform invoice type
     */
    public function getIsProformAttribute()
    {
        return $this->isType('proform');
    }

    /*
     * Check if is invoice type
     */
    public function getIsInvoiceAttribute()
    {
        return $this->isType('invoice');
    }

    /*
     * Check if is return invoice type
     */
    public function getIsReturnAttribute()
    {
        return $this->isType('return');
    }

    /*
     * Check type of invoice
     */
    public function isType($type)
    {
        return $this->type == $type;
    }

    /*
     * Return invoice of proform
     */
    public function proformInvoice()
    {
        return $this->belongsTo(Invoice::class, 'id', 'proform_id')->where('type', 'invoice');
    }

    /*
     * Return original invoice of return invoice
     */
    public function returnInvoice()
    {
        return $this->belongsTo(Invoice::class, 'id', 'return_id');
    }

    /**
     * Get pdf file of invoice
     * If pdf is not actual or generater, then regenerate pdf and re-save filename into db
     * @param  boolean $regenerate force regeneration of PDF
     */
    public function getPdf($force_regenerate = false)
    {
        //Regenerate invoice if needed
        $this->generatePDF(true, $force_regenerate);

        return $this->pdf;
    }

    /*
     * Generate invoice
     */
    public function createInvoice()
    {
        $invoice = $this->replicate();
        $invoice->type = 'invoice';
        $invoice->proform_id = $this->getKey();
        $invoice->paid_at = Carbon::now();
        $invoice->payment_date = $this->payment_date < Carbon::now()->setTime(0, 0, 0) ? Carbon::now() : $this->payment_date;
        $invoice->snapshot_sha = null;
        $invoice->email_sent = null;
        $invoice->save();

        //Clone proform items
        $this->cloneItems($this, $invoice);

        //Generate pdf and save it
        $invoice->setRelations([]);
        $invoice->generatePdf(false);
        $invoice->save();

        return $invoice;
    }

    /*
     * Generate return invoice
     */
    public function createReturn()
    {
        $invoice = $this->replicate();
        $invoice->setInvoiceNumber();
        $invoice->type = 'return';
        $invoice->return_id = $this->getKey();
        $invoice->setNewVs($this->getOriginal('number'));
        $invoice->paid_at = null;
        $invoice->payment_date = Carbon::now();
        $invoice->price = -$invoice->price;
        $invoice->price_vat = -$invoice->price_vat;
        $invoice->snapshot_sha = null;
        $invoice->email_sent = null;
        $invoice->save();

        //Clone proform items
        $this->cloneItems($this, $invoice, true);

        //Generate pdf and save it
        $invoice->setRelations([]);
        $invoice->generatePdf(false);
        $invoice->save();

        return $invoice;
    }
}