<?php

namespace Gogol\Invoices\Traits;

use Exception;
use \ZipArchive;

trait HasInvoiceExport
{
    public function makeExportZip($invoices, $exportInterval)
    {
        $tempFile = @tempnam('tmp', 'zip');

        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $this->buildExportZip($zip, $invoices, $exportInterval);

        $zip->close();

        if ( file_exists($tempFile) ){
            $data = file_get_contents($tempFile);

            @unlink($tempFile);
        } else {
            throw new Exception('No zip file created.');
        }


        return $data;
    }

    protected function buildExportZip($zip, $invoices, $exportInterval)
    {
        $exports = config('invoices.exports');

        foreach ($this->outputs ?: [] as $output) {
            if ( !($exporter = $exports[$output] ?? null) ){
                continue;
            }

            $class = new $exporter['exporter']($invoices, $this, $exportInterval);

            $class->add($zip);
        }
    }
}

?>