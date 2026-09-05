<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::requireAdmin();
$pageTitle = 'Newsletter';

// Trimitere email
$sendMsg = $sendErr = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['actiune'] ?? '') === 'send') {
    $subiect = sanitize($_POST['subiect'] ?? '');
    $corp    = $_POST['corp'] ?? '';
    $onlyConfirmed = isset($_POST['only_confirmed']);

    if (!$subiect || !$corp) {
        $sendErr = 'Subiectul și conținutul sunt obligatorii.';
    } else {
        $where = $onlyConfirmed ? 'is_active=1 AND confirmed_at IS NOT NULL' : 'is_active=1';
        $abonati = Database::fetchAll("SELECT email, name, confirm_token FROM newsletter WHERE $where");
        $ok = $fail = 0;
        foreach ($abonati as $ab) {
            $unsub = SITE_DOMAIN . '/newsletter/dezabonare/?token=' . urlencode($ab['confirm_token'] ?? $ab['email']);
            $headers = "From: " . SITE_NAME . " <" . SITE_EMAIL . ">\r\nContent-Type: text/html; charset=UTF-8\r\nMIME-Version: 1.0";
            $body = $corp . '<br><br><hr style="border:none;border-top:1px solid #eee"><p style="font-size:12px;color:#888">Ai primit acest email pentru că ești abonat la <a href="'.SITE_DOMAIN.'">'.SITE_NAME.'</a>. <a href="'.$unsub.'">Dezabonează-te</a>.</p>';
            if (mail($ab['email'], $subiect, $body, $headers)) $ok++; else $fail++;
        }
        $sendMsg = "Trimis: $ok ✓" . ($fail ? " | Eșuat: $fail ✕" : '');
    }
}

$total = Database::fetchColumn('SELECT COUNT(*) FROM newsletter WHERE is_active=1');
$recent   = Database::fetchAll('SELECT * FROM newsletter WHERE is_active=1 ORDER BY created_at DESC LIMIT 50');
require __DIR__ . '/../templates/admin-layout.php';
?>

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);max-width:500px;margin-bottom:1.25rem">
  <div class="stat-card">
    <div class="stat-value"><?= number_format($total) ?></div>
    <div class="stat-label">Abonați activi</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= Database::fetchColumn('SELECT COUNT(*) FROM newsletter WHERE confirmed_at IS NOT NULL') ?></div>
    <div class="stat-label">Confirmați</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= Database::fetchColumn('SELECT COUNT(*) FROM newsletter WHERE unsubscribed_at IS NOT NULL') ?></div>
    <div class="stat-label">Dezabonați</div>
  </div>
</div>

<?php if ($sendMsg): ?><div class="admin-notice success">✓ <?= e($sendMsg) ?></div><?php endif; ?>
<?php if ($sendErr): ?><div class="admin-notice error">✕ <?= e($sendErr) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:1.25rem;margin-bottom:1.25rem">
<div>
<div class="admin-card">
  <div class="admin-card-header">
    <span class="admin-card-title">Abonați recenți</span>
    <a href="/api/export-newsletter.php" class="btn btn-sm btn-secondary">Export CSV</a>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Email</th><th>Nume</th><th>Confirmat</th><th>Data abonare</th></tr></thead>
      <tbody>
        <?php foreach ($recent as $sub): ?>
        <tr>
          <td><?= e($sub['email']) ?></td>
          <td><?= e($sub['name'] ?? '—') ?></td>
          <td><?= $sub['confirmed_at'] ? '<span style="color:#16a34a">✓ Da</span>' : '<span style="color:#dc2626">Nu</span>' ?></td>
          <td style="font-size:.8rem"><?= formatDate($sub['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</div>

<!-- Trimitere email -->
<div class="admin-card">
  <div class="admin-card-header"><span class="admin-card-title">✉️ Trimite email abonaților</span></div>
  <div class="admin-card-body">
    <form method="POST">
      <input type="hidden" name="actiune" value="send">
      <div class="form-group">
        <label>Subiect email *</label>
        <input type="text" name="subiect" class="form-control" required placeholder="ex: Ghiduri noi pe GhidRomânesc">
      </div>
      <div class="form-group">
        <label>Conținut (HTML permis) *</label>
        <textarea name="corp" class="form-control" rows="8" required placeholder="<h2>Salut!</h2><p>...</p>"></textarea>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="only_confirmed" checked> Trimite doar la abonații confirmați</label>
      </div>
      <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem">Link de dezabonare se adaugă automat la final. Se trimite prin <code>mail()</code> de pe server.</p>
      <button type="submit" class="btn btn-primary" onclick="return confirm('Ești sigur? Se trimite email la toți abonații selectați.')">Trimite newsletter</button>
    </form>
  </div>
</div>
</div>
<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
