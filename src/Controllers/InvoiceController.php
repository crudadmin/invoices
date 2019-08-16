<?php

namespace Gogol\Invoices\Controllers;

use Gogol\Invoices\Model\Invoice;
use Gogol\Invoices\Model\InvoicesExport;
use Admin\Helpers\File;
use Illuminate\Http\Request;
use \ZipArchive;
use Admin;

class InvoiceController extends Controller
{
    public function getInvoiceByNumber()
    {
        $number = request('number');

        return Invoice::where('type', 'invoice')->where('number', $number)->first();
    }

    public function generateInvoicePdf($id)
    {
        $invoice = Admin::getModel('Invoice')->findOrFail($id);

        if ( ! ($pdf = $invoice->getPdf(config('invoices.testing_pdf', false))) )
            abort(404);

        if ( config('invoices.testing_pdf', false) === true )
            return response()->file($pdf->path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$pdf->filename.'"'
            ]);

        return redirect($pdf);
    }

    private function makeZip($invoices, $export, $export_interval)
    {
        $temp_file = @tempnam('tmp', 'zip');

        $zip = new ZipArchive();
        $zip->open($temp_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        //Add money s3 export
        $zip->addFromString('./money_s3_'.$export_interval.'.xml', view('invoices::xml.moneys3_export', compact('invoices', 'export'))->render());

        foreach ($invoices as $invoice)
        {
            if (!($pdf = $invoice->getPdf()))
                continue;

            $zip->addFile($pdf->path, './'.$invoice->typeName.'/'.$pdf->filename);
        }

        // Zip archive will be created only after closing object
        $zip->close();

        $data = file_get_contents($temp_file);

        @unlink($temp_file);

        return $data;
    }

    public function downloadExport(InvoicesExport $export)
    {
        $invoices = Admin::getModel('Invoice')->whereDate('created_at', '>=', $export->from)
                           ->whereDate('created_at', '<=', $export->to)
                           ->whereIn('type', $export->types ?: [])
                           ->with(['items', 'proformInvoice'])
                           ->orderBy('created_at', 'ASC')
                           ->get();

        $export_interval = $export->from->format('d-m-Y').'_'.$export->to->format('d-m-Y');

        $zip = $this->makeZip($invoices, $export, $export_interval);

        return response($zip)->withHeaders([
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="export-'.$export_interval.'.zip"'
        ]);
    }
}
