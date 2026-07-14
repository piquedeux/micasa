<?php

require_once __DIR__ . '/../lib/admin.php';

admin_require_login();

$existing_id = (string) ($_GET['id'] ?? '');
$project = $existing_id !== '' ? project_find($existing_id) : project_blank();
if (!$project) {
    http_response_code(404);
    exit('PROJECT NOT FOUND.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $existing_id = trim((string) ($_POST['existing_id'] ?? ''));
    $image = trim((string) ($_POST['image'] ?? 'assets/placeholders/project-blank.svg'));
    $uploaded = isset($_FILES['image_upload']) ? handle_project_upload($_FILES['image_upload']) : null;

    if ($uploaded !== null) {
        $image = $uploaded;
    }

    $payload = [
        'id' => trim((string) ($_POST['id'] ?? '')),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'kicker' => trim((string) ($_POST['kicker'] ?? '')),
        'status' => trim((string) ($_POST['status'] ?? '')),
        'type' => trim((string) ($_POST['type'] ?? '')),
        'release_order' => (int) ($_POST['release_order'] ?? 99),
        'summary' => trim((string) ($_POST['summary'] ?? '')),
        'body' => trim((string) ($_POST['body'] ?? '')),
        'image' => $image,
        'video_url' => trim((string) ($_POST['video_url'] ?? '')),
        'artists' => trim((string) ($_POST['artists'] ?? '')),
        'shopify_handle' => trim((string) ($_POST['shopify_handle'] ?? '')),
        'published' => isset($_POST['published']),
        'featured' => isset($_POST['featured']),
    ];

    if ($payload['title'] === '') {
        $error = 'TITLE REQUIRED';
        $project = array_replace(project_blank(), $payload);
    } else {
        $saved = project_save($payload, $existing_id !== '' ? $existing_id : null);
        header('Location: ' . url_for('admin/index.php#' . rawurlencode((string) $saved['id'])));
        exit;
    }
}

$page_title = 'MICASA ADMIN / PROJECT';
$body_class = 'page-admin';
include __DIR__ . '/../partials/header.php';
?>

<section class="admin-layout">
  <form class="paper admin-form project-form" method="post" enctype="multipart/form-data" action="<?= e(url_for('admin/project.php' . ($existing_id !== '' ? '?id=' . rawurlencode($existing_id) : ''))) ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="existing_id" value="<?= e($existing_id) ?>">
    <p class="spiked-label"><?= $existing_id === '' ? 'NEW PROJECT' : 'EDIT PROJECT' ?></p>
    <?php if ($error !== ''): ?>
      <p class="notice"><?= e($error) ?></p>
    <?php endif; ?>
    <div class="form-grid">
      <label>
        ID
        <input type="text" name="id" value="<?= e((string) ($project['id'] ?? '')) ?>" placeholder="AUTO FROM TITLE">
      </label>
      <label>
        ORDER
        <input type="number" name="release_order" value="<?= e((string) ($project['release_order'] ?? 99)) ?>">
      </label>
      <label>
        TITLE
        <input type="text" name="title" value="<?= e((string) ($project['title'] ?? '')) ?>" required>
      </label>
      <label>
        KICKER
        <input type="text" name="kicker" value="<?= e((string) ($project['kicker'] ?? '')) ?>">
      </label>
      <label>
        STATUS
        <input type="text" name="status" value="<?= e((string) ($project['status'] ?? '')) ?>">
      </label>
      <label>
        TYPE
        <input type="text" name="type" value="<?= e((string) ($project['type'] ?? '')) ?>">
      </label>
      <label>
        ARTISTS
        <input type="text" name="artists" value="<?= e((string) ($project['artists'] ?? '')) ?>">
      </label>
      <label>
        SHOPIFY HANDLE
        <input type="text" name="shopify_handle" value="<?= e((string) ($project['shopify_handle'] ?? '')) ?>">
      </label>
    </div>
    <label>
      SUMMARY
      <textarea name="summary" rows="4"><?= e((string) ($project['summary'] ?? '')) ?></textarea>
    </label>
    <label>
      BODY
      <textarea name="body" rows="10"><?= e((string) ($project['body'] ?? '')) ?></textarea>
    </label>
    <div class="form-grid">
      <label>
        IMAGE PATH OR URL
        <input type="text" name="image" value="<?= e((string) ($project['image'] ?? '')) ?>">
      </label>
      <label>
        UPLOAD IMAGE
        <input type="file" name="image_upload" accept="image/*">
      </label>
      <label>
        VIDEO URL
        <input type="url" name="video_url" value="<?= e((string) ($project['video_url'] ?? '')) ?>">
      </label>
      <label class="checkbox-label">
        <input type="checkbox" name="published" <?= !empty($project['published']) ? 'checked' : '' ?>>
        PUBLISHED
      </label>
      <label class="checkbox-label">
        <input type="checkbox" name="featured" <?= !empty($project['featured']) ? 'checked' : '' ?>>
        FEATURED
      </label>
    </div>
    <div class="button-row">
      <button class="stroke-button" type="submit">SAVE</button>
      <a class="stroke-button" href="<?= e(url_for('admin/index.php')) ?>">CANCEL</a>
    </div>
  </form>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>
