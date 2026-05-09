<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/ProjectRepository.php';
require_once __DIR__ . '/includes/MusicRepository.php';

$projectsRepo = new ProjectRepository($pdo);
$musicRepo = new MusicRepository($pdo);

$featuredProjects = array_slice($projectsRepo->allOrdered(), 0, 3);
$featuredTracks = array_slice($musicRepo->allOrdered(), 0, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Portfolio — engineering, projects, and music.">
  <title>Home · Portfolio</title>
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
      --shadow:0 24px 80px rgba(0,0,0,.55);
      --radius:18px;
      --max:1120px;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:radial-gradient(1200px 600px at 10% -10%,rgba(99,102,241,.22),transparent),radial-gradient(900px 500px at 90% 10%,rgba(34,211,238,.15),transparent),var(--bg);color:var(--text);line-height:1.6}
    a{color:inherit;text-decoration:none}
    img{max-width:100%;display:block;height:auto}
    .wrap{max-width:var(--max);margin:0 auto;padding:0 20px}
    header{position:sticky;top:0;z-index:40;backdrop-filter:saturate(130%) blur(12px);background:rgba(7,11,20,.62);border-bottom:1px solid rgba(31,41,55,.75)}
    .nav{display:flex;align-items:center;justify-content:space-between;padding:14px 0;gap:16px}
    .brand{display:flex;align-items:center;gap:12px;font-weight:800;letter-spacing:.2px}
    .brand-badge{width:34px;height:34px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent2));box-shadow:0 12px 40px rgba(99,102,241,.35)}
    nav ul{display:flex;gap:18px;list-style:none;margin:0;padding:0;flex-wrap:wrap;justify-content:flex-end}
    nav a{padding:10px 12px;border-radius:12px;color:var(--muted)}
    nav a:hover{color:var(--text);background:rgba(255,255,255,.04)}
    nav a.active{color:var(--text);outline:1px solid rgba(99,102,241,.35);background:rgba(99,102,241,.12)}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:12px 16px;border-radius:14px;font-weight:700;border:1px solid transparent}
    .btn-primary{background:linear-gradient(135deg,var(--accent),#4f46e5);color:white;box-shadow:0 18px 55px rgba(79,70,229,.35)}
    .btn-primary:hover{filter:brightness(1.06)}
    .btn-ghost{border-color:rgba(148,163,184,.22);background:rgba(255,255,255,.03);color:var(--text)}
    .hero{padding:64px 0 34px;position:relative;overflow:hidden}
    .hero-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:34px;align-items:center}
    @media (max-width:920px){.hero-grid{grid-template-columns:1fr}}
    .kicker{display:inline-flex;align-items:center;gap:10px;padding:8px 12px;border-radius:999px;border:1px solid rgba(148,163,184,.18);background:rgba(17,24,39,.55);color:var(--muted);font-size:13px}
    .dot{width:8px;height:8px;border-radius:999px;background:linear-gradient(135deg,var(--accent2),var(--accent));box-shadow:0 0 0 6px rgba(99,102,241,.12)}
    h1{font-size:clamp(34px,4vw,54px);line-height:1.05;margin:18px 0 14px;letter-spacing:-.02em}
    .lead{font-size:clamp(16px,2vw,18px);color:var(--muted);margin:0 0 22px;max-width:58ch}
    .hero-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:10px}
    .hero-card{border:1px solid rgba(31,41,55,.85);background:linear-gradient(180deg,rgba(17,24,39,.78),rgba(15,23,42,.55));border-radius:calc(var(--radius) + 8px);box-shadow:var(--shadow);overflow:hidden;position:relative}
    .hero-media{aspect-ratio:16/11;background:#0b1220}
    .hero-media img{width:100%;height:100%;object-fit:cover;opacity:.92}
    .hero-meta{padding:16px 16px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-top:1px solid rgba(31,41,55,.75)}
    .pill{font-size:12px;color:var(--muted)}
    section{padding:46px 0}
    .section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:18px}
    .section-head h2{margin:0;font-size:22px;letter-spacing:-.01em}
    .muted{color:var(--muted);margin:6px 0 0;font-size:14px}
    .cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
    @media (max-width:980px){.cards{grid-template-columns:1fr}}
    .card{border:1px solid rgba(31,41,55,.85);background:rgba(17,24,39,.55);border-radius:var(--radius);overflow:hidden;display:flex;flex-direction:column;min-height:100%}
    .thumb{aspect-ratio:16/10;background:#0b1220;border-bottom:1px solid rgba(31,41,55,.75)}
    .thumb img{width:100%;height:100%;object-fit:cover}
    .card-body{padding:14px 14px 16px;display:flex;flex-direction:column;gap:8px;flex:1}
    .card-body h3{margin:0;font-size:16px}
    .card-body p{margin:0;color:var(--muted);font-size:14px}
    .row-links{display:flex;gap:10px;flex-wrap:wrap;margin-top:auto;padding-top:10px}
    .link{font-size:13px;color:var(--accent2)}
    footer{border-top:1px solid rgba(31,41,55,.75);padding:28px 0;color:var(--muted);font-size:13px}
    .footer-grid{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:center}
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
          <li><a class="active" href="<?= e(url_for('index.php')) ?>">Home</a></li>
          <li><a href="<?= e(url_for('about.php')) ?>">About</a></li>
          <li><a href="<?= e(url_for('projects.php')) ?>">Projects</a></li>
          <li><a href="<?= e(url_for('music.php')) ?>">Music</a></li>
          <li><a href="<?= e(url_for('contact.php')) ?>">Contact</a></li>
          <li><a href="<?= e(url_for('admin/login.php')) ?>">Admin</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main>
    <section class="hero">
      <div class="wrap hero-grid">
        <div>
          <span class="kicker"><span class="dot" aria-hidden="true"></span> Available for collaborations</span>
          <h1>Precision engineering with a cinematic edge.</h1>
          <p class="lead">A production-ready portfolio CMS in pure PHP — modular repositories, secure uploads, and a refined dark UI designed to scale from localhost to shared hosting.</p>
          <div class="hero-actions">
            <a class="btn btn-primary" href="<?= e(url_for('projects.php')) ?>">View projects</a>
            <a class="btn btn-ghost" href="<?= e(url_for('contact.php')) ?>">Let’s talk</a>
          </div>
        </div>
        <div class="hero-card" aria-label="Featured visual">
          <div class="hero-media">
            <img src="<?= e(url_for('assets/img/hero-pattern.svg')) ?>" alt="Abstract gradient pattern">
          </div>
          <div class="hero-meta">
            <div>
              <div style="font-weight:800">Crafted for clarity</div>
              <div class="pill">Inline HTML/CSS · Minimal JS</div>
            </div>
            <span class="pill">v1.0</span>
          </div>
        </div>
      </div>
    </section>

    <section>
      <div class="wrap">
        <div class="section-head">
          <div>
            <h2>Featured projects</h2>
            <p class="muted">Curated builds — swap cover art from the admin panel.</p>
          </div>
          <a class="btn btn-ghost" href="<?= e(url_for('projects.php')) ?>">See all</a>
        </div>
        <div class="cards">
          <?php foreach ($featuredProjects as $p): ?>
            <article class="card">
              <div class="thumb">
                <img src="<?= e(url_for($p['image_path'])) ?>" alt="<?= e($p['title']) ?> cover">
              </div>
              <div class="card-body">
                <h3><?= e($p['title']) ?></h3>
                <p><?= e($p['summary']) ?></p>
                <div class="row-links">
                  <?php if ($p['demo_url'] !== ''): ?>
                    <a class="link" href="<?= e($p['demo_url']) ?>" target="_blank" rel="noopener noreferrer">Demo</a>
                  <?php endif; ?>
                  <?php if ($p['github_url'] !== ''): ?>
                    <a class="link" href="<?= e($p['github_url']) ?>" target="_blank" rel="noopener noreferrer">Repository</a>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section>
      <div class="wrap">
        <div class="section-head">
          <div>
            <h2>Music highlights</h2>
            <p class="muted">Gallery tiles pulled live from MySQL.</p>
          </div>
          <a class="btn btn-ghost" href="<?= e(url_for('music.php')) ?>">Browse music</a>
        </div>
        <div class="cards">
          <?php foreach ($featuredTracks as $m): ?>
            <article class="card">
              <div class="thumb">
                <img src="<?= e(url_for($m['image_path'])) ?>" alt="<?= e($m['title']) ?> artwork">
              </div>
              <div class="card-body">
                <h3><?= e($m['title']) ?></h3>
                <p><?= e($m['artist']) ?> — <?= e(portfolio_excerpt(strip_tags($m['description']), 120)) ?></p>
                <div class="row-links">
                  <?php if ($m['external_url'] !== ''): ?>
                    <a class="link" href="<?= e($m['external_url']) ?>" target="_blank" rel="noopener noreferrer">Open link</a>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <div class="wrap footer-grid">
      <div>© <?= date('Y') ?> Portfolio CMS · Pure PHP</div>
      <div style="display:flex;gap:14px;flex-wrap:wrap">
        <a href="<?= e(url_for('about.php')) ?>">About</a>
        <a href="<?= e(url_for('contact.php')) ?>">Contact</a>
      </div>
    </div>
  </footer>
</body>
</html>
