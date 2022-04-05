<?php

namespace Gogol\Invoices\Model;

use Admin\Eloquent\AdminModel;
use Admin\Fields\Group;
use Gogol\Invoices\Admin\Rules\SetDefault;

class Country extends AdminModel
{
    /*
     * Model created date, for ordering tables in database and in user interface
     */
    protected $migration_date = '2018-01-07 17:51:25';

    /*
     * Template name
     */
    protected $name = 'Krajiny';

    /*
     * Template title
     * Default ''
     */
    protected $title = '';

    protected $group = 'invoices';

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
            'name' => 'name:Krajina|required',
            'code' => 'name:Skratka krajiny|max:5|required',
            'default' => 'name:Predvolená krajina|type:checkbox',
        ];
    }

    protected $settings = [
        'title.insert' => 'Nová krajina',
        'title.update' => ':name',
        'columns.id.hidden' => true,
    ];

    protected $rules = [
        SetDefault::class,
    ];

    public function setCodeAttribute($code)
    {
        $this->attributes['code'] = mb_strtolower($code);
    }

    public function onTableCreate()
    {
        //When roles table is created, set all users as super admins.
        $this->create([
            'name' => 'Slovensko',
            'code' => 'sk',
            'default' => true,
        ]);
    }
}