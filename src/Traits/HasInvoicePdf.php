<?php

namespace Gogol\Invoices\Traits;

use Admin\Helpers\File;
use Log;
use Throwable;
use Mpdf\Mpdf;
use Gogol\Invoices\Helpers\QRCodeGenerator;
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

    public function getMpdf()
    {
        return new Mpdf(
            $this->getMpdfOptions()
        );
    }

    public function getMpdfOptions()
    {
        return [
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'mirrorMargins' => true,
            'defaultfooterline' => false,
        ];
    }

    public function getInvoiceTemplate()
    {
        $summary = $this->getInvoiceSummary();

        $qr = new QRCodeGenerator($this, $summary['totalWithTax']);

        return view('invoices::pdf.invoice_pdf', [
            'settings' => $this->subject,
            'invoice' => $this,
            'items' => $this->items,
            'qrimage' => $summary['totalWithTax'] > 0 ? $qr->generate() : null,
            'additionalRows' => $this->getAdditionalRows(),
            'summary' => $summary,
        ])->render();
    }

    public function getPricesSummary()
    {
        $withTax = [];
        $withoutTax = [];
        $tax = [];

        foreach( $this->items as $item ) {
            foreach ([&$withTax, &$withoutTax, &$tax] as &$refValue) {
                if ( ! array_key_exists(''.$item->vat, $refValue) ) {
                    $refValue[$item->vat] = 0;
                }
            }

            //Round order item price by configuration
            $withoutTax[$item->vat] += $item->price * $item->quantity;
            $withTax[$item->vat] += $item->totalPriceWithTax;
        }

        foreach ($withTax as $taxValue => $value) {
            $tax[$taxValue] = $value - $withoutTax[$taxValue];
        }

        ksort($withTax);

        $totalWithTax = array_sum($withTax);
        $totalWithoutTax = array_sum($withoutTax);

        return [
            'totalWithTax' => $totalWithTax,
            'totalWithoutTax' => $totalWithoutTax,
            'withTax' => $withTax,
            'withoutTax' => $withoutTax,
            'tax' => $tax,
        ];
    }

    public function getInvoiceSummary()
    {
        $summary = $this->getPricesSummary();

        if ( in_array($this->type, ['invoice', 'advance']) && $this->paid_at ){
            $summary['totalWithoutTax'] = 0;
            $summary['totalWithTax'] = 0;
        } else if ( $this->canSubtractInvoice ){
            $summary['totalWithoutTax'] -= $this->proform->price;
            $summary['totalWithTax'] -= $this->proform->price_vat;
        }

        return $summary;
    }

    /**
     * Generate PDF of invoice
     * @param  boolean $auto_save        auto save pdf filename
     * @param  boolean $forceRegenerate forced regeneration of pdf
     */
    public function generatePDF($auto_save = true, $forceRegenerate = false)
    {
        $this->withLocale(function() use ($auto_save, $forceRegenerate) {
            $snapshot_sha = $this->getSnapshotSha();
            $snapshot_sha_short = substr($snapshot_sha, -10);

            //Generate pdf name
            $filename = $this->number . '-' . $snapshot_sha_short . '.pdf';

            //Check if we can generate new invoice by checking old and new data hash
            if ( $this->pdf && $this->pdf->exists() && $forceRegenerate === false && $this->snapshot_sha == $snapshot_sha ){
                return $this;
            }

            //Try load all removed relationship rows
            try {
                $this->loadDeletedInvoiceAttributes();
            } catch (Throwable $error){
                Log::error($error);
            }

            //Generate pdf
            $mpdf = $this->getMpdf();

            $this->setPdfFooter($mpdf);

            $mpdf->WriteHTML(
                $this->getInvoiceTemplate()
            );

            //Save pdf into storage
            $this->getFieldStorage('pdf')->put(
                $this->getStorageFilePath('pdf', $filename),
                $mpdf->output(null, 'S')
            );

            $this->pdf = $filename;
            $this->snapshot_sha = $snapshot_sha;

            if ( $auto_save !== false ) {
                $this->save();
            }
        });

        return $this;
    }

    public function setPdfFooter($mpdf)
    {
        $mpdf->setFooter('<table style="width: 100%">
            <tr>
                <!-- <td align="left" style="font-style: normal">'.$this->number.'</td> -->
                <td align="right" style="font-weight: bold; font-style: normal">{PAGENO} / {nbpg}</td>
            </tr>
        </table>');
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