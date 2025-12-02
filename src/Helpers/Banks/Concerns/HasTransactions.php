<?php

namespace Gogol\Invoices\Helpers\Banks\Concerns;

use Admin;
use Exception;
use Carbon\Carbon;
use Gogol\Invoices\Events\InvoicePaid;

trait HasTransactions
{
    protected $transactions = [];

    public function syncUnpaidInvoices()
    {
        $unpaidInvoices = $this->getUnpaidInvoices();

        if ( $unpaidInvoices->isEmpty() ) {
            return false;
        }

        $from = new Carbon($unpaidInvoices->min('created_at'));
        $to = now();

        try {
            $transactions = $this->getTransactions($from, $to);
        } catch (Exception $e) {
            $this->error('Error getting transactions for account ' . $this->account->name . ': ' . $e->getMessage());

            return false;
        }

        $unpaidInvoices->each(function($invoice) use ($transactions) {
            $this->findInvoiceTransaction($invoice, $transactions);
        });

        return true;
    }

    private function getUnpaidInvoices()
    {
        $subjects = $this->account->invoicesSettings;

        $unpaidInvoices = Admin::getModel('Invoice')
                ->select('id', 'number', 'price_vat', 'type', 'vs', 'created_at')
                ->whereIn('subject_id', $subjects->pluck('id'))
                ->whereIn('type', ['invoice', 'proform'])
                ->whereNull('paid_at')
                // Dont sync invoices from previous years
                ->whereDate('created_at', '>=', now()->addYears(-1)->startOfYear())
                ->get();

        return $unpaidInvoices;
    }

    private function findInvoiceTransaction($invoice, $transactions)
    {
        $pairedTransactions = collect($transactions)->where('vs', $invoice->vs);

        // No transactions found for invoice
        if ( $pairedTransactions->isEmpty() ) {
            return;
        }

        $totalPaidAmount = $pairedTransactions->sum('amount');

        // Exact sum match
        if ( $totalPaidAmount == $invoice->price_vat ) {
            event(new InvoicePaid($invoice, $pairedTransactions));

            $this->log('Invoice ' . $invoice->number . ' paid with exact sum match.');
        } else {
            $this->log('Invoice ' . $invoice->number . ' paid with different sum match. Expected: ' . $invoice->price_vat . ' Paid: ' . $totalPaidAmount);

            $invoice->update([
                'paid_amount' => $totalPaidAmount,
            ]);
        }
    }
}