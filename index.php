<?php

require_once __DIR__ . '/lib/bootstrap.php';

$projects = projects_all();
$featured = array_values(array_filter($projects, static fn (array $project): bool => !empty($project['featured'])));
$page_title = 'MICASA ARCHIVE';
$body_class = 'page-home';

include __DIR__ . '/partials/header.php';
?>

<section class="intro-spread" aria-labelledby="intro-title">
  <article class="paper hero-paper">
    <p class="spiked-label"><?= e(tr('DIGITALES MAGAZIN', 'DIGITAL MAGAZINE')) ?></p>
    <h1 id="intro-title"><?= e(tr('MICASA IST EIN INTERDISZIPLINAERES KOLLEKTIV JUNGER KREATIVER.', 'MICASA IS AN INTERDISCIPLINARY COLLECTIVE OF YOUNG CREATIVES.')) ?></h1>
    <p><?= e(tr('Mode, Film, Musik, Design und gesellschaftliche Themen werden miteinander verzahnt.', 'Fashion, film, music, design and social topics are connected as one.')) ?></p>
    <a class="stroke-button" href="<?= e(url_for('about.php')) ?>"><?= e(tr('UEBER MICASA', 'ABOUT MICASA')) ?></a>
  </article>
  <article class="paper hero-paper hero-paper-offset">
    <p class="spiked-label"><?= e(tr('ARCHIV / KATALOGISIERUNG', 'ARCHIVE / CATALOGING')) ?></p>
    <p><?= e(tr('Jede Kampagne traegt eine Erzaehlung, eine Aesthetik und eine Kollaboration.', 'Each campaign carries a narrative, an aesthetic and a collaboration.')) ?></p>
    <p><?= e(tr('Die Website waechst wie ein analoges Magazin: Seiten, Bilder, Videos, Credits und Pieces koennen im Admin Panel ergaenzt werden.', 'The site grows like an analog magazine: pages, images, videos, credits and pieces can be added through the admin panel.')) ?></p>
  </article>
</section>

<section class="featured-grid" aria-label="Ausgewaehlte Projekte">
  <?php foreach ($featured as $index => $project): ?>
    <a class="paper project-tile tile-<?= e((string) (($index % 4) + 1)) ?>" href="<?= e(url_for('project.php?id=' . rawurlencode((string) $project['id']))) ?>">
      <img src="<?= e(rotating_upload_image($index, (string) ($project['image'] ?? '')) ?? '') ?>" alt="<?= e(localized($project, 'title')) ?>">
      <span class="spiked-label"><?= e(localized($project, 'kicker')) ?></span>
      <strong><?= e(localized($project, 'title')) ?></strong>
      <span><?= e(localized($project, 'summary')) ?></span>
    </a>
  <?php endforeach; ?>
</section>

<section class="archive-section" id="archive" aria-labelledby="archive-title">
  <div class="section-heading">
    <p class="spiked-label">INDEX</p>
    <h2 id="archive-title"><?= e(tr('ARCHIV KATALOG', 'ARCHIVE CATALOG')) ?></h2>
  </div>
  <ol class="archive-list">
    <?php foreach ($projects as $project): ?>
      <li class="paper archive-entry">
        <a href="<?= e(url_for('project.php?id=' . rawurlencode((string) $project['id']))) ?>">
          <span><?= e(str_pad((string) ($project['release_order'] ?? 0), 2, '0', STR_PAD_LEFT)) ?></span>
          <strong><?= e(localized($project, 'title')) ?></strong>
          <span><?= e((string) $project['status']) ?></span>
          <span><?= e((string) $project['type']) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ol>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
