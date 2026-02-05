<?php

namespace Gogol\Invoices\Model;

use Admin\Eloquent\AdminModel;
use Admin\Fields\Group;
use Gogol\Invoices\Admin\Rules\SetDefault;

class Vat extends AdminModel
{
    /*
     * Model created date, for ordering tables in database and in user interface
     */
    protected $migration_date = '2018-01-07 17:52:15';

    /*
     * Template name
     */
    protected $name = 'Sadzby DPH';

    protected $group = 'invoices';

    protected $sortable = false;

    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'published_at'];

    protected $orderBy = ['vat', 'asc'];

    protected $icon = 'fa-percent';

    protected $settings = [
        'title.insert' => 'Nová sadzba',
        'title.update' => ':name',
        'increments' => true,
    ];

    protected $rules = [
        SetDefault::class,
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
            'name' => 'name:Názov|required',
            'vat' => 'name:Sadzba DPH|type:decimal|required',
            'default' => 'name:Predvolená DPH|type:checkbox|title:Bude platit pre zľavy, a všetký ceny bez definovanej DPH.',
        ];
    }

    public function onTableCreate()
    {
        foreach([0, 5, 20, 23] as $vat) {
            $this->create([
                'name' => $vat . '%',
                'vat' => $vat,
                'default' => $vat == 23,
            ]);
        }
    }
}