<?php

require_once __DIR__ . '/lib/bootstrap.php';

$id = (string) ($_GET['id'] ?? '');
$project = project_find($id);

if (!$project) {
    http_response_code(404);
    $page_title = 'MICASA / PROJECT NOT FOUND';
    include __DIR__ . '/partials/header.php';
    echo '<section class="detail-layout"><article class="paper"><p class="spiked-label">404</p><p>PROJECT NOT FOUND.</p><a class="stroke-button" href="' . e(url_for('index.php#archive')) . '">ARCHIVE</a></article></section>';
    include __DIR__ . '/partials/footer.php';
    exit;
}

$embed = youtube_embed_url((string) ($project['video_url'] ?? ''));
$product = !empty($project['shopify_handle']) ? shopify_product_by_handle((string) $project['shopify_handle']) : null;
$page_title = 'MICASA / ' . (string) $project['title'];
$body_class = 'page-detail';

include __DIR__ . '/partials/header.php';
?>

<section class="detail-layout">
  <figure class="paper detail-media">
    <img src="<?= e(asset_url((string) $project['image'])) ?>" alt="<?= e((string) $project['title']) ?>">
    <figcaption class="spiked-label"><?= e((string) $project['kicker']) ?></figcaption>
  </figure>
  <article class="paper detail-copy">
    <p class="spiked-label"><?= e((string) $project['status']) ?> / <?= e((string) $project['type']) ?></p>
    <h1><?= e((string) $project['title']) ?></h1>
    <p><?= e((string) $project['summary']) ?></p>
    <div class="body-copy"><?= nl2br(e((string) $project['body'])) ?></div>
    <?php if (!empty($project['artists'])): ?>
      <p class="spiked-label">CREDITS / <?= e((string) $project['artists']) ?></p>
    <?php endif; ?>
    <div class="button-row">
      <a class="stroke-button" href="<?= e(url_for('index.php#archive')) ?>">ARCHIV</a>
      <?php if ($product): ?>
        <a class="stroke-button" href="<?= e(url_for('piece.php?handle=' . rawurlencode((string) $product['handle']))) ?>">PIECE</a>
      <?php elseif (!empty($project['shopify_handle'])): ?>
        <a class="stroke-button" href="<?= e('https://' . shopify_domain() . '/products/' . rawurlencode((string) $project['shopify_handle'])) ?>">SHOPIFY</a>
      <?php endif; ?>
    </div>
  </article>
</section>

<?php if ($embed !== ''): ?>
  <section class="video-band">
    <div class="paper video-frame">
      <iframe src="<?= e($embed) ?>" title="<?= e((string) $project['title']) ?>" loading="lazy" allowfullscreen></iframe>
    </div>
  </section>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
