<?php

namespace Gogol\Invoices\Helpers\Exports\MoneyS3;

use Gogol\Invoices\Helpers\Exports\InvoiceExport;

class MoneyS3Export extends InvoiceExport
{
    public function add($zip)
    {
        $xml = view('invoices::xml.moneys3_export', [
            'invoices' => $this->invoices,
            'export' => $this->export,
        ])->render();

        //Add money s3 export
        $zip->addFromString(
            './money_s3_'.$this->exportInterval.'.xml',
            $xml
        );
    }
}