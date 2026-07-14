<?php

require_once __DIR__ . '/../lib/admin.php';

admin_boot();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    csrf_check();
    if (!admin_login((string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''))) {
        $error = 'LOGIN FAILED';
    }
}

$page_title = 'MICASA ADMIN';
$body_class = 'page-admin';
include __DIR__ . '/../partials/header.php';
?>

<?php if (!admin_is_logged_in()): ?>
  <section class="admin-layout">
    <form class="paper admin-form" method="post" action="<?= e(url_for('admin/index.php')) ?>">
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
        <a class="stroke-button" href="<?= e(url_for('admin/project.php')) ?>">NEW PROJECT</a>
        <form method="post" action="<?= e(url_for('admin/logout.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <button class="stroke-button" type="submit">LOGOUT</button>
        </form>
      </div>
    </article>
    <ol class="admin-list">
      <?php foreach ($projects as $project): ?>
        <li class="paper admin-row">
          <div>
            <span class="spiked-label"><?= e(str_pad((string) ($project['release_order'] ?? 0), 2, '0', STR_PAD_LEFT)) ?> / <?= !empty($project['published']) ? 'LIVE' : 'HIDDEN' ?></span>
            <strong><?= e((string) $project['title']) ?></strong>
            <span><?= e((string) $project['status']) ?> / <?= e((string) $project['type']) ?></span>
          </div>
          <div class="button-row">
            <a class="stroke-button" href="<?= e(url_for('admin/project.php?id=' . rawurlencode((string) $project['id']))) ?>">EDIT</a>
            <form method="post" action="<?= e(url_for('admin/delete.php')) ?>" onsubmit="return confirm('DELETE PROJECT?');">
              <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= e((string) $project['id']) ?>">
              <button class="stroke-button" type="submit">DELETE</button>
            </form>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  </section>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
