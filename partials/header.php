<?php
$page_title = $page_title ?? app_config('site.name', 'MICASA');
$body_class = $body_class ?? '';
$is_admin_page = str_contains($body_class, 'page-admin');
$current_lang = current_lang();
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page_title) ?></title>
  <meta name="description" content="MICASA archive, campaigns, pieces and shop.">
  <link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>">
</head>
<body class="<?= e($body_class) ?>">
<?php if (!$is_admin_page): ?>
  <header class="site-header" data-paper>
    <a class="site-brand spiked-label" href="<?= e(url_for('index.php')) ?>">MICASA®</a>
    <nav class="site-nav" aria-label="Hauptnavigation">
      <a href="<?= e(url_for('index.php#archive')) ?>"><?= e(tr('MAGAZIN', 'ARCHIVE')) ?></a>
      <a href="<?= e(url_for('shop.php')) ?>"><?= e(tr('SHOP', 'STORE')) ?></a>
      <a href="<?= e(url_for('about.php')) ?>"><?= e(tr('INFO', 'ABOUT')) ?></a>
    </nav>
  </header>
<?php endif; ?>
  <main>
