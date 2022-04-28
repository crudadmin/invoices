<?php

namespace Gogol\Invoices\Providers;

use Admin;
use Gogol\Invoices\Commands\ImportCountries;
use Illuminate\Foundation\Http\Kernel;
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
        ]);
    }
}