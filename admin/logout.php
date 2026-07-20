<?php

require_once __DIR__ . '/../lib/admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

admin_logout();
header('Location: ' . panel_url());
exit;
