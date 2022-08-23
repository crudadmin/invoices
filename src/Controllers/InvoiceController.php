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

        if ( config('invoices.testing_pdf', false) === true ) {
            return response($pdf->get(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$pdf->filename.'"'
            ]);
        }

        return redirect($pdf);
    }

    public function downloadExport($id)
    {
        $export = Admin::getModel('InvoicesExport')->find($id);

        //10 minutes timeout
        ini_set('max_execution_time', 10 * 60);
        ini_set('memory_limit', '2G');
        ini_set('display_errors', 1);

        $invoices = Admin::getModel('Invoice')->whereDate('created_at', '>=', $export->from)
                            ->where('subject_id', $export->subject_id)
                            ->whereDate('created_at', '<=', $export->to)
                            ->whereIn('type', $export->types ?: [])
                            ->with(['items', 'proformInvoice'])
                            ->orderBy('created_at', 'ASC')
                            ->get();

        $exportInterval = $export->from->format('d-m-Y').'_'.$export->to->format('d-m-Y');

        $zip = $export->makeExportZip($invoices, $exportInterval);

        return response($zip)->withHeaders([
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="export-'.$exportInterval.'.zip"'
        ]);
    }
}
