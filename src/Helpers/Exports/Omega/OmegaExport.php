<?php

namespace Gogol\Invoices\Helpers\Exports\Omega;

use Gogol\Invoices\Helpers\Exports\InvoiceExport;

class OmegaExport extends InvoiceExport
{
    const higherVat = 23;

    public function getOmegaRates($invoice)
    {
        $higherVat = $invoice->items->pluck('vat')
                        ->map(fn($vat) => (float)$vat)
                        ->filter(fn($vat) => $vat >= self::higherVat)
                        ->max();

        $lowerVat = $invoice->items->pluck('vat')
                        ->map(fn($vat) => (float)$vat)
                        ->filter(fn($vat) => $vat > 0)
                        ->min();

        $mediumVat = $invoice->items->pluck('vat')
            ->map(fn($vat) => (float)$vat)
            ->filter(fn($vat) => $vat > $lowerVat && $vat < $higherVat)
            ->max();

        $lowerVat = $lowerVat == $higherVat ? null : $lowerVat;

        return [
            'lower' => $lowerVat,
            'medium' => $mediumVat,
            'higher' => $higherVat,
        ];
    }

    protected function getInvoiceTaxSum($invoice, $vat, $withVat = false)
    {
        $price = $invoice->items->filter(function($item) use ($vat) {
            return $item->vat == $vat;
        })->sum('totalPriceWithVat');

        if ( $withVat === false ) {
            return roundInvoicePrice(calculateWithoutVat($price, $vat));
        }

        return $price;
    }

    public function getCsvString()
    {
        $rows = $this->getRows();

        $string = $this->toTxt($rows);

        return $string;
    }

    protected function toTxt($rows)
    {
        foreach ($rows as $key => $row) {
            $rows[$key] = implode("\t", $row);
        }

        //crlf
        $data = implode("\r\n", $rows);

        return $this->encode($data);
    }

    protected function encode($string)
    {
        return iconv( mb_detect_encoding( $string ) , 'Windows-1252//TRANSLIT', $string);
    }

    protected function getItemVat($item)
    {
        if ( $vat = $item->omegaVat ){
            return $vat;
        }

        $vat = (int)$item->vat;
        $rates = $this->getOmegaRates();

        if ( $vat >= $rates['higher'] ){
            return 'V';
        } else if ( $vat == 0 ){
            return '0';
        } else if ( $vat < $rates['lower'] ){
            return 'N';
        } else {
            return 'X';
        }
    }

    protected function getInvoiceNumberSequence($invoice)
    {
        if ( $prefix = $invoice->omegaNumberPrefix ){
            return $prefix;
        }

        return rtrim($invoice->numberPrefix, '-');
    }
}