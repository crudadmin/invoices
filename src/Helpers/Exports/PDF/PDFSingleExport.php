<?php

namespace Gogol\Invoices\Helpers\Exports\PDF;

use Gogol\Invoices\Helpers\Exports\InvoiceExport;
use Mpdf\Mpdf;
use setasign\Fpdi\PdfParser\StreamReader;

class PDFSingleExport extends InvoiceExport
{
    public function add($zip)
    {
        if ( $path = $this->mergedPDFPath($zip) ) {
            $zip->addFile($path, './'.str_slug(_('sumarne_faktury')).'.pdf');
        }
    }

    public function mergedPDFPath($zip)
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

        if ( $this->invoices->count() == 0) {
            return;
        }

        $filesTotal = sizeof($this->invoices);
        $fileNumber = 1;

        if (!file_exists($finalPath)) {
            $handle = fopen($finalPath, 'w');
            fclose($handle);
        }

        foreach ($this->invoices as $invoice) {
            $stream = StreamReader::createByString(
                $invoice->getPdf()->get()
            );

            $pagesInFile = $pdf->SetSourceFile($stream);

            for ($i = 1; $i <= $pagesInFile; $i++) {
                $tplId = $pdf->importPage($i);
                $pdf->UseTemplate($tplId);

                if (($fileNumber < $filesTotal) || ($i != $pagesInFile)) {
                    $pdf->WriteHTML('<pagebreak />');
                }
            }

            $fileNumber++;
        }

        $pdf->Output($finalPath);

        return $finalPath;
    }
}