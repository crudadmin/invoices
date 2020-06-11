<?php

namespace Gogol\Invoices\Commands;

use Gogol\Invoices\Helpers\ImportCountriesHelper;
use Illuminate\Console\Command;

class ImportCountries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'countries:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all countries into countries list';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ( (new ImportCountriesHelper($this))->handle() ){
            $this->line('Countries has been successfuly imported');
        }
    }
}
