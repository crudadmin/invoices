<?php

namespace Gogol\Invoices\Controllers;

use Gogol\Invoices\Model\Invoice;
use Gogol\Invoices\Model\InvoicesExport;
use Admin\Helpers\File;
use Illuminate\Http\Request;
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

    public function downloadExport(InvoicesExport $export)
    {
        //10 minutes timeout
        ini_set('max_execution_time', 10 * 60);

        $invoices = Admin::getModel('Invoice')->whereDate('created_at', '>=', $export->from)
                            ->where('subject_id', $export->subject_id)
                            ->whereDate('created_at', '<=', $export->to)
                            ->whereIn('type', $export->types ?: [])
                            ->with(['items', 'proformInvoice'])
                            ->orderBy('created_at', 'ASC')
                            ->get();

        $export_interval = $export->from->format('d-m-Y').'_'.$export->to->format('d-m-Y');

        $zip = (new Invoice)->makeExportZip($invoices, $export, $export_interval);

        return response($zip)->withHeaders([
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="export-'.$export_interval.'.zip"'
        ]);
    }
}
