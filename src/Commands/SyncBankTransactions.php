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
    protected $signature = 'invoices:bank-accounts-sync {--account= : Sync specific account} {--all : Sync all transactions}';

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
        $this->info('Synchronization started.');

        $accounts = InvoicesAccount::whereNotNull('token')->when($this->option('account'), function($query) {
            $query->where('id', $this->option('account'));
        })->get();

        $syncAll = $this->option('all') ? true : false;

        $accounts->each(function($account) use ($syncAll) {
            $account->syncAccount($this, $syncAll);
        });

        $this->line('Bank accounts transactions has been successfuly synced');
    }
}
