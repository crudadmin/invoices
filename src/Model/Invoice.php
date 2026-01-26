<?php

namespace Gogol\Invoices\Model;

use Admin;
use Admin\Fields\Group;
use Admin\Eloquent\AdminModel;
use Gogol\Invoices\Traits\HasInvoicePdf;
use Gogol\Invoices\Traits\HasInvoiceHash;
use Gogol\Invoices\Traits\HasInvoiceMail;
use Admin\Eloquent\Concerns\HasEntryLocales;
use Gogol\Invoices\Traits\InvoiceProcessTrait;
use Gogol\Invoices\Admin\Layouts\InvoiceComponent;
use Gogol\Invoices\Admin\Rules\ProcessInvoiceRule;
use Gogol\Invoices\Admin\Buttons\SetInvoicePaidButton;
use Gogol\Invoices\Admin\Buttons\DownloadInvoiceButton;
use Gogol\Invoices\Admin\Buttons\SendInvoiceEmailButton;
use Gogol\Invoices\Admin\Buttons\CreateReturnFromInvoice;
use Gogol\Invoices\Admin\Buttons\CreateInvoiceFromProform;

class Invoice extends AdminModel
{
    use InvoiceProcessTrait,
        HasEntryLocales,
        HasInvoiceMail,
        HasInvoiceHash,
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
        DownloadInvoiceButton::class,
        CreateInvoiceFromProform::class,
        CreateReturnFromInvoice::class,
        SendInvoiceEmailButton::class,
        SetInvoicePaidButton::class,
    ];

    protected $casts = [
        'vs' => 'integer',
    ];

    protected $settings = [
        'increments' => false,
        'autoreset' => false,
        'search.enabled' => true,
        'search.autosave' => true,
        'form.actions' => true,
        'xls' => true,
        'buttons.create' => 'Nový doklad',
        'filter.single' => true,
        'title' => [
            'create' => 'Nový doklad',
            'update' => 'Upravujete doklad č. :formated_number',
        ],
        'grid' => [
            'default' => 'full',
            'disabled' => true,
        ],
        'columns' => [
            'formated_number.name' => 'Č. dokladu',
            'formated_number.before' => 'type',
            'company_name.name' => 'Odberateľ',
            'company_name.after' => 'type',
            'notified.name' => 'Notifikovaný',
            'notified.before' => 'created',
            'notified.encode' => false,
            'pdf.encode' => false,
            'created.name' => 'Vytvorené',
        ],
    ];

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
    public function fields($row)
    {
        return [
            'Nastavenia dokladu' => Group::fields([
                Group::inline([
                    // TODO: hide subjects when there is only one, via javascript.
                    'subject' => 'name:Subjekt|belongsTo:invoices_settings,:name|sub_component:SetDefaultSubject|readonlyIfNot:id,NULL|required',
                    'type' => 'name:Typ dokladu|type:select|'.($row ? '' : 'required').'|index|max:20|readonlyIfNot:id,NULL',
                    Group::inline([
                        'number_manual' => 'name:Manuálne číslo dokladu|type:checkbox|default:0|hidden',
                        'number' => 'name:Č. dokladu|index|removeFromFormIfNot:number_manual,1|max:30|index|hidden',
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
                    'payment_date' => 'name:Dátum splatnosti|type:date|title:Vypočítava sa automatický od dátumu vytvorenia +('.getInvoiceSettings('payment_term').' dní)|hidden',
                    'paid_at' => 'name:Zaplatené dňa|type:date|title:Zadajte dátum zaplatenia faktúry|hidden',
                ]),
                Group::inline([
                    'delivery_at' => 'name:Dodané dňa|type:datetime|hidden|required|default:CURRENT_TIMESTAMP',
                    'created_at' => 'name:Vystavené dňa|type:datetime|title:Tento údaj určuje, do ktorého daňového obdobia bude faktúra zarataná.|required|default:CURRENT_TIMESTAMP',
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
                        'delivery_zipcode' => 'name:PSČ|max:10|hidden',
                        'delivery_country' => 'name:Štát|belongsTo:countries,name|hidden|max:90',
                    ])->name('Doručovacia adresa')->add('hidden')->id('delivery') : [],
                ])->inline(),
            ]),
            // Metadata fields
            Group::fields([
                'paid_amount' => 'name:Zaplatená suma|type:decimal',
                Group::fields([
                    'language' => 'name:Jazyk objednávky|belongsTo:languages'
                ])->if(Admin::isEnabledLocalization()),
                'snapshot_sha' => 'name:SHA Dát fakúry|max:50|invisible',
                'guid' => 'name:GUID|max:50',
                'hash' => 'name:Hash|type:string|max:8',
                'notified_at' => 'name:Odoslané notifikácie|type:json',
            ])->add('inaccessible'),
        ];
    }

    public function options()
    {
        return [
            'subject_id' => $this->getSubjectOptions(),
            'type' => array_map(function($item){
                return $item['name'];
            }, config('invoices.invoice_types', [])),
            'return' => [],
        ];
    }

    private function getSubjectOptions()
    {
        return Admin::getModel('InvoicesSetting')
            ->select(['invoices_settings.id', 'invoices_settings.name'])
            ->when(config('invoices.multi_subjects'), function($query){
                $query
                    ->addSelect('vats.vat as vat_default_value')
                    ->leftJoin('vats', function($join){
                        $join->on('invoices_settings.vat_default_id', '=', 'vats.id');
                    });
            })->get();
    }

    public function scopeAdminRows($query)
    {
        $query->with('proformInvoice:id,proform_id,number,pdf,type');
    }

    public function setAdminAttributes($attributes)
    {
        $attributes['formated_number'] = $this->number;

        $attributes['return_number'] = $this->return_id && $this->return ? $this->return->number : null;

        return $attributes;
    }

    public function setAdminRowsAttributes($attributes)
    {
        $isNotified = isset($this->notified_at['notification']);
        $attributes['notified'] = '<i style="color: '.($isNotified ? 'green' : 'red').'" class="fa fa-'.($isNotified ? 'check' : 'times').'"></i>';

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
        return $this->belongsTo($this::class, 'id', 'proform_id')->whereIn('type', ['invoice', 'advance']);
    }

    /*
     * Return original invoice of return invoice
     */
    public function returnInvoice()
    {
        return $this->belongsTo($this::class, 'id', 'return_id');
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
            $data['delivery_at'] = now();
        }

        //Remove uneccessary columns from invoice
        foreach (['deleted_at', 'created_at', 'updated_at', 'number', 'number_prefix'] as $column) {
            if ( array_key_exists($column, $data) ) {
                unset($data[$column]);
            }
        }

        return new static($data);
    }

    /*
     * Generate invoice
     */
    public function createInvoice($type = 'invoice')
    {
        $invoice = $this->replicate();
        $invoice->type = $type;
        $invoice->proform_id = $this->getKey();
        $invoice->paid_at = $this->paid_at ?: now();
        $invoice->paid_amount = $this->paid_amount; //Copy from original invoice
        $invoice->payment_date = $this->payment_date < now()->startOfDay() ? now() : $this->payment_date;
        $invoice->snapshot_sha = null;
        $invoice->notified_at = [];

        //Set delivery date as paid date when we create invoice from proform.
        if ( $this->type == 'proform' ) {
            $invoice->delivery_at = $invoice->paid_at;
        }

        $invoice->save();

        //Clone proform items
        $this->cloneItems($this, $invoice);

        //Generate pdf and save it
        $invoice->setRelations([]);
        $invoice->generatePdf(false);
        $invoice->save();

        //Change paid status after generating proform
        if ( ! $this->paid_at ) {
            $this->update([ 'paid_at' => $invoice->paid_at ?: now() ]);
        }

        return $invoice;
    }

    public function createAdvance()
    {
        return $this->createInvoice('advance');
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
        $invoice->payment_date = now()->addDays($this->subject->payment_term);
        $invoice->price = -$invoice->price;
        $invoice->price_vat = -$invoice->price_vat;
        $invoice->snapshot_sha = null;
        $invoice->notified_at = [];
        $invoice->save();

        //Clone proform items
        $this->cloneItems($this, $invoice, true);

        //Generate pdf and save it
        $invoice->setRelations([]);
        $invoice->generatePdf(false);
        $invoice->save();

        return $invoice;
    }

    public function getFilterStates()
    {
        return [
            'paid' => [
                'color' => 'green',
                'name' => _('Uhradené'),
                'active' => function() {
                    return $this->paid_at !== null;
                },
                'query' => function($query) {
                    return $query->whereNotNull('paid_at');
                },
            ],
            'wrong_amount' => [
                'color' => 'orange',
                'name' => function() {
                    if ( $this->paid_amount ) {
                        return sprintf(_('Prijatá suma %s'), priceFormat($this->paid_amount).' €');
                    }

                    return _('Nesprávna úhrada');
                },
                'active' => function() {
                    return $this->paid_amount && $this->paid_amount !== $this->price_vat;
                },
                'query' => function($query) {
                    return $query->where('paid_amount', '>', 0)->whereColumn('paid_amount', '!=', 'price_vat')->whereNull('paid_at');
                },
            ],
            'payment_due' => [
                'color' => 'red',
                'name' => _('Po splatnosti'),
                'active' => function() {
                    return $this->paid_at === null && $this->payment_date < now()->startOfDay();
                },
                'query' => function($query) {
                    return $query->isPaymentDue();
                },
            ],
        ];
    }

    public function scopeIsPaymentDue($query)
    {
        return $query->whereNull('paid_at')->where('payment_date', '<', now());
    }
}