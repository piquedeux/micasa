<?php

require_once __DIR__ . '/lib/bootstrap.php';

$shopify_products = shopify_products(24);
$products = $shopify_products;

if (!$products) {
    $products = [
        [
            'title' => 'MICASA PIECE 001',
            'handle' => 'piece-001',
            'description' => 'SHOPIFY FEED PENDING',
            'image' => asset_url('assets/placeholders/project-blank.svg'),
            'image_alt' => 'MICASA piece placeholder',
            'price' => null,
            'available' => false,
            'url' => 'https://' . shopify_domain(),
        ],
        [
            'title' => 'MICASA PIECE 002',
            'handle' => 'piece-002',
            'description' => 'PRODUCTS ARE MANAGED IN SHOPIFY',
            'image' => asset_url('assets/placeholders/project-02.svg'),
            'image_alt' => 'MICASA piece placeholder',
            'price' => null,
            'available' => false,
            'url' => 'https://' . shopify_domain(),
        ],
        [
            'title' => 'MICASA PIECE 003',
            'handle' => 'piece-003',
            'description' => 'ARCHIVE CONNECTION READY',
            'image' => asset_url('assets/placeholders/project-03.svg'),
            'image_alt' => 'MICASA piece placeholder',
            'price' => null,
            'available' => false,
            'url' => 'https://' . shopify_domain(),
        ],
    ];
}

$page_title = 'MICASA SHOP';
$body_class = 'page-shop';

include __DIR__ . '/partials/header.php';
?>

<section class="shop-hero">
  <article class="paper">
    <p class="spiked-label">B1 / SHOP</p>
    <h1>DISCOUNTER ANGEBOTSÜBERSICHT FÜR PIECES, DIE IN SHOPIFY GEPFLEGT WERDEN.</h1>
    <p>PRODUKTDATEN, PREISE UND VERFÜGBARKEIT KOMMEN AUS SHOPIFY. DAS ARCHIV BLEIBT MICASA.</p>
  </article>
</section>

<section class="product-grid" aria-label="Shopify Produkte">
  <?php foreach ($products as $index => $product): ?>
    <article class="paper product-card product-card-<?= e((string) (($index % 5) + 1)) ?>">
      <a href="<?= e(!empty($shopify_products) ? url_for('piece.php?handle=' . rawurlencode((string) $product['handle'])) : (string) $product['url']) ?>">
        <?php if (!empty($product['image'])): ?>
          <img src="<?= e((string) $product['image']) ?>" alt="<?= e((string) ($product['image_alt'] ?: $product['title'])) ?>">
        <?php endif; ?>
        <span class="spiked-label">ANGEBOT <?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
        <strong><?= e((string) $product['title']) ?></strong>
        <span><?= e(money_label($product['price'] ?? null)) ?></span>
        <span><?= !empty($product['available']) ? 'AVAILABLE' : 'ARCHIVE / SOLD / PENDING' ?></span>
      </a>
    </article>
  <?php endforeach; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
