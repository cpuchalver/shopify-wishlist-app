<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shopify App Credentials
    |--------------------------------------------------------------------------
    |
    | Credentials from the Shopify Partner Dashboard for this app.
    |
    */

    'api_key' => env('SHOPIFY_API_KEY'),
    'api_secret' => env('SHOPIFY_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Access Scopes
    |--------------------------------------------------------------------------
    |
    | Comma-separated list of access scopes requested during installation.
    |
    */

    'scopes' => env('SHOPIFY_SCOPES', 'read_products,write_customers'),

    /*
    |--------------------------------------------------------------------------
    | Admin API Version
    |--------------------------------------------------------------------------
    */

    'api_version' => env('SHOPIFY_API_VERSION', '2026-10'),

];
