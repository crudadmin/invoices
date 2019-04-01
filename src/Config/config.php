<?php

return [
    //Types of invoices
    'invoice_types' => [
        'proform' => [ 'prefix' => 'PF-', 'name' => 'Proforma' ],
        'invoice' => [ 'prefix' => 'FV-', 'name' => 'Faktúra (daňový doklad)' ],
        'return' => [ 'prefix' => 'DP-', 'name' => 'Dobropis' ],
    ],

    /*
     * Allow clients support in invoices
     */
    'clients' => false,

    /*
     * Allow delivery adress in invoice
     */
    'delivery' => false,

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