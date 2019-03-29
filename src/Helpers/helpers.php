<?php

use Gogol\Invoices\Model\Invoice;
use Gogol\Invoices\Model\InvoicesSetting;

function priceFormat($number){
    return number_format($number, 2, '.', ' ');
}

function getSettings($key = null)
{
    if ( app()->runningInConsole() )
        return;

    $settings = Admin::cache('settings', function(){
        return InvoicesSetting::first();
    });

    return $key && $settings ? $settings->{$key} : $settings;
}

function invoice($data = null)
{
    return new Invoice($data);
}
?>