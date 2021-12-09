<?php

namespace Gogol\Invoices\Traits;

use Admin\Helpers\File;
use Carbon\Carbon;
use Facades\Gogol\Invoices\Helpers\QRCodeGenerator;
use Gogol\Invoices\Mail\SendInvoiceEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Log;
use Mpdf\Mpdf;
use Throwable;
use chillerlan\QRCode\QROptions;

trait InvoiceProcessTrait
{
    /*
     * Set unique rule for variable symbol field
     */
    protected function vsRuleUnique($row)
    {
        return Rule::unique('invoices')->ignore($row ? $row->getKey() : null)->where(function($query) use($row) {
            $query->whereNull('deleted_at');

            if ( ! $row )
                return;

            //Also except invoice/proform
            if ( $row->proform_id )
                $query->where('id', '!=', $row->proform_id);
            else
                $query->where('proform_id', '!=', $row->getKey());
        });
    }

    /*
     * Return snapshot in sha1 of actual invoice
     */
    protected function getSnapshotSha()
    {
        //Get just used and allowed columns for snapshot
        $not_allowed = array_flip(['pdf', 'snapshot_sha', 'email_sent', 'email']);
        $allowed_invoice_columns = array_diff_key($this->getFields(), $not_allowed);

        //Get just allowed data from invoice
        $invoice_data = array_intersect_key($this->toArray(), $allowed_invoice_columns);
        $invoice_data = array_filter($invoice_data);
        ksort($invoice_data);

        $invoice_data = json_encode($invoice_data);

        //Get data from invoice items, and hide updated_at attribute
        $items_data = $this->items->map(function($item){
            $item = array_filter($item->setHidden(['id', 'updated_at', 'created_at'])->toArray());

            ksort($item);

            return $item;
        })->tojson();

        return sha1($invoice_data . $items_data);
    }

    /**
     * Generate PDF of invoice
     * @param  boolean $auto_save        auto save pdf filename
     * @param  boolean $force_regenerate forced regeneration of pdf
     */
    public function generatePDF($auto_save = true, $force_regenerate = false)
    {
        $snapshot_sha = $this->getSnapshotSha();
        $snapshot_sha_short = substr($snapshot_sha, -10);

        //Generate pdf name
        $filename = $this->number . '-' . $snapshot_sha_short . '.pdf';

        //Check if we can generate new invoice by checking old and new data hash
        if ( $this->pdf && $this->pdf->exists() && $force_regenerate === false && $this->snapshot_sha == $snapshot_sha )
            return;

        //Try load all removed relationship rows
        try {
            $this->loadDeletedInvoiceAttributes();
        } catch (Throwable $error){
            Log::error($error);
        }

        //Generate pdf
        $mpdf = new Mpdf([
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'mirrorMargins' => true,
            'defaultfooterline' => false,
        ]);

        $mpdf->setFooter('<table style="width: 100%">
            <tr>
                <!-- <td align="left" style="font-style: normal">'.$this->number.'</td> -->
                <td align="right" style="font-weight: bold; font-style: normal">{PAGENO} / {nbpg}</td>
            </tr>
        </table>');

        $mpdf->WriteHTML(view('invoices::pdf.invoice_pdf', [
            'settings' => $this->subject,
            'invoice' => $this,
            'items' => $this->items,
            'qrimage' => QRCodeGenerator::generate($this),
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

    private function loadDeletedInvoiceAttributes()
    {
        foreach ($this->getFields() as $key => $field) {
            if ( $this->hasFieldParam($key, 'belongsTo') == false ){
                continue;
            }

            $relation = trim_end($key, '_id');

            if (
                $this->{$key} //Exists row id
                && !$this->{$relation} //Row is not found, probably has been deleted
                && @$this->getRelation($relation) //Relations builder must exists
            ){
                $this->load([
                    $relation => function($query){
                        $query->withTrashed()->withUnpublished();
                    },
                ]);
            }
        }
    }

    /**
     * Clone items from given invoice to actual invoice
     * @param  [type]  $row     copy from invoice
     * @param  [type]  $invoice copy to invoice
     * @param  boolean $return  set as return items, price will be multipled by -1
     */
    protected function cloneItems($row, $invoice, $return = false)
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
            $price += calculateWithoutVat($item->quantity * $item->price_vat, $item->vat);
            $price_vat += $item->quantity * $item->price_vat;
        }

        $this->price = $price;
        $this->price_vat = $price_vat;
    }

    /*
     * Separate invoices by type
     */
    public function getInvoiceNumberCategory()
    {
        return $this->type;
    }

    /*
     * Generate invoice number increment
     */
    public function setInvoiceNumber()
    {
        //If number is already set
        if ( $this->getRawOriginal('number') )
            return;

        $pad = config('invoices.numbers_length', 5);

        $prefixYearDate = $this->created_at ? $this->created_at : Carbon::now();

        $last_invoice = $this->newQuery()
                             ->where('subject_id', $this->subject_id)
                             ->whereRaw('YEAR(created_at) = '.$prefixYearDate->format('Y'))
                             ->whereIn('type', array_wrap($this->getInvoiceNumberCategory()))
                             ->latest('id')
                             ->first();

        $prefix = $prefixYearDate->format('Y');

        //Get last invoice increment
        $invoice_count = ! $last_invoice ? 0 : (int)substr($last_invoice->getRawOriginal('number'), strlen($prefix));

        //Set invoice ID
        $next_number = substr($invoice_count + 1, -$pad);

        $this->number = $prefix . str_pad($next_number, $pad, 0, STR_PAD_LEFT);
    }

    /*
     * Automatically set payment date
     */
    public function setPaymentDate()
    {
        if ( ! $this->payment_date ) {
            $this->payment_date = Carbon::now()->addDays($this->subject->payment_term ?: 0);
        }
    }

    /*
     * Send email with invoice on given/saved email adress
     */
    public function sendEmail($email = null, $message = null)
    {
        Mail::to($email ?: $this->email)->send(new SendInvoiceEmail($this, $message));

        //Save that email has been sent
        $this->checkSentEmail($email);
    }

    /*
     * Check if given/saved email is checked
     */
    public function isEmailChecked($email = null)
    {
        return is_array($this->email_sent) && in_array($email ?: $this->email, $this->email_sent);
    }

    /*
     * Check given email as sent
     */
    public function checkSentEmail($email = null)
    {
        $this->update([ 'email_sent' => array_unique(array_merge((array)$this->email_sent, [ $email ?: $this->email ])) ]);
    }

    /*
     * Return GUID of invoice
     */
    public function getGUID(){
        if ( $this->guid )
            return $this->guid;

        if (function_exists('com_create_guid')){
            $guid = com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = chr(123)
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);
            $guid = $uuid;
        }

        $this->guid = $guid;
        $this->save();

        return $guid;
    }

    /*
     * Set new vs and check if can be setted
     * if vs exists, then regenerate new unexisting vs
     */
    public function setNewVs($number = null)
    {
        //Check if VS exists, if yes, then generate random
        while (empty($number) || $this->newQuery()->where('vs', $number)->exists())
            $number = date('Ym') . rand(0000, 9999);

        $this->vs = $number;
    }

    public function getInvoiceQrCodeOptions()
    {
        return new QROptions([
            'addQuietzone' => false,
            'scale' => 4,
        ]);
    }
}

?>