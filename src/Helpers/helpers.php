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
    if ( app()->runningInConsole() ) {
        return;
    }

    $settings = null;

    //Return invoice settings
    if ( $model = Admin::getModel('InvoicesSetting') ) {
        $settings = Admin::cache('invoices.settings', function() use ($model) {
            return $model && ($settings = $model->first()) ? $settings : $model;
        });
    }

    return $key && $settings ? ($settings->{$key} ?: $default) : ($settings ?: $default);
}

function invoice($data = [])
{
    return Admin::getModel('Invoice')->fill($data ?: []);
}

function getVatValues()
{
    return Admin::cache('invoices.vats', function(){
        $model = Admin::getModel('Vat');

        if ( $model ) {
            return $model->pluck('vat')->sort()->values()->toArray();
        }

        return config('invoices.vats', [0, 20]);
    });
}

function getDefaultVatValue($subject = null)
{
    if ( $subject && $subject->vat_default ){
        return $subject->vat_default->vat;
    }

    return Admin::cache('invoices.defaultVat', function(){
        $model = Admin::getModel('Vat');

        if ( $model && $defaultVat = $model->where('default', 1)->first() ) {
            return $defaultVat->vat;
        }

        return config('invoices.default_item_vat', 0);
    });
}

function canRoundSummary()
{
    return config('invoices.round_summary', true);
}
?>