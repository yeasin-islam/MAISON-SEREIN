<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/functions.php';

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=63072000');
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('maison_serein_session');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

if (!isset($_SESSION['initialized'])) {
    session_regenerate_id(true);
    $_SESSION['initialized'] = true;
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

if (!empty($_SESSION['admin_id'])) {
    $now = time();
    if (isset($_SESSION['admin_last_activity']) && $now - (int) $_SESSION['admin_last_activity'] > 1800) {
        unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_last_activity']);
        session_regenerate_id(true);
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Your studio session expired. Please sign in again.'];
    } else {
        $_SESSION['admin_last_activity'] = $now;
    }
}
