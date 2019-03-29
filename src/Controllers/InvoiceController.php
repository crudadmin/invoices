<?php

namespace Gogol\Invoices\Controllers;

use Gogol\Invoices\Model\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function getInvoiceByNumber()
    {
        $number = request('number');

        return Invoice::where('type', 'invoice')->where('number', $number)->first();
    }

    public function generateInvoicePdf($id)
    {
        $invoice = Invoice::findOrFail($id);

        if ( ! ($pdf = $invoice->getPdf(config('invoices.testing_pdf', false))) )
            abort(404);

        if ( config('invoices.testing_pdf', false) === true )
            return response()->file($pdf->path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$pdf->filename.'"'
            ]);

        return redirect($pdf);
    }
}
