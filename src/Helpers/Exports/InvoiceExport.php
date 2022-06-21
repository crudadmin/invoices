<?php

namespace Gogol\Invoices\Helpers\Exports;

class InvoiceExport
{
    public $invoices;
    public $export;
    public $exportInterval;

    public function __construct($invoices, $export, $exportInterval)
    {
        $this->invoices = $invoices;
        $this->export = $export;
        $this->exportInterval = $exportInterval;
    }
}