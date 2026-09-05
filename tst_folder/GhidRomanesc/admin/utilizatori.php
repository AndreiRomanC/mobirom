<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::requireRole('administrator');
$pageTitle = 'Utilizatori';
$me = Auth::user();

$actiune = sanitize($_GET['actiune'] ?? '');
$id = (int)($_GET['id'] ?? 0);

// POST: salvare
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = sanitize($_POST['actiune'] ?? '');

    if ($act === 'add') {
        $errors = [];
        $email = sanitize($_POST['email'] ?? '');
        $name  = sanitize($_POST['name'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $role  = sanitize($_POST['role'] ?? 'autor');
        if (!$name)  $errors[] = 'Numele e obligatoriu.';
        if (!$email) $errors[] = 'Email-ul e obligatoriu.';
        if (strlen($pass) < 8) $errors[] = 'Parola trebuie să aibă minim 8 caractere.';
        if (Database::fetchOne('SELECT id FROM users WHERE email=?', [$email])) $errors[] = 'Email deja folosit.';
        if (empty($errors)) {
            Database::insert('users', ['name'=>$name,'email'=>$email,'password'=>Auth::hashPassword($pass),'role'=>$role,'is_active'=>1,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
            redirect('/admin/utilizatori/?ok=adaugat');
        }
    }

    if ($act === 'edit') {
        $uid = (int)$_POST['id'];
        $data = ['name'=>sanitize($_POST['name']??''),'email'=>sanitize($_POST['email']??''),'role'=>sanitize($_POST['role']??'autor'),'is_active'=>(int)($_POST['is_active']??1)];
        if (!empty($_POST['password']) && strlen($_POST['password']) >= 8) {
            $data['password'] = Auth::hashPassword($_POST['password']);
        }
        Database::update('users', $data, 'id=?', [$uid]);
        redirect('/admin/utilizatori/?ok=salvat');
    }

    if ($act === 'toggle') {
        $uid = (int)$_POST['id'];
        if ($uid !== (int)$me['id']) {
            $u = Database::fetchOne('SELECT is_active FROM users WHERE id=?', [$uid]);
            if ($u) Database::update('users', ['is_active' => $u['is_active'] ? 0 : 1], 'id=?', [$uid]);
        }
        redirect('/admin/utilizatori/?ok=salvat');
    }
}

$editUser = $id ? Database::fetchOne('SELECT * FROM users WHERE id=?', [$id]) : null;
$users = Database::fetchAll('SELECT * FROM users ORDER BY role, name');
$roles = ['administrator'=>'Administrator','editor'=>'Editor','autor'=>'Autor','reviewer'=>'Reviewer'];

require __DIR__ . '/../templates/admin-layout.php';
?>

<?php if (isset($_GET['ok'])): ?><div class="admin-notice success">✓ <?= $_GET['ok']==='adaugat'?'Utilizator adăugat.':'Salvat.' ?></div><?php endif; ?>
<?php if (!empty($errors)): ?><div class="admin-notice error"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.25rem">

  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">Utilizatori (<?= count($users) ?>)</span></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Nume</th><th>Email</th><th>Rol</th><th>Status</th><th>Ultimul login</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><strong><?= e($u['name']) ?></strong><?= $u['id']==$me['id'] ? ' <span style="font-size:.7rem;color:var(--text-muted)">(tu)</span>' : '' ?></td>
            <td style="font-size:.8rem"><?= e($u['email']) ?></td>
            <td><span class="badge badge-gray"><?= e($roles[$u['role']]??$u['role']) ?></span></td>
            <td><?= $u['is_active'] ? '<span class="badge badge-green">Activ</span>' : '<span class="badge badge-red">Inactiv</span>' ?></td>
            <td style="font-size:.78rem;color:var(--text-muted)"><?= $u['last_login'] ? formatDate($u['last_login'],'d.m.Y H:i') : '—' ?></td>
            <td>
              <div style="display:flex;gap:.3rem">
                <a href="?actiune=editeaza&id=<?= $u['id'] ?>" class="btn btn-sm btn-secondary">✏️</a>
                <?php if ($u['id'] != $me['id']): ?>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="actiune" value="toggle">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn btn-sm <?= $u['is_active']?'btn-danger':'btn-success' ?>"><?= $u['is_active']?'Dezactivează':'Activează' ?></button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title"><?= $editUser ? 'Editează: '.e($editUser['name']) : 'Utilizator nou' ?></span></div>
    <div class="admin-card-body">
      <form method="POST">
        <input type="hidden" name="actiune" value="<?= $editUser ? 'edit' : 'add' ?>">
        <?php if ($editUser): ?><input type="hidden" name="id" value="<?= $editUser['id'] ?>"><?php endif; ?>
        <div class="form-group">
          <label>Nume *</label>
          <input type="text" name="name" class="form-control" value="<?= e($editUser['name']??'') ?>" required>
        </div>
        <div class="form-group">
          <label>Email *</label>
          <input type="email" name="email" class="form-control" value="<?= e($editUser['email']??'') ?>" required>
        </div>
        <div class="form-group">
          <label>Parolă <?= $editUser ? '(lasă gol = nu se schimbă)' : '*' ?></label>
          <input type="password" name="password" class="form-control" <?= $editUser?'':'required' ?> minlength="8" placeholder="minim 8 caractere">
        </div>
        <div class="form-group">
          <label>Rol</label>
          <select name="role" class="form-control">
            <?php foreach ($roles as $k=>$v): ?>
            <option value="<?=$k?>" <?= ($editUser['role']??'autor')===$k?'selected':'' ?>><?=$v?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php if ($editUser && $editUser['id'] != $me['id']): ?>
        <div class="form-group">
          <label><input type="checkbox" name="is_active" value="1" <?= $editUser['is_active']?'checked':'' ?>> Cont activ</label>
        </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary" style="width:100%"><?= $editUser ? 'Salvează' : '+ Adaugă' ?></button>
        <?php if ($editUser): ?><a href="/admin/utilizatori/" class="btn btn-secondary" style="width:100%;margin-top:.5rem;text-align:center;display:block">Utilizator nou</a><?php endif; ?>
      </form>
    </div>
  </div>

</div>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
