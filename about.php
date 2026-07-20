<?php

require_once __DIR__ . '/lib/bootstrap.php';

$about = site_content_section('about');
$page_title = 'MICASA INFO';
$body_class = 'page-about';

include __DIR__ . '/partials/header.php';
?>

<section class="about-layout">
  <article class="paper">
    <p class="spiked-label"><?= e(tr('WAS IST MICASA', 'WHAT IS MICASA')) ?></p>
    <h1><?= e(localized($about, 'heading')) ?></h1>
    <div class="body-copy"><?= nl2br(e(localized($about, 'body'))) ?></div>
  </article>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
