<?php

namespace Gogol\Invoices\Providers;

use Gogol\Invoices\Events\InvoicePaid;
use Admin\Resources\Events\OnAdminUpdate;
use Gogol\Invoices\Events\InvoicePaidWrongly;
use Gogol\Invoices\Listeners\InvoicePaidListener;
use Gogol\Invoices\Listeners\OnAdminUpdateListener;
use Gogol\Invoices\Listeners\InvoicePaidWronglyListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventsServiceProvider extends ServiceProvider
{
    protected $listen = [
        OnAdminUpdate::class => [
            OnAdminUpdateListener::class,
        ],
        InvoicePaid::class => [
            InvoicePaidListener::class,
        ],
        InvoicePaidWrongly::class => [
            InvoicePaidWronglyListener::class,
        ],
    ];
}
