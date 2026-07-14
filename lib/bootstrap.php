<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/projects.php';
require_once __DIR__ . '/shopify.php';

$timezone = app_config('site.timezone', 'Europe/Berlin');
if (is_string($timezone) && $timezone !== '') {
    date_default_timezone_set($timezone);
}
