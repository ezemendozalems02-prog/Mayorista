<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Platform Distribution Mode
    |--------------------------------------------------------------------------
    |
    | This value determines the behavior of the platform.
    |
    | saas: Standard multi-tenant subscription platform.
    | single_license: Single-company ERP mode (subscriptions disabled).
    | white_label: Rebrandable instance for reselling.
    |
    */

    'mode' => env('PLATFORM_MODE', 'saas'),

    /*
    |--------------------------------------------------------------------------
    | Branding Configuration
    |--------------------------------------------------------------------------
    |
    | These values are used to customize the interface dynamically.
    |
    */

    'brand' => [
        'name' => env('PLATFORM_MODE') === 'single_license'
            ? env('APP_NAME', 'Mito Yamile')
            : 'Mito Yamile',
        'company_name' => env('PLATFORM_COMPANY', 'Mito Yamile'),
        'logo' => env('PLATFORM_LOGO', '/img/logo-mito-yamile.svg'),
        'logo_white' => '/img/logo-mito-yamile-blanco.svg',
        'favicon' => env('PLATFORM_FAVICON', '/img/logo-mito-yamile.svg'),
        'colors' => [
            'primary' => env('PLATFORM_COLOR_PRIMARY', '#a855f7'),
            'secondary' => env('PLATFORM_COLOR_SECONDARY', '#7c3aed'),
            'dark' => '#0F0F12',
            'dark-alt' => '#1F1F23',
        ],
        // TODO: reemplazar por el email de soporte real de Mito Yamile cuando lo tengan definido.
        'support_email' => env('PLATFORM_SUPPORT_EMAIL'),
        'footer_text' => env('PLATFORM_FOOTER', '© ' . date('Y') . ' Mito Yamile. Todos los derechos reservados.'),
    ],
];
