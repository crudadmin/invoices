<?php

namespace Gogol\Invoices\Providers;

use Admin\Resources\Events\OnAdminUpdate;
use Gogol\Invoices\Listeners\OnAdminUpdateListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventsServiceProvider extends ServiceProvider
{
    protected $listen = [
        OnAdminUpdate::class => [
            OnAdminUpdateListener::class,
        ],
    ];
}
