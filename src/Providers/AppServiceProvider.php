<?php

namespace Gogol\Invoices\Providers;

use Admin;
use Illuminate\Console\Scheduling\Schedule;
use Admin\Providers\AdminHelperServiceProvider;

class AppServiceProvider extends AdminHelperServiceProvider
{
    protected $providers = [
        PublishServiceProvider::class,
        EventsServiceProvider::class
    ];

    protected $facades = [

    ];

    protected $routeMiddleware = [

    ];

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeAdminConfigs(
            require __DIR__.'/../Config/admin.php'
        );

        Admin::registerAdminModels(__dir__ . '/../Model/**', 'Gogol\Invoices\Model');

        //Boot providers after this provider boot
        $this->registerProviders([
            ViewServiceProvider::class
        ]);

        $this->app['config']->set('logging.channels.invoices', [
            'driver' => 'single',
            'path' => storage_path('logs/invoices.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ]);

        $this->app['config']->set('logging.channels.bank_accounts', [
            'driver' => 'single',
            'path' => storage_path('logs/bank_accounts.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ]);

        $this->registerSchedules();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'invoices'
        );

        $this->registerFacades();

        $this->registerProviders();

        $this->bootRouteMiddleware();

        //Load routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/routes.php');

        $this->commands([
            \Gogol\Invoices\Commands\ImportCountries::class,
            \Gogol\Invoices\Commands\SyncBankTransactions::class,
            \Gogol\Invoices\Commands\PastDueInvoicesCheck::class,
        ]);
    }

    public function registerSchedules()
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            foreach ( config('invoices.banks.scheduler', []) as $time ) {
                $schedule->command('invoices:bank-accounts-sync')->dailyAt($time);
            }

            if ( config('invoices.mail.past_due_invoice.enabled', false) ) {
                $schedule->command('invoices:past-due-invoice')->dailyAt(config('invoices.mail.past_due_invoice.at', '15:00'));
            }
        });
    }
}