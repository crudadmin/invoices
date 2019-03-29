<?php
namespace Gogol\Invoices\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Artisan;

class PublishServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }

    public function boot()
    {
        /*
         * Publishes
         */
        $this->publishes([__DIR__ . '/../Resources/css' => public_path('vendor/invoices') ], 'invoices.resources');

        $this->publishes([__DIR__ . '/../Config/config.php' => config_path('invoices.php') ], 'invoices.config');

        $this->publishes([
            __DIR__ . '/../Views/mail' => resource_path('views/vendor/invoices/mail'),
            __DIR__ . '/../Views/pdf' => resource_path('views/vendor/invoices/pdf'),
            __DIR__ . '/../Views/xml' => resource_path('views/vendor/invoices/xml')
        ], 'invoices.views');
    }
}