<?php

declare(strict_types=1);

function site_content_path(): string
{
    return (string) app_config('storage.site_content', dirname(__DIR__) . '/data/site-content.json');
}

function site_content_defaults(): array
{
    return [
        'about' => [
            'heading_de' => 'MICASA IST EINE OFFENE PLATTFORM FUER KREATIVE PROJEKTE.',
            'heading_en' => 'MICASA IS AN OPEN PLATFORM FOR CREATIVE PROJECTS.',
            'body_de' => "MICASA verbindet Mode, Film, Musik, Design und gesellschaftlich relevante Themen.\n\nIm Zentrum stehen Geschichten, Haltungen und Kooperationen.",
            'body_en' => "MICASA connects fashion, film, music, design and social topics.\n\nThe focus is on stories, attitudes and collaborations.",
        ],
        'shop' => [
            'heading_de' => 'PIECES UND DROPS AUS DEM BIG CARTEL SHOP.',
            'heading_en' => 'PIECES AND DROPS FROM THE BIG CARTEL SHOP.',
            'body_de' => 'Produktdaten, Preise und Verfuegbarkeit kommen aus Big Cartel.',
            'body_en' => 'Product data, prices and availability come from Big Cartel.',
            'checkout_url' => 'https://r2s.bigcartel.com/cart',
        ],
    ];
}

function site_content_all(): array
{
    return array_replace_recursive(site_content_defaults(), read_json_file(site_content_path(), []));
}

function site_content_section(string $section): array
{
    $content = site_content_all();

    return is_array($content[$section] ?? null) ? $content[$section] : [];
}

function site_content_save_section(string $section, array $payload): bool
{
    $content = site_content_all();
    $content[$section] = array_replace($content[$section] ?? [], $payload);

    return write_json_file(site_content_path(), $content);
}
