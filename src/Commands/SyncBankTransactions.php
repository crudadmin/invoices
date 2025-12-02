<?php

namespace Gogol\Invoices\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice\InvoicesAccount;

class SyncBankTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:bank-accounts-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync bank accounts transactions';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $accounts = InvoicesAccount::whereNotNull('token')->get();

        $accounts->each(function($account) {
            $account->syncAccount();
        });

        $this->line('Bank accounts transactions has been successfuly synced');
    }
}
