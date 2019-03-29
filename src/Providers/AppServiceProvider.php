<?php

namespace Gogol\Invoices\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\Kernel;
use Admin;

class AppServiceProvider extends ServiceProvider
{
    protected $providers = [

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
        Admin::addModelPath('Gogol\Invoices\Models', __dir__ . '/../Models/**');

        //Boot providers after this provider boot
        $this->bootProviders([
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
        $this->bootFacades();

        $this->bootProviders();

        $this->bootRouteMiddleware();

        $this->addPublishes();

        //Load routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/routes.php');
    }

    private function addPublishes()
    {
        $this->publishes([__DIR__ . '/../Views' => resource_path('vendor/invoices') ], 'invoices.views');
    }

    public function bootFacades()
    {
        $this->app->booting(function()
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();

            foreach ($this->facades as $alias => $facade)
            {
                $loader->alias($alias, $facade);
            }

        });
    }

    public function bootProviders($providers = null)
    {
        foreach ($providers ?: $this->providers as $provider)
        {
            app()->register($provider);
        }
    }

    public function bootRouteMiddleware()
    {
        foreach ($this->routeMiddleware as $name => $middleware)
        {
            $router = $this->app['router'];

            $router->aliasMiddleware($name, $middleware);
        }
    }
}