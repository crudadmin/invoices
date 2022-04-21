<?php

namespace Gogol\Invoices\Traits;

use Admin\Helpers\File;
use Log;
use Throwable;
use Mpdf\Mpdf;
use Facades\Gogol\Invoices\Helpers\QRCodeGenerator;
use chillerlan\QRCode\QROptions;
use Localization;

trait HasInvoicePdf
{
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
        //Make sure localization is booted
        Localization::boot();

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
            'additionalRows' => $this->getAdditionalRows(),
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

    private function getAdditionalRows()
    {
        $supplierRows = config('invoices.additional_rows.supplier') ?: function(){};
        $supplierRows = $supplierRows($this, $this->subject) ?: [];

        $customerRows = config('invoices.additional_rows.customer') ?: function(){};
        $customerRows = $customerRows($this, $this->subject) ?: [];

        $totalRows = max(count($supplierRows), count($customerRows));
        $rows = [];
        for ($i=0; $i < $totalRows; $i++) {
            $rows[] = [
                'supplier' => $supplierRows[$i] ?? null,
                'customer' => $customerRows[$i] ?? null,
            ];
        }

        return $rows;
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

    public function getInvoiceQrCodeOptions()
    {
        return new QROptions([
            'addQuietzone' => false,
            'scale' => 4,
        ]);
    }
}

?>