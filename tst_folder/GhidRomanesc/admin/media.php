<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::requireAdmin();
$pageTitle = 'Media';

$uploadDir = __DIR__ . '/../assets/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$msg = $err = '';

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $f = $_FILES['file'];
    $allowed = ['image/jpeg','image/png','image/webp','image/gif','image/svg+xml'];
    $maxSize = 5 * 1024 * 1024;

    if ($f['error'] !== UPLOAD_ERR_OK) {
        $err = 'Eroare upload.';
    } elseif (!in_array($f['type'], $allowed)) {
        $err = 'Tip fișier nepermis. Acceptăm: JPG, PNG, WebP, GIF, SVG.';
    } elseif ($f['size'] > $maxSize) {
        $err = 'Fișierul depășește 5MB.';
    } else {
        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
        $safe = preg_replace('/[^a-z0-9_-]/', '', strtolower(pathinfo($f['name'], PATHINFO_FILENAME)));
        $filename = $safe . '_' . time() . '.' . $ext;
        if (move_uploaded_file($f['tmp_name'], $uploadDir . $filename)) {
            Database::insert('media', [
                'filename'    => $filename,
                'original'    => $f['name'],
                'mime'        => $f['type'],
                'size'        => $f['size'],
                'uploaded_by' => Auth::user()['id'],
            ]);
            $msg = 'Fișier urcat: /assets/uploads/' . $filename;
        } else {
            $err = 'Nu s-a putut salva fișierul.';
        }
    }
}

// Ștergere
if (isset($_GET['sterge'])) {
    $fname = basename(sanitize($_GET['sterge']));
    $fpath = $uploadDir . $fname;
    if (file_exists($fpath) && is_file($fpath)) {
        unlink($fpath);
        Database::query('DELETE FROM media WHERE filename=?', [$fname]);
    }
    redirect('/admin/media/?ok=1');
}

// Listare fișiere
$files = Database::fetchAll('SELECT * FROM media ORDER BY created_at DESC');

require __DIR__ . '/../templates/admin-layout.php';
?>

<?php if ($msg): ?><div class="admin-notice success">✓ <?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="admin-notice error">✕ <?= e($err) ?></div><?php endif; ?>
<?php if (isset($_GET['ok'])): ?><div class="admin-notice success">✓ Fișier șters.</div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.25rem">

  <!-- Galerie fișiere -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">Fișiere uploadate (<?= count($files) ?>)</span>
    </div>
    <div class="admin-card-body">
      <?php if (empty($files)): ?>
        <p style="color:var(--text-muted);text-align:center;padding:2rem">Niciun fișier urcat încă.</p>
      <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem">
        <?php foreach ($files as $f):
          $url = '/assets/uploads/' . $f['filename'];
          $isImg = str_starts_with($f['mime']??'','image/');
        ?>
        <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;background:#f8fafc">
          <?php if ($isImg): ?>
          <div style="height:100px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;overflow:hidden">
            <img src="<?= e($url) ?>" style="width:100%;height:100%;object-fit:cover" loading="lazy">
          </div>
          <?php else: ?>
          <div style="height:100px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-size:2rem">📄</div>
          <?php endif; ?>
          <div style="padding:.5rem">
            <div style="font-size:.72rem;color:var(--text-muted);word-break:break-all;margin-bottom:.4rem"><?= e(truncate($f['filename'],25)) ?></div>
            <div style="font-size:.7rem;color:var(--text-muted);margin-bottom:.5rem"><?= round($f['size']/1024,1) ?>KB</div>
            <div style="display:flex;gap:.3rem">
              <button class="btn btn-sm btn-secondary" style="flex:1;font-size:.7rem" onclick="copyUrl('<?= e($url) ?>')">📋 URL</button>
              <a href="?sterge=<?= urlencode($f['filename']) ?>" class="btn btn-sm btn-danger" style="font-size:.7rem" onclick="return confirm('Ștergi fișierul?')">🗑</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Upload -->
  <div class="admin-card" style="align-self:start">
    <div class="admin-card-header"><span class="admin-card-title">Upload fișier</span></div>
    <div class="admin-card-body">
      <form method="POST" enctype="multipart/form-data">
        <div style="border:2px dashed var(--border);border-radius:8px;padding:2rem;text-align:center;margin-bottom:1rem;cursor:pointer" onclick="document.getElementById('f-input').click()">
          <div style="font-size:2rem;margin-bottom:.5rem">📁</div>
          <div style="font-size:.85rem;color:var(--text-muted)">Click sau trage fișierul aici</div>
          <div style="font-size:.75rem;color:var(--text-muted);margin-top:.3rem">JPG, PNG, WebP, GIF, SVG · max 5MB</div>
        </div>
        <input type="file" id="f-input" name="file" accept="image/*" style="display:none" onchange="this.form.submit()">
        <button type="submit" class="btn btn-primary" style="width:100%">Urcă fișier</button>
      </form>
    </div>
  </div>

</div>

<div id="copy-toast" style="position:fixed;bottom:1.5rem;right:1.5rem;background:#1d3557;color:#fff;padding:.6rem 1.2rem;border-radius:8px;font-size:.85rem;display:none;z-index:999">URL copiat!</div>

<script>
function copyUrl(url) {
  const full = window.location.origin + url;
  navigator.clipboard.writeText(full).then(() => {
    const t = document.getElementById('copy-toast');
    t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 2000);
  });
}
</script>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
