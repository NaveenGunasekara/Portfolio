<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/AdminRepository.php';

if (is_logged_in_admin()) {
    redirect('admin/dashboard.php');
}

$error = '';

if (!empty($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_post();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $repo = new AdminRepository($pdo);
    $admin = $repo->verify($username, $password);
    if ($admin) {
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = (string) $admin['username'];
        redirect('admin/dashboard.php');
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login · Portfolio CMS</title>
  <style>
    :root{
      --bg:#070b14;
      --panel:#0f172a;
      --border:#1f2937;
      --text:#e5e7eb;
      --muted:#94a3b8;
      --accent:#6366f1;
      --accent2:#22d3ee;
      --danger:#fb7185;
      --radius:18px;
    }
    *{box-sizing:border-box}
    body{margin:0;min-height:100vh;display:grid;place-items:center;padding:22px;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:radial-gradient(900px 520px at 30% 10%,rgba(99,102,241,.22),transparent),var(--bg);color:var(--text)}
    .card{width:min(520px,100%);border:1px solid rgba(31,41,55,.85);background:rgba(17,24,39,.62);border-radius:calc(var(--radius) + 8px);padding:18px;box-shadow:0 24px 80px rgba(0,0,0,.55)}
    h1{margin:6px 0 6px;font-size:22px;letter-spacing:-.02em}
    p{margin:0;color:var(--muted);font-size:14px;line-height:1.55}
    label{display:block;margin:12px 0 8px;font-size:13px;color:#cbd5e1;font-weight:800}
    input{width:100%;padding:12px 12px;border-radius:14px;border:1px solid rgba(148,163,184,.22);background:rgba(15,23,42,.65);color:var(--text);outline:none}
    input:focus{border-color:rgba(99,102,241,.55);box-shadow:0 0 0 4px rgba(99,102,241,.18)}
    button{margin-top:14px;width:100%;padding:12px 14px;border-radius:14px;border:0;cursor:pointer;font-weight:900;color:white;background:linear-gradient(135deg,var(--accent),#4f46e5);box-shadow:0 18px 55px rgba(79,70,229,.35)}
    button:hover{filter:brightness(1.06)}
    .err{border:1px solid rgba(251,113,133,.35);background:rgba(251,113,133,.08);color:#fecdd3;padding:12px;border-radius:14px;margin:12px 0;font-size:13px}
    .foot{margin-top:14px;display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;font-size:13px;color:var(--muted)}
    a{color:var(--accent2);font-weight:800;text-decoration:none}
  </style>
</head>
<body>
  <main class="card" aria-label="Admin login">
    <h1>CMS Login</h1>
    <p>First run seeds <strong>admin</strong> / <strong>Admin@2026</strong> if the admins table is empty.</p>

    <?php if ($error !== ''): ?>
      <div class="err" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url_for('admin/login.php')) ?>">
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
      <label for="username">Username</label>
      <input id="username" name="username" autocomplete="username" required>

      <label for="password">Password</label>
      <input id="password" name="password" type="password" autocomplete="current-password" required>

      <button type="submit">Sign in</button>
    </form>

    <div class="foot">
      <a href="<?= e(url_for('index.php')) ?>">← Back to site</a>
      <span>Portfolio CMS</span>
    </div>
  </main>
</body>
</html>
