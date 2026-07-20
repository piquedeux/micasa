<?php

require_once __DIR__ . '/lib/bootstrap.php';

$handle = trim((string) ($_GET['handle'] ?? ''));
$product = $handle !== '' ? bigcartel_product_by_handle($handle) : null;

if (!$product) {
    http_response_code(404);
    $page_title = 'MICASA / PIECE NOT FOUND';
    include __DIR__ . '/partials/header.php';
  echo '<section class="detail-layout"><article class="paper"><p class="spiked-label">404 / BIG CARTEL / NICHT GEFUNDEN</p><p>PIECE NOT FOUND IN THE CURRENT BIG CARTEL FEED / PRODUKT NICHT GEFUNDEN.</p><a class="stroke-button" href="' . e(url_for('shop.php')) . '">SHOP / SHOP</a></article></section>';
    include __DIR__ . '/partials/footer.php';
    exit;
}

$page_title = 'MICASA / ' . (string) $product['title'];
$body_class = 'page-piece';
$shop = site_content_section('shop');
$checkout_url = trim((string) ($shop['checkout_url'] ?? $product['cart_url'] ?? ''));

include __DIR__ . '/partials/header.php';
?>

<section class="detail-layout">
  <figure class="paper detail-media">
    <?php if (!empty($product['image'])): ?>
      <img src="<?= e((string) $product['image']) ?>" alt="<?= e((string) ($product['image_alt'] ?: $product['title'])) ?>">
    <?php endif; ?>
    <figcaption class="spiked-label">BIG CARTEL / <?= e((string) $product['handle']) ?></figcaption>
  </figure>
  <article class="paper detail-copy">
    <p class="spiked-label"><?= !empty($product['available']) ? 'AVAILABLE / VERFUEGBAR' : 'ARCHIVE / SOLD / PENDING / ARCHIV / VERKAUFT / OFFEN' ?></p>
    <h1><?= e((string) $product['title']) ?></h1>
    <p><?= e((string) ($product['price_label'] ?? money_label($product['price'] ?? null))) ?></p>
    <div class="body-copy"><?= nl2br(e((string) $product['description'])) ?></div>
    <div class="button-row">
      <a class="stroke-button" href="<?= e((string) $product['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e(tr('BIG CARTEL OEFFNEN', 'OPEN BIG CARTEL')) ?></a>
      <?php if ($checkout_url !== ''): ?>
        <a class="stroke-button" href="<?= e($checkout_url) ?>" target="_blank" rel="noopener noreferrer"><?= e(tr('WARENKORB', 'CART')) ?></a>
      <?php endif; ?>
      <a class="stroke-button" href="<?= e(url_for('shop.php')) ?>"><?= e(tr('SHOP', 'SHOP')) ?></a>
    </div>
  </article>
</section>

<!-- <section class="video-band">
  <article class="paper product-iframe-frame">
    <p class="spiked-label">DETAIL VIEW</p>
    <iframe
      src="<?= e((string) $product['url']) ?>"
      title="<?= e((string) $product['title']) ?>"
      loading="lazy"
      sandbox="allow-forms allow-popups allow-popups-to-escape-sandbox allow-scripts allow-top-navigation-by-user-activation"
      referrerpolicy="no-referrer-when-downgrade"
    ></iframe>
  </article>
</section> -->

<?php include __DIR__ . '/partials/footer.php'; ?>
