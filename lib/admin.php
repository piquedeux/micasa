<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

function admin_boot(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name((string) app_config('admin.session_name', 'micasa_admin'));
        session_start();
    }
}

function admin_is_logged_in(): bool
{
    admin_boot();

    return !empty($_SESSION['admin_logged_in']);
}

function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        header('Location: ' . url_for('admin/index.php'));
        exit;
    }
}

function admin_login(string $username, string $password): bool
{
    admin_boot();
    $expected_user = (string) app_config('admin.username', 'admin');
    $hash = (string) app_config('admin.password_hash', '');

    if ($username !== $expected_user || $hash === '' || !password_verify($password, $hash)) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_user'] = $username;

    return true;
}

function admin_logout(): void
{
    admin_boot();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function csrf_token(): string
{
    admin_boot();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_check(): void
{
    admin_boot();
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
        http_response_code(419);
        exit('CSRF token mismatch.');
    }
}

function handle_project_upload(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    $mime = function_exists('mime_content_type') ? (mime_content_type($file['tmp_name']) ?: '') : '';
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/svg+xml' => 'svg',
    ];

    if (isset($extensions[$mime])) {
        $extension = $extensions[$mime];
    } else {
        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
            return null;
        }
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
    }

    $upload_dir = (string) app_config('storage.uploads_dir');
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $base = slugify(pathinfo((string) ($file['name'] ?? 'upload'), PATHINFO_FILENAME));
    $filename = date('YmdHis') . '-' . $base . '.' . $extension;
    $target = rtrim($upload_dir, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return null;
    }

    return rtrim((string) app_config('storage.uploads_url', 'assets/uploads'), '/') . '/' . $filename;
}
