<?php

return [
    'enabled' => true,

    /*
     * Logo and signature height in invoice
     */
    'logo_height' => 60,
    'signature_height' => 180,
    'billing_border_size' => 2,
    'line_padding_size' => 5,
    'issued_by' => true,

    /*
     * Mark path
     */
    'signature_path' => resource_path('/images/signature.png'),

    //Types of invoices
    'invoice_types' => [
        'proform' => [ 'prefix' => 'PF-', 'name' => _('Proforma faktúra'), 'color' => 'silver' ],
        'invoice' => [ 'prefix' => 'FV-', 'name' => _('Faktúra (daňový doklad)'), 'color' => 'var(--primary)' ],
        'advance' => [ 'prefix' => 'DD-', 'name' => _('Daňový doklad k prijatej platbe'), 'color' => 'silver' ],
        'return' => [ 'prefix' => 'DP-', 'name' => _('Dobropis'), 'color' => 'orange' ],
    ],

    'exports' => [
        'money_s3' => [ 'name' => 'Money S3', 'exporter' => Gogol\Invoices\Helpers\Exports\MoneyS3\MoneyS3Export::class ],
        'omega-invoices' => [ 'name' => 'Omega - Faktúry', 'exporter' => Gogol\Invoices\Helpers\Exports\Omega\OmegaInvoiceExport::class ],
        'omega-eud' => [ 'name' => 'Omega - EUD', 'exporter' => Gogol\Invoices\Helpers\Exports\Omega\OmegaEUDExport::class ],
        'pdfs' => [ 'name' => 'PDF - Separatne', 'exporter' => Gogol\Invoices\Helpers\Exports\PDF\PDFExport::class ],
        'pdfs-summary' => [ 'name' => 'PDF - Spolu v 1 PDF', 'exporter' => Gogol\Invoices\Helpers\Exports\PDF\PDFSingleExport::class ],
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
    'testing_pdf' => env('INVOICES_PDF_TEST', false),

    /*
     * QR Code feature
     */
    'qrcode' => false,
    'qrcode_width' => 75,

    /*
     * SK => paybysquare
     * CZ => CZ Format
     */
    'qrcode_type' => 'sk',

    /*
     * Multiple subjects support
     */
    'multi_subjects' => false,

    /*
     * Number length
     */
    'numbers_length' => 5,

    /*
     * Prices configuration
     */
    'prices' => [
        /*
         * First will be calculated final VAT price with all quantity (item VAT PRICE * QUANTITY), and from this price we will calculate final no VAT price
         * This fixes big differences in final price with many (10+) quantities of the same product, when base no vat price is rounded.
         */
        'vat_priority' => true,
    ],

    'banks' => [
        'fio' => [
            'name' => 'FIO Banka (SK)',
            'import' => \Gogol\Invoices\Helpers\Banks\Fio\FioBank::class,
        ],
    ],

    'mail' => [
        // Send email to invoice owner after successfuly received payment on proform
        'auto_mail_after_payment' => true,
    ],
];