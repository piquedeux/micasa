<?php

require_once __DIR__ . '/lib/bootstrap.php';

$handle = trim((string) ($_GET['handle'] ?? ''));
$product = $handle !== '' ? shopify_product_by_handle($handle) : null;

if (!$product) {
    http_response_code(404);
    $page_title = 'MICASA / PIECE NOT FOUND';
    include __DIR__ . '/partials/header.php';
    echo '<section class="detail-layout"><article class="paper"><p class="spiked-label">404 / SHOPIFY</p><p>PIECE NOT FOUND IN THE CURRENT SHOPIFY SYNC.</p><a class="stroke-button" href="' . e(url_for('shop.php')) . '">SHOP</a></article></section>';
    include __DIR__ . '/partials/footer.php';
    exit;
}

$page_title = 'MICASA / ' . (string) $product['title'];
$body_class = 'page-piece';

include __DIR__ . '/partials/header.php';
?>

<section class="detail-layout">
  <figure class="paper detail-media">
    <?php if (!empty($product['image'])): ?>
      <img src="<?= e((string) $product['image']) ?>" alt="<?= e((string) ($product['image_alt'] ?: $product['title'])) ?>">
    <?php else: ?>
      <img src="<?= e(asset_url('assets/placeholders/project-blank.svg')) ?>" alt="<?= e((string) $product['title']) ?>">
    <?php endif; ?>
    <figcaption class="spiked-label">SHOPIFY / <?= e((string) $product['handle']) ?></figcaption>
  </figure>
  <article class="paper detail-copy">
    <p class="spiked-label"><?= !empty($product['available']) ? 'AVAILABLE' : 'ARCHIVE / SOLD / PENDING' ?></p>
    <h1><?= e((string) $product['title']) ?></h1>
    <p><?= e(money_label($product['price'] ?? null)) ?></p>
    <div class="body-copy"><?= nl2br(e((string) $product['description'])) ?></div>
    <div class="button-row">
      <a class="stroke-button" href="<?= e((string) $product['url']) ?>">BUY ON SHOPIFY</a>
      <a class="stroke-button" href="<?= e(url_for('shop.php')) ?>">SHOP</a>
    </div>
  </article>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
