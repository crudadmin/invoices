<?php

namespace Gogol\Invoices\Model;

use Gogol\Admin\Models\Model as AdminModel;
use Gogol\Invoices\Admin\Buttons\ExportInvoicesButton;

class InvoicesExport extends AdminModel
{
    /*
     * Model created date, for ordering tables in database and in user interface
     */
    protected $migration_date = '2019-03-26 21:10:10';

    /*
     * Template name
     */
    protected $name = 'Export faktúr';

    protected $icon = 'fa-download';
    protected $publishable = false;
    protected $editable = false;

    protected $group = 'invoices';

    /*
     * Template title
     * Default ''
     */
    protected $title = 'V tejto sekcii môžete vyexportovať faktúry a proformy podľa požadovaného intervalu.';

    protected $buttons = [
        ExportInvoicesButton::class,
    ];

    /*
     * Automatic form and database generation
     * @name - field name
     * @placeholder - field placeholder
     * @type - field type | string/text/editor/select/integer/decimal/file/password/date/datetime/time/checkbox
     * ... other validation methods from laravel
     */
    protected $fields = [
        'from' => 'name:Exportovať od|placeholder:Vrátane dátumu|type:date|required',
        'to' => 'name:Exportovať do|placeholder:Vrátane dátumu|type:date|required',
        'types' => 'name:Exportovať typy dokladov|type:select|limit:100|multiple|required|max:20',
    ];

    protected $settings = [
        'grid.default' => 'small',
        'title.insert' => 'Nový export',
    ];

    public function options()
    {
        return [
            'types' => array_map(function($item){
                return $item['name'];
            }, config('invoices.invoice_types', [])),
        ];
    }

}