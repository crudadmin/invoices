<?php

return [
    /*
     * Logo and signature height in invoice
     */
    'logo_height' => 60,
    'signature_height' => 180,

    /*
     * Mark path
     */
    'signature_path' => resource_path('/images/signature.png'),

    //Types of invoices
    'invoice_types' => [
        'proform' => [ 'prefix' => 'PF-', 'name' => 'Proforma faktúra' ],
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
     * Set default invoice item vat
     */
    'default_item_vat' => 0,

    /*
     * When this property is true
     * you can open invoice in admin panel, and refresh response
     * without changing data in invoice and regeneraiting it
     */
    'testing_pdf' => false,

    /*
     * Does not round decimals for vat price in products. For multiple quantity total price may be different
     * true => (1.11*1.2 => 1.332)*6=>7.99 in total
     * false => (1.11*1.2 => 1.33)*6=>7.98 in total
     */
    'round_summary' => true,

    /*
     * QR Code feature
     */
    'gqcode' => false,
];