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
        $exports = config('invoices.exports');

        foreach ($export->outputs as $output) {
            if ( !($exporter = $exports[$output] ?? null) ){
                continue;
            }

            $class = new $exporter['exporter']($invoices, $export, $exportInterval);

            $class->add($zip);
        }
    }
}

?>