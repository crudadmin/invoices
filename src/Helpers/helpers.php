<?php

function priceFormat($number){
    return number_format($number, 2, '.', ' ');
}

function calculateWithoutVat($number, $vat)
{
    return $number / (1 + ($vat / 100));
}

function calculateWithVat($number, $vat)
{
    return $number * (1 + ($vat / 100));
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

    $settings = Admin::cache('invoices.settings', function(){
        return Admin::getModel('InvoicesSetting')->first();
    });

    return $key && $settings ? ($settings->{$key} ?: $default) : ($settings ?: $default);
}

function invoice($data = [])
{
    return Admin::getModel('Invoice')->fill($data ?: []);
}
?>