<?php

namespace Gogol\Invoices\Helpers\Omega;

use Store;

class OmegaExport
{
    const VAT_LOWER = 10;
    const VAT_HIGHER = 20;

    public $invoices;
    public $export;
    public $exportInterval;

    public function __construct($invoices, $export, $exportInterval)
    {
        $this->invoices = $invoices;
        $this->export = $export;
        $this->exportInterval = $exportInterval;
    }

    protected function getInvoiceTaxSum($invoice, $vat, $sumColumn = 'price')
    {
        return $invoice->items->filter(function($item) use ($vat) {
            return $item->vat == $vat;
        })->sum($sumColumn);
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

        if ( $vat >= self::VAT_HIGHER ){
            return 'V';
        } else if ( $vat == 0 ){
            return '0';
        } else if ( $vat < self::VAT_LOWER ){
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