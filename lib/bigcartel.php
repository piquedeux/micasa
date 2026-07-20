<?php

declare(strict_types=1);

function bigcartel_storefront_url(): string
{
    $url = trim((string) app_config('bigcartel.storefront_url', ''));

    return $url !== '' ? rtrim($url, '/') : '';
}

function bigcartel_cache_path(): string
{
    return (string) app_config('storage.bigcartel_cache', dirname(__DIR__) . '/data/bigcartel-cache.json');
}

function bigcartel_product_sources(): array
{
    $sources = app_config('bigcartel.products', []);

    if (!is_array($sources) || $sources === []) {
        $storefront = bigcartel_storefront_url();

        return $storefront !== '' ? [['handle' => 'uniform-head', 'url' => $storefront . '/product/uniform-head']] : [];
    }

    return array_values(array_filter($sources, static fn (mixed $source): bool => is_array($source) || is_string($source)));
}

function bigcartel_handle_from_url(string $url): string
{
    if (preg_match('~/product/([^/?#]+)~', $url, $match)) {
        return trim((string) $match[1]);
    }

    return slugify(basename(parse_url($url, PHP_URL_PATH) ?: 'product'));
}

function bigcartel_request(string $url): string
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0\r\nAccept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n",
            'timeout' => 8,
        ],
        'https' => [
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0\r\nAccept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n",
            'timeout' => 8,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);

    return is_string($body) ? $body : '';
}

function bigcartel_extract_meta(string $html, string $name): string
{
    if (preg_match('~<meta[^>]+(?:property|name)=["\']' . preg_quote($name, '~') . '["\'][^>]+content=["\']([^"\']+)["\']~i', $html, $match)) {
        return html_entity_decode(trim((string) $match[1]), ENT_QUOTES, 'UTF-8');
    }

    return '';
}

function bigcartel_extract_first_image(string $html): string
{
    $candidates = [
        bigcartel_extract_meta($html, 'og:image'),
        bigcartel_extract_meta($html, 'twitter:image'),
    ];

    foreach ($candidates as $candidate) {
        if ($candidate !== '') {
            return $candidate;
        }
    }

    if (preg_match('~<img[^>]+src=["\']([^"\']+)~i', $html, $match)) {
        return html_entity_decode(trim((string) $match[1]), ENT_QUOTES, 'UTF-8');
    }

    return '';
}

function bigcartel_extract_title(string $html): string
{
    if (preg_match('~<h1[^>]*>(.*?)</h1>~si', $html, $match)) {
        return trim(strip_tags(html_entity_decode((string) $match[1], ENT_QUOTES, 'UTF-8')));
    }

    $title = bigcartel_extract_meta($html, 'og:title');
    if ($title !== '') {
        return trim($title);
    }

    if (preg_match('~<title[^>]*>(.*?)</title>~si', $html, $match)) {
        return trim(strip_tags(html_entity_decode((string) $match[1], ENT_QUOTES, 'UTF-8')));
    }

    return '';
}

function bigcartel_extract_price(string $html): string
{
    if (preg_match('~([0-9]+(?:[\.,][0-9]{2})?)\s*€~', $html, $match)) {
        return trim((string) $match[1]) . ' €';
    }

    return '';
}

function bigcartel_extract_description(string $html): string
{
    $description = bigcartel_extract_meta($html, 'description');
    if ($description !== '') {
        return trim($description);
    }

    if (preg_match('~<body[^>]*>(.*?)</body>~si', $html, $match)) {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $match[1])) ?? '');

        return $text;
    }

    return '';
}

function bigcartel_fetch_product(array $source): array
{
    $url = trim((string) ($source['url'] ?? ''));
    $handle = trim((string) ($source['handle'] ?? ''));

    if ($url === '') {
        return [];
    }

    $html = bigcartel_request($url);
    $storefront = bigcartel_storefront_url();
    $handle = $handle !== '' ? $handle : bigcartel_handle_from_url($url);
    $title = bigcartel_extract_title($html);
    $image = bigcartel_extract_first_image($html);
    $description = bigcartel_extract_description($html);
    $price = bigcartel_extract_price($html);

    return [
        'handle' => $handle,
        'title' => $title !== '' ? $title : ucfirst(str_replace(['-', '_'], ' ', $handle)),
        'image' => $image,
        'image_alt' => $title !== '' ? $title : $handle,
        'price_label' => $price,
        'available' => stripos($html, 'Add to cart') !== false,
        'description' => $description !== '' ? $description : 'BIGCARTEL PRODUCT',
        'url' => $url,
        'storefront_url' => $storefront,
        'cart_url' => $storefront !== '' ? $storefront . '/cart' : '',
    ];
}

function bigcartel_products(int $limit = 24): array
{
    $cache_path = bigcartel_cache_path();
    $ttl = (int) app_config('bigcartel.cache_ttl', 3600);
    $cache = read_json_file($cache_path, ['products' => []]);
    $cached_products = is_array($cache['products'] ?? null) ? $cache['products'] : [];
    $products = [];

    foreach (array_slice(bigcartel_product_sources(), 0, max(1, $limit)) as $source) {
        $source = is_string($source) ? ['url' => $source] : $source;
        $url = trim((string) ($source['url'] ?? ''));
        if ($url === '') {
            continue;
        }

        $cache_key = md5($url);
        $cached_item = $cached_products[$cache_key] ?? null;
        $product = [];

        if (is_array($cached_item) && !empty($cached_item['saved_at']) && time() - (int) $cached_item['saved_at'] < $ttl && is_array($cached_item['product'] ?? null)) {
            $product = $cached_item['product'];
        } else {
            $product = bigcartel_fetch_product($source);
            $cache['products'][$cache_key] = [
                'saved_at' => time(),
                'product' => $product,
            ];
        }

        if ($product !== []) {
            $products[] = $product;
        }
    }

    if (!empty($cache['products'])) {
        write_json_file($cache_path, $cache);
    }

    return $products;
}

function bigcartel_product_by_handle(string $handle): ?array
{
    foreach (bigcartel_products(50) as $product) {
        if (($product['handle'] ?? '') === $handle) {
            return $product;
        }
    }

    return null;
}