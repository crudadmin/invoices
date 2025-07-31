<?php

Route::group([ 'namespace' => 'Gogol\Invoices\Controllers', 'middleware' => ['web', 'admin.providers'] ], function(){
    Route::get('/admin/invoices/pdf/{id}/{hash?}', 'InvoiceController@generateInvoicePdf');
});

Route::group([ 'namespace' => 'Gogol\Invoices\Controllers', 'middleware' => ['web', 'admin'] ], function(){
    Route::get('/admin/invoices/get-by-number', 'InvoiceController@getInvoiceByNumber');
    Route::get('/admin/invoices/export/{export}', 'InvoiceController@downloadExport');
});