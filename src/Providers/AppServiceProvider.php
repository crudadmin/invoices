<?php

namespace Gogol\Invoices\Providers;

use Admin;
use Illuminate\Foundation\Http\Kernel;
use Gogol\Invoices\Commands\ImportCountries;
use Admin\Providers\AdminHelperServiceProvider;
use Gogol\Invoices\Commands\SyncBankTransactions;

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

        $this->app['config']->set('logging.channels.bank_accounts', [
            'driver' => 'single',
            'path' => storage_path('logs/bank_accounts.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ]);
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
            ImportCountries::class,
            SyncBankTransactions::class,
        ]);
    }
}