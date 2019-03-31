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

function getSettings($key = null, $default = null)
{
    if ( app()->runningInConsole() )
        return;

    $settings = Admin::cache('settings', function(){
        return InvoicesSetting::first();
    });

    return $key && $settings ? ($settings->{$key} ?: $default) : ($settings ?: $default);
}

function invoice($data = null)
{
    return new Invoice($data);
}
?>