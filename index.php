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
    <p class="spiked-label">A1 / DIGITALES MAGAZIN</p>
    <h1 id="intro-title">MICASA IST EIN INTERDISZIPLINAERES KOLLEKTIV JUNGER KREATIVER.</h1>
    <p>MODE, FILM, MUSIK, DESIGN UND GESELLSCHAFTLICHE THEMEN WERDEN NICHT GETRENNT, SONDERN MITEINANDER VERZAHNT.</p>
    <a class="stroke-button" href="<?= e(url_for('about.php')) ?>">UEBER MICASA</a>
  </article>
  <article class="paper hero-paper hero-paper-offset">
    <p class="spiked-label">ARCHIV / KATALOGISIERUNG</p>
    <p>JEDE KAMPAGNE IST MEHR ALS EINE PRODUKTVEROEFFENTLICHUNG. SIE TRAEGT EINE ERZAEHLUNG, EINE AESTHETIK UND EINE KOLLABORATION.</p>
    <p>DIE WEBSITE WAECHST WIE EIN ANALOGES MAGAZIN: SEITEN, BILDER, VIDEOS, CREDITS UND PIECES KOENNEN UEBER DAS ADMIN PANEL ERGAENZT WERDEN.</p>
  </article>
</section>

<section class="featured-grid" aria-label="Ausgewaehlte Projekte">
  <?php foreach ($featured as $index => $project): ?>
    <a class="paper project-tile tile-<?= e((string) (($index % 4) + 1)) ?>" href="<?= e(url_for('project.php?id=' . rawurlencode((string) $project['id']))) ?>">
      <img src="<?= e(asset_url((string) $project['image'])) ?>" alt="<?= e((string) $project['title']) ?>">
      <span class="spiked-label"><?= e((string) $project['kicker']) ?></span>
      <strong><?= e((string) $project['title']) ?></strong>
      <span><?= e((string) $project['summary']) ?></span>
    </a>
  <?php endforeach; ?>
</section>

<section class="archive-section" id="archive" aria-labelledby="archive-title">
  <div class="section-heading">
    <p class="spiked-label">INDEX 001</p>
    <h2 id="archive-title">ARCHIV KATALOG</h2>
  </div>
  <ol class="archive-list">
    <?php foreach ($projects as $project): ?>
      <li class="paper archive-entry">
        <a href="<?= e(url_for('project.php?id=' . rawurlencode((string) $project['id']))) ?>">
          <span><?= e(str_pad((string) ($project['release_order'] ?? 0), 2, '0', STR_PAD_LEFT)) ?></span>
          <strong><?= e((string) $project['title']) ?></strong>
          <span><?= e((string) $project['status']) ?></span>
          <span><?= e((string) $project['type']) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ol>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
