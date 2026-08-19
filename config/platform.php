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
            ? env('APP_NAME', 'Vortex Control Phone') 
            : 'Vortex Control Phone',
        'company_name' => env('PLATFORM_COMPANY', 'Vortex Control'),
        'logo' => env('PLATFORM_LOGO', '/img/LogoVortexControlPhone.png'),
        'logo_white' => '/img/LogoVortexControlPhoneBlanco.png',
        'favicon' => env('PLATFORM_FAVICON', '/img/LogoVortexControlPhone.png'),
        'colors' => [
            'primary' => env('PLATFORM_COLOR_PRIMARY', '#a855f7'),
            'secondary' => env('PLATFORM_COLOR_SECONDARY', '#7c3aed'),
            'dark' => '#0F0F12',
            'dark-alt' => '#1F1F23',
        ],
        'support_email' => env('PLATFORM_SUPPORT_EMAIL', 'soporte@vortexcontrol.com'),
        'footer_text' => env('PLATFORM_FOOTER', '© ' . date('Y') . ' Vortex Control Phone. Todos los derechos reservados.'),
    ],
];
