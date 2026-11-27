<?php

declare(strict_types=1);

function load_app_env(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

load_app_env(dirname(__DIR__) . '/.env');

define('APP_NAME', getenv('APP_NAME') ?: 'Maison Serein');
define('APP_CURRENCY', getenv('APP_CURRENCY') ?: 'BDT');
define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'Asia/Dhaka');
date_default_timezone_set(APP_TIMEZONE);
