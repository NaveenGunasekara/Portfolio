<?php
/**
 * Application bootstrap: session, PDO, default admin seed (first run only).
 */
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$configPath = dirname(__DIR__) . '/config/db.php';
if (!is_file($configPath)) {
    http_response_code(500);
    exit('Missing config/db.php');
}

$dbConfig = require $configPath;

/**
 * URL prefix for the site root (e.g. "" or "/portfolio") derived from SCRIPT_NAME.
 */
function portfolio_web_root_prefix(): string
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
 * Verify imported schema so pages don't fatal with "table doesn't exist".
 */
function portfolio_required_tables(): array
{
    return ['admins', 'site_about', 'projects', 'music_items', 'contact_messages'];
}

function portfolio_find_missing_tables(PDO $pdo): array
{
    $missing = [];
    foreach (portfolio_required_tables() as $table) {
        try {
            $stmt = $pdo->query('SHOW TABLES LIKE ' . $pdo->quote($table));
            $found = $stmt ? $stmt->fetchColumn() : false;
            if (!$found) {
                $missing[] = $table;
            }
        } catch (Throwable $e) {
            $missing[] = $table;
        }
    }
    return $missing;
}

/**
 * Simple HTML error/setup pages (no dependencies on helpers).
 */
function portfolio_render_db_error_page(string $title, string $bodyHtml): string
{
    $installHref = htmlspecialchars(portfolio_web_root_prefix() . '/install.php', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</title>'
        . '<style>body{margin:0;font-family:system-ui,sans-serif;background:#070b14;color:#e5e7eb;line-height:1.55;}'
        . '.wrap{max-width:720px;margin:0 auto;padding:28px 18px;}code{background:#111827;padding:2px 6px;border-radius:6px;}'
        . '.card{border:1px solid #1f2937;border-radius:14px;padding:18px;background:#0f172a;}'
        . 'h1{margin:0 0 10px;font-size:22px;} a{color:#22d3ee;font-weight:700;}</style></head><body><div class="wrap">'
        . '<div class="card"><h1>' . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h1>'
        . $bodyHtml
        . '<p style="margin-top:14px;font-size:14px;color:#94a3b8">Open <a href="' . $installHref . '">install.php</a> for a connection checklist.</p>'
        . '</div></div></body></html>';
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $dbConfig['host'],
    $dbConfig['port'] ?? '3306',
    $dbConfig['name'],
    $dbConfig['charset']
);

try {
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    exit(portfolio_render_db_error_page(
        'Cannot connect to MySQL.',
        '<p>Start MySQL in XAMPP (or your stack), then confirm credentials in <code>config/db.php</code>.</p>'
    ));
}

if (PHP_SAPI !== 'cli') {
    $missingTables = portfolio_find_missing_tables($pdo);
    if ($missingTables !== []) {
        http_response_code(503);
        header('Content-Type: text/html; charset=UTF-8');
        $list = '<ul>' . implode('', array_map(static fn ($t) => '<li><code>' . htmlspecialchars($t, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></li>', $missingTables)) . '</ul>';
        exit(portfolio_render_db_error_page(
            'Database setup required',
            '<p>Import <code>database.sql</code> into your MySQL server (database name in <code>config/db.php</code>, default <code>portfolio_cms</code>).</p>'
            . '<p><strong>Missing tables:</strong></p>' . $list
            . '<p>In phpMyAdmin: Import → choose <code>database.sql</code> → Go.</p>'
        ));
    }
}

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/upload_handler.php';

/**
 * First-run safety net: if admins table has zero rows, create default admin.
 * Default credentials: admin / Admin@2026
 * Remove or disable after setup in production by inserting your own admin manually.
 */
function portfolio_ensure_default_admin(PDO $pdo): void
{
    try {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
        if ($count === 0) {
            $username = 'admin';
            $hash = password_hash('Admin@2026', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash, created_at) VALUES (:u, :p, NOW())');
            $stmt->execute(['u' => $username, 'p' => $hash]);
        }
    } catch (Throwable $e) {
        // Table might not exist yet; user must import database.sql first.
    }
}

portfolio_ensure_default_admin($pdo);