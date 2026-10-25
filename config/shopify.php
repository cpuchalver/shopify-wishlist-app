<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shopify App Configuration
    |--------------------------------------------------------------------------
    */

    'app_name' => env('SHOPIFY_APP_NAME', 'Wishlist App'),
    'app_url' => env('APP_URL'),
    'api_key' => env('SHOPIFY_API_KEY'),
    'api_secret_key' => env('SHOPIFY_API_SECRET_KEY'),
    'api_version' => env('SHOPIFY_API_VERSION', '2024-10'),
    'scopes' => env('SHOPIFY_SCOPES', 'write_products,read_products,read_customers,write_customers'),
    'redirect_uri' => env('SHOPIFY_REDIRECT_URI', env('APP_URL') . '/shopify/auth/callback'),
    'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */

    'webhooks' => [
        'app_uninstalled' => '/shopify/uninstall',
        'customers/create' => '/shopify/webhooks/customers',
        'customers/update' => '/shopify/webhooks/customers',
    ],
];
