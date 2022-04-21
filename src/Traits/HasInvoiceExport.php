<?php

namespace Gogol\Invoices\Traits;

use \ZipArchive;

trait HasInvoiceExport
{
    public function makeExportZip($invoices, $export, $exportInterval)
    {
        $temp_file = @tempnam('tmp', 'zip');

        $zip = new ZipArchive();
        $zip->open($temp_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $this->buildExportZip($zip, $invoices, $export, $exportInterval);

        // Zip archive will be created only after closing object
        $zip->close();

        $data = file_get_contents($temp_file);

        @unlink($temp_file);

        return $data;
    }

    protected function buildExportZip($zip, $invoices, $export, $exportInterval)
    {
        $this->addMoneyS3IntoExport($zip, $invoices, $export, $exportInterval);
        $this->addPdfsIntoExport($zip, $invoices, $export, $exportInterval);
    }

    protected function addPdfsIntoExport($zip, $invoices, $export, $exportInterval)
    {
        foreach ($invoices as $invoice) {
            if (!($pdf = $invoice->getPdf())) {
                continue;
            }

            $zip->addFile($pdf->basepath, './'.$invoice->typeName.'/'.$pdf->filename);
        }
    }

    protected function addMoneyS3IntoExport($zip, $invoices, $export, $exportInterval)
    {
        //Add money s3 export
        $zip->addFromString(
            './money_s3_'.$exportInterval.'.xml',
            view('invoices::xml.moneys3_export', compact('invoices', 'export'))->render()
        );
    }
}

?>