<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::requireAdmin();

$pageTitle = 'Raportări erori';

if ($_GET['actiune'] ?? '' === 'status' && isset($_GET['id'], $_GET['status'])) {
    Database::update('error_reports', ['status' => sanitize($_GET['status'])], 'id=?', [(int)$_GET['id']]);
    redirect('/admin/raportari/?ok=1');
}

$filterStatus = sanitize($_GET['status'] ?? 'new');
$reports = Database::fetchAll(
    'SELECT er.*, a.title AS article_title, a.slug AS article_slug
     FROM error_reports er
     LEFT JOIN articles a ON er.article_id=a.id
     WHERE er.status=?
     ORDER BY er.created_at DESC',
    [$filterStatus]
);

require __DIR__ . '/../templates/admin-layout.php';
?>
<?php if (isset($_GET['ok'])): ?><div class="admin-notice success">✓ Status actualizat.</div><?php endif; ?>

<div class="admin-toolbar">
  <div class="admin-toolbar-left">
    <?php foreach (['new'=>'Noi','reviewed'=>'Verificate','fixed'=>'Rezolvate','dismissed'=>'Ignorate'] as $s => $l): ?>
    <a href="?status=<?=$s?>" class="filter-tab<?= $filterStatus===$s?' active':'' ?>"><?=$l?>
      <?php if ($s==='new'): ?>(<?= Database::fetchColumn('SELECT COUNT(*) FROM error_reports WHERE status="new"') ?>)<?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="admin-card">
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Descriere</th><th>URL / Articol</th><th>Email</th><th>Data</th><th>Acțiuni</th></tr></thead>
      <tbody>
        <?php foreach ($reports as $r): ?>
        <tr>
          <td style="max-width:300px"><?= e(truncate($r['description'], 120)) ?></td>
          <td style="font-size:.8rem">
            <?php if ($r['article_title']): ?>
            <a href="/admin/articole/?actiune=editeaza&id=<?= $r['article_id'] ?>"><?= e(truncate($r['article_title'], 40)) ?></a>
            <?php elseif ($r['article_url']): ?>
            <a href="<?= e($r['article_url']) ?>" target="_blank" style="font-size:.78rem"><?= e(truncate($r['article_url'], 50)) ?></a>
            <?php else: ?>—<?php endif; ?>
          </td>
          <td style="font-size:.8rem"><?= e($r['email'] ?? '—') ?></td>
          <td style="font-size:.8rem"><?= formatDate($r['created_at']) ?></td>
          <td>
            <div style="display:flex;gap:.3rem">
              <a href="?actiune=status&id=<?=$r['id']?>&status=reviewed" class="btn btn-sm btn-secondary" title="Marchează verificat">✓</a>
              <a href="?actiune=status&id=<?=$r['id']?>&status=fixed" class="btn btn-sm btn-success" title="Rezolvat">✔</a>
              <a href="?actiune=status&id=<?=$r['id']?>&status=dismissed" class="btn btn-sm btn-danger" title="Ignoră">✕</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($reports)): ?><tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted)">Nicio raportare în această categorie.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
