<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/AboutRepository.php';

$aboutRepo = new AboutRepository($pdo);
$about = $aboutRepo->get();

$skills = array_filter(array_map('trim', preg_split('/[,·|]/u', (string) $about['skills']) ?: []));
if (!$skills) {
    $skills = preg_split('/\s*,\s*/', (string) $about['skills']) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="About — background, skills, and focus areas.">
  <title>About · Portfolio</title>
  <style>
    :root{
      --bg:#070b14;
      --surface:#0f172a;
      --surface2:#111827;
      --border:#1f2937;
      --text:#e5e7eb;
      --muted:#94a3b8;
      --accent:#6366f1;
      --accent2:#22d3ee;
      --radius:18px;
      --max:1120px;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:radial-gradient(900px 500px at 15% 0%,rgba(99,102,241,.18),transparent),var(--bg);color:var(--text);line-height:1.65}
    a{color:inherit;text-decoration:none}
    img{max-width:100%;display:block;height:auto;border-radius:calc(var(--radius) + 6px)}
    .wrap{max-width:var(--max);margin:0 auto;padding:0 20px}
    header{position:sticky;top:0;z-index:40;backdrop-filter:saturate(130%) blur(12px);background:rgba(7,11,20,.62);border-bottom:1px solid rgba(31,41,55,.75)}
    .nav{display:flex;align-items:center;justify-content:space-between;padding:14px 0;gap:16px}
    .brand{display:flex;align-items:center;gap:12px;font-weight:800}
    .brand-badge{width:34px;height:34px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent2));box-shadow:0 12px 40px rgba(99,102,241,.35)}
    nav ul{display:flex;gap:18px;list-style:none;margin:0;padding:0;flex-wrap:wrap;justify-content:flex-end}
    nav a{padding:10px 12px;border-radius:12px;color:var(--muted)}
    nav a:hover{color:var(--text);background:rgba(255,255,255,.04)}
    nav a.active{color:var(--text);outline:1px solid rgba(99,102,241,.35);background:rgba(99,102,241,.12)}
    main{padding:42px 0 56px}
    .grid{display:grid;grid-template-columns:0.95fr 1.05fr;gap:28px;align-items:start}
    @media (max-width:920px){.grid{grid-template-columns:1fr}}
    .panel{border:1px solid rgba(31,41,55,.85);background:rgba(17,24,39,.55);border-radius:calc(var(--radius) + 8px);padding:18px;box-shadow:0 24px 80px rgba(0,0,0,.45)}
    h1{margin:10px 0 10px;font-size:clamp(30px,3.4vw,44px);letter-spacing:-.02em}
    .subtitle{margin:0;color:var(--muted);font-size:16px}
    .prose{color:var(--muted)}
    .prose p{margin:0 0 14px}
    .chips{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}
    .chip{border:1px solid rgba(148,163,184,.18);color:var(--text);background:rgba(255,255,255,.03);padding:10px 12px;border-radius:999px;font-size:13px}
    footer{border-top:1px solid rgba(31,41,55,.75);padding:28px 0;color:var(--muted);font-size:13px}
    .burger{display:none;background:transparent;border:1px solid rgba(148,163,184,.22);border-radius:12px;padding:10px;color:var(--text)}
    @media (max-width:820px){
      .burger{display:inline-flex}
      #siteNav{display:none;width:100%}
      #siteNav.open{display:block}
      nav ul{flex-direction:column;align-items:stretch;padding:10px 0 6px}
    }
  </style>
</head>
<body>
  <header>
    <div class="wrap nav">
      <a class="brand" href="<?= e(url_for('index.php')) ?>">
        <span class="brand-badge" aria-hidden="true"></span>
        <span>Portfolio</span>
      </a>
      <button class="burger" type="button" aria-label="Open menu" onclick="document.getElementById('siteNav').classList.toggle('open')">Menu</button>
      <nav id="siteNav" aria-label="Primary">
        <ul>
          <li><a href="<?= e(url_for('index.php')) ?>">Home</a></li>
          <li><a class="active" href="<?= e(url_for('about.php')) ?>">About</a></li>
          <li><a href="<?= e(url_for('projects.php')) ?>">Projects</a></li>
          <li><a href="<?= e(url_for('music.php')) ?>">Music</a></li>
          <li><a href="<?= e(url_for('contact.php')) ?>">Contact</a></li>
          <li><a href="<?= e(url_for('admin/login.php')) ?>">Admin</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <div class="grid">
      <aside class="panel">
        <img src="<?= e(url_for($about['image_path'])) ?>" alt="<?= e($about['headline']) ?> portrait">
        <div style="margin-top:14px;color:var(--muted);font-size:13px;line-height:1.5">
          Image path is stored in the database and rendered dynamically.
        </div>
      </aside>
      <section class="panel">
        <h1><?= e($about['headline']) ?></h1>
        <p class="subtitle"><?= e($about['subtitle']) ?></p>
        <div class="prose" style="margin-top:16px">
          <?php
            $paragraphs = preg_split("/\r\n|\n|\r/", (string) $about['bio']) ?: [];
            foreach ($paragraphs as $para) {
                $para = trim($para);
                if ($para === '') {
                    continue;
                }
                echo '<p>' . nl2br(e($para)) . '</p>';
            }
          ?>
        </div>
        <div class="chips" aria-label="Skills">
          <?php foreach ($skills as $skill): ?>
            <?php if (trim($skill) !== ''): ?>
              <span class="chip"><?= e(trim($skill)) ?></span>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </section>
    </div>
  </main>

  <footer>
    <div class="wrap">© <?= date('Y') ?> Portfolio · Content managed via admin panel</div>
  </footer>
</body>
</html>
