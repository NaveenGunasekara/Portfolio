<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/MessageRepository.php';

require_admin();

$repo = new MessageRepository($pdo);
$note = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_post();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $repo->delete($id);
            $note = 'Message deleted.';
        }
    }
}

$messages = $repo->allRecent(300);
$viewId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$active = $viewId > 0 ? $repo->find($viewId) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages · Admin</title>
  <style>
    :root{--bg:#070b14;--text:#e5e7eb;--muted:#94a3b8;--accent:#6366f1;--accent2:#22d3ee;--radius:18px;--max:1180px}
    *{box-sizing:border-box}
    body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:radial-gradient(900px 520px at 40% 0%,rgba(99,102,241,.14),transparent),var(--bg);color:var(--text)}
    a{color:inherit;text-decoration:none}
    .wrap{max-width:var(--max);margin:0 auto;padding:0 20px}
    header{border-bottom:1px solid rgba(31,41,55,.75);background:rgba(7,11,20,.62);backdrop-filter:blur(12px)}
    .top{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 0;flex-wrap:wrap}
    .brand{font-weight:950}
    .links{display:flex;gap:10px;flex-wrap:wrap}
    .links a{padding:10px 12px;border-radius:12px;color:var(--muted);border:1px solid rgba(148,163,184,.18);background:rgba(255,255,255,.03)}
    .links a:hover{color:var(--text)}
    main{padding:22px 0 40px}
    h1{margin:10px 0 8px;font-size:22px}
    .split{display:grid;grid-template-columns:1fr .95fr;gap:14px}
    @media (max-width:980px){.split{grid-template-columns:1fr}}
    .panel{border:1px solid rgba(31,41,55,.85);background:rgba(17,24,39,.55);border-radius:calc(var(--radius) + 6px);padding:14px}
    table{width:100%;border-collapse:collapse;margin-top:10px;font-size:13px}
    th,td{border-bottom:1px solid rgba(31,41,55,.85);padding:10px 8px;text-align:left;vertical-align:top}
    th{color:#cbd5e1;font-size:12px}
    .btn{padding:10px 12px;border-radius:14px;border:1px solid rgba(148,163,184,.18);background:rgba(255,255,255,.03);cursor:pointer;color:var(--text);font-weight:900;display:inline-block}
    .btn-danger{border-color:rgba(251,113,133,.35);color:#fecdd3;background:rgba(251,113,133,.08)}
    .muted{color:var(--muted)}
    .ok{border:1px solid rgba(52,211,153,.35);background:rgba(52,211,153,.08);color:#d1fae5;padding:12px;border-radius:14px;margin:12px 0;font-size:13px}
    pre{white-space:pre-wrap;word-break:break-word;background:rgba(15,23,42,.65);border:1px solid rgba(31,41,55,.85);padding:12px;border-radius:14px;color:#cbd5e1;font-size:13px;line-height:1.55}
    .meta{font-size:12px;color:var(--muted);line-height:1.55}
    code{color:#cbd5e1}
  </style>
</head>
<body>
  <header>
    <div class="wrap top">
      <div class="brand">CMS · Messages</div>
      <nav class="links" aria-label="Admin">
        <a href="<?= e(url_for('admin/dashboard.php')) ?>">Dashboard</a>
        <a href="<?= e(url_for('admin/manage_about.php')) ?>">About</a>
        <a href="<?= e(url_for('admin/manage_projects.php')) ?>">Projects</a>
        <a href="<?= e(url_for('admin/manage_music.php')) ?>">Music</a>
        <a href="<?= e(url_for('admin/messages.php')) ?>">Messages</a>
        <a href="<?= e(url_for('admin/login.php?logout=1')) ?>">Logout</a>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <h1>Contact inbox</h1>

    <?php if ($note !== ''): ?>
      <div class="ok" role="status"><?= e($note) ?></div>
    <?php endif; ?>

    <section class="split">
      <div class="panel">
        <h2 style="margin:0 0 10px;font-size:14px;color:#cbd5e1">Recent submissions</h2>
        <div class="muted" style="font-size:12px;line-height:1.45;margin-bottom:10px">These rows come from the public <code>contact.php</code> form.</div>

        <?php if (!$messages): ?>
          <p class="muted">No messages yet.</p>
        <?php else: ?>
          <div style="overflow:auto">
            <table>
              <thead>
                <tr>
                  <th>When</th>
                  <th>From</th>
                  <th>Subject</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($messages as $m): ?>
                  <tr>
                    <td style="white-space:nowrap;font-size:12px;color:#cbd5e1"><?= e((string) $m['created_at']) ?></td>
                    <td>
                      <div style="font-weight:950"><?= e((string) $m['name']) ?></div>
                      <div class="muted" style="margin-top:6px;font-size:12px"><?= e((string) $m['email']) ?></div>
                    </td>
                    <td><?= e((string) $m['subject']) ?></td>
                    <td style="white-space:nowrap">
                      <a class="btn" href="<?= e(url_for('admin/messages.php?id=' . (int) $m['id'])) ?>">View</a>
                      <form method="post" action="<?= e(url_for('admin/messages.php')) ?>" style="display:inline" onsubmit="return confirm('Delete this message?');">
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                        <button class="btn btn-danger" type="submit">Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <aside class="panel" aria-label="Message detail">
        <h2 style="margin:0 0 10px;font-size:14px;color:#cbd5e1">Detail</h2>

        <?php if (!$active): ?>
          <p class="muted">Select “View” on a row to read the full message.</p>
        <?php else: ?>
          <div class="meta">
            <div><strong>ID:</strong> <?= (int) $active['id'] ?></div>
            <div><strong>Received:</strong> <?= e((string) $active['created_at']) ?></div>
            <div><strong>Name:</strong> <?= e((string) $active['name']) ?></div>
            <div><strong>Email:</strong> <?= e((string) $active['email']) ?></div>
            <div><strong>Subject:</strong> <?= e((string) $active['subject']) ?></div>
            <div><strong>IP:</strong> <?= e((string) $active['ip_address']) ?></div>
          </div>

          <h3 style="margin:14px 0 8px;font-size:13px;color:#cbd5e1">Message</h3>
          <pre><?= e((string) $active['body']) ?></pre>

          <h3 style="margin:14px 0 8px;font-size:13px;color:#cbd5e1">User agent</h3>
          <pre><?= e((string) $active['user_agent']) ?></pre>

          <form method="post" action="<?= e(url_for('admin/messages.php')) ?>" style="margin-top:12px" onsubmit="return confirm('Delete this message?');">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int) $active['id'] ?>">
            <button class="btn btn-danger" type="submit">Delete message</button>
          </form>
        <?php endif; ?>
      </aside>
    </section>
  </main>
</body>
</html>
