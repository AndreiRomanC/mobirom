<?php
$pageTitle = 'Contact';
$metaDescription = 'Contactează echipa GhidRomânesc pentru întrebări, sugestii sau raportare erori.';
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['name'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (!$name || !$email || !$message) {
        $error = 'Completează toate câmpurile obligatorii.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresa de email nu este validă.';
    } else {
        // Trimite email (dacă mail() este configurat)
        $to      = ADMIN_EMAIL;
        $subj    = '[GhidRomânesc Contact] ' . $subject;
        $body    = "Nume: $name\nEmail: $email\n\n$message";
        $headers = "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'ghidromanesc.ro') . "\r\nReply-To: $email";
        @mail($to, $subj, $body, $headers);
        $success = true;
    }
}

require __DIR__ . '/../templates/header.php';
?>
<div class="page-header"><div class="container">
  <h1 class="page-title">Contact</h1>
  <p class="page-subtitle">Scrie-ne dacă ai întrebări, sugestii sau o eroare de raportat</p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
  <?php if ($success): ?>
  <div class="alert alert-success">✓ Mesajul tău a fost trimis. Vom reveni cu un răspuns în cel mai scurt timp posibil.</div>
  <?php else: ?>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="POST">
    <div class="form-group"><label>Numele tău *</label><input type="text" name="name" class="form-control" value="<?= e($_POST['name'] ?? '') ?>" required></div>
    <div class="form-group"><label>Email *</label><input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required></div>
    <div class="form-group"><label>Subiect</label><input type="text" name="subject" class="form-control" value="<?= e($_POST['subject'] ?? '') ?>"></div>
    <div class="form-group"><label>Mesaj *</label><textarea name="message" class="form-control" rows="5" required><?= e($_POST['message'] ?? '') ?></textarea></div>
    <button type="submit" class="btn btn-primary">Trimite mesajul</button>
  </form>
  <div style="margin-top:2rem;padding:1.25rem;background:var(--gray-50);border-radius:var(--radius);font-size:.875rem">
    <p>📧 <strong>Email direct:</strong> <?= e(SITE_EMAIL) ?></p>
    <p style="margin-top:.5rem">Poți și <a href="/raporteaza-eroare/">raporta o eroare</a> sau <a href="/sugereaza-subiect/">sugera un subiect nou</a>.</p>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>
