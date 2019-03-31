<?php

Route::group([ 'namespace' => 'Gogol\Invoices\Controllers', 'middleware' => ['web', 'admin'] ], function(){
    Route::get('/admin/invoices/get-by-number', 'InvoiceController@getInvoiceByNumber');
    Route::get('/admin/invoices/get-pdf/{id}', 'InvoiceController@generateInvoicePdf');
    Route::get('/admin/invoices/export/{export}', 'InvoiceController@downloadExport');
});