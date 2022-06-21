<?php

namespace Gogol\Invoices\Helpers\Exports\PDF;

use Gogol\Invoices\Helpers\Exports\InvoiceExport;
use Store;

class PDFExport extends InvoiceExport
{
    public function add($zip)
    {
        foreach ($this->invoices as $invoice) {
            if (!($pdf = $invoice->getPdf())) {
                continue;
            }

            $zip->addFile($pdf->basepath, './'.$invoice->typeName.'/'.$pdf->filename);
        }
    }
}