<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/ProjectRepository.php';

require_admin();

$repo = new ProjectRepository($pdo);
$note = '';

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editing = $editId > 0 ? $repo->find($editId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_post();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $repo->delete($id);
            $note = 'Project deleted.';
            $editing = null;
            $editId = 0;
        }
    }

    if ($action === 'create') {
        $data = [
            'title'       => trim((string) ($_POST['title'] ?? '')),
            'summary'     => trim((string) ($_POST['summary'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'demo_url'    => trim((string) ($_POST['demo_url'] ?? '')),
            'github_url'  => trim((string) ($_POST['github_url'] ?? '')),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
            'image_path'  => 'assets/img/project-placeholder.svg',
        ];
        if ($data['title'] === '') {
            $note = 'Title is required.';
        } else {
            if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $up = portfolio_upload_image($_FILES['image'], 'projects');
                if ($up) {
                    $data['image_path'] = $up;
                } else {
                    $note = 'Image upload failed (invalid file).';
                }
            }
            if ($note === '') {
                $repo->create($data);
                $note = 'Project created.';
            }
        }
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $existing = $id > 0 ? $repo->find($id) : null;
        if (!$existing) {
            $note = 'Project not found.';
        } else {
            $data = [
                'title'       => trim((string) ($_POST['title'] ?? '')),
                'summary'     => trim((string) ($_POST['summary'] ?? '')),
                'description' => trim((string) ($_POST['description'] ?? '')),
                'demo_url'    => trim((string) ($_POST['demo_url'] ?? '')),
                'github_url'  => trim((string) ($_POST['github_url'] ?? '')),
                'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
                'image_path'  => '',
            ];
            if ($data['title'] === '') {
                $note = 'Title is required.';
            } else {
                $clear = !empty($_POST['clear_image']);
                if ($clear) {
                    if (!empty($existing['image_path']) && str_starts_with((string) $existing['image_path'], 'uploads/')) {
                        portfolio_delete_upload((string) $existing['image_path']);
                    }
                    $data['image_path'] = 'assets/img/project-placeholder.svg';
                }

                if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    $up = portfolio_upload_image($_FILES['image'], 'projects');
                    if ($up) {
                        if (!empty($existing['image_path']) && str_starts_with((string) $existing['image_path'], 'uploads/')) {
                            portfolio_delete_upload((string) $existing['image_path']);
                        }
                        $data['image_path'] = $up;
                    } elseif ($note === '') {
                        $note = 'Image upload failed (invalid file).';
                    }
                }

                if ($note === '') {
                    // If neither replacement nor clear happened, omit image_path column update.
                    if ($data['image_path'] === '') {
                        unset($data['image_path']);
                    }
                    $repo->update($id, $data);
                    $note = 'Project updated.';
                    $editing = $repo->find($id);
                }
            }
        }
    }
}

$all = $repo->allOrdered();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Projects · Admin</title>
  <style>
    :root{--bg:#070b14;--text:#e5e7eb;--muted:#94a3b8;--accent:#6366f1;--accent2:#22d3ee;--radius:18px;--max:1180px}
    *{box-sizing:border-box}
    body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:radial-gradient(900px 520px at 20% 0%,rgba(34,211,238,.12),transparent),var(--bg);color:var(--text)}
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
    .panel{border:1px solid rgba(31,41,55,.85);background:rgba(17,24,39,.55);border-radius:calc(var(--radius) + 6px);padding:14px;margin-top:14px}
    label{display:block;margin:10px 0 8px;font-size:13px;color:#cbd5e1;font-weight:900}
    input,textarea{width:100%;padding:12px;border-radius:14px;border:1px solid rgba(148,163,184,.22);background:rgba(15,23,42,.65);color:var(--text);outline:none}
    textarea{min-height:120px;resize:vertical}
    table{width:100%;border-collapse:collapse;margin-top:10px;font-size:13px}
    th,td{border-bottom:1px solid rgba(31,41,55,.85);padding:10px 8px;text-align:left;vertical-align:top}
    th{color:#cbd5e1;font-size:12px}
    .thumb{width:84px;height:54px;border-radius:12px;object-fit:cover;border:1px solid rgba(31,41,55,.85)}
    .btn{padding:10px 12px;border-radius:14px;border:1px solid rgba(148,163,184,.18);background:rgba(255,255,255,.03);cursor:pointer;color:var(--text);font-weight:900}
    .btn-danger{border-color:rgba(251,113,133,.35);color:#fecdd3;background:rgba(251,113,133,.08)}
    .btn-primary{border:0;color:white;background:linear-gradient(135deg,var(--accent),#4f46e5)}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media (max-width:900px){.grid2{grid-template-columns:1fr}}
    .ok{border:1px solid rgba(52,211,153,.35);background:rgba(52,211,153,.08);color:#d1fae5;padding:12px;border-radius:14px;margin:12px 0;font-size:13px}
    .warn{border:1px solid rgba(251,191,36,.35);background:rgba(251,191,36,.08);color:#fde68a;padding:12px;border-radius:14px;margin:12px 0;font-size:13px}
    .muted{color:var(--muted)}
    code{color:#cbd5e1}
    .split{display:grid;grid-template-columns:1.05fr .95fr;gap:14px}
    @media (max-width:980px){.split{grid-template-columns:1fr}}
    .chk{display:flex;gap:10px;align-items:center;color:#cbd5e1;font-size:13px;font-weight:800;margin-top:10px}
  </style>
</head>
<body>
  <header>
    <div class="wrap top">
      <div class="brand">CMS · Projects</div>
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
    <h1>Manage Projects</h1>

    <?php if ($note !== ''): ?>
      <div class="<?= str_contains(strtolower($note), 'fail') || str_contains(strtolower($note), 'required') || str_contains(strtolower($note), 'not found') ? 'warn' : 'ok' ?>" role="status"><?= e($note) ?></div>
    <?php endif; ?>

    <section class="panel split">
      <div>
        <h2 style="margin:0 0 10px;font-size:14px;color:#cbd5e1"><?= $editing ? 'Edit project' : 'Create project' ?></h2>

        <?php if ($editing): ?>
          <form method="post" enctype="multipart/form-data" action="<?= e(url_for('admin/manage_projects.php?edit=' . (int) $editing['id'])) ?>">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">

            <div style="display:flex;gap:12px;align-items:center;margin-bottom:10px">
              <img class="thumb" src="<?= e(url_for((string) $editing['image_path'])) ?>" alt="">
              <div class="muted" style="font-size:12px;line-height:1.45">Path: <code><?= e((string) $editing['image_path']) ?></code></div>
            </div>

            <label for="title">Title</label>
            <input id="title" name="title" required maxlength="200" value="<?= e((string) $editing['title']) ?>">

            <label for="summary">Summary</label>
            <input id="summary" name="summary" maxlength="400" value="<?= e((string) $editing['summary']) ?>">

            <label for="description">Description</label>
            <textarea id="description" name="description" maxlength="20000"><?= e((string) $editing['description']) ?></textarea>

            <div class="grid2">
              <div>
                <label for="demo_url">Demo URL</label>
                <input id="demo_url" name="demo_url" maxlength="512" value="<?= e((string) $editing['demo_url']) ?>">
              </div>
              <div>
                <label for="github_url">GitHub URL</label>
                <input id="github_url" name="github_url" maxlength="512" value="<?= e((string) $editing['github_url']) ?>">
              </div>
            </div>

            <div class="grid2">
              <div>
                <label for="sort_order">Sort order</label>
                <input id="sort_order" name="sort_order" type="number" value="<?= (int) $editing['sort_order'] ?>">
              </div>
              <div>
                <label for="image">Replace image</label>
                <input id="image" name="image" type="file" accept="image/*">
              </div>
            </div>

            <label class="chk">
              <input type="checkbox" name="clear_image" value="1">
              Remove uploaded image (use placeholder)
            </label>

            <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap">
              <button class="btn btn-primary" type="submit">Save changes</button>
              <a class="btn" href="<?= e(url_for('admin/manage_projects.php')) ?>">Cancel edit</a>
            </div>
          </form>
        <?php else: ?>
          <form method="post" enctype="multipart/form-data" action="<?= e(url_for('admin/manage_projects.php')) ?>">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">

            <label for="title">Title</label>
            <input id="title" name="title" required maxlength="200">

            <label for="summary">Summary</label>
            <input id="summary" name="summary" maxlength="400">

            <label for="description">Description</label>
            <textarea id="description" name="description" maxlength="20000"></textarea>

            <div class="grid2">
              <div>
                <label for="demo_url">Demo URL</label>
                <input id="demo_url" name="demo_url" maxlength="512">
              </div>
              <div>
                <label for="github_url">GitHub URL</label>
                <input id="github_url" name="github_url" maxlength="512">
              </div>
            </div>

            <div class="grid2">
              <div>
                <label for="sort_order">Sort order</label>
                <input id="sort_order" name="sort_order" type="number" value="0">
              </div>
              <div>
                <label for="image">Cover image (optional)</label>
                <input id="image" name="image" type="file" accept="image/*">
              </div>
            </div>

            <div style="margin-top:12px">
              <button class="btn btn-primary" type="submit">Create project</button>
            </div>
          </form>
        <?php endif; ?>
      </div>

      <div>
        <h2 style="margin:0 0 10px;font-size:14px;color:#cbd5e1">All projects</h2>
        <div class="muted" style="font-size:12px;margin-bottom:10px;line-height:1.45">Lower <code>sort_order</code> values appear earlier on the public page.</div>

        <?php if (!$all): ?>
          <p class="muted">No projects yet.</p>
        <?php else: ?>
          <div style="overflow:auto">
            <table>
              <thead>
                <tr>
                  <th>Cover</th>
                  <th>Title</th>
                  <th>Order</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($all as $p): ?>
                  <tr>
                    <td style="width:110px">
                      <img class="thumb" src="<?= e(url_for((string) $p['image_path'])) ?>" alt="">
                    </td>
                    <td>
                      <div style="font-weight:950"><?= e((string) $p['title']) ?></div>
                      <div class="muted" style="margin-top:6px;font-size:12px"><?= e((string) $p['summary']) ?></div>
                    </td>
                    <td style="white-space:nowrap"><?= (int) $p['sort_order'] ?></td>
                    <td style="white-space:nowrap">
                      <a class="btn" href="<?= e(url_for('admin/manage_projects.php?edit=' . (int) $p['id'])) ?>">Edit</a>
                      <form method="post" action="<?= e(url_for('admin/manage_projects.php')) ?>" style="display:inline" onsubmit="return confirm('Delete this project?');">
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
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
    </section>
  </main>
</body>
</html>
