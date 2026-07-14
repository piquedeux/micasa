<?php

return array_replace_recursive([
    'site' => [
        'name' => 'MICASA',
        'base_url' => '',
        'timezone' => 'Europe/Berlin',
    ],
    'shopify' => [
        'domain' => 'micasa-archive-u70pw.myshopify.com',
        'api_version' => '2026-07',
        'access_mode' => 'storefront',
        'storefront_token' => getenv('SHOPIFY_STOREFRONT_TOKEN') ?: '',
        'admin_token' => getenv('SHOPIFY_ADMIN_TOKEN') ?: '',
        'allow_tokenless' => false,
        'cache_ttl' => 600,
    ],
    'admin' => [
        'username' => getenv('MICASA_ADMIN_USER') ?: 'admin',
        'password_hash' => getenv('MICASA_ADMIN_PASSWORD_HASH') ?: '$2y$12$S82kND8By/mqtCJZNNMiBOauVwSJj5vFRJmLXsrhZhCaJBMCqURlO',
        'session_name' => 'micasa_admin',
    ],
    'storage' => [
        'projects' => __DIR__ . '/data/projects.json',
        'shopify_cache' => __DIR__ . '/data/shopify-cache.json',
        'uploads_dir' => __DIR__ . '/assets/uploads',
        'uploads_url' => 'assets/uploads',
    ],
], is_file(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : []);
