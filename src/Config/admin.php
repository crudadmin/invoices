<?php

return [
    'styles' => [
        'vendor/invoices/admin.css',
    ],

    'groups' => function(){
        return [
            'invoices' => [_('Faktúry'), 'fa-file-text-o']
        ];
    },

    'components' => [
        __DIR__ . '/../Views/components',
    ],

    /*
     * Add invoices translates resources
     */
    'gettext_source_paths' => [
        base_path('config/invoices.php'),
        __DIR__.'/../Config/admin.php',
        __DIR__.'/../Mail',
        __DIR__.'/../Model',
        __DIR__.'/../Views',
    ],

    'gettext_admin_source_paths' => [
        __DIR__.'/../Config/admin.php',
        __DIR__.'/../Admin',
        __DIR__.'/../Mail',
        __DIR__.'/../Views/components',
        __DIR__.'/../Model',
    ],
];