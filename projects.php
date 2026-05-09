<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ProjectRepository.php';

$repo = new ProjectRepository($pdo);
$projects = $repo->allOrdered();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Projects — selected engineering work.">
  <title>Projects · Portfolio</title>
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
    body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:radial-gradient(900px 520px at 85% -10%,rgba(34,211,238,.14),transparent),var(--bg);color:var(--text);line-height:1.6}
    a{color:inherit;text-decoration:none}
    img{max-width:100%;display:block;height:auto}
    .wrap{max-width:var(--max);margin:0 auto;padding:0 20px}
    header{position:sticky;top:0;z-index:40;backdrop-filter:saturate(130%) blur(12px);background:rgba(7,11,20,.62);border-bottom:1px solid rgba(31,41,55,.75)}
    .nav{display:flex;align-items:center;justify-content:space-between;padding:14px 0;gap:16px}
    .brand{display:flex;align-items:center;gap:12px;font-weight:800}
    .brand-badge{width:34px;height:34px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent2));box-shadow:0 12px 40px rgba(99,102,241,.35)}
    nav ul{display:flex;gap:18px;list-style:none;margin:0;padding:0;flex-wrap:wrap;justify-content:flex-end}
    nav a{padding:10px 12px;border-radius:12px;color:var(--muted)}
    nav a:hover{color:var(--text);background:rgba(255,255,255,.04)}
    nav a.active{color:var(--text);outline:1px solid rgba(99,102,241,.35);background:rgba(99,102,241,.12)}
    main{padding:34px 0 56px}
    .page-head{margin:8px 0 18px}
    h1{margin:0;font-size:clamp(28px,3.2vw,40px);letter-spacing:-.02em}
    .lede{margin:10px 0 0;color:var(--muted);max-width:72ch}
    .grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
    @media (max-width:900px){.grid{grid-template-columns:1fr}}
    article{border:1px solid rgba(31,41,55,.85);background:rgba(17,24,39,.55);border-radius:var(--radius);overflow:hidden;display:flex;flex-direction:column}
    .thumb{aspect-ratio:16/10;background:#0b1220;border-bottom:1px solid rgba(31,41,55,.75)}
    .thumb img{width:100%;height:100%;object-fit:cover}
    .body{padding:16px;display:flex;flex-direction:column;gap:10px;flex:1}
    h2{margin:0;font-size:18px}
    .summary{margin:0;color:var(--muted);font-size:14px}
    .desc{margin:0;color:#cbd5e1;font-size:14px}
    .links{display:flex;gap:12px;flex-wrap:wrap;margin-top:auto;padding-top:12px}
    .link{font-weight:700;color:var(--accent2);font-size:13px}
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
          <li><a href="<?= e(url_for('about.php')) ?>">About</a></li>
          <li><a class="active" href="<?= e(url_for('projects.php')) ?>">Projects</a></li>
          <li><a href="<?= e(url_for('music.php')) ?>">Music</a></li>
          <li><a href="<?= e(url_for('contact.php')) ?>">Contact</a></li>
          <li><a href="<?= e(url_for('admin/login.php')) ?>">Admin</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <div class="page-head">
      <h1>Projects</h1>
      <p class="lede">Each card reads cover image paths from MySQL so you can upload replacements without touching code.</p>
    </div>

    <div class="grid">
      <?php foreach ($projects as $p): ?>
        <article>
          <div class="thumb">
            <img src="<?= e(url_for($p['image_path'])) ?>" alt="<?= e($p['title']) ?> cover">
          </div>
          <div class="body">
            <h2><?= e($p['title']) ?></h2>
            <p class="summary"><?= e($p['summary']) ?></p>
            <p class="desc"><?= nl2br(e($p['description'])) ?></p>
            <div class="links">
              <?php if ($p['demo_url'] !== ''): ?>
                <a class="link" href="<?= e($p['demo_url']) ?>" target="_blank" rel="noopener noreferrer">Live demo</a>
              <?php endif; ?>
              <?php if ($p['github_url'] !== ''): ?>
                <a class="link" href="<?= e($p['github_url']) ?>" target="_blank" rel="noopener noreferrer">Source</a>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </main>

  <footer>
    <div class="wrap">© <?= date('Y') ?> Portfolio</div>
  </footer>
</body>
</html>
