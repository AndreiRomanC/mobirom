<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::requireRole('administrator');

$pageTitle = 'Setări';
$success   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = ['site_name','site_tagline','admin_email','anthropic_api_key','articles_per_page','enable_newsletter','newsletter_text','prompt_articol','prompt_idei'];
    foreach ($keys as $k) {
        $val = sanitize($_POST[$k] ?? '');
        // Prompt-urile nu trebuie sanitizate (pot conține orice text)
        if (in_array($k, ['prompt_articol','prompt_idei'])) {
            $val = $_POST[$k] ?? '';
        }
        $existing = Database::fetchOne('SELECT id FROM settings WHERE key_name=?', [$k]);
        if ($existing) {
            Database::update('settings', ['value' => $val, 'updated_at' => date('Y-m-d H:i:s')], 'key_name=?', [$k]);
        } else {
            Database::insert('settings', ['key_name' => $k, 'value' => $val]);
        }
    }
    // Actualizează config.php dacă cheia API s-a schimbat
    $apiKey = sanitize($_POST['anthropic_api_key'] ?? '');
    if ($apiKey && $apiKey !== ANTHROPIC_API_KEY) {
        $cfg = file_get_contents(__DIR__ . '/../config.php');
        $cfg = preg_replace("/define\('ANTHROPIC_API_KEY',\s*'[^']*'\)/", "define('ANTHROPIC_API_KEY', '" . addslashes($apiKey) . "')", $cfg);
        file_put_contents(__DIR__ . '/../config.php', $cfg);
    }
    $success = true;
}

// Citește setările existente
require_once __DIR__ . '/../src/AI.php';
$settings = [];
foreach (Database::fetchAll('SELECT key_name, value FROM settings') as $s) {
    $settings[$s['key_name']] = $s['value'];
}
$settings['anthropic_api_key'] = $settings['anthropic_api_key'] ?? ANTHROPIC_API_KEY;
// Fallback la prompturile implicite dacă nu sunt salvate
$settings['prompt_articol'] = $settings['prompt_articol'] ?: AI::getDefaultArticlePrompt();
$settings['prompt_idei']    = $settings['prompt_idei']    ?: AI::getDefaultIdeasPrompt();

require __DIR__ . '/../templates/admin-layout.php';
?>

<?php if ($success): ?><div class="admin-notice success">✓ Setările au fost salvate.</div><?php endif; ?>

<form method="POST" style="max-width:760px">

  <div class="admin-card" style="margin-bottom:1.25rem">
    <div class="admin-card-header"><span class="admin-card-title">Setări generale</span></div>
    <div class="admin-card-body" style="display:flex;flex-direction:column;gap:.75rem">
      <div class="form-group" style="margin-bottom:0">
        <label>Numele site-ului</label>
        <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? 'GhidRomânesc') ?>">
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label>Tagline</label>
        <input type="text" name="site_tagline" class="form-control" value="<?= e($settings['site_tagline'] ?? '') ?>">
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label>Email admin</label>
        <input type="email" name="admin_email" class="form-control" value="<?= e($settings['admin_email'] ?? '') ?>">
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label>Articole pe pagină</label>
        <input type="number" name="articles_per_page" class="form-control" value="<?= e($settings['articles_per_page'] ?? '12') ?>" style="width:100px">
      </div>
    </div>
  </div>

  <div class="admin-card" style="margin-bottom:1.25rem">
    <div class="admin-card-header"><span class="admin-card-title">🤖 AI — Cheie API Anthropic</span></div>
    <div class="admin-card-body">
      <div class="form-group" style="margin-bottom:.5rem">
        <label>Cheie API Anthropic (Claude)</label>
        <input type="text" name="anthropic_api_key" class="form-control" value="<?= e($settings['anthropic_api_key'] ?? '') ?>" placeholder="sk-ant-api03-..." autocomplete="off">
        <p class="form-hint">Obține cheia de pe <strong>console.anthropic.com</strong>. Este necesară pentru AI Studio.</p>
      </div>
      <?php if (!empty(ANTHROPIC_API_KEY)): ?>
      <div class="alert alert-success" style="font-size:.85rem">✓ Cheia API este configurată.</div>
      <?php else: ?>
      <div class="alert alert-warning" style="font-size:.85rem">⚠️ Cheia API nu este configurată. AI Studio nu va funcționa.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="admin-card" style="margin-bottom:1.25rem">
    <div class="admin-card-header"><span class="admin-card-title">Newsletter</span></div>
    <div class="admin-card-body">
      <div class="form-group" style="margin-bottom:.75rem">
        <label>
          <input type="checkbox" name="enable_newsletter" value="1" <?= ($settings['enable_newsletter'] ?? '1') === '1' ? 'checked' : '' ?>>
          Activează newsletter-ul
        </label>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label>Text newsletter</label>
        <input type="text" name="newsletter_text" class="form-control" value="<?= e($settings['newsletter_text'] ?? '') ?>">
      </div>
    </div>
  </div>

  <div class="admin-card" style="margin-bottom:1.25rem">
    <div class="admin-card-header">
      <span class="admin-card-title">⚙️ Prompt generare articole (editabil)</span>
    </div>
    <div class="admin-card-body">
      <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem">Lasă gol pentru a folosi promptul implicit din sistem. Variabile: [SUBIECT] [CATEGORIE] [TIP_ARTICOL] [PUBLIC] [RISC] [KEYWORD] [SURSE] [TON] [LUNGIME]</p>
      <textarea name="prompt_articol" class="form-control prompt-editor" rows="12"><?= htmlspecialchars($settings['prompt_articol'] ?? '') ?></textarea>
    </div>
  </div>

  <div class="admin-card" style="margin-bottom:1.5rem">
    <div class="admin-card-header">
      <span class="admin-card-title">⚙️ Prompt generare idei (editabil)</span>
    </div>
    <div class="admin-card-body">
      <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem">Lasă gol pentru a folosi promptul implicit. Variabile: [TRENDURI]</p>
      <textarea name="prompt_idei" class="form-control prompt-editor" rows="10"><?= htmlspecialchars($settings['prompt_idei'] ?? '') ?></textarea>
    </div>
  </div>

  <button type="submit" class="btn btn-primary btn-lg">💾 Salvează toate setările</button>
</form>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
