<?php

return [
    'api_key' => env('SHOPIFY_API_KEY'),
    'api_secret' => env('SHOPIFY_API_SECRET'),
    'api_version' => env('SHOPIFY_API_VERSION', '2026-01'),
    'redirect_uri' => env('SHOPIFY_REDIRECT_URI'),

    'scopes' => [
        'read_products',
        'write_products',
        'read_orders',
        'write_orders',
        'read_customers',
        'write_customers',
        'read_inventory',
        'write_inventory',
    ],

    'app_url' => env('APP_URL'),

    'webhooks' => [
        'app/uninstalled' => '/webhooks/shopify/uninstalled',
        'products/create' => '/webhooks/shopify/products/create',
        'products/update' => '/webhooks/shopify/products/update',
        'orders/create' => '/webhooks/shopify/orders/create',
    ],
];
