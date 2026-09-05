<?php
$pageTitle = 'Raportează o eroare';
$metaDescription = 'Găsit o informație greșită sau depășită pe GhidRomânesc? Raportează-ne și o vom verifica.';
$success = false;
$articleUrl = sanitize($_GET['url'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $desc    = sanitize($_POST['description'] ?? '');
    $artUrl  = sanitize($_POST['article_url'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    if (!$desc) {
        $error = 'Descrie eroarea găsită.';
    } else {
        // Caută articolul
        $articleId = null;
        if ($artUrl) {
            // Extrage slug din URL
            preg_match('/\/([^\/]+)\/([^\/]+)\/?$/', $artUrl, $m);
            if (isset($m[1], $m[2])) {
                $art = Database::fetchOne('SELECT a.id FROM articles a LEFT JOIN categories c ON a.category_id=c.id WHERE c.slug=? AND a.slug=?', [$m[1], $m[2]]);
                $articleId = $art['id'] ?? null;
            }
        }
        Database::insert('error_reports', [
            'article_id'  => $articleId,
            'article_url' => $artUrl,
            'description' => $desc,
            'email'       => $email ?: null,
            'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        $success = true;
    }
}
require __DIR__ . '/../templates/header.php';
?>
<div class="page-header"><div class="container">
  <h1 class="page-title">Raportează o eroare</h1>
  <p class="page-subtitle">Ne ajuți să menținem informațiile corecte și actualizate</p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
<?php if ($success): ?>
<div class="alert alert-success">✓ Mulțumim! Am primit raportarea ta. Vom verifica informația în cel mai scurt timp posibil.</div>
<?php else: ?>
<?php if (isset($error)): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<p style="margin-bottom:1.5rem;color:var(--text-muted)">Dacă ai găsit o informație greșită, depășită sau neclară, spune-ne. Toate raportările sunt verificate de echipa editorială.</p>
<form method="POST">
  <div class="form-group">
    <label>URL-ul paginii cu eroarea</label>
    <input type="url" name="article_url" class="form-control" value="<?= e($articleUrl) ?>" placeholder="https://ghidromanesc.ro/...">
  </div>
  <div class="form-group">
    <label>Descrie eroarea *</label>
    <textarea name="description" class="form-control" rows="4" required placeholder="Ce informație este greșită sau depășită?"></textarea>
  </div>
  <div class="form-group">
    <label>Emailul tău (opțional — pentru a te anunța când am corectat)</label>
    <input type="email" name="email" class="form-control">
  </div>
  <button type="submit" class="btn btn-primary">Trimite raportarea</button>
</form>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>
