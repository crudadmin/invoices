<?php

namespace Gogol\Invoices\Traits;

use Gogol\Invoices\Mail\SendInvoiceEmail;
use Carbon\Carbon;
use Gogol\Admin\Helpers\File;
use Illuminate\Support\Facades\Mail;
use Mpdf\Mpdf;

trait InvoiceProcessTrait
{
    /*
     * Return snapshot in sha1 of actual invoice
     */
    public function getSnapshotSha()
    {
        //Get just used and allowed columns for snapshot
        $allowed_invoice_columns = array_diff_key($this->getFields(), array_flip(['pdf', 'snapshot_sha', 'email_sent']));

        //Get just allowed data from invoice
        $invoice_data = json_encode(array_intersect_key($this->toArray(), $allowed_invoice_columns));

        //Get data from invoice items, and hide update_at attribute
        $items_data = $this->items->each(function($model){
            $model->setHidden(['updated_at']);
        })->tojson();

        return sha1($invoice_data . $items_data);
    }

    public function generatePDF($auto_save = true, $regenerate = false)
    {
        $snapshot_sha = $this->getSnapshotSha();
        $snapshot_sha_short = substr($snapshot_sha, -10);

        //Generate pdf name
        $filename = $this->number . '-' . $snapshot_sha_short . '.pdf';

        //Check if we can generate new invoice by checking old and new data hash
        if ( $regenerate === false && $this->snapshot_sha == $snapshot_sha )
            return;

        //Generate pdf
        $mpdf = new Mpdf();
        $mpdf->WriteHTML(view('invoices::pdf.invoice_pdf', [
            'settings' => getSettings(),
            'invoice' => $this,
            'items' => $this->items,
        ])->render());

        //Create directory if does not exists
        File::makeDirs($this->filePath('pdf'));

        //Save pdf
        $mpdf->output($this->filePath('pdf', $filename));

        $this->pdf = $filename;
        $this->snapshot_sha = $snapshot_sha;

        if ( $auto_save !== false )
            $this->save();
    }

    public function createInvoice()
    {
        $invoice = $this->replicate();
        $invoice->type = 'invoice';
        $invoice->proform_id = $this->getKey();
        $invoice->paid_at = Carbon::now();
        $invoice->payment_date = $this->payment_date < Carbon::now()->setTime(0, 0, 0) ? Carbon::now() : $this->payment_date;
        $invoice->snapshot_sha = null;
        $invoice->email_sent = null;
        $invoice->save();

        //Clone proform items
        $this->cloneItems($this, $invoice);

        //Generate pdf and save it
        $invoice->setRelations([]);
        $invoice->generatePdf(false);
        $invoice->save();

        return $invoice;
    }

    public function createReturn()
    {
        $invoice = $this->replicate();
        $invoice->type = 'return';
        $invoice->return_id = $this->getKey();
        $invoice->vs = 'DP' . $invoice->vs;
        $invoice->paid_at = null;
        $invoice->payment_date = Carbon::now();
        $invoice->price = -$invoice->price;
        $invoice->price_vat = -$invoice->price_vat;
        $invoice->snapshot_sha = null;
        $invoice->email_sent = null;
        $invoice->save();

        //Clone proform items
        $this->cloneItems($this, $invoice, true);

        //Generate pdf and save it
        $invoice->setRelations([]);
        $invoice->generatePdf(false);
        $invoice->save();

        return $invoice;
    }


    private function cloneItems($row, $invoice, $return = false)
    {
        foreach ($row->items()->get() as $item)
        {
            $item = $item->replicate();
            $item->invoice_id = $invoice->getKey();

            if ( $return == true )
            {
                $item->price *= -1;
                $item->price_vat *= -1;
            }

            $item->save();
        }
    }

    /*
     * Count items sum and set prices
     */
    public function reloadInvoicePrice()
    {
        $price = 0;
        $price_vat = 0;

        foreach ($this->items as $item)
        {
            $price += ($item->quantity * $item->price);
            $price_vat += ($item->quantity * $item->price_vat);
        }

        $this->price = $price;
        $this->price_vat = $price_vat;
    }

    public function setInvoiceNumber()
    {
        $last_invoice = $this->newQuery()
                             ->whereRaw('YEAR(created_at) = YEAR(NOW())')
                             ->whereType($this->type)
                             ->latest()
                             ->first();

        //Get last invoice increment
        $invoice_count = ! $last_invoice ? 0 : (int)substr($last_invoice->number, strlen($last_invoice->numberPrefix) + 4);

        //Set invoice ID
        $next_number = $invoice_count + 1;

        $this->number = date('Y') . str_pad($next_number, 5, 0, STR_PAD_LEFT);
    }

    public function setPaymentDate()
    {
        if ( ! $this->payment_date )
            $this->payment_date = Carbon::now()->addDays(getSettings('payment_term') ?: 0);
    }

    public function sendEmail($email = null, $message = null)
    {
        Mail::to($email ?: $this->email)->send(new SendInvoiceEmail($this, $message));

        //Save that email has been sent
        $this->checkSentEmail($email);
    }

    public function checkSentEmail($email = null)
    {
        $this->update([ 'email_sent' => array_unique(array_merge((array)$this->email_sent, [ $email ?: $this->email ])) ]);
    }
}

?>