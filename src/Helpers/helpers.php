<?php

use Gogol\Invoices\Model\Invoice;
use Gogol\Invoices\Model\InvoicesSetting;

function priceFormat($number){
    return number_format($number, 2, '.', ' ');
}

function getDefaultInvoiceLanguage()
{
    if ( count(config('invoices.countries')) == 0 )
        return;

    return array_keys(config('invoices.countries'))[0];
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