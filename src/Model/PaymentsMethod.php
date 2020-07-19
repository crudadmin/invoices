<?php

namespace Gogol\Invoices\Model;

use Admin\Eloquent\AdminModel;
use Admin\Fields\Group;
use Gogol\Invoices\Admin\Rules\SetDefault;

class PaymentsMethod extends AdminModel
{
    /*
     * Model created date, for ordering tables in database and in user interface
     */
    protected $migration_date = '2018-01-07 17:48:18';

    /*
     * Template name
     */
    protected $name = 'Platobné metódy';

    protected $group = 'invoices';

    protected $publishable = false;

    protected $icon = 'fa-money';

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
            'name' => 'name:Názov platby|max:40|required',
            'default' => 'name:Predvolená možnosť platby|type:checkbox|title:Bude automatický vyplnená pri tvorbe novej faktúry.',
        ];
    }

    protected $settings = [
        'grid.default' => 'medium',
        'title.update' => ':name',
        'columns.id.hidden' => true,
    ];

    protected $rules = [
        SetDefault::class,
    ];
}