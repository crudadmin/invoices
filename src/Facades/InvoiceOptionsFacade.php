<?php
namespace Gogol\Invoices\Facades;

use Illuminate\Support\Facades\Facade;

class InvoiceOptionsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'admin.invoices';
    }
}