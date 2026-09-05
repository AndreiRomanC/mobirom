<?php
$pageTitle = 'Sugerează un subiect';
$metaDescription = 'Ai o întrebare la care nu ai găsit răspuns? Sugerează un subiect și îl vom transforma în ghid.';
$success = false;
$prefillSubject = sanitize($_GET['subiect'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize($_POST['subject'] ?? '');
    $desc    = sanitize($_POST['description'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    if (!$subject) {
        $error = 'Completează subiectul.';
    } else {
        Database::insert('topic_suggestions', [
            'subject'     => $subject,
            'description' => $desc ?: null,
            'email'       => $email ?: null,
            'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        $success = true;
    }
}
require __DIR__ . '/../templates/header.php';
?>
<div class="page-header"><div class="container">
  <h1 class="page-title">Sugerează un subiect</h1>
  <p class="page-subtitle">Ai o întrebare la care nu ai găsit răspuns? Spune-ne și creăm un ghid.</p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
<?php if ($success): ?>
<div class="alert alert-success">✓ Mulțumim pentru sugestie! O vom analiza și vom crea un ghid dacă este util pentru mai mulți utilizatori.</div>
<?php else: ?>
<?php if (isset($error)): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<p style="margin-bottom:1.5rem;color:var(--text-muted)">Cel mai bun conținut vine din întrebările reale. Spune-ne ce nu ai reușit să găsești pe site.</p>
<form method="POST">
  <div class="form-group">
    <label>Ce subiect ai dori să explicăm? *</label>
    <input type="text" name="subject" class="form-control" value="<?= e($_POST['subject'] ?? $prefillSubject) ?>" placeholder="ex: Cum prelungesc permisul de conducere în Italia" required>
  </div>
  <div class="form-group">
    <label>Detalii suplimentare (opțional)</label>
    <textarea name="description" class="form-control" rows="3" placeholder="Spune-ne mai mult despre ce ai nevoie..."><?= e($_POST['description'] ?? '') ?></textarea>
  </div>
  <div class="form-group">
    <label>Email (opțional — te anunțăm când publicăm ghidul)</label>
    <input type="email" name="email" class="form-control">
  </div>
  <button type="submit" class="btn btn-primary">Trimite sugestia</button>
</form>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>
