<?php

namespace Gogol\Invoices\Helpers\Banks\Concerns;

use Admin;
use Exception;
use Carbon\Carbon;
use Gogol\Invoices\Events\InvoicePaid;
use Gogol\Invoices\Events\InvoicePaidWrongly;

trait HasTransactions
{
    protected $transactions = [];

    protected $syncAllTransactions = false;

    /**
     * Set transactions sync all flag
     *
     * @param  bool $syncAll
     * @return self
     */
    public function setSyncAllTransactions($syncAll = false)
    {
        $this->syncAllTransactions = $syncAll;

        return $this;
    }

    /**
     * Sync unpaid invoices transactions
     *
     * @return void
     */
    public function syncUnpaidInvoices()
    {
        $unpaidInvoices = $this->getUnpaidInvoices();

        if ( $unpaidInvoices->isEmpty() ) {
            return $this->log('No unpaid invoices found to sync');
        }

        $from = new Carbon($unpaidInvoices->min('created_at'));
        $to = now();

        $this->log('Found ' . $unpaidInvoices->count() . ' unpaid invoices to sync from date ' . $from->format('Y-m-d'));

        try {
            $transactions = $this->getTransactions($from, $to);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        $unpaidInvoices->each(function($invoice) use ($transactions) {
            $this->findInvoiceTransaction($invoice, $transactions);
        });

        // Update last sync date
        $this->account->update([ 'last_sync_at' => now() ]);

        return true;
    }

    /**
     * Receive unpaid invoices
     *
     * @return void
     */
    private function getUnpaidInvoices()
    {
        $subjects = $this->account->invoicesSettings;

        $unpaidInvoices = Admin::getModel('Invoice')
                ->whereIn('subject_id', $subjects->pluck('id'))
                ->whereIn('type', ['invoice', 'proform', 'return'])
                ->whereNull('paid_at')
                ->get();

        return $unpaidInvoices;
    }

    /**
     * Pair transactionss with invoice
     *
     * @param  mixed $invoice
     * @param  mixed $transactions
     * @return void
     */
    private function findInvoiceTransaction($invoice, $transactions)
    {
        $pairedTransactions = collect($transactions)->where('vs', $invoice->vs);

        // No transactions found for invoice
        if ( $pairedTransactions->isEmpty() ) {
            return;
        }

        $totalPaidAmount = $pairedTransactions->sum('amount');

        try {
            // Exact sum match
            if ( $totalPaidAmount == $invoice->price_vat ) {
                event(new InvoicePaid($invoice, $pairedTransactions, $totalPaidAmount));

                $this->log('Invoice ' . $invoice->number . ' paid with exact sum match.');
            } else {
                event(new InvoicePaidWrongly($invoice, $pairedTransactions, $invoice->price_vat, $totalPaidAmount));

                $this->log('Invoice ' . $invoice->number . ' paid with different sum match. Expected: ' . $invoice->price_vat . ' Paid: ' . $totalPaidAmount);
            }
        } catch (Exception $e) {
            report($e);

            $this->error($e->getMessage());
        }
    }
}