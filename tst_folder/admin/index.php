<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$flash = '';

// Handle email notification update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'email') {
    $contact = load_content('contact');
    $new_email = trim($_POST['notifications_email'] ?? '');
    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $contact['notifications_email'] = $new_email;
        save_content('contact', $contact);
        $flash = 'email_saved';
    } else {
        $flash = 'email_err';
    }
}

// Handle visibility update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'visibility') {
    $sections = [
        'home_features','home_subjects','home_about','home_testimonials','home_cta',
        'program_prices','program_enroll','about_method','services_how','services_cta',
    ];
    $vis = load_content('visibility');
    foreach ($sections as $key) {
        $vis[$key] = isset($_POST[$key]);
    }
    save_content('visibility', $vis);
    $flash = 'vis_saved';
}

$contact = load_content('contact');
$vis     = load_content('visibility');

$notif_email = $contact['notifications_email'] ?? $contact['email'] ?? SITE_EMAIL;

$sections_grouped = [
    'Acasă' => [
        'home_features'     => 'De ce noi',
        'home_subjects'     => 'Materii',
        'home_about'        => 'Despre echipă',
        'home_testimonials' => 'Testimoniale',
        'home_cta'          => 'Banner CTA',
    ],
    'Program & Tarife' => [
        'program_prices' => 'Tarife',
        'program_enroll' => 'Pași înscriere',
    ],
    'Despre' => [
        'about_method' => 'Metoda de predare',
    ],
    'Servicii' => [
        'services_how' => 'Structura orei',
        'services_cta' => 'Banner CTA',
    ],
];

// Count unread messages
$sub_file = CONTENT_DIR . '/submissions.json';
$sub_count = 0;
if (file_exists($sub_file)) {
    $subs = json_decode(file_get_contents($sub_file), true) ?: [];
    $sub_count = count($subs);
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
  <style>
    .dash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 2rem; }
    @media (max-width: 700px) { .dash-grid { grid-template-columns: 1fr; } }
    .dash-card { background: #fff; border: 1px solid var(--border); border-radius: var(--r-lg); padding: 1.5rem; }
    .dash-card-title { font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--text-3); margin-bottom: 1rem; }
    .email-row { display: flex; gap: .6rem; }
    .email-row input { flex: 1; }
    .vis-group-lbl { font-size: .72rem; font-weight: 700; color: var(--text-3); text-transform: uppercase; letter-spacing: .06em; margin: .9rem 0 .4rem; }
    .vis-group-lbl:first-child { margin-top: 0; }
    .vis-row { display: flex; align-items: center; gap: .6rem; padding: .3rem 0; }
    .vis-row label { font-size: .88rem; color: var(--text-2); cursor: pointer; flex: 1; }
    .vis-row input[type=checkbox] { width: 1rem; height: 1rem; accent-color: var(--primary); cursor: pointer; flex-shrink: 0; }
    .vis-row.off label { color: var(--text-3); text-decoration: line-through; }
    .flash { padding: .6rem 1rem; border-radius: var(--r-sm); font-size: .85rem; margin-bottom: 1.25rem; }
    .flash.ok  { background: #e8f5e9; color: #2e7d32; }
    .flash.err { background: #ffebee; color: #c62828; }
    .badge-count { display:inline-flex;align-items:center;justify-content:center; background:var(--accent);color:#fff; font-size:.65rem;font-weight:700; border-radius:99px;padding:.1rem .45rem;margin-left:.4rem;vertical-align:middle; }
  </style>
</head>
<body>
<div class="admin-wrap">
  <header class="admin-header">
    <span class="admin-logo">⚙️ &nbsp;Admin — <?= SITE_NAME ?></span>
    <div style="display:flex;gap:1rem;align-items:center">
      <a href="<?= BASE_PATH ?>/index.php" style="font-size:.85rem;color:var(--text-3)">← Vezi site-ul</a>
      <a href="<?= BASE_PATH ?>/admin/logout.php" class="btn btn-outline btn-sm">Deconectare</a>
    </div>
  </header>

  <div class="admin-content">
    <h1 style="margin-bottom:.4rem">Bun venit!</h1>
    <p style="margin-bottom:1.75rem;color:var(--text-3)">Gestionează conținutul și setările site-ului.</p>

    <?php if ($flash === 'email_saved'): ?>
      <div class="flash ok">✓ &nbsp;Adresa de email a fost actualizată.</div>
    <?php elseif ($flash === 'email_err'): ?>
      <div class="flash err">Adresa introdusă nu este validă.</div>
    <?php elseif ($flash === 'vis_saved'): ?>
      <div class="flash ok">✓ &nbsp;Vizibilitatea secțiunilor a fost salvată.</div>
    <?php endif; ?>

    <!-- Quick settings row -->
    <div class="dash-grid">

      <!-- Email notificări -->
      <div class="dash-card">
        <div class="dash-card-title">📬 &nbsp;Email notificări formular</div>
        <p style="font-size:.83rem;color:var(--text-3);margin-bottom:.9rem">
          Mesajele trimise prin formularul de contact ajung la această adresă.
        </p>
        <form method="POST">
          <input type="hidden" name="_action" value="email">
          <div class="email-row">
            <input type="email" name="notifications_email"
                   class="admin-input" style="margin:0"
                   value="<?= e($notif_email) ?>" required>
            <button type="submit" class="btn btn-primary btn-sm">Salvează</button>
          </div>
        </form>
      </div>

      <!-- Vizibilitate secțiuni -->
      <div class="dash-card">
        <div class="dash-card-title">👁 &nbsp;Secțiuni vizibile pe site</div>
        <form method="POST" id="vis-form">
          <input type="hidden" name="_action" value="visibility">
          <?php foreach ($sections_grouped as $group_name => $items): ?>
            <div class="vis-group-lbl"><?= e($group_name) ?></div>
            <?php foreach ($items as $key => $label):
              $on = $vis[$key] ?? true;
            ?>
            <div class="vis-row <?= $on ? '' : 'off' ?>">
              <input type="checkbox" id="v_<?= $key ?>" name="<?= $key ?>" <?= $on ? 'checked' : '' ?>>
              <label for="v_<?= $key ?>"><?= e($label) ?></label>
            </div>
            <?php endforeach; ?>
          <?php endforeach; ?>
          <div style="margin-top:1rem">
            <button type="submit" class="btn btn-primary btn-sm" style="width:100%;justify-content:center">
              Salvează vizibilitatea
            </button>
          </div>
        </form>
      </div>

    </div>

    <!-- Page editors -->
    <p style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);margin-bottom:.85rem">Editare conținut pagini</p>
    <div class="admin-nav">
      <a href="<?= BASE_PATH ?>/admin/edit.php?page=home" class="admin-nav-link">
        <span class="icon">🏠</span>
        <span class="title">Pagina principală</span>
        <span class="sub">Hero, funcționalități, testimoniale</span>
      </a>
      <a href="<?= BASE_PATH ?>/admin/edit.php?page=about" class="admin-nav-link">
        <span class="icon">👥</span>
        <span class="title">Despre</span>
        <span class="sub">Bio echipă, metodă, statistici</span>
      </a>
      <a href="<?= BASE_PATH ?>/admin/edit.php?page=services" class="admin-nav-link">
        <span class="icon">📚</span>
        <span class="title">Servicii</span>
        <span class="sub">Materii, teme, structura orei</span>
      </a>
      <a href="<?= BASE_PATH ?>/admin/edit.php?page=program" class="admin-nav-link">
        <span class="icon">📅</span>
        <span class="title">Program & Tarife</span>
        <span class="sub">Orar, preț, pași înscriere</span>
      </a>
      <a href="<?= BASE_PATH ?>/admin/edit.php?page=contact" class="admin-nav-link">
        <span class="icon">✉️</span>
        <span class="title">Contact</span>
        <span class="sub">Adresă, telefon, hartă</span>
      </a>
      <a href="<?= BASE_PATH ?>/admin/edit.php?page=footer" class="admin-nav-link">
        <span class="icon">🔻</span>
        <span class="title">Footer</span>
        <span class="sub">Descriere brand, titluri coloane</span>
      </a>
      <a href="<?= BASE_PATH ?>/admin/submissions.php" class="admin-nav-link">
        <span class="icon">📨</span>
        <span class="title">
          Mesaje primite
          <?php if ($sub_count > 0): ?>
            <span class="badge-count"><?= $sub_count ?></span>
          <?php endif; ?>
        </span>
        <span class="sub">Formulare completate de vizitatori</span>
      </a>
    </div>
  </div>
</div>
<script>
document.querySelectorAll('#vis-form input[type=checkbox]').forEach(cb => {
  cb.addEventListener('change', () => {
    cb.closest('.vis-row').classList.toggle('off', !cb.checked);
  });
});
</script>
</body>
</html>
