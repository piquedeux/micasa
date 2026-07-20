<?php

declare(strict_types=1);

function shopify_domain(): string
{
    $domain = strtolower(trim((string) app_config('shopify.domain', '')));
    $domain = preg_replace('/^https?:\/\//', '', $domain) ?? $domain;

    return rtrim($domain, '/');
}

function shopify_access_mode(): string
{
    $mode = strtolower((string) app_config('shopify.access_mode', 'storefront'));

    return in_array($mode, ['storefront', 'admin'], true) ? $mode : 'storefront';
}

function shopify_has_credentials(?string $mode = null): bool
{
    $mode = $mode ?: shopify_access_mode();
    if ($mode === 'admin') {
        return (string) app_config('shopify.admin_token', '') !== '';
    }

    return (string) app_config('shopify.storefront_token', '') !== '' || (bool) app_config('shopify.allow_tokenless', false);
}

function shopify_endpoint(?string $mode = null): string
{
    $version = (string) app_config('shopify.api_version', '2026-07');
    $domain = shopify_domain();
    $mode = $mode ?: shopify_access_mode();

    if ($mode === 'admin') {
        return "https://{$domain}/admin/api/{$version}/graphql.json";
    }

    return "https://{$domain}/api/{$version}/graphql.json";
}

function shopify_graphql(string $query, array $variables = [], ?string $mode = null): array
{
    $mode = $mode ?: shopify_access_mode();
    $domain = shopify_domain();
    if ($domain === '') {
        return ['ok' => false, 'errors' => ['Missing Shopify domain.']];
    }

    if ($mode === 'admin' && !shopify_has_credentials('admin')) {
        return ['ok' => false, 'errors' => ['Missing Shopify Admin API token.']];
    }

    if ($mode === 'storefront' && !shopify_has_credentials('storefront')) {
        return ['ok' => false, 'errors' => ['Missing Shopify Storefront API token.']];
    }

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    if ($mode === 'admin') {
        $headers[] = 'X-Shopify-Access-Token: ' . app_config('shopify.admin_token', '');
    } else {
        $token = (string) app_config('shopify.storefront_token', '');
        if ($token !== '') {
            $headers[] = 'X-Shopify-Storefront-Access-Token: ' . $token;
        }
    }

    $payload = json_encode(['query' => $query, 'variables' => (object) $variables]);
    if ($payload === false) {
        return ['ok' => false, 'errors' => ['Could not encode GraphQL payload.']];
    }

    if (function_exists('curl_init')) {
        $ch = curl_init(shopify_endpoint($mode));
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $payload,
                'timeout' => 12,
            ],
        ]);
        $body = @file_get_contents(shopify_endpoint($mode), false, $context);
        $status = isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match) ? (int) $match[1] : 0;
        $error = $body === false ? 'HTTP request failed.' : '';
    }

    if ($body === false || $body === '') {
        return ['ok' => false, 'status' => $status, 'errors' => [$error ?: 'Empty Shopify response.']];
    }

    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'status' => $status, 'errors' => ['Invalid JSON from Shopify.']];
    }

    return [
        'ok' => $status >= 200 && $status < 300 && empty($decoded['errors']),
        'status' => $status,
        'data' => $decoded['data'] ?? [],
        'errors' => $decoded['errors'] ?? [],
    ];
}

function shopify_products(int $first = 24): array
{
    $cache_path = (string) app_config('storage.shopify_cache', dirname(__DIR__) . '/data/shopify-cache.json');
    $ttl = (int) app_config('shopify.cache_ttl', 600);
    $cache = read_json_file($cache_path, []);

    if (!empty($cache['saved_at']) && time() - (int) $cache['saved_at'] < $ttl && !empty($cache['products'])) {
        return $cache['products'];
    }

    $mode = shopify_access_mode();
    if (!shopify_has_credentials($mode)) {
        return !empty($cache['products']) ? $cache['products'] : [];
    }

    $query = $mode === 'admin' ? shopify_admin_products_query() : shopify_storefront_products_query();
    $response = shopify_graphql($query, ['first' => $first], $mode);

    if (!$response['ok']) {
        return !empty($cache['products']) ? $cache['products'] : [];
    }

    $edges = $response['data']['products']['edges'] ?? [];
    $products = array_map(static fn (array $edge): array => shopify_normalize_product($edge['node'] ?? [], $mode), $edges);
    $products = array_values(array_filter($products, static fn (array $product): bool => ($product['handle'] ?? '') !== ''));
    write_json_file($cache_path, ['saved_at' => time(), 'products' => $products]);

    return $products;
}

function shopify_product_by_handle(string $handle): ?array
{
    foreach (shopify_products(50) as $product) {
        if (($product['handle'] ?? '') === $handle) {
            return $product;
        }
    }

    return null;
}

function shopify_storefront_products_query(): string
{
    return <<<'GRAPHQL'
query Products($first: Int!) {
  products(first: $first, sortKey: UPDATED_AT, reverse: true) {
    edges {
      node {
        id
        id
        title
        handle
        description
        availableForSale
        onlineStoreUrl
        featuredImage {
          url
          altText
        }
        priceRange {
          minVariantPrice {
            amount
            currencyCode
          }
        }
        variants(first: 1) {
          edges {
            node {
              id
              availableForSale
            }
          }
        }
      }
    }
  }
}
GRAPHQL;
}

function shopify_admin_products_query(): string
{
    return <<<'GRAPHQL'
query Products($first: Int!) {
  products(first: $first, sortKey: UPDATED_AT, reverse: true) {
    edges {
      node {
        id
        title
        handle
        descriptionHtml
        onlineStoreUrl
        status
        totalInventory
        featuredImage {
          url
          altText
        }
        priceRangeV2 {
          minVariantPrice {
            amount
            currencyCode
          }
        }
        variants(first: 1) {
          edges {
            node {
              id
            }
          }
        }
      }
    }
  }
}
GRAPHQL;
}

function shopify_normalize_product(array $node, string $mode): array
{
  $id = (string) ($node['id'] ?? '');
    $image = $node['featuredImage'] ?? [];
    $variant = $node['variants']['edges'][0]['node'] ?? [];
    $price = $mode === 'admin' ? ($node['priceRangeV2']['minVariantPrice'] ?? null) : ($node['priceRange']['minVariantPrice'] ?? null);
    $description = $mode === 'admin' ? strip_tags((string) ($node['descriptionHtml'] ?? '')) : (string) ($node['description'] ?? '');
    $online_url = (string) ($node['onlineStoreUrl'] ?? '');
    $handle = (string) ($node['handle'] ?? '');

    return [
        'id' => (string) ($node['id'] ?? ''),
        'buy_button_id' => shopify_buy_button_id($id),
        'title' => (string) ($node['title'] ?? ''),
        'handle' => $handle,
        'description' => $description,
        'image' => (string) ($image['url'] ?? ''),
        'image_alt' => (string) ($image['altText'] ?? ''),
        'price' => $price,
        'available' => $mode === 'admin' ? (($node['status'] ?? '') === 'ACTIVE' && (int) ($node['totalInventory'] ?? 0) !== 0) : !empty($node['availableForSale']),
        'variant_id' => (string) ($variant['id'] ?? ''),
        'url' => $online_url !== '' ? $online_url : 'https://' . shopify_domain() . '/products/' . rawurlencode($handle),
    ];
}

    function shopify_buy_button_id(string $gid): string
    {
      if ($gid === '') {
        return '';
      }

      if (preg_match('/(\d+)$/', $gid, $match) === 1) {
        return $match[1];
      }

      return '';
    }
