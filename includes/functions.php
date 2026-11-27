<?php

declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(float|int|string|null $amount): string
{
    return '৳' . number_format((float) $amount, 0);
}

function app_url(string $page = 'home', array $params = []): string
{
    return '?' . http_build_query(['page' => $page] + $params);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function verify_csrf(?string $token): void
{
    if (!$token || !hash_equals(csrf_token(), $token)) {
        throw new RuntimeException('Your session expired. Refresh the page and try again.');
    }
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function redirect(string $location): never
{
    header('Location: ' . $location, true, 303);
    exit;
}

function normalize_phone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (str_starts_with($digits, '880')) {
        $digits = '0' . substr($digits, 3);
    } elseif (str_starts_with($digits, '1') && strlen($digits) === 10) {
        $digits = '0' . $digits;
    }
    return $digits;
}

function valid_phone(string $phone): bool
{
    return (bool) preg_match('/^01[3-9]\d{8}$/', normalize_phone($phone));
}

function safe_link_url(?string $url, string $fallback = '#'): string
{
    $url = trim($url ?? '');
    if ($url === '') return $fallback;
    if (str_starts_with($url, '?') || str_starts_with($url, '#') || (str_starts_with($url, '/') && !str_starts_with($url, '//'))) return $url;
    if (preg_match('/^index\\.php(?:\\?|$)/i', $url)) return $url;
    $parts = parse_url($url);
    if (is_array($parts) && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true) && !empty($parts['host'])) return $url;
    return $fallback;
}

function cart_count(): int
{
    return array_sum(array_map('intval', $_SESSION['cart'] ?? []));
}

function current_customer_id(): ?int
{
    return isset($_SESSION['customer_id']) ? (int) $_SESSION['customer_id'] : null;
}

function require_customer(): void
{
    if (!current_customer_id()) {
        redirect(app_url('login', ['next' => 'account']));
    }
}

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        redirect('../admin/index.php?page=login');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}
