<?php
require_once __DIR__ . '/../includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['user']     ?? '';
    $p = $_POST['password'] ?? '';
    if ($u === ADMIN_USER && $p === ADMIN_PASS) {
        $_SESSION['acm_admin'] = true;
        header('Location: ' . BASE_PATH . '/admin/index.php');
        exit;
    }
    $error = 'Date de autentificare incorecte.';
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
  <style>
    body { background: #f1f3ff; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .login-box {
      background: #fff; border-radius: 24px; padding: 2.5rem 2rem;
      width: 100%; max-width: 380px;
      box-shadow: 0 12px 52px rgba(61,82,213,.14);
      border: 1px solid rgba(61,82,213,.08);
    }
    .login-logo { text-align:center; margin-bottom:1.75rem; }
    .login-logo .icon {
      width:52px; height:52px; border-radius:14px;
      background: linear-gradient(135deg,#3d52d5,#6376e8);
      display:inline-flex; align-items:center; justify-content:center;
      font-size:1rem; font-weight:800; color:#fff; margin-bottom:.75rem;
    }
    .login-logo h1 { font-size:1.1rem; color:#1a1d35; margin-bottom:.2rem; }
    .login-logo p  { font-size:.8rem; color:#8890a6; }
  </style>
</head>
<body>
  <div class="login-box">
    <div class="login-logo">
      <div class="icon">CM</div>
      <h1>Panou de administrare</h1>
      <p>Afterschool Claudia Muntean</p>
    </div>

    <?php if ($error): ?>
      <div class="admin-alert err" style="margin-bottom:1.25rem"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="admin-form-group">
        <label class="admin-label">Utilizator</label>
        <input type="text" name="user" class="admin-input" autocomplete="username" required>
      </div>
      <div class="admin-form-group">
        <label class="admin-label">Parolă</label>
        <input type="password" name="password" class="admin-input" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn-primary admin-btn" style="width:100%;justify-content:center">Autentifică-te</button>
    </form>
    <p style="text-align:center;margin-top:1.25rem">
      <a href="<?= BASE_PATH ?>/index.php" style="font-size:.82rem;color:#8890a6">← Înapoi la site</a>
    </p>
  </div>
</body>
</html>
