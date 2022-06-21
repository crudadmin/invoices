<?php

namespace Gogol\Invoices\Helpers\Exports\PDF;

use Gogol\Invoices\Helpers\Exports\InvoiceExport;
use Mpdf\Mpdf;

class PDFSingleExport extends InvoiceExport
{
    public function add($zip)
    {
        $invoicesToMerge = $this->invoices->map(function($file){
            return $file->getPdf()->basepath;
        });

        if ( $path = $this->mergedPDFPath($zip, $invoicesToMerge) ) {
            $zip->addFile($path, './'.str_slug(_('sumarne_faktury')).'.pdf');
        }
    }

    public function mergedPDFPath($zip, $mergeFiles)
    {
        $pdf = new Mpdf([
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'mirrorMargins' => true,
            'defaultfooterline' => false,
        ]);

        $finalPath = @tempnam('tmp', 'pdfs_merge');

        if ( $mergeFiles->count() == 0) {
            return;
        }

        $filesTotal = sizeof($mergeFiles);
        $fileNumber = 1;

        if (!file_exists($finalPath)) {
            $handle = fopen($finalPath, 'w');
            fclose($handle);
        }

        foreach ($mergeFiles as $fileName) {
            if (file_exists($fileName)) {
                $pagesInFile = $pdf->SetSourceFile($fileName);

                for ($i = 1; $i <= $pagesInFile; $i++) {
                    $tplId = $pdf->importPage($i);
                    $pdf->UseTemplate($tplId);

                    if (($fileNumber < $filesTotal) || ($i != $pagesInFile)) {
                        $pdf->WriteHTML('<pagebreak />');
                    }
                }

                $fileNumber++;
            }
        }

        $pdf->Output($finalPath);

        return $finalPath;
    }
}