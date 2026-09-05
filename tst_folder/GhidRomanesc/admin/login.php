<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

// Redirecționează dacă deja logat
if (Auth::check()) {
    redirect('/admin/');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (Auth::attempt($email, $password)) {
        // Loghează sesiunea
        Database::insert('admin_sessions', [
            'user_id'    => $_SESSION['user_id'],
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300),
        ]);
        redirect('/admin/');
    } else {
        $error = 'Email sau parolă incorecte.';
        // Mică întârziere anti-brute-force
        sleep(1);
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Autentificare Admin — GhidRomânesc</title>
<meta name="robots" content="noindex, nofollow">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f0f2f5;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
  .card{background:#fff;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.1);width:100%;max-width:400px;padding:2.5rem}
  .logo{font-size:1.5rem;font-weight:800;color:#1d3557;margin-bottom:.3rem}
  .logo span{color:#2b6cb0}
  .subtitle{color:#64748b;font-size:.9rem;margin-bottom:2rem}
  label{display:block;font-weight:600;font-size:.875rem;color:#374151;margin-bottom:.4rem}
  input{width:100%;padding:.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.95rem;margin-bottom:1.1rem;font-family:inherit;transition:border-color .2s;color:#1e293b}
  input:focus{outline:none;border-color:#2b6cb0}
  .btn{width:100%;background:#1d3557;color:#fff;border:none;padding:.85rem;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;font-family:inherit;transition:background .2s}
  .btn:hover{background:#2b6cb0}
  .error{background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1.25rem}
  .back{display:block;text-align:center;margin-top:1.25rem;font-size:.875rem;color:#64748b;text-decoration:none}
  .back:hover{color:#1d3557}
</style>
</head>
<body>
<div class="card">
  <div class="logo">Ghid<span>Românesc</span></div>
  <p class="subtitle">Panou de administrare</p>

  <?php if ($error): ?>
  <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus autocomplete="username">

    <label for="password">Parolă</label>
    <input type="password" id="password" name="password" required autocomplete="current-password">

    <button type="submit" class="btn">Autentifică-te</button>
  </form>

  <a href="/" class="back">← Înapoi la site</a>
</div>
</body>
</html>
