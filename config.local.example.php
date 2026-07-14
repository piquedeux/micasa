<?php

return [
    'shopify' => [
        'domain' => 'micasa-archive-u70pw.myshopify.com',
        'api_version' => '2026-07',
        'access_mode' => 'storefront',
        'storefront_token' => '',
        'admin_token' => '',
        'allow_tokenless' => false,
    ],
    'admin' => [
        'username' => 'admin',
        'password_hash' => password_hash('replace-this-password', PASSWORD_DEFAULT),
    ],
];
