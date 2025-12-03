<?php

namespace Gogol\Invoices\Traits;

use Carbon\Carbon;
use Illuminate\Validation\Rule;

trait InvoiceProcessTrait
{
    /*
     * Set unique rule for variable symbol field
     */
    protected function vsRuleUnique($row)
    {
        return Rule::unique('invoices')->ignore($row ? $row->getKey() : null)->where(function($query) use($row) {
            $query->whereNull('deleted_at');

            if ( ! $row ) {
                return;
            }

            //Also except invoice/proform
            if ( $row->proform_id ) {
                $query->where('id', '!=', $row->proform_id);
            }

            $query->where('proform_id', '!=', $row->getKey());
        });
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
        $summary = $this->getPricesSummary();

        $this->price = roundInvoicePrice($summary['totalWithoutTax']);
        $this->price_vat = roundInvoicePrice($summary['totalWithTax']);
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

        $lastInvoice = $this->newQuery()
                             ->where('subject_id', $this->subject_id)
                             ->whereRaw('YEAR(created_at) = '.$prefixYearDate->format('Y'))
                             ->whereIn('type', array_wrap($this->getInvoiceNumberCategory()))
                             ->latest('number')
                             ->first();

        $prefix = $this->getOrderYearPrefix($prefixYearDate);

        //Get last invoice increment
        $invoiceCount = ! $lastInvoice ? 0 : (int)substr($lastInvoice->getRawOriginal('number'), strlen($prefix));

        //Set invoice ID
        $nextNumber = substr($invoiceCount + 1, -$pad);

        $this->number = $prefix . str_pad($nextNumber, $pad, 0, STR_PAD_LEFT);
    }

    public function getOrderYearPrefix($date)
    {
        return $date->format('Y');
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
}

?>