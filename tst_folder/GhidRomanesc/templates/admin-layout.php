<?php
// Layout admin — header
$adminTitle = ($pageTitle ?? 'Dashboard') . ' — Admin GhidRomânesc';
$currentAdmin = Auth::user();
$avatarLetter = strtoupper(substr($currentAdmin['name'] ?? 'A', 0, 1));

// Contoare pentru badge-uri
$pendingCount = (int)Database::fetchColumn('SELECT COUNT(*) FROM articles WHERE status="pending"');
$errorCount   = (int)Database::fetchColumn('SELECT COUNT(*) FROM error_reports WHERE status="new"');
$needsUpdateCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM articles WHERE status='published' AND review_date <= DATE('now')");
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($adminTitle) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
<script>window.csrfToken = '<?= csrf() ?>';</script>
</head>
<body class="admin-body">

<aside class="admin-sidebar" aria-label="Navigare admin">
  <div class="admin-logo">
    Ghid<span>Românesc</span>
    <small>Panou de administrare</small>
  </div>

  <nav class="admin-nav">
    <div class="admin-nav-group">
      <div class="admin-nav-label">General</div>
      <a href="/admin/" class="admin-nav-link<?= currentPath() === '/admin/' ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
        <?php if ($needsUpdateCount > 0): ?><span class="badge-count"><?= $needsUpdateCount ?></span><?php endif; ?>
      </a>
    </div>

    <div class="admin-nav-group">
      <div class="admin-nav-label">Conținut</div>
      <a href="/admin/articole/" class="admin-nav-link<?= isActivePath('/admin/articole') ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
        Articole
        <?php if ($pendingCount > 0): ?><span class="badge-count"><?= $pendingCount ?></span><?php endif; ?>
      </a>
      <a href="/admin/articole/?actiune=nou" class="admin-nav-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Articol nou
      </a>
      <a href="/admin/calendar/" class="admin-nav-link<?= isActivePath('/admin/calendar') ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Calendar editorial
      </a>
    </div>

    <div class="admin-nav-group">
      <div class="admin-nav-label">AI & Trenduri</div>
      <a href="/admin/ai-studio/" class="admin-nav-link<?= isActivePath('/admin/ai-studio') ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
        AI Studio
      </a>
      <a href="/admin/trenduri/" class="admin-nav-link<?= isActivePath('/admin/trenduri') ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23,6 13.5,15.5 8.5,10.5 1,18"/><polyline points="17,6 23,6 23,12"/></svg>
        Trenduri și idei
      </a>
    </div>

    <div class="admin-nav-group">
      <div class="admin-nav-label">Statistici</div>
      <a href="/admin/vizitatori/" class="admin-nav-link<?= isActivePath('/admin/vizitatori') ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        Trafic & Vizitatori
      </a>
    </div>

    <div class="admin-nav-group">
      <div class="admin-nav-label">Site</div>
      <a href="/admin/categorii/" class="admin-nav-link<?= isActivePath('/admin/categorii') ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Categorii
      </a>
      <a href="/admin/media/" class="admin-nav-link<?= isActivePath('/admin/media') ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
        Media
      </a>
      <a href="/admin/utilizatori/" class="admin-nav-link<?= isActivePath('/admin/utilizatori') ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Utilizatori
      </a>
    </div>

    <div class="admin-nav-group">
      <div class="admin-nav-label">Feedback</div>
      <a href="/admin/raportari/" class="admin-nav-link<?= isActivePath('/admin/raportari') ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        Raportări erori
        <?php if ($errorCount > 0): ?><span class="badge-count"><?= $errorCount ?></span><?php endif; ?>
      </a>
      <a href="/admin/sugestii/" class="admin-nav-link<?= isActivePath('/admin/sugestii') ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        Sugestii subiecte
      </a>
      <a href="/admin/newsletter/" class="admin-nav-link<?= isActivePath('/admin/newsletter') ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Newsletter
      </a>
    </div>

    <div class="admin-nav-group">
      <div class="admin-nav-label">Configurare</div>
      <a href="/admin/setari/" class="admin-nav-link<?= isActivePath('/admin/setari') ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
        Setări
      </a>
    </div>
  </nav>

  <div class="admin-sidebar-footer">
    <div style="margin-bottom:.4rem">
      <strong style="color:#fff"><?= e($currentAdmin['name']) ?></strong>
      <span style="display:block;font-size:.72rem;text-transform:capitalize"><?= e($currentAdmin['role']) ?></span>
    </div>
    <a href="/" target="_blank">Vedere site ↗</a> · <a href="/admin/logout.php">Deconectare</a>
  </div>
</aside>

<!-- Overlay mobil -->
<div class="admin-overlay" id="admin-overlay"></div>

<div class="admin-main">
  <div class="admin-topbar">
    <div style="display:flex;align-items:center;gap:.6rem">
      <button id="sidebar-toggle" aria-label="Deschide meniu">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <span class="admin-topbar-title"><?= e($pageTitle ?? 'Dashboard') ?></span>
    </div>
    <div class="admin-topbar-right">
      <a href="/admin/articole/?actiune=nou" class="btn btn-primary btn-sm">+ Articol nou</a>
      <div class="admin-user">
        <div class="admin-avatar"><?= $avatarLetter ?></div>
        <span class="admin-user-name"><?= e($currentAdmin['name']) ?></span>
      </div>
    </div>
  </div>
  <div class="admin-content">
