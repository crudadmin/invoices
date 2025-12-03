<?php

namespace Gogol\Invoices\Helpers\Banks;

use Gogol\Invoices\Model\InvoicesAccount;
use Gogol\Invoices\Helpers\Banks\Concerns\HasLogger;
use Gogol\Invoices\Helpers\Banks\Concerns\HasRequestCache;
use Gogol\Invoices\Helpers\Banks\Concerns\HasTransactions;

abstract class BankAccount
{
    use HasRequestCache,
        HasLogger,
        HasTransactions;

    public function __construct(InvoicesAccount $account)
    {
        $this->account = $account;

        $this->setClient($account->token);
    }

    /**
     * Set guzzle client
     *
     * @param  mixed $token
     * @return void
     */
    abstract protected function setClient($token);

    /**
     * Sync all integration data
     *
     * @return void
     */
    public function sync()
    {
        $this->syncUnpaidInvoices();

        return $this;
    }
}