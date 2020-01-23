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

function getInvoiceSettings($key = null, $default = null)
{
    if ( app()->runningInConsole() )
        return;

    $settings = Admin::cache('invoices.settings', function(){
        $model = Admin::getModel('InvoicesSetting');

        return $model && ($settings = $model->first()) ? $settings : $model;
    });

    return $key && $settings ? ($settings->{$key} ?: $default) : ($settings ?: $default);
}

function invoice($data = [])
{
    return Admin::getModel('Invoice')->fill($data ?: []);
}

function getVatValues()
{
    return Admin::cache('invoices.taxes', function(){
        $model = Admin::getModel('Tax');

        if ( $model ) {
            return $model->pluck('tax')->toArray();
        }

        return config('invoices.vats', [0, 20]);
    });
}

function getDefaultVatValue()
{
    return Admin::cache('invoices.defaultVat', function(){
        $model = Admin::getModel('Tax');

        if ( $model && $defaultVat = $model->where('default', 1)->first() ) {
            return $defaultVat->tax;
        }

        return config('invoices.default_item_vat', 0);
    });
}
?>