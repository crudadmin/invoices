<?php

namespace Gogol\Invoices\Events;

use Gogol\Invoices\Model\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class InvoicePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invoice;
    public $transactions;
    public $paidAmount;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($invoice, $transactions, $paidAmount)
    {
        $this->invoice = $invoice;
        $this->transactions = $transactions;
        $this->paidAmount = $paidAmount;
    }

    public function getInvoice() : Invoice
    {
        return $this->invoice;
    }

    public function getPaidAmount() : float
    {
        return $this->paidAmount;
    }

    public function getTransactions() : Collection
    {
        return $this->transactions;
    }
}
