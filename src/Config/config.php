<?php

return [
    //Types of invoices
    'invoice_types' => [
        'proform' => 'Proforma',
        'invoice' => 'Ostra faktúra',
        'return' => 'Dobropis',
    ],

    /*
     * Allow clients support in invoices
     */
    'allow_client' => false,

    /*
     * Countries
     */
    'countries' => [
        'sk' => 'Slovenská republika',
        'cz' => 'Česká republika',
        'pl' => 'Poľsko',
        'hu' => 'Maďarsko',
        'at' => 'Rakúsko',
    ],

    /*
     * Available payment methods
     */
    'payment_methods' => [
        'sepa' => 'Bankovým prevodom',
        'cart' => 'Platba kartou',
        'cash' => 'V hotovosti',
    ],

    /*
     * When this property is true
     * you can open invoice in admin panel, and refresh response
     * without changing data in invoice and regeneraiting it
     */
    'testing_pdf' => false,
];