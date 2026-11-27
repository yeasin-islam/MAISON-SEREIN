<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

function prompt(string $label): string
{
    fwrite(STDOUT, $label);
    return trim((string) fgets(STDIN));
}

function promptSecret(string $label): string
{
    fwrite(STDOUT, $label);
    $isWindows = PHP_OS_FAMILY === 'Windows';
    if (!$isWindows) {
        exec('stty -echo 2>/dev/null');
    }
    $value = trim((string) fgets(STDIN));
    if (!$isWindows) {
        exec('stty echo 2>/dev/null');
        fwrite(STDOUT, PHP_EOL);
    }
    return $value;
}

$name = prompt('Admin full name: ');
$email = strtolower(prompt('Admin email: '));
$password = promptSecret('Admin password (min. 12 characters): ');
$confirmation = promptSecret('Confirm password: ');

if (mb_strlen($name) < 2 || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Enter a valid name and email address.\n");
    exit(1);
}
if (strlen($password) < 12 || !hash_equals($password, $confirmation)) {
    fwrite(STDERR, "Use a password of at least 12 characters and make both entries match.\n");
    exit(1);
}

try {
    $pdo = database();
    $existing = $pdo->prepare('SELECT id FROM admins WHERE email = :email LIMIT 1');
    $existing->execute(['email' => $email]);
    if ($existing->fetchColumn()) {
        fwrite(STDERR, "An administrator with that email already exists. No account was changed.\n");
        exit(1);
    }
    $insert = $pdo->prepare('INSERT INTO admins (full_name, email, password_hash, is_active, created_at) VALUES (:name, :email, :hash, 1, NOW())');
    $insert->execute(['name' => $name, 'email' => $email, 'hash' => password_hash($password, PASSWORD_DEFAULT)]);
    fwrite(STDOUT, "Administrator account created. Sign in at /admin/index.php?page=login.\n");
} catch (Throwable $error) {
    fwrite(STDERR, "Could not create the admin account: " . $error->getMessage() . PHP_EOL);
    exit(1);
}
