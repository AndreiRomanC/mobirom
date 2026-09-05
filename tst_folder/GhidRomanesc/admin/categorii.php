<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::requireRole('administrator');
$pageTitle = 'Categorii';

// Salvare
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actPost = sanitize($_POST['actiune'] ?? '');

    if ($actPost === 'edit') {
        $catId = (int)$_POST['id'];
        if ($catId) {
            Database::query(
                'UPDATE categories SET name=?, description=?, sort_order=? WHERE id=?',
                [sanitize($_POST['name']), sanitize($_POST['description'] ?? ''), (int)($_POST['sort_order'] ?? 0), $catId]
            );
        }
        redirect('/admin/categorii/?ok=1');
    }

    if ($actPost === 'add') {
        $name = sanitize($_POST['name'] ?? '');
        $slugNew = slug($name);
        if ($name && $slugNew) {
            Database::insert('categories', [
                'slug'        => $slugNew,
                'name'        => $name,
                'description' => sanitize($_POST['description'] ?? ''),
                'sort_order'  => (int)($_POST['sort_order'] ?? 99),
            ]);
        }
        redirect('/admin/categorii/?ok=1');
    }
}

$categories = Database::fetchAll(
    'SELECT c.*, COUNT(a.id) AS nr_articole
     FROM categories c
     LEFT JOIN articles a ON a.category_id=c.id AND a.status="published"
     GROUP BY c.id ORDER BY c.sort_order, c.name'
);

require __DIR__ . '/../templates/admin-layout.php';
?>

<?php if (isset($_GET['ok'])): ?><div class="admin-notice success">✓ Salvat.</div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:1.25rem">

  <!-- Listă categorii -->
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">Categorii existente</span></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Slug</th><th>Nume</th><th>Descriere</th><th style="text-align:center">Ordine</th><th style="text-align:center">Articole</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($categories as $c): ?>
          <tr id="row-<?= $c['id'] ?>">
            <td style="font-family:monospace;font-size:.8rem;color:var(--text-muted)"><?= e($c['slug']) ?></td>
            <td><strong><?= e($c['name']) ?></strong></td>
            <td style="font-size:.8rem"><?= e(truncate($c['description']??'',60)) ?></td>
            <td style="text-align:center"><?= $c['sort_order'] ?></td>
            <td style="text-align:center"><?= $c['nr_articole'] ?></td>
            <td>
              <button class="btn btn-sm btn-secondary" onclick="editCat(<?= htmlspecialchars(json_encode($c)) ?>)">✏️ Editează</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Formular edit / add -->
  <div>
    <div class="admin-card" id="edit-card">
      <div class="admin-card-header"><span class="admin-card-title" id="form-title">Editează categorie</span></div>
      <div class="admin-card-body">
        <form method="POST" id="cat-form">
          <input type="hidden" name="actiune" id="f-actiune" value="edit">
          <input type="hidden" name="id" id="f-id" value="">
          <div class="form-group">
            <label>Slug <span style="font-size:.75rem;color:var(--text-muted)">(nu se poate schimba)</span></label>
            <input type="text" id="f-slug" class="form-control" disabled style="color:var(--text-muted)">
          </div>
          <div class="form-group">
            <label>Nume *</label>
            <input type="text" name="name" id="f-name" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Descriere</label>
            <textarea name="description" id="f-description" class="form-control" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label>Ordine afișare</label>
            <input type="number" name="sort_order" id="f-order" class="form-control" min="0">
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%">Salvează</button>
        </form>
      </div>
    </div>

    <div class="admin-card" style="margin-top:1rem">
      <div class="admin-card-header"><span class="admin-card-title">Adaugă categorie nouă</span></div>
      <div class="admin-card-body">
        <form method="POST">
          <input type="hidden" name="actiune" value="add">
          <div class="form-group">
            <label>Nume *</label>
            <input type="text" name="name" class="form-control" required placeholder="ex: Sănătate">
          </div>
          <div class="form-group">
            <label>Descriere</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label>Ordine afișare</label>
            <input type="number" name="sort_order" class="form-control" value="99" min="0">
          </div>
          <p style="font-size:.75rem;color:var(--text-muted);margin-bottom:.75rem">Slug-ul se generează automat din nume.</p>
          <button type="submit" class="btn btn-secondary" style="width:100%">+ Adaugă</button>
        </form>
      </div>
    </div>
  </div>

</div>

<script>
function editCat(c) {
  document.getElementById('f-actiune').value = 'edit';
  document.getElementById('f-id').value = c.id;
  document.getElementById('f-slug').value = c.slug;
  document.getElementById('f-name').value = c.name;
  document.getElementById('f-description').value = c.description || '';
  document.getElementById('f-order').value = c.sort_order || 0;
  document.getElementById('form-title').textContent = 'Editează: ' + c.name;
  document.getElementById('edit-card').scrollIntoView({behavior:'smooth'});
}
</script>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
