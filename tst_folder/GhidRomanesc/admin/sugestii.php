<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::requireAdmin();
$pageTitle = 'Sugestii subiecte';

if ($_GET['actiune'] ?? '' === 'status' && isset($_GET['id'], $_GET['status'])) {
    Database::update('topic_suggestions', ['status' => sanitize($_GET['status'])], 'id=?', [(int)$_GET['id']]);
    redirect('/admin/sugestii/?ok=1');
}

$suggestions = Database::fetchAll(
    'SELECT * FROM topic_suggestions ORDER BY CASE status WHEN "new" THEN 1 WHEN "accepted" THEN 2 WHEN "rejected" THEN 3 WHEN "published" THEN 4 ELSE 5 END, created_at DESC LIMIT 100'
);

require __DIR__ . '/../templates/admin-layout.php';
?>
<?php if (isset($_GET['ok'])): ?><div class="admin-notice success">✓ Actualizat.</div><?php endif; ?>

<div class="admin-card">
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Subiect</th><th>Detalii</th><th>Email</th><th>Status</th><th>Data</th><th>Acțiuni</th></tr></thead>
      <tbody>
        <?php foreach ($suggestions as $s): ?>
        <tr>
          <td style="font-weight:600;max-width:240px"><?= e(truncate($s['subject'],60)) ?></td>
          <td style="font-size:.8rem;max-width:200px"><?= e(truncate($s['description']??'',80)) ?></td>
          <td style="font-size:.8rem"><?= e($s['email']??'—') ?></td>
          <td><?= statusBadge($s['status']) ?></td>
          <td style="font-size:.8rem"><?= formatDate($s['created_at']) ?></td>
          <td>
            <div style="display:flex;gap:.3rem">
              <a href="/admin/ai-studio/?subiect=<?= urlencode($s['subject']) ?>" class="btn btn-sm btn-primary">AI →</a>
              <a href="?actiune=status&id=<?=$s['id']?>&status=accepted" class="btn btn-sm btn-success" title="Acceptă">✓</a>
              <a href="?actiune=status&id=<?=$s['id']?>&status=rejected" class="btn btn-sm btn-danger" title="Respinge">✕</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($suggestions)): ?><tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">Nicio sugestie.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
