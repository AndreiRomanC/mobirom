<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$sections = [
    'Pagina principală (Acasă)' => [
        'home_features'    => 'De ce noi — funcționalități',
        'home_subjects'    => 'Materii — Matematică & Română',
        'home_about'       => 'Despre echipă',
        'home_testimonials'=> 'Testimoniale',
        'home_cta'         => 'Îndemn la acțiune (CTA)',
    ],
    'Program & Tarife' => [
        'program_prices' => 'Tarife — pachete disponibile',
        'program_enroll' => 'Pași de înscriere',
    ],
    'Despre' => [
        'about_method' => 'Metoda de predare',
    ],
    'Servicii' => [
        'services_how' => 'Structura unei ședințe',
        'services_cta' => 'Îndemn la acțiune (CTA)',
    ],
];

$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = load_content('visibility');
    foreach ($sections as $group) {
        foreach ($group as $key => $label) {
            $current[$key] = isset($_POST[$key]);
        }
    }
    save_content('visibility', $current);
    $saved = true;
}

$vis = load_content('visibility');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vizibilitate secțiuni — Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
  <style>
    .vis-group { margin-bottom: 2rem; }
    .vis-group-title { font-weight: 700; font-size: 1rem; color: var(--text-1); margin-bottom: .75rem; padding-bottom: .5rem; border-bottom: 1px solid var(--border); }
    .vis-row { display: flex; align-items: center; gap: .75rem; padding: .6rem .5rem; border-radius: 8px; transition: background .15s; }
    .vis-row:hover { background: var(--bg); }
    .vis-row label { cursor: pointer; font-size: .9rem; color: var(--text-2); flex: 1; }
    .vis-row input[type=checkbox] { width: 1.1rem; height: 1.1rem; accent-color: var(--primary); cursor: pointer; flex-shrink: 0; }
    .vis-row.disabled label { color: var(--text-3); text-decoration: line-through; }
    .vis-save-bar { position: sticky; bottom: 1.5rem; display: flex; justify-content: flex-end; padding-top: 1rem; }
  </style>
</head>
<body>
<div class="admin-wrap">
  <header class="admin-header">
    <span class="admin-logo">⚙️ &nbsp;Admin — <?= SITE_NAME ?></span>
    <div style="display:flex;gap:1rem;align-items:center">
      <a href="<?= BASE_PATH ?>/admin/index.php" style="font-size:.85rem;color:var(--text-3)">← Înapoi</a>
      <a href="<?= BASE_PATH ?>/admin/logout.php" class="btn btn-outline btn-sm">Deconectare</a>
    </div>
  </header>

  <div class="admin-content">
    <h1 style="margin-bottom:.3rem">Vizibilitate secțiuni</h1>
    <p style="margin-bottom:2rem;color:var(--text-3)">Debifează o secțiune pentru a o ascunde de pe site, fără a pierde conținutul.</p>

    <?php if ($saved): ?>
    <div class="admin-alert success" style="margin-bottom:1.5rem">Modificările au fost salvate.</div>
    <?php endif; ?>

    <form method="POST">
      <?php foreach ($sections as $group_name => $items): ?>
      <div class="admin-card vis-group">
        <div class="vis-group-title"><?= e($group_name) ?></div>
        <?php foreach ($items as $key => $label):
          $checked = $vis[$key] ?? true;
        ?>
        <div class="vis-row <?= $checked ? '' : 'disabled' ?>">
          <input type="checkbox" id="<?= e($key) ?>" name="<?= e($key) ?>" <?= $checked ? 'checked' : '' ?>>
          <label for="<?= e($key) ?>"><?= e($label) ?></label>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>

      <div class="vis-save-bar">
        <button type="submit" class="btn btn-primary btn-lg">Salvează modificările</button>
      </div>
    </form>
  </div>
</div>
<script>
document.querySelectorAll('.vis-row input').forEach(cb => {
  cb.addEventListener('change', () => {
    cb.closest('.vis-row').classList.toggle('disabled', !cb.checked);
  });
});
</script>
</body>
</html>
