<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::requireAdmin();

$pageTitle = 'Calendar editorial';

// Adaugă intrare în calendar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title'       => sanitize($_POST['title'] ?? ''),
        'category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
        'status'      => sanitize($_POST['status'] ?? 'idee'),
        'target_date' => $_POST['target_date'] ?? null,
        'notes'       => sanitize($_POST['notes'] ?? ''),
        'assigned_to' => Auth::user()['id'],
    ];
    if ($data['title']) {
        Database::insert('editorial_calendar', $data);
        redirect('/admin/calendar/?ok=1');
    }
}

// Schimbă status
if ($_GET['actiune'] ?? '' === 'status' && isset($_GET['id'], $_GET['status'])) {
    Database::update('editorial_calendar', ['status' => sanitize($_GET['status'])], 'id=?', [(int)$_GET['id']]);
    redirect('/admin/calendar/?ok=1');
}
// Șterge
if ($_GET['actiune'] ?? '' === 'sterge' && isset($_GET['id'])) {
    Database::delete('editorial_calendar', 'id=?', [(int)$_GET['id']]);
    redirect('/admin/calendar/');
}

$statuses = ['idee','draft_ai','verificare','aprobat','programat','publicat','actualizare','blocat'];
$statusLabels = [
    'idee'       => 'Idee',
    'draft_ai'   => 'Draft AI',
    'verificare' => 'În verificare',
    'aprobat'    => 'Aprobat',
    'programat'  => 'Programat',
    'publicat'   => 'Publicat',
    'actualizare'=> 'De actualizat',
    'blocat'     => 'Blocat',
];

$columns = [];
foreach ($statuses as $s) {
    $columns[$s] = Database::fetchAll(
        'SELECT ec.*, c.name AS category_name
         FROM editorial_calendar ec
         LEFT JOIN categories c ON ec.category_id=c.id
         WHERE ec.status=?
         ORDER BY ec.target_date ASC, ec.created_at DESC',
        [$s]
    );
}
$categories = Database::fetchAll('SELECT * FROM categories ORDER BY name');

require __DIR__ . '/../templates/admin-layout.php';
?>

<?php if (isset($_GET['ok'])): ?><div class="admin-notice success">✓ Salvat cu succes.</div><?php endif; ?>

<!-- Formular adaugă idee rapidă -->
<div class="admin-card" style="margin-bottom:1.25rem">
  <div class="admin-card-header"><span class="admin-card-title">+ Adaugă idee în calendar</span></div>
  <div class="admin-card-body">
    <form method="POST" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
      <div style="flex:2;min-width:200px">
        <label style="font-size:.8rem;margin-bottom:.3rem;display:block">Titlu / idee</label>
        <input type="text" name="title" class="form-control" required placeholder="ex: Cum reînnoiești pașaportul în diaspora">
      </div>
      <div style="min-width:160px">
        <label style="font-size:.8rem;margin-bottom:.3rem;display:block">Categorie</label>
        <select name="category_id" class="form-control">
          <option value="">—</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="min-width:140px">
        <label style="font-size:.8rem;margin-bottom:.3rem;display:block">Status</label>
        <select name="status" class="form-control">
          <?php foreach ($statuses as $s): ?><option value="<?=$s?>"><?= $statusLabels[$s] ?></option><?php endforeach; ?>
        </select>
      </div>
      <div style="min-width:140px">
        <label style="font-size:.8rem;margin-bottom:.3rem;display:block">Data țintă</label>
        <input type="date" name="target_date" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary">+ Adaugă</button>
    </form>
  </div>
</div>

<!-- Kanban board -->
<div style="overflow-x:auto">
<div class="calendar-grid" style="grid-template-columns:repeat(<?= count($statuses) ?>,minmax(180px,1fr));min-width:900px">
  <?php foreach ($statuses as $s): ?>
  <div>
    <div class="calendar-column-title">
      <?= $statusLabels[$s] ?>
      <span style="background:#e5e7eb;border-radius:10px;padding:.1rem .45rem;font-size:.72rem;font-weight:700;margin-left:.4rem"><?= count($columns[$s]) ?></span>
    </div>
    <?php foreach ($columns[$s] as $item): ?>
    <div class="calendar-item">
      <div style="font-weight:600;font-size:.875rem;color:#1d3557;margin-bottom:.3rem"><?= e(truncate($item['title'], 60)) ?></div>
      <?php if ($item['category_name']): ?><div style="font-size:.72rem;color:#6b7280;margin-bottom:.3rem"><?= e($item['category_name']) ?></div><?php endif; ?>
      <?php if ($item['target_date']): ?><div style="font-size:.72rem;color:#9ca3af">📅 <?= formatDate($item['target_date']) ?></div><?php endif; ?>
      <div style="display:flex;gap:.3rem;margin-top:.5rem;flex-wrap:wrap">
        <?php if ($s !== 'draft_ai'): ?>
        <a href="/admin/ai-studio/?subiect=<?= urlencode($item['title']) ?>" class="btn btn-sm" style="padding:.2rem .5rem;font-size:.72rem;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe">AI →</a>
        <?php endif; ?>
        <!-- Mută la status următor -->
        <?php
        $nextIdx = array_search($s, $statuses) + 1;
        if (isset($statuses[$nextIdx])):
        $nextS = $statuses[$nextIdx];
        ?>
        <a href="/admin/calendar/?actiune=status&id=<?=$item['id']?>&status=<?=$nextS?>" class="btn btn-sm" style="padding:.2rem .5rem;font-size:.72rem;background:#f0fdf4;color:#166534;border:1px solid #86efac">→ <?= $statusLabels[$nextS] ?></a>
        <?php endif; ?>
        <a href="/admin/calendar/?actiune=sterge&id=<?=$item['id']?>" class="btn btn-sm" style="padding:.2rem .5rem;font-size:.72rem;background:#fef2f2;color:#dc2626;border:1px solid #fca5a5" onclick="return confirm('Ștergi această intrare?')">✕</a>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($columns[$s])): ?>
    <div style="border:2px dashed #e5e7eb;border-radius:8px;padding:1.5rem;text-align:center;font-size:.8rem;color:#9ca3af">Gol</div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
</div>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
