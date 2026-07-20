<?php

require_once __DIR__ . '/lib/bootstrap.php';

$id = (string) ($_GET['id'] ?? '');
$project = project_find($id);

if (!$project) {
    http_response_code(404);
    $page_title = 'MICASA / PROJECT NOT FOUND';
    include __DIR__ . '/partials/header.php';
    echo '<section class="detail-layout"><article class="paper"><p class="spiked-label">404 / NICHT GEFUNDEN</p><p>PROJECT NOT FOUND / PROJEKT NICHT GEFUNDEN.</p><a class="stroke-button" href="' . e(url_for('index.php#archive')) . '">ARCHIVE / ARCHIV</a></article></section>';
    include __DIR__ . '/partials/footer.php';
    exit;
}

$embed = youtube_embed_url((string) ($project['video_url'] ?? ''));
$product = !empty($project['bigcartel_handle']) ? bigcartel_product_by_handle((string) $project['bigcartel_handle']) : null;
$project_images = upload_images();
$project_order_index = array_search($project['id'], array_column(projects_all(true), 'id'), true);
$project_image = $project_images !== [] ? $project_images[is_int($project_order_index) ? $project_order_index % count($project_images) : 0] : (string) ($project['image'] ?? '');
$page_title = 'MICASA / ' . localized($project, 'title');
$body_class = 'page-detail';

include __DIR__ . '/partials/header.php';
?>

<section class="detail-layout">
  <figure class="paper detail-media">
    <img src="<?= e($project_image !== '' ? $project_image : asset_url((string) ($project['image'] ?? ''))) ?>" alt="<?= e(localized($project, 'title')) ?>">
    <figcaption class="spiked-label"><?= e(localized($project, 'kicker')) ?></figcaption>
  </figure>
  <article class="paper detail-copy">
    <p class="spiked-label"><?= e((string) $project['status']) ?> / <?= e((string) $project['type']) ?></p>
    <h1><?= e(localized($project, 'title')) ?></h1>
    <p><?= e(localized($project, 'summary')) ?></p>
    <div class="body-copy"><?= nl2br(e(localized($project, 'body'))) ?></div>
    <?php if (!empty($project['artists'])): ?>
      <p class="spiked-label">CREDITS / MITWIRKENDE / <?= e((string) $project['artists']) ?></p>
    <?php endif; ?>
    <div class="button-row">
      <a class="stroke-button" href="<?= e(url_for('index.php#archive')) ?>"><?= e(tr('ARCHIV', 'ARCHIVE')) ?></a>
      <?php if ($product): ?>
        <a class="stroke-button" href="<?= e(url_for('piece.php?handle=' . rawurlencode((string) $product['handle']))) ?>">PIECE / PRODUKT</a>
      <?php elseif (!empty($project['bigcartel_handle'])): ?>
        <a class="stroke-button" href="<?= e('https://' . trim((string) app_config('bigcartel.storefront_url', 'r2s.bigcartel.com'), '/') . '/product/' . rawurlencode((string) $project['bigcartel_handle'])) ?>">BIG CARTEL</a>
      <?php endif; ?>
    </div>
  </article>
</section>

<?php if ($embed !== ''): ?>
  <section class="video-band">
    <div class="paper video-frame">
      <iframe src="<?= e($embed) ?>" title="<?= e(localized($project, 'title')) ?>" loading="lazy" allowfullscreen></iframe>
    </div>
  </section>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
