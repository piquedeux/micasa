<?php

require_once __DIR__ . '/../lib/admin.php';

admin_boot();

$error = '';
$tab = in_array((string) ($_GET['tab'] ?? 'projects'), ['projects', 'about', 'shop'], true) ? (string) ($_GET['tab'] ?? 'projects') : 'projects';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    csrf_check();
    if (!admin_login((string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''))) {
        $error = 'LOGIN FAILED';
    } else {
        header('Location: ' . panel_url());
        exit;
    }
}

if (admin_is_logged_in() && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_about') {
    csrf_check();
    site_content_save_section('about', [
        'heading_de' => trim((string) ($_POST['heading_de'] ?? '')),
        'heading_en' => trim((string) ($_POST['heading_en'] ?? '')),
        'body_de' => trim((string) ($_POST['body_de'] ?? '')),
        'body_en' => trim((string) ($_POST['body_en'] ?? '')),
    ]);
    header('Location: ' . panel_url('?tab=about'));
    exit;
}

if (admin_is_logged_in() && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_shop') {
    csrf_check();
    site_content_save_section('shop', [
        'heading_de' => trim((string) ($_POST['heading_de'] ?? '')),
        'heading_en' => trim((string) ($_POST['heading_en'] ?? '')),
        'body_de' => trim((string) ($_POST['body_de'] ?? '')),
        'body_en' => trim((string) ($_POST['body_en'] ?? '')),
        'checkout_url' => trim((string) ($_POST['checkout_url'] ?? '')),
    ]);
    header('Location: ' . panel_url('?tab=shop'));
    exit;
}

$page_title = 'MICASA ADMIN';
$body_class = 'page-admin';
include __DIR__ . '/../partials/header.php';
?>

<?php if (!admin_is_logged_in()): ?>
  <section class="admin-layout">
    <form class="paper admin-form" method="post" action="<?= e(panel_url()) ?>">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="login">
      <p class="spiked-label">ADMIN / LOGIN</p>
      <?php if ($error !== ''): ?>
        <p class="notice"><?= e($error) ?></p>
      <?php endif; ?>
      <label>
        USERNAME
        <input type="text" name="username" autocomplete="username" required>
      </label>
      <label>
        PASSWORD
        <input type="password" name="password" autocomplete="current-password" required>
      </label>
      <button class="stroke-button" type="submit">LOGIN</button>
    </form>
  </section>
<?php else: ?>
  <?php $projects = projects_all(true); ?>
  <section class="admin-layout">
    <article class="paper admin-toolbar">
      <p class="spiked-label">ADMIN / PROJECTS</p>
      <?php if ((string) app_config('admin.password_hash') === '$2y$12$S82kND8By/mqtCJZNNMiBOauVwSJj5vFRJmLXsrhZhCaJBMCqURlO'): ?>
        <p class="notice">DEFAULT PASSWORD ACTIVE. CHANGE CONFIG.LOCAL.PHP BEFORE DEPLOYMENT.</p>
      <?php endif; ?>
      <div class="button-row">
        <a class="stroke-button" href="<?= e(panel_url()) ?>">PROJECTS</a>
        <a class="stroke-button" href="<?= e(panel_url('?tab=about')) ?>">ABOUT</a>
        <a class="stroke-button" href="<?= e(panel_url('?tab=shop')) ?>">SHOP</a>
        <a class="stroke-button" href="<?= e(panel_url('project.php')) ?>">NEW PROJECT</a>
        <form method="post" action="<?= e(panel_url('logout.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <button class="stroke-button" type="submit">LOGOUT</button>
        </form>
      </div>
    </article>
    <?php if ($tab === 'about'): ?>
      <?php $about = site_content_section('about'); ?>
      <form class="paper admin-form" method="post" action="<?= e(panel_url('?tab=about')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save_about">
        <p class="spiked-label">ABOUT PAGE</p>
        <div class="form-grid">
          <label>HEADING DE<input type="text" name="heading_de" value="<?= e((string) ($about['heading_de'] ?? '')) ?>"></label>
          <label>HEADING EN<input type="text" name="heading_en" value="<?= e((string) ($about['heading_en'] ?? '')) ?>"></label>
        </div>
        <div class="form-grid">
          <label>BODY DE<textarea name="body_de" rows="10"><?= e((string) ($about['body_de'] ?? '')) ?></textarea></label>
          <label>BODY EN<textarea name="body_en" rows="10"><?= e((string) ($about['body_en'] ?? '')) ?></textarea></label>
        </div>
        <button class="stroke-button" type="submit">SAVE ABOUT</button>
      </form>
    <?php elseif ($tab === 'shop'): ?>
      <?php $shop = site_content_section('shop'); ?>
      <form class="paper admin-form" method="post" action="<?= e(panel_url('?tab=shop')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save_shop">
        <p class="spiked-label">SHOP PAGE</p>
        <div class="form-grid">
          <label>HEADING DE<input type="text" name="heading_de" value="<?= e((string) ($shop['heading_de'] ?? '')) ?>"></label>
          <label>HEADING EN<input type="text" name="heading_en" value="<?= e((string) ($shop['heading_en'] ?? '')) ?>"></label>
        </div>
        <div class="form-grid">
          <label>BODY DE<textarea name="body_de" rows="8"><?= e((string) ($shop['body_de'] ?? '')) ?></textarea></label>
          <label>BODY EN<textarea name="body_en" rows="8"><?= e((string) ($shop['body_en'] ?? '')) ?></textarea></label>
        </div>
        <label>BIG CARTEL CART / CHECKOUT URL<input type="url" name="checkout_url" value="<?= e((string) ($shop['checkout_url'] ?? '')) ?>"></label>
        <button class="stroke-button" type="submit">SAVE SHOP</button>
      </form>
    <?php else: ?>
    <ol class="admin-list">
      <?php foreach ($projects as $project): ?>
        <li class="paper admin-row">
          <div>
            <span class="spiked-label"><?= e(str_pad((string) ($project['release_order'] ?? 0), 2, '0', STR_PAD_LEFT)) ?> / <?= !empty($project['published']) ? 'LIVE' : 'HIDDEN' ?></span>
            <strong><?= e((string) $project['title']) ?></strong>
            <span><?= e((string) $project['status']) ?> / <?= e((string) $project['type']) ?></span>
          </div>
          <div class="button-row">
            <a class="stroke-button" href="<?= e(panel_url('project.php?id=' . rawurlencode((string) $project['id']))) ?>">EDIT</a>
            <form method="post" action="<?= e(panel_url('delete.php')) ?>" onsubmit="return confirm('DELETE PROJECT?');">
              <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= e((string) $project['id']) ?>">
              <button class="stroke-button" type="submit">DELETE</button>
            </form>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
    <?php endif; ?>
  </section>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
