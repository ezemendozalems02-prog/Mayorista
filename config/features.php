<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global Feature Toggles
    |--------------------------------------------------------------------------
    |
    | Enable or disable high-level modules regardless of subscription plans.
    |
    */

    'subscriptions' => env('FEATURE_SUBSCRIPTIONS', true),
    'multi_tenant'  => env('FEATURE_MULTI_TENANT', true),
    'affiliates'    => env('FEATURE_AFFILIATES', true),
    'white_label'   => env('FEATURE_WHITE_LABEL', false),
    'referrals'     => env('FEATURE_REFERRALS', true),

    /*
    |--------------------------------------------------------------------------
    | Business Modules
    |--------------------------------------------------------------------------
    |
    | Standard modules available in the system.
    |
    */

    'modules' => [
        'inventory'    => true,
        'sales'        => true,
        'repairs'      => true,
        'technicians'  => true,
        'reports'      => true,
        'multi_branch' => true,
        'trade_ins'    => true,
    ],

];
