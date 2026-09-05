<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Article.php';
Auth::requireAdmin();

$pageTitle = 'Dashboard';

// Acțiuni căutări
if (($_GET['actiune'] ?? '') === 'sterge-cautari') {
    Database::query('DELETE FROM search_queries');
    redirect('/admin/?ok=cautari-sterse');
}
if (($_GET['actiune'] ?? '') === 'sterge-cautare' && isset($_GET['id'])) {
    Database::query('DELETE FROM search_queries WHERE id=?', [(int)$_GET['id']]);
    redirect('/admin/?ok=cautare-stearsa');
}

// Statistici
$stats = [
    'published'    => Database::fetchColumn('SELECT COUNT(*) FROM articles WHERE status="published"'),
    'scheduled'    => Database::fetchColumn('SELECT COUNT(*) FROM articles WHERE status="scheduled"'),
    'pending'      => Database::fetchColumn('SELECT COUNT(*) FROM articles WHERE status="pending"'),
    'draft'        => Database::fetchColumn('SELECT COUNT(*) FROM articles WHERE status="draft"'),
    'blocked'      => Database::fetchColumn('SELECT COUNT(*) FROM articles WHERE status="blocked"'),
    'needs_update' => Database::fetchColumn("SELECT COUNT(*) FROM articles WHERE status='published' AND review_date <= DATE('now')"),
    'total_views'  => Database::fetchColumn('SELECT SUM(views) FROM articles WHERE status="published"'),
    'subscribers'  => Database::fetchColumn('SELECT COUNT(*) FROM newsletter WHERE is_active=1'),
    'reports'      => Database::fetchColumn('SELECT COUNT(*) FROM error_reports WHERE status="new"'),
    'suggestions'  => Database::fetchColumn('SELECT COUNT(*) FROM topic_suggestions WHERE status="new"'),
];

// Cele mai citite
$topArticles = Database::fetchAll(
    'SELECT a.title, a.slug, a.views, a.status, c.slug AS cat_slug
     FROM articles a LEFT JOIN categories c ON a.category_id=c.id
     WHERE a.status="published"
     ORDER BY a.views DESC LIMIT 8'
);

// Articole de verificat
$toReview = Database::fetchAll(
    "SELECT a.*, c.name AS category_name
     FROM articles a LEFT JOIN categories c ON a.category_id=c.id
     WHERE a.status='published' AND a.review_date <= DATE('now')
     ORDER BY a.review_date ASC LIMIT 8"
);

// Cele mai recente
$recentArticles = Database::fetchAll(
    'SELECT a.*, c.name AS category_name
     FROM articles a LEFT JOIN categories c ON a.category_id=c.id
     ORDER BY a.created_at DESC LIMIT 8'
);

// Top căutări
$topSearches = Database::fetchAll('SELECT id, query, count FROM search_queries ORDER BY count DESC LIMIT 100');

// Widget trafic rapid pentru dashboard
$trafficToday  = (int)Database::fetchColumn("SELECT COUNT(DISTINCT ip) FROM visits WHERE is_bot=0 AND DATE(created_at)=DATE('now','localtime')");
$traffic7days  = (int)Database::fetchColumn("SELECT COUNT(DISTINCT ip) FROM visits WHERE is_bot=0 AND created_at >= DATE('now','-7 days')");
$traffic30days = (int)Database::fetchColumn("SELECT COUNT(DISTINCT ip) FROM visits WHERE is_bot=0 AND created_at >= DATE('now','-30 days')");

require __DIR__ . '/../templates/admin-layout.php';
?>

<!-- Alerte importante -->
<?php if (($_GET['ok'] ?? '') === 'cautari-sterse'): ?>
<div class="admin-notice success">✓ Tot istoricul căutărilor a fost șters.</div>
<?php elseif (($_GET['ok'] ?? '') === 'cautare-stearsa'): ?>
<div class="admin-notice success">✓ Căutarea a fost ștearsă.</div>
<?php endif; ?>
<?php if ($stats['needs_update'] > 0): ?>
<div class="admin-notice">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
  <span><strong><?= $stats['needs_update'] ?> articole</strong> trebuie reverificăte acum.
  <a href="/admin/articole/?status=needs_update">Verifică-le →</a></span>
</div>
<?php endif; ?>

<?php if ($stats['reports'] > 0): ?>
<div class="admin-notice">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
  <span><strong><?= $stats['reports'] ?> raportări de erori</strong> neverificate.
  <a href="/admin/raportari/">Verifică-le →</a></span>
</div>
<?php endif; ?>

<!-- Statistici -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-value"><?= number_format($stats['published']) ?></div>
    <div class="stat-label">Articole publicate</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $stats['pending'] ?></div>
    <div class="stat-label">În verificare</div>
    <?php if ($stats['pending'] > 0): ?><div class="stat-change"><a href="/admin/articole/?status=pending">Verifică →</a></div><?php endif; ?>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $stats['draft'] ?></div>
    <div class="stat-label">Drafturi</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $stats['scheduled'] ?></div>
    <div class="stat-label">Programate</div>
  </div>
  <div class="stat-card">
    <div class="stat-value" style="color:<?= $stats['needs_update'] > 0 ? '#dc2626' : '#1d3557' ?>"><?= $stats['needs_update'] ?></div>
    <div class="stat-label">De actualizat</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= number_format($stats['total_views']) ?></div>
    <div class="stat-label">Total vizualizări</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= number_format($stats['subscribers']) ?></div>
    <div class="stat-label">Abonați newsletter</div>
  </div>
  <div class="stat-card">
    <div class="stat-value" style="color:<?= $stats['blocked'] > 0 ? '#b45309' : '#1d3557' ?>"><?= $stats['blocked'] ?></div>
    <div class="stat-label">Blocate</div>
  </div>
</div>

<!-- Widget trafic rapid -->
<div class="admin-card" style="margin-bottom:1.25rem">
  <div class="admin-card-header">
    <span class="admin-card-title">📊 Analiză trafic</span>
    <a href="/admin/vizitatori/" class="btn btn-sm btn-secondary">Detalii complete →</a>
  </div>
  <div class="admin-card-body" style="padding:1rem">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:.875rem">
      <div style="text-align:center;background:#f0f4ff;border-radius:10px;padding:.85rem">
        <div style="font-size:1.5rem;font-weight:900;color:#1d3557"><?= number_format($trafficToday) ?></div>
        <div style="font-size:.75rem;color:#6b7280;margin-top:.2rem">Vizitatori azi</div>
      </div>
      <div style="text-align:center;background:#f0fdf4;border-radius:10px;padding:.85rem">
        <div style="font-size:1.5rem;font-weight:900;color:#15803d"><?= number_format($traffic7days) ?></div>
        <div style="font-size:.75rem;color:#6b7280;margin-top:.2rem">Ultimele 7 zile</div>
      </div>
      <div style="text-align:center;background:#fef9c3;border-radius:10px;padding:.85rem">
        <div style="font-size:1.5rem;font-weight:900;color:#92400e"><?= number_format($traffic30days) ?></div>
        <div style="font-size:.75rem;color:#6b7280;margin-top:.2rem">Ultimele 30 zile</div>
      </div>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
      <a href="/admin/vizitatori/?zile=7&tip=human" class="btn btn-sm btn-secondary">📅 7 zile</a>
      <a href="/admin/vizitatori/?zile=30&tip=human" class="btn btn-sm btn-secondary">📅 30 zile</a>
      <a href="/admin/vizitatori/?tip=bot" class="btn btn-sm btn-secondary">🤖 Boți</a>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-top:.5rem">

  <!-- Articole de verificat -->
  <?php if (!empty($toReview)): ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title" style="color:#dc2626">⏰ Articole pentru reverificare</span>
      <a href="/admin/articole/?status=needs_update" class="btn btn-sm btn-secondary">Toate</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Articol</th><th>Data verif.</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($toReview as $art): ?>
          <tr>
            <td class="col-title"><a href="/admin/articole/?id=<?= $art['id'] ?>"><?= e(truncate($art['title'], 50)) ?></a></td>
            <td style="color:#dc2626"><?= formatDate($art['review_date']) ?></td>
            <td><a href="/admin/articole/?actiune=editeaza&id=<?= $art['id'] ?>" class="btn btn-sm btn-secondary">Verifică</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Cele mai citite -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">📊 Cele mai citite</span>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Articol</th><th>Vizualizări</th></tr></thead>
        <tbody>
          <?php foreach ($topArticles as $art): ?>
          <tr>
            <td class="col-title"><a href="/<?= e($art['cat_slug']) ?>/<?= e($art['slug']) ?>/" target="_blank"><?= e(truncate($art['title'], 50)) ?></a></td>
            <td><?= number_format($art['views']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Căutări interne -->
  <?php if (!empty($topSearches)): ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">🔍 Căutări interne (<?= count($topSearches) ?>)</span>
      <div style="display:flex;gap:.4rem">
        <button onclick="toggleSearches()" class="btn btn-sm btn-secondary" id="btn-searches">Extinde toate</button>
        <a href="?actiune=sterge-cautari" class="btn btn-sm btn-danger" data-confirm="Ștergi tot istoricul de căutări?" title="Șterge tot istoricul">🗑 Șterge tot</a>
      </div>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table" id="searches-table">
        <thead><tr><th>Termen căutat</th><th>Căutări</th><th>Acțiuni</th></tr></thead>
        <tbody>
          <?php foreach ($topSearches as $i => $sq): ?>
          <tr class="search-row<?= $i >= 10 ? ' search-extra' : '' ?>" <?= $i >= 10 ? 'style="display:none"' : '' ?> id="sq-<?= $sq['id'] ?>">
            <td><?= e($sq['query']) ?></td>
            <td><strong><?= $sq['count'] ?></strong></td>
            <td style="white-space:nowrap">
              <a href="/admin/ai-studio/?subiect=<?= urlencode($sq['query']) ?>" class="btn btn-sm btn-secondary">AI →</a>
              <a href="?actiune=sterge-cautare&id=<?= $sq['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Ștergi căutarea «<?= e($sq['query']) ?>»?" title="Șterge această căutare">✕</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <script>
  function toggleSearches() {
    var rows = document.querySelectorAll('.search-extra');
    var btn = document.getElementById('btn-searches');
    var hidden = rows[0] && rows[0].style.display === 'none';
    rows.forEach(function(r){ r.style.display = hidden ? '' : 'none'; });
    btn.textContent = hidden ? 'Restrânge' : 'Extinde toate';
  }
  </script>
  <?php endif; ?>

  <!-- Articole recente -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">🕐 Articole recente</span>
      <a href="/admin/articole/?actiune=nou" class="btn btn-sm btn-primary">+ Nou</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Articol</th><th>Status</th><th>Data</th></tr></thead>
        <tbody>
          <?php foreach ($recentArticles as $art): ?>
          <tr>
            <td class="col-title"><a href="/admin/articole/?actiune=editeaza&id=<?= $art['id'] ?>"><?= e(truncate($art['title'], 45)) ?></a></td>
            <td><?= statusBadge($art['status']) ?></td>
            <td style="font-size:.8rem"><?= formatDate($art['created_at'], 'd.m.Y') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Acțiuni rapide -->
<div class="admin-card" style="margin-top:1.25rem">
  <div class="admin-card-header"><span class="admin-card-title">⚡ Acțiuni rapide</span></div>
  <div class="admin-card-body" style="display:flex;gap:.75rem;flex-wrap:wrap">
    <a href="/admin/ai-studio/" class="btn btn-primary">🤖 AI Studio</a>
    <a href="/admin/articole/?actiune=nou" class="btn btn-secondary">✏️ Articol nou</a>
    <a href="/admin/trenduri/" class="btn btn-secondary">📈 Trenduri și idei</a>
    <a href="/admin/calendar/" class="btn btn-secondary">📅 Calendar editorial</a>
    <a href="/admin/setari/" class="btn btn-secondary">⚙️ Setări și prompturi AI</a>
  </div>
</div>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
