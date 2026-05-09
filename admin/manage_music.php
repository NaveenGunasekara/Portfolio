<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/MusicRepository.php';

require_admin();

$repo = new MusicRepository($pdo);
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
            $note = 'Music item deleted.';
            $editing = null;
            $editId = 0;
        }
    }

    if ($action === 'create') {
        $data = [
            'title'        => trim((string) ($_POST['title'] ?? '')),
            'artist'       => trim((string) ($_POST['artist'] ?? '')),
            'description'  => trim((string) ($_POST['description'] ?? '')),
            'external_url' => trim((string) ($_POST['external_url'] ?? '')),
            'sort_order'   => (int) ($_POST['sort_order'] ?? 0),
            'image_path'   => 'assets/img/music-placeholder.svg',
        ];
        if ($data['title'] === '') {
            $note = 'Title is required.';
        } else {
            if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $up = portfolio_upload_image($_FILES['image'], 'music');
                if ($up) {
                    $data['image_path'] = $up;
                } else {
                    $note = 'Image upload failed (invalid file).';
                }
            }
            if ($note === '') {
                $repo->create($data);
                $note = 'Music item created.';
            }
        }
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $existing = $id > 0 ? $repo->find($id) : null;
        if (!$existing) {
            $note = 'Item not found.';
        } else {
            $data = [
                'title'        => trim((string) ($_POST['title'] ?? '')),
                'artist'       => trim((string) ($_POST['artist'] ?? '')),
                'description'  => trim((string) ($_POST['description'] ?? '')),
                'external_url' => trim((string) ($_POST['external_url'] ?? '')),
                'sort_order'   => (int) ($_POST['sort_order'] ?? 0),
                'image_path'   => '',
            ];

            if ($data['title'] === '') {
                $note = 'Title is required.';
            } else {
                $clear = !empty($_POST['clear_image']);
                if ($clear) {
                    if (!empty($existing['image_path']) && str_starts_with((string) $existing['image_path'], 'uploads/')) {
                        portfolio_delete_upload((string) $existing['image_path']);
                    }
                    $data['image_path'] = 'assets/img/music-placeholder.svg';
                }

                if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    $up = portfolio_upload_image($_FILES['image'], 'music');
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
                    if ($data['image_path'] === '') {
                        unset($data['image_path']);
                    }
                    $repo->update($id, $data);
                    $note = 'Music item updated.';
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
  <title>Manage Music · Admin</title>
  <style>
    :root{--bg:#070b14;--text:#e5e7eb;--muted:#94a3b8;--accent:#6366f1;--accent2:#22d3ee;--radius:18px;--max:1180px}
    *{box-sizing:border-box}
    body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:radial-gradient(900px 520px at 85% 0%,rgba(167,139,250,.14),transparent),var(--bg);color:var(--text)}
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
    .thumb{width:74px;height:74px;border-radius:16px;object-fit:cover;border:1px solid rgba(31,41,55,.85)}
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
      <div class="brand">CMS · Music</div>
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
    <h1>Manage Music Gallery</h1>

    <?php if ($note !== ''): ?>
      <div class="<?= str_contains(strtolower($note), 'fail') || str_contains(strtolower($note), 'required') || str_contains(strtolower($note), 'not found') ? 'warn' : 'ok' ?>" role="status"><?= e($note) ?></div>
    <?php endif; ?>

    <section class="panel split">
      <div>
        <h2 style="margin:0 0 10px;font-size:14px;color:#cbd5e1"><?= $editing ? 'Edit music item' : 'Create music item' ?></h2>

        <?php if ($editing): ?>
          <form method="post" enctype="multipart/form-data" action="<?= e(url_for('admin/manage_music.php?edit=' . (int) $editing['id'])) ?>">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">

            <div style="display:flex;gap:12px;align-items:center;margin-bottom:10px">
              <img class="thumb" src="<?= e(url_for((string) $editing['image_path'])) ?>" alt="">
              <div class="muted" style="font-size:12px;line-height:1.45">Path: <code><?= e((string) $editing['image_path']) ?></code></div>
            </div>

            <label for="title">Title</label>
            <input id="title" name="title" required maxlength="200" value="<?= e((string) $editing['title']) ?>">

            <label for="artist">Artist</label>
            <input id="artist" name="artist" maxlength="200" value="<?= e((string) $editing['artist']) ?>">

            <label for="description">Description</label>
            <textarea id="description" name="description" maxlength="20000"><?= e((string) $editing['description']) ?></textarea>

            <label for="external_url">External URL (Spotify, SoundCloud, etc.)</label>
            <input id="external_url" name="external_url" maxlength="512" value="<?= e((string) $editing['external_url']) ?>">

            <div class="grid2">
              <div>
                <label for="sort_order">Sort order</label>
                <input id="sort_order" name="sort_order" type="number" value="<?= (int) $editing['sort_order'] ?>">
              </div>
              <div>
                <label for="image">Replace artwork</label>
                <input id="image" name="image" type="file" accept="image/*">
              </div>
            </div>

            <label class="chk">
              <input type="checkbox" name="clear_image" value="1">
              Remove uploaded artwork (use placeholder)
            </label>

            <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap">
              <button class="btn btn-primary" type="submit">Save changes</button>
              <a class="btn" href="<?= e(url_for('admin/manage_music.php')) ?>">Cancel edit</a>
            </div>
          </form>
        <?php else: ?>
          <form method="post" enctype="multipart/form-data" action="<?= e(url_for('admin/manage_music.php')) ?>">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">

            <label for="title">Title</label>
            <input id="title" name="title" required maxlength="200">

            <label for="artist">Artist</label>
            <input id="artist" name="artist" maxlength="200">

            <label for="description">Description</label>
            <textarea id="description" name="description" maxlength="20000"></textarea>

            <label for="external_url">External URL</label>
            <input id="external_url" name="external_url" maxlength="512">

            <div class="grid2">
              <div>
                <label for="sort_order">Sort order</label>
                <input id="sort_order" name="sort_order" type="number" value="0">
              </div>
              <div>
                <label for="image">Artwork (optional)</label>
                <input id="image" name="image" type="file" accept="image/*">
              </div>
            </div>

            <div style="margin-top:12px">
              <button class="btn btn-primary" type="submit">Create item</button>
            </div>
          </form>
        <?php endif; ?>
      </div>

      <div>
        <h2 style="margin:0 0 10px;font-size:14px;color:#cbd5e1">All items</h2>
        <div class="muted" style="font-size:12px;margin-bottom:10px;line-height:1.45">Art uploads are stored under <code>uploads/music/</code> with randomized filenames.</div>

        <?php if (!$all): ?>
          <p class="muted">No music items yet.</p>
        <?php else: ?>
          <div style="overflow:auto">
            <table>
              <thead>
                <tr>
                  <th>Art</th>
                  <th>Title</th>
                  <th>Order</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($all as $m): ?>
                  <tr>
                    <td style="width:100px">
                      <img class="thumb" src="<?= e(url_for((string) $m['image_path'])) ?>" alt="">
                    </td>
                    <td>
                      <div style="font-weight:950"><?= e((string) $m['title']) ?></div>
                      <div class="muted" style="margin-top:6px;font-size:12px"><?= e((string) $m['artist']) ?></div>
                    </td>
                    <td style="white-space:nowrap"><?= (int) $m['sort_order'] ?></td>
                    <td style="white-space:nowrap">
                      <a class="btn" href="<?= e(url_for('admin/manage_music.php?edit=' . (int) $m['id'])) ?>">Edit</a>
                      <form method="post" action="<?= e(url_for('admin/manage_music.php')) ?>" style="display:inline" onsubmit="return confirm('Delete this item?');">
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
    </section>
  </main>
</body>
</html>
