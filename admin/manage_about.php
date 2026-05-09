<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/AboutRepository.php';

require_admin();

$repo = new AboutRepository($pdo);
$about = $repo->get();
$note = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_post();

    $headline = trim((string) ($_POST['headline'] ?? ''));
    $subtitle = trim((string) ($_POST['subtitle'] ?? ''));
    $bio = trim((string) ($_POST['bio'] ?? ''));
    $skills = trim((string) ($_POST['skills'] ?? ''));
    $clearImage = !empty($_POST['clear_image']);

    $nextImagePath = (string) $about['image_path'];

    if ($clearImage) {
        if (str_starts_with($nextImagePath, 'uploads/')) {
            portfolio_delete_upload($nextImagePath);
        }
        $nextImagePath = 'assets/img/profile-placeholder.svg';
    }

    if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploaded = portfolio_upload_image($_FILES['image'], 'about');
        if ($uploaded) {
            if (str_starts_with((string) $about['image_path'], 'uploads/')) {
                portfolio_delete_upload((string) $about['image_path']);
            }
            $nextImagePath = $uploaded;
        } else {
            $note = 'Image upload failed (type/size). Allowed: JPG/PNG/WebP/GIF up to ~5MB.';
        }
    }

    if ($headline === '') {
        $note = 'Headline is required.';
    } else {
        $repo->update([
            'headline'   => $headline,
            'subtitle'   => $subtitle,
            'bio'        => $bio,
            'skills'     => $skills,
            'image_path' => $nextImagePath,
        ]);
        if ($note === '') {
            $note = 'Saved.';
        }
        $about = $repo->get();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage About · Admin</title>
  <style>
    :root{--bg:#070b14;--text:#e5e7eb;--muted:#94a3b8;--accent:#6366f1;--accent2:#22d3ee;--radius:18px;--max:980px}
    *{box-sizing:border-box}
    body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:radial-gradient(900px 520px at 70% 0%,rgba(99,102,241,.14),transparent),var(--bg);color:var(--text)}
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
    .panel{border:1px solid rgba(31,41,55,.85);background:rgba(17,24,39,.55);border-radius:calc(var(--radius) + 6px);padding:14px}
    label{display:block;margin:12px 0 8px;font-size:13px;color:#cbd5e1;font-weight:900}
    input,textarea{width:100%;padding:12px;border-radius:14px;border:1px solid rgba(148,163,184,.22);background:rgba(15,23,42,.65);color:var(--text);outline:none}
    textarea{min-height:170px;resize:vertical}
    input:focus,textarea:focus{border-color:rgba(99,102,241,.55);box-shadow:0 0 0 4px rgba(99,102,241,.18)}
    button{padding:12px 14px;border-radius:14px;border:0;cursor:pointer;font-weight:950;color:white;background:linear-gradient(135deg,var(--accent),#4f46e5)}
    .hint{font-size:12px;color:var(--muted);margin-top:10px;line-height:1.55}
    .preview{display:grid;grid-template-columns:220px 1fr;gap:14px;align-items:start;margin-top:12px}
    @media (max-width:820px){.preview{grid-template-columns:1fr}}
    .preview img{width:100%;border-radius:16px;border:1px solid rgba(31,41,55,.85)}
    .ok{border:1px solid rgba(52,211,153,.35);background:rgba(52,211,153,.08);color:#d1fae5;padding:12px;border-radius:14px;margin:12px 0;font-size:13px}
    .warn{border:1px solid rgba(251,191,36,.35);background:rgba(251,191,36,.08);color:#fde68a;padding:12px;border-radius:14px;margin:12px 0;font-size:13px}
    code{color:#cbd5e1}
    .row{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:10px}
    .chk{display:flex;gap:10px;align-items:center;color:#cbd5e1;font-size:13px;font-weight:800}
  </style>
</head>
<body>
  <header>
    <div class="wrap top">
      <div class="brand">CMS · About</div>
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
    <h1>Manage About</h1>

    <?php if ($note !== ''): ?>
      <div class="<?= str_contains(strtolower($note), 'fail') || str_contains(strtolower($note), 'required') ? 'warn' : 'ok' ?>" role="status"><?= e($note) ?></div>
    <?php endif; ?>

    <section class="panel">
      <div class="preview">
        <div>
          <img src="<?= e(url_for((string) $about['image_path'])) ?>" alt="Current about image">
          <div class="hint">Stored path: <code><?= e((string) $about['image_path']) ?></code></div>
        </div>
        <div>
          <form method="post" action="<?= e(url_for('admin/manage_about.php')) ?>" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

            <label for="headline">Headline</label>
            <input id="headline" name="headline" required maxlength="255" value="<?= e((string) $about['headline']) ?>">

            <label for="subtitle">Subtitle</label>
            <input id="subtitle" name="subtitle" maxlength="255" value="<?= e((string) $about['subtitle']) ?>">

            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" maxlength="20000"><?= e((string) $about['bio']) ?></textarea>

            <label for="skills">Skills (comma-separated)</label>
            <input id="skills" name="skills" maxlength="512" value="<?= e((string) $about['skills']) ?>">

            <label for="image">Replace image</label>
            <input id="image" name="image" type="file" accept="image/*">

            <div class="row">
              <label class="chk">
                <input type="checkbox" name="clear_image" value="1">
                Remove uploaded image (revert to placeholder)
              </label>
            </div>

            <div style="margin-top:14px">
              <button type="submit">Save About</button>
            </div>
            <p class="hint">Uploads validate MIME type and size; stored paths are dynamic under <code>uploads/about/</code>.</p>
          </form>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
