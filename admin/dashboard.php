<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/ProjectRepository.php';
require_once dirname(__DIR__) . '/includes/MusicRepository.php';
require_once dirname(__DIR__) . '/includes/MessageRepository.php';

require_admin();

$projects = new ProjectRepository($pdo);
$music = new MusicRepository($pdo);
$msgs = new MessageRepository($pdo);

$counts = [
    'projects' => count($projects->allOrdered()),
    'music'    => count($music->allOrdered()),
    'messages' => count($msgs->allRecent(500)),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard · Admin</title>
  <style>
    :root{
      --bg:#070b14;
      --panel:#0f172a;
      --border:#1f2937;
      --text:#e5e7eb;
      --muted:#94a3b8;
      --accent:#6366f1;
      --accent2:#22d3ee;
      --radius:18px;
      --max:1100px;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:radial-gradient(900px 520px at 15% 0%,rgba(34,211,238,.12),transparent),var(--bg);color:var(--text)}
    a{color:inherit;text-decoration:none}
    .wrap{max-width:var(--max);margin:0 auto;padding:0 20px}
    header{border-bottom:1px solid rgba(31,41,55,.75);background:rgba(7,11,20,.62);backdrop-filter:blur(12px)}
    .top{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 0;flex-wrap:wrap}
    .brand{font-weight:950;letter-spacing:-.02em}
    .links{display:flex;gap:10px;flex-wrap:wrap}
    .links a{padding:10px 12px;border-radius:12px;color:var(--muted);border:1px solid rgba(148,163,184,.18);background:rgba(255,255,255,.03)}
    .links a:hover{color:var(--text)}
    main{padding:22px 0 40px}
    h1{margin:10px 0 8px;font-size:22px}
    .sub{margin:0;color:var(--muted);font-size:14px;max-width:80ch;line-height:1.55}
    .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:16px}
    @media (max-width:900px){.grid{grid-template-columns:1fr}}
    .tile{border:1px solid rgba(31,41,55,.85);background:rgba(17,24,39,.55);border-radius:var(--radius);padding:14px}
    .tile h2{margin:0;font-size:14px;color:#cbd5e1}
    .tile .num{margin-top:8px;font-size:34px;font-weight:950;letter-spacing:-.03em}
    .tile a{display:inline-block;margin-top:10px;font-weight:900;color:var(--accent2);font-size:13px}
    .panel{margin-top:16px;border:1px solid rgba(31,41,55,.85);background:rgba(17,24,39,.45);border-radius:calc(var(--radius) + 6px);padding:14px}
    .panel h3{margin:0 0 10px;font-size:14px;color:#cbd5e1}
    .quick{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
    @media (max-width:700px){.quick{grid-template-columns:1fr}}
    .q{padding:12px;border-radius:14px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.55)}
    .q strong{display:block;margin-bottom:6px}
    .q span{color:var(--muted);font-size:13px;line-height:1.45}
    footer{padding:18px 0 30px;color:var(--muted);font-size:12px}
    code{color:#cbd5e1}
  </style>
</head>
<body>
  <header>
    <div class="wrap top">
      <div class="brand">Portfolio CMS</div>
      <nav class="links" aria-label="Admin">
        <a href="<?= e(url_for('admin/dashboard.php')) ?>">Dashboard</a>
        <a href="<?= e(url_for('admin/manage_about.php')) ?>">About</a>
        <a href="<?= e(url_for('admin/manage_projects.php')) ?>">Projects</a>
        <a href="<?= e(url_for('admin/manage_music.php')) ?>">Music</a>
        <a href="<?= e(url_for('admin/messages.php')) ?>">Messages</a>
        <a href="<?= e(url_for('admin/login.php?logout=1')) ?>">Logout</a>
        <a href="<?= e(url_for('index.php')) ?>">View site</a>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <h1>Dashboard</h1>
    <p class="sub">Manage content stored in MySQL. Uploaded images are saved under <code>uploads/</code> and referenced by relative paths in the database.</p>

    <div class="grid" aria-label="Counts">
      <div class="tile">
        <h2>Projects</h2>
        <div class="num"><?= (int) $counts['projects'] ?></div>
        <a href="<?= e(url_for('admin/manage_projects.php')) ?>">Manage →</a>
      </div>
      <div class="tile">
        <h2>Music items</h2>
        <div class="num"><?= (int) $counts['music'] ?></div>
        <a href="<?= e(url_for('admin/manage_music.php')) ?>">Manage →</a>
      </div>
      <div class="tile">
        <h2>Contact messages</h2>
        <div class="num"><?= (int) $counts['messages'] ?></div>
        <a href="<?= e(url_for('admin/messages.php')) ?>">Open inbox →</a>
      </div>
    </div>

    <section class="panel">
      <h3>Quick checklist</h3>
      <div class="quick">
        <div class="q">
          <strong>Subdirectory installs</strong>
          <span>If your URL is <code>http://localhost/portfolio/</code>, set <code>base_path</code> to <code>/portfolio</code> in <code>config/app.php</code>.</span>
        </div>
        <div class="q">
          <strong>Upload permissions</strong>
          <span>Ensure the web server user can write to <code>uploads/</code> on shared hosting (typically chmod 775).</span>
        </div>
      </div>
    </section>
  </main>

  <footer class="wrap">
    Signed in as <?= e((string) ($_SESSION['admin_username'] ?? '')) ?> · Pure PHP CMS
  </footer>
</body>
</html>
