<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$file = CONTENT_DIR . '/submissions.json';
$subs = [];
if (file_exists($file)) {
    $subs = json_decode(file_get_contents($file), true) ?: [];
}
$subs = array_reverse($subs); // newest first
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mesaje — Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
  <style>
    .msg-card { background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);padding:1.5rem;margin-bottom:1rem; }
    .msg-meta { display:flex;flex-wrap:wrap;gap:.5rem 1.5rem;margin-bottom:.85rem; }
    .msg-meta span { font-size:.78rem;color:var(--text-3); }
    .msg-meta strong { color:var(--text);font-size:.88rem; }
    .msg-body { font-size:.9rem;color:var(--text-2);white-space:pre-wrap;border-left:3px solid var(--border);padding-left:1rem; }
  </style>
</head>
<body>
<div class="admin-wrap">
  <header class="admin-header">
    <span class="admin-logo">📨 &nbsp;Mesaje primite</span>
    <div style="display:flex;gap:1rem;align-items:center">
      <a href="<?= BASE_PATH ?>/admin/index.php" style="font-size:.85rem;color:var(--text-3)">← Dashboard</a>
      <a href="<?= BASE_PATH ?>/admin/logout.php" class="btn btn-outline btn-sm">Deconectare</a>
    </div>
  </header>

  <div class="admin-content">
    <h1 style="margin-bottom:.35rem">Mesaje de contact</h1>
    <p style="color:var(--text-3);margin-bottom:2rem">Total: <strong><?= count($subs) ?></strong> mesaje</p>

    <?php if (empty($subs)): ?>
      <div class="admin-card">
        <p style="text-align:center;color:var(--text-3)">Nu există mesaje încă.</p>
      </div>
    <?php else: ?>
      <?php foreach ($subs as $s): ?>
        <div class="msg-card">
          <div class="msg-meta">
            <span>🕒 <?= htmlspecialchars($s['date'] ?? '—') ?></span>
            <span><strong><?= htmlspecialchars($s['name'] ?? '—') ?></strong></span>
            <span><a href="mailto:<?= htmlspecialchars($s['email'] ?? '') ?>"><?= htmlspecialchars($s['email'] ?? '—') ?></a></span>
            <?php if (!empty($s['phone'])): ?>
              <span>📞 <?= htmlspecialchars($s['phone']) ?></span>
            <?php endif; ?>
            <?php if (!empty($s['subject'])): ?>
              <span>📌 <?= htmlspecialchars($s['subject']) ?></span>
            <?php endif; ?>
          </div>
          <div class="msg-body"><?= htmlspecialchars($s['message'] ?? '—') ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
