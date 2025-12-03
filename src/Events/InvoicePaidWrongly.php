<?php

namespace Gogol\Invoices\Events;

use Gogol\Invoices\Model\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class InvoicePaidWrongly
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invoice;
    public $transactions;
    public $expectedAmount;
    public $paidAmount;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($invoice, $transactions, $expectedAmount, $paidAmount)
    {
        $this->invoice = $invoice;
        $this->transactions = $transactions;
        $this->expectedAmount = $expectedAmount;
        $this->paidAmount = $paidAmount;
    }

    public function getInvoice() : Invoice
    {
        return $this->invoice;
    }

    public function getTransactions() : Collection
    {
        return $this->transactions;
    }
}
