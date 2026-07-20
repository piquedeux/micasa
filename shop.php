<?php

require_once __DIR__ . '/lib/bootstrap.php';

$products = bigcartel_products(24);
$shop = site_content_section('shop');
$checkout_url = trim((string) ($shop['checkout_url'] ?? ''));
$upload_images = array_values(array_map(static fn (string $file): string => asset_url('assets/uploads/' . rawurlencode($file)), array_filter(scandir(__DIR__ . '/assets/uploads') ?: [], static fn (string $file): bool => !in_array($file, ['.', '..', '.gitkeep', '.htaccess'], true) && !is_dir(__DIR__ . '/assets/uploads/' . $file))));

if ($products === []) {
    $products = [
        [
      'handle' => 'uniform-head',
      'title' => 'Uniform Head',
            'image' => $upload_images[0] ?? '',
      'image_alt' => 'Uniform Head',
      'price_label' => '30,00 €',
      'available' => true,
      'description' => 'BIGCARTEL PRODUCT',
      'url' => 'https://r2s.bigcartel.com/product/uniform-head',
      'storefront_url' => 'https://r2s.bigcartel.com',
      'cart_url' => 'https://r2s.bigcartel.com/cart',
        ],
    ];
}

$page_title = 'MICASA SHOP';
$body_class = 'page-shop';

include __DIR__ . '/partials/header.php';
?>

<section class="shop-hero">
  <article class="paper">
    <p class="spiked-label"><?= e(tr('SHOP', 'STORE')) ?></p>
    <h1><?= e(localized($shop, 'heading')) ?></h1>
    <div class="body-copy"><?= nl2br(e(localized($shop, 'body'))) ?></div>
    <?php if ($checkout_url !== ''): ?>
      <a class="stroke-button" href="<?= e($checkout_url) ?>" target="_blank" rel="noopener noreferrer"><?= e(tr('WARENKORB / CHECKOUT', 'CART / CHECKOUT')) ?></a>
    <?php endif; ?>
  </article>
</section>

<section class="product-grid" aria-label="Big Cartel Produkte">
  <?php foreach ($products as $index => $product): ?>
    <article class="paper product-card product-card-<?= e((string) (($index % 5) + 1)) ?>">
      <a href="<?= e(url_for('piece.php?handle=' . rawurlencode((string) $product['handle']))) ?>">
        <img class="product-thumb" src="<?= e((string) ($upload_images !== [] ? $upload_images[$index % count($upload_images)] : ($product['image'] ?? ''))) ?>" alt="<?= e((string) ($product['image_alt'] ?: $product['title'])) ?>">
        <span class="spiked-label">PIECE <?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
        <strong><?= e((string) $product['title']) ?></strong>
        <span><?= e((string) ($product['price_label'] ?? money_label($product['price'] ?? null))) ?></span>
        <span><?= !empty($product['available']) ? e(tr('VERFUEGBAR', 'AVAILABLE')) : e(tr('ARCHIV / VERKAUFT / OFFEN', 'ARCHIVE / SOLD / PENDING')) ?></span>
      </a>
    </article>
  <?php endforeach; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
