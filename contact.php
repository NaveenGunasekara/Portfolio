<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/MessageRepository.php';

$errors = [];
$flashOk = false;

/**
 * Byte-safe-ish length cap for form validation (good enough for hosted PHP without mbstring).
 */
function portfolio_len(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_post();

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $body = trim((string) ($_POST['message'] ?? ''));

    if ($name === '' || portfolio_len($name) > 120) {
        $errors[] = 'Please enter your name (max 120 characters).';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($subject === '' || portfolio_len($subject) > 200) {
        $errors[] = 'Please enter a subject (max 200 characters).';
    }
    if ($body === '' || portfolio_len($body) > 6000) {
        $errors[] = 'Please enter a message (max 6000 characters).';
    }

    if (!$errors) {
        $repo = new MessageRepository($pdo);
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        if (strlen($ua) > 512) {
            $ua = substr($ua, 0, 512);
        }

        $repo->create([
            'name'       => $name,
            'email'      => $email,
            'subject'    => $subject,
            'body'       => $body,
            'ip_address' => $ip,
            'user_agent' => $ua,
        ]);

        // Email notification via PHP mail()
        $to = contact_notification_email();
        $mailSubject = '[Portfolio] ' . $subject;
        $mailBody = "New contact submission\r\n\r\n"
            . 'Name: ' . $name . "\r\n"
            . 'Email: ' . $email . "\r\n"
            . 'Subject: ' . $subject . "\r\n\r\n"
            . $body . "\r\n";

        $host = (string) ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'From: Portfolio Site <no-reply@' . $host . '>';
        $headers[] = 'Reply-To: ' . $email;

        @mail($to, $mailSubject, $mailBody, implode("\r\n", $headers));

        $flashOk = true;
        $_POST = [];
    }
}

$old = [
    'name'    => (string) ($_POST['name'] ?? ''),
    'email'   => (string) ($_POST['email'] ?? ''),
    'subject' => (string) ($_POST['subject'] ?? ''),
    'message' => (string) ($_POST['message'] ?? ''),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Contact — send a message; saved to the database and emailed.">
  <title>Contact · Portfolio</title>
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
      --danger:#fb7185;
      --ok:#34d399;
      --radius:18px;
      --max:920px;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;background:radial-gradient(900px 520px at 70% 0%,rgba(99,102,241,.18),transparent),var(--bg);color:var(--text);line-height:1.6}
    a{color:inherit;text-decoration:none}
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
    h1{margin:8px 0 10px;font-size:clamp(28px,3.2vw,40px);letter-spacing:-.02em}
    .lede{margin:0;color:var(--muted);max-width:70ch}
    .panel{margin-top:18px;border:1px solid rgba(31,41,55,.85);background:rgba(17,24,39,.55);border-radius:calc(var(--radius) + 8px);padding:18px;box-shadow:0 24px 80px rgba(0,0,0,.45)}
    label{display:block;font-size:13px;color:#cbd5e1;margin:12px 0 8px;font-weight:700}
    input,textarea{width:100%;padding:12px 12px;border-radius:14px;border:1px solid rgba(148,163,184,.22);background:rgba(15,23,42,.65);color:var(--text);outline:none}
    input:focus,textarea:focus{border-color:rgba(99,102,241,.55);box-shadow:0 0 0 4px rgba(99,102,241,.18)}
    textarea{min-height:170px;resize:vertical}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:12px 16px;border-radius:14px;font-weight:800;border:1px solid transparent;cursor:pointer}
    .btn-primary{background:linear-gradient(135deg,var(--accent),#4f46e5);color:white;box-shadow:0 18px 55px rgba(79,70,229,.35)}
    .btn-primary:hover{filter:brightness(1.06)}
    .alert{border:1px solid rgba(251,113,133,.35);background:rgba(251,113,133,.08);color:#fecdd3;padding:12px 12px;border-radius:14px;margin:12px 0}
    .ok{border:1px solid rgba(52,211,153,.35);background:rgba(52,211,153,.08);color:#d1fae5;padding:12px 12px;border-radius:14px;margin:12px 0}
    footer{border-top:1px solid rgba(31,41,55,.75);padding:28px 0;color:var(--muted);font-size:13px}
    .burger{display:none;background:transparent;border:1px solid rgba(148,163,184,.22);border-radius:12px;padding:10px;color:var(--text)}
    @media (max-width:820px){
      .burger{display:inline-flex}
      #siteNav{display:none;width:100%}
      #siteNav.open{display:block}
      nav ul{flex-direction:column;align-items:stretch;padding:10px 0 6px}
    }
    .hint{font-size:12px;color:var(--muted);margin-top:10px;line-height:1.5}
    code{color:#cbd5e1}
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
          <li><a href="<?= e(url_for('projects.php')) ?>">Projects</a></li>
          <li><a href="<?= e(url_for('music.php')) ?>">Music</a></li>
          <li><a class="active" href="<?= e(url_for('contact.php')) ?>">Contact</a></li>
          <li><a href="<?= e(url_for('admin/login.php')) ?>">Admin</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <h1>Contact</h1>
    <p class="lede">Messages are stored in MySQL for your inbox inside the admin panel, and an email is sent to <strong><?= e(contact_notification_email()) ?></strong> using PHP <code>mail()</code>.</p>

    <section class="panel" aria-label="Contact form">
      <?php if ($flashOk): ?>
        <div class="ok" role="status">Thanks — your message was saved and the notification email was queued.</div>
      <?php endif; ?>

      <?php foreach ($errors as $err): ?>
        <div class="alert" role="alert"><?= e($err) ?></div>
      <?php endforeach; ?>

      <form method="post" action="<?= e(url_for('contact.php')) ?>" novalidate>
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

        <label for="name">Name</label>
        <input id="name" name="name" type="text" autocomplete="name" required maxlength="120" value="<?= e($old['name']) ?>">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" autocomplete="email" required maxlength="190" value="<?= e($old['email']) ?>">

        <label for="subject">Subject</label>
        <input id="subject" name="subject" type="text" required maxlength="200" value="<?= e($old['subject']) ?>">

        <label for="message">Message</label>
        <textarea id="message" name="message" required maxlength="6000"><?= e($old['message']) ?></textarea>

        <div style="margin-top:14px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
          <button class="btn btn-primary" type="submit">Send message</button>
          <span class="hint">If emails don’t arrive on localhost, configure SMTP/sendmail in php.ini — DB storage still works.</span>
        </div>
      </form>
    </section>
  </main>

  <footer>
    <div class="wrap">© <?= date('Y') ?> Portfolio</div>
  </footer>
</body>
</html>
