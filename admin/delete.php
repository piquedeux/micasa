<?php

require_once __DIR__ . '/../lib/admin.php';

admin_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . panel_url());
    exit;
}

csrf_check();
$id = trim((string) ($_POST['id'] ?? ''));
if ($id !== '') {
    project_delete($id);
}

header('Location: ' . panel_url());
exit;
