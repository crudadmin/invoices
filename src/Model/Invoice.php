<?php

namespace Gogol\Invoices\Model;

use Admin;
use Admin\Eloquent\AdminModel;
use Admin\Fields\Group;
use Carbon\Carbon;
use Gogol\Invoices\Admin\Buttons\CreateInvoiceFromProform;
use Gogol\Invoices\Admin\Buttons\CreateReturnFromInvoice;
use Gogol\Invoices\Admin\Buttons\SendInvoiceEmailButton;
use Gogol\Invoices\Admin\Layouts\InvoiceComponent;
use Gogol\Invoices\Admin\Rules\ProcessInvoiceRule;
use Gogol\Invoices\Model\InvoicesSetting;
use Gogol\Invoices\Traits\HasInvoicePdf;
use Gogol\Invoices\Traits\HasInvoiceQrCode;
use Gogol\Invoices\Traits\InvoiceProcessTrait;

class Invoice extends AdminModel
{
    use InvoiceProcessTrait,
        HasInvoicePdf;

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

    protected $casts = [
        'vs' => 'integer',
    ];

    protected $settings = [
        'increments' => false,
        'autoreset' => false,
        'search.enabled' => true,
        'xls' => true,
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
            'company_name.after' => 'type',
            'email_sent.before' => 'price_vat',
            'email_sent.encode' => false,
            'pdf.encode' => false,
            'created.name' => 'Vytvorené',
        ],
    ];

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
                Group::inline([
                    'subject' => 'name:Subjekt|belongsTo:invoices_settings,name|component:SetDefaultSubject|required|'.($this->hasMultipleSubjects() ? '' : 'hidden'),
                    'type' => 'name:Typ dokladu|type:select|'.($row ? '' : 'required').'|index|max:20',
                    Group::inline([
                        'number_manual' => 'name:Manuálne číslo dokladu|type:checkbox|default:0|hidden',
                        'number' => 'name:Č. dokladu|index|removeFromFormIfNot:number_manual,1|max:30',
                    ])
                ]),
                'return' => 'name:Dobropis k faktúre|belongsTo:invoices,'.config('invoices.invoice_types.invoice.prefix').':number|exists:invoices,id,type,invoice|component:setReturnField|required_if:type,return|hidden',
                'proform' => 'name:Proforma|belongsTo:invoices,id|invisible',
                'vs' => [
                    'name' => 'Variabilný symbol',
                    'title' => 'Pri prázdnej hodnote vygenerovaný automaticky',
                    'digits_between' => '0,10',
                    'max' => 10,
                    'index' => true,
                    'placeholder' => 'Zadajte variabilný symbol',
                    'required' => isset($row) ? true : false,
                    'hidden' => true,
                    $this->vsRuleUnique($row)
                ],
                'payment_method' => 'name:Spôsob platby|belongsTo:payments_methods,name|defaultByOption:default,1|required|canAdd|hidden',
                Group::inline([
                    'payment_date' => 'name:Dátum splatnosti|type:date|format:d.m.Y|title:Vypočítava sa automatický od dátumu vytvorenia +('.getInvoiceSettings('payment_term').' dní)|hidden',
                    'paid_at' => 'name:Zaplatené dňa|type:date|format:d.m.Y|title:Zadajte dátum zaplatenia faktúry|hidden',
                ]),
                Group::inline([
                    'delivery_at' => 'name:Dodané dňa|type:datetime|format:d.m.Y|hidden|required|default:CURRENT_TIMESTAMP',
                    'created_at' => 'name:Vystavené dňa|type:datetime|format:d.m.Y H:i:s|title:Tento údaj určuje, do ktorého daňového obdobia bude faktúra zarataná.|required|default:CURRENT_TIMESTAMP',
                ]),
                Group::fields([
                    'note' => 'name:Poznámka|type:text|hidden',
                    Group::fields([
                        'price' => 'name:Cena bez DPH (€)|type:decimal|required|default:0|hidden',
                        'price_vat' => 'name:Cena s DPH (€)|type:decimal|required|default:0',
                    ])->add('removeFromForm')->inline()
                ]),
                'pdf' => 'name:Doklad|type:file|extension:pdf|removeFromForm',
            ]),
            'Fakturačné údaje' => Group::fields([
                'client_id' => 'name:Klient|'.(config('invoices.clients', false) ? 'belongsTo:clients,company_name' : 'type:imaginary').'|hidden|canAdd',
                Group::fields([
                    'Firemné údaje' => Group::half([
                        Group::fields([
                            'company_name' => 'name:Meno a priezivsko / Firma|fillBy:client|placeholder:Zadajte názov odoberateľa|required|max:90',
                            'email' => 'name:Email|fillBy:client|placeholder:Slúži pre odoslanie faktúry na email|hidden',
                        ])->inline(),
                        'company_id' => 'name:IČO|type:string|fillBy:client|placeholder:Zadajte IČO|hidden',
                        'company_tax_id' => 'name:DIČ|type:string|fillBy:client|placeholder:Zadajte DIČ|hidden',
                        'company_vat_id' => 'name:IČ DPH|type:string|fillBy:client|placeholder:Zadajte IČ DPH|hidden',
                    ]),
                    'Fakturačná adresa' => Group::half([
                        'city' => 'name:Mesto|fillBy:client|placeholder:Zadajte mesto|required|hidden|max:90',
                        'zipcode' => 'name:PSČ|fillBy:client|placeholder:Zadajte PSČ|default:080 01|required|hidden|max:90',
                        'street' => 'name:Ulica|fillBy:client|placeholder:Zadajte ulicu|required|hidden|max:90',
                        'country' => 'name:Štát|fillBy:client|belongsTo:countries,name|defaultByOption:default,1|hidden|required',
                    ]),
                    config('invoices.delivery') ? Group::half([
                        'delivery_company_name' => 'name:Meno a priezvisko / Firma|max:90',
                        'delivery_city' => 'name:Mesto/Obec|max:90',
                        'delivery_street' => 'name:Ulica|max:90',
                        'delivery_zipcode' => 'name:PSČ|default:08001|max:10|hidden',
                        'delivery_country' => 'name:Štát|belongsTo:countries,name|defaultByOption:default,1|hidden|max:90',
                    ])->name('Doručovacia adresa')->add('hidden')->id('delivery') : [],
                ])->inline(),
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
            'return' => [],
        ];
    }

    public function scopeAdminRows($query)
    {
        $query->with('proformInvoice:id,proform_id,number,pdf,type');
    }

    public function setAdminRowsAttributes($attributes)
    {
        $attributes['number'] = $this->number;

        $attributes['email_sent'] = '<i style="color: '.($this->isEmailChecked() ? 'green' : 'red').'" class="fa fa-'.($this->isEmailChecked() ? 'check' : 'times').'"></i>';

        $attributes['return_number'] = $this->return_id && $this->return ? $this->return->number : null;

        $attributes['pdf'] = '<a href="'.action('\Gogol\Invoices\Controllers\InvoiceController@generateInvoicePdf', $this->getKey()).'" target="_blank">'._('Zobraziť doklad').'</a>';

        $attributes['created'] = $this->created_at->format('d.m.Y H:i');

        return $attributes;
    }

    /*
     * Return type of invoice
     */
    public function getTypeNameAttribute()
    {
        return config('invoices.invoice_types.'.$this->type.'.name', '');
    }

    /*
     * Return typename with number
     */
    public function getTypeNameNumberAttribute()
    {
        return sprintf('%s  č. %s', config('invoices.invoice_types.'.$this->type.'.name', ''), $this->number);
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
        if ( $value ) {
            return $this->numberPrefix . $value;
        }
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

    public function make(string $type, $data = null)
    {
        $data['type'] = $type;

        //Add default subject if is missing
        if ( !isset($data['subject_id']) || !$data['subject_id'] ){
            $data['subject_id'] = getInvoiceSettings()->getKey();
        }

        if ( $data instanceof AdminModel ) {
            $data = $data->toArray();
        }

        if ( !($data['delivery_at'] ?? null) ){
            $data['delivery_at'] = Carbon::now();
        }

        //Remove uneccessary columns from invoice
        foreach (['deleted_at', 'created_at', 'updated_at', 'client_id', 'number', 'number_prefix'] as $column) {
            if ( array_key_exists($column, $data) ) {
                unset($data[$column]);
            }
        }

        return new static($data);
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

        //Change paid status after generating proform
        if ( ! $this->paid_at ) {
            $this->update(['paid_at' => Carbon::now()]);
        }

        return $invoice;
    }

    /*
     * Generate return invoice
     */
    public function createReturn()
    {
        $invoice = $this->replicate();
        $invoice->type = 'return';
        $invoice->setInvoiceNumber();
        $invoice->return_id = $this->getKey();
        $invoice->setNewVs($this->getRawOriginal('number'));
        $invoice->paid_at = null;
        $invoice->payment_date = Carbon::now()->addDays($this->subject->payment_term);
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

    private function hasMultipleSubjects()
    {
        if ( app()->runningInConsole() ){
            return;
        }

        return Admin::cache('invoices.subjects.count', function(){
            return InvoicesSetting::count() > 1;
        });
    }
}