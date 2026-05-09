<?php
/**
 * Shared helpers: escaping, redirects, CSRF, auth guards.
 */
declare(strict_types=1);

function app_config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $path = dirname(__DIR__) . '/config/app.php';
        $cfg = is_file($path) ? require $path : ['base_path' => ''];
    }
    return $cfg;
}

/**
 * Guess URL prefix when the app lives in a subfolder (e.g. /portfolio/).
 * Skips a trailing /admin segment so admin pages resolve assets and links correctly.
 */
function portfolio_infer_base_path(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $script = str_replace('\\', '/', (string) $script);
    $dir = dirname($script);
    $dir = ($dir === '\\' || $dir === '.') ? '/' : $dir;

    if (preg_match('#/admin$#', $dir)) {
        $dir = dirname($dir);
    }

    if ($dir === '/' || $dir === '.' || $dir === '') {
        return '';
    }

    return rtrim($dir, '/');
}

/**
 * Build a URL relative to base_path (explicit in config/app.php) or inferred from the request.
 */
function url_for(string $path): string
{
    $configured = rtrim((string) (app_config()['base_path'] ?? ''), '/');
    $base = $configured !== '' ? $configured : portfolio_infer_base_path();
    $path = ltrim($path, '/');
    return ($base === '' ? '' : $base) . '/' . $path;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    if (preg_match('#^https?://#i', $path)) {
        header('Location: ' . $path);
        exit;
    }
    // Already absolute site path (starts with /) — do not run through url_for again.
    if ($path !== '' && $path[0] === '/') {
        header('Location: ' . $path);
        exit;
    }
    header('Location: ' . url_for(ltrim($path, '/')));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_verify(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $token);
}

function require_csrf_post(): void
{
    $token = $_POST['_csrf'] ?? null;
    if (!csrf_verify(is_string($token) ? $token : null)) {
        http_response_code(419);
        exit('Invalid session token. Please refresh and try again.');
    }
}

function is_logged_in_admin(): bool
{
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_username']);
}

function require_admin(): void
{
    if (!is_logged_in_admin()) {
        redirect('admin/login.php');
    }
}

/**
 * Notify email for contact form (matches user requirement).
 */
function contact_notification_email(): string
{
    return 'naveengunasekara62@gmail.com';
}

/**
 * Short plain-text excerpt without relying on mbstring being enabled.
 */
function portfolio_excerpt(string $text, int $max = 120): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
    if ($text === '') {
        return '';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') <= $max) {
            return $text;
        }
        return mb_substr($text, 0, max(0, $max - 1), 'UTF-8') . '…';
    }
    return strlen($text) <= $max ? $text : substr($text, 0, max(0, $max - 1)) . '…';
}
