<?php

declare(strict_types=1);

function config_all(): array
{
    static $config = null;

    if ($config === null) {
        $config = require dirname(__DIR__) . '/config.php';
    }

    return $config;
}

function app_config(string $key, mixed $default = null): mixed
{
    $value = config_all();

    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function base_path(): string
{
    $configured = trim((string) app_config('site.base_url', ''));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($script));
    if (str_ends_with($dir, '/admin') || str_ends_with($dir, '/panel')) {
        $dir = dirname($dir);
    }

    return $dir === '/' || $dir === '.' ? '' : rtrim($dir, '/');
}

function url_for(string $path = ''): string
{
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return base_path() . '/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    return url_for($path);
}

function current_lang(): string
{
    static $lang = null;

    if ($lang !== null) {
        return $lang;
    }

    $requested = strtolower((string) ($_GET['lang'] ?? ''));
    if (in_array($requested, ['de', 'en'], true)) {
        $lang = $requested;
        setcookie('micasa_lang', $lang, time() + 31536000, base_path() === '' ? '/' : base_path() . '/', '', !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', true);

        return $lang;
    }

    $cookie = strtolower((string) ($_COOKIE['micasa_lang'] ?? ''));
    if (in_array($cookie, ['de', 'en'], true)) {
        return $lang = $cookie;
    }

    $browser = strtolower((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));

    return $lang = str_starts_with($browser, 'de') ? 'de' : 'en';
}

function tr(string $de, string $en): string
{
    return current_lang() === 'de' ? $de : $en;
}

function localized(array $item, string $field): string
{
    $lang = current_lang();
    $primary = trim((string) ($item[$field . '_' . $lang] ?? ''));
    if ($primary !== '') {
        return $primary;
    }

    $fallback_lang = $lang === 'de' ? 'en' : 'de';
    $fallback = trim((string) ($item[$field . '_' . $fallback_lang] ?? ''));

    return $fallback !== '' ? $fallback : trim((string) ($item[$field] ?? ''));
}

function language_url(string $lang): string
{
    $params = $_GET;
    $params['lang'] = $lang;
    $path = strtok((string) ($_SERVER['REQUEST_URI'] ?? ''), '?') ?: '';

    return $path . '?' . http_build_query($params);
}

function upload_images(): array
{
    $upload_dir = dirname(__DIR__) . '/assets/uploads';
    $files = glob($upload_dir . '/*') ?: [];
    $images = [];

    foreach ($files as $file) {
        if (!is_file($file)) {
            continue;
        }

        $name = basename($file);
        if ($name === '.gitkeep' || $name === '.htaccess') {
            continue;
        }

        $images[] = asset_url('assets/uploads/' . rawurlencode($name));
    }

    return $images;
}

function rotating_upload_image(int $index = 0, ?string $fallback = null): ?string
{
    $images = upload_images();

    if ($images !== []) {
        return $images[$index % count($images)];
    }

    return $fallback;
}

function read_json_file(string $path, array $fallback = []): array
{
    if (!is_file($path)) {
        return $fallback;
    }

    $json = file_get_contents($path);
    if ($json === false || trim($json) === '') {
        return $fallback;
    }

    $data = json_decode($json, true);

    return is_array($data) ? $data : $fallback;
}

function write_json_file(string $path, array $data): bool
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($encoded === false) {
        return false;
    }

    $handle = fopen($path, 'c+');
    if ($handle === false) {
        return false;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return false;
        }
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, $encoded . PHP_EOL);
        fflush($handle);
        flock($handle, LOCK_UN);

        return true;
    } finally {
        fclose($handle);
    }
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    if (function_exists('iconv')) {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    }
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value !== '' ? $value : 'project-' . date('YmdHis');
}

function excerpt(string $value, int $limit = 180): string
{
    $clean = trim(preg_replace('/\s+/', ' ', strip_tags($value)) ?? '');
    $length = function_exists('mb_strlen') ? mb_strlen($clean) : strlen($clean);
    if ($length <= $limit) {
        return $clean;
    }

    $slice = function_exists('mb_substr') ? mb_substr($clean, 0, $limit - 3) : substr($clean, 0, $limit - 3);

    return rtrim($slice) . '...';
}

function money_label(?array $money): string
{
    if (!$money || !isset($money['amount'])) {
        return 'PRICE OPEN';
    }

    $amount = number_format((float) $money['amount'], 2, ',', '.');
    $currency = strtoupper((string) ($money['currencyCode'] ?? 'EUR'));

    return $amount . ' ' . $currency;
}

function youtube_embed_url(string $url): string
{
    if ($url === '') {
        return '';
    }

    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $match)) {
        return 'https://www.youtube.com/embed/' . $match[1];
    }

    if (preg_match('/[?&]v=([a-zA-Z0-9_-]+)/', $url, $match)) {
        return 'https://www.youtube.com/embed/' . $match[1];
    }

    if (str_contains($url, 'youtube.com/embed/')) {
        return $url;
    }

    return '';
}

function split_csv(string $value): array
{
    $items = array_map('trim', explode(',', $value));

    return array_values(array_filter($items, static fn (string $item): bool => $item !== ''));
}
