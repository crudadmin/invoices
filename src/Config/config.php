<?php

return [
    /*
     * Logo and signature height in invoice
     */
    'logo_height' => 60,
    'signature_height' => 180,
    'billing_border_size' => 2,
    'issued_by' => true,

    /*
     * Mark path
     */
    'signature_path' => resource_path('/images/signature.png'),

    //Types of invoices
    'invoice_types' => [
        'proform' => [ 'prefix' => 'PF-', 'name' => _('Proforma faktúra') ],
        'invoice' => [ 'prefix' => 'FV-', 'name' => _('Faktúra (daňový doklad)') ],
        'return' => [ 'prefix' => 'DP-', 'name' => _('Dobropis') ],
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
     * Does not round decimals for vat price in products. For multiple quantity total price may be different
     * true => (1.11*1.2 => 1.332)*6=>7.99 in total
     * false => (1.11*1.2 => 1.33)*6=>7.98 in total
     */
    'round_summary' => true,

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
];