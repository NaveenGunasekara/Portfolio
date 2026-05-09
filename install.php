<?php
/**
 * Lightweight diagnostics (does not load full bootstrap / schema gate).
 * Open in browser: http://localhost/portfolio/install.php
 */
declare(strict_types=1);

$configPath = __DIR__ . '/config/db.php';
if (!is_file($configPath)) {
    http_response_code(500);
    exit('Missing config/db.php');
}

$dbConfig = require $configPath;

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $dbConfig['host'],
    $dbConfig['name'],
    $dbConfig['charset']
);

$connected = false;
$error = '';
$pdo = null;

try {
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $connected = true;
} catch (PDOException $e) {
    $error = $e->getMessage();
}

$required = ['admins', 'site_about', 'projects', 'music_items', 'contact_messages'];
$missing = [];
$tableRows = [];

if ($pdo instanceof PDO) {
    foreach ($required as $table) {
        try {
            $stmt = $pdo->query('SHOW TABLES LIKE ' . $pdo->quote($table));
            $found = $stmt ? $stmt->fetchColumn() : false;
            $tableRows[$table] = (bool) $found;
            if (!$found) {
                $missing[] = $table;
            }
        } catch (Throwable $e) {
            $tableRows[$table] = false;
            $missing[] = $table;
        }
    }
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portfolio · Install check</title>
  <style>
    body{margin:0;font-family:system-ui,sans-serif;background:#070b14;color:#e5e7eb;line-height:1.55;}
    .wrap{max-width:820px;margin:0 auto;padding:28px 18px;}
    .card{border:1px solid #1f2937;border-radius:14px;padding:18px;background:#0f172a;margin-bottom:14px;}
    h1{margin:0 0 10px;font-size:22px;}
    code{background:#111827;padding:2px 6px;border-radius:6px;}
    .ok{color:#6ee7b7;font-weight:800;}
    .bad{color:#fda4af;font-weight:800;}
    table{width:100%;border-collapse:collapse;margin-top:12px;font-size:14px;}
    th,td{border-bottom:1px solid #1f2937;padding:8px;text-align:left;}
    a{color:#22d3ee;font-weight:800;}
    .btn{display:inline-block;margin-top:12px;padding:10px 14px;border-radius:12px;background:#4f46e5;color:white;text-decoration:none;font-weight:900;}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Portfolio install check</h1>
      <p>Config file: <code>config/db.php</code></p>
      <p>DSN target database: <code><?= htmlspecialchars((string) $dbConfig['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></code></p>

      <?php if (!$connected): ?>
        <p class="bad">MySQL connection failed.</p>
        <p><code><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></code></p>
        <p>Start MySQL (XAMPP) and verify host/user/password.</p>
      <?php else: ?>
        <p class="ok">Connected to MySQL.</p>
      <?php endif; ?>
    </div>

    <?php if ($connected): ?>
      <div class="card">
        <h2 style="margin:0 0 10px;font-size:18px;">Tables</h2>
        <table>
          <thead><tr><th>Table</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($tableRows as $name => $ok): ?>
              <tr>
                <td><code><?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></code></td>
                <td><?= $ok ? '<span class="ok">OK</span>' : '<span class="bad">Missing — import database.sql</span>' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($missing !== []): ?>
          <p style="margin-top:12px">In phpMyAdmin choose your database → <strong>Import</strong> → select <code>database.sql</code>.</p>
        <?php else: ?>
          <p style="margin-top:12px" class="ok">Schema looks complete.</p>
          <a class="btn" href="index.php">Open portfolio home</a>
          <a class="btn" href="admin/login.php" style="margin-left:10px;background:#0ea5e9;">Admin login</a>
          <p style="margin-top:14px;font-size:13px;color:#94a3b8">Default admin (if table was empty on first bootstrap): <code>admin</code> / <code>Admin@2026</code></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
