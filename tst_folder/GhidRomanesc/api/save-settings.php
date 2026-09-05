<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

Auth::requireRole('administrator');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/admin/setari/'); }

$allowedKeys = ['site_name','site_tagline','admin_email','anthropic_api_key','articles_per_page','enable_newsletter','newsletter_text','prompt_articol','prompt_idei'];
foreach ($allowedKeys as $k) {
    if (!isset($_POST[$k])) continue;
    $val = in_array($k, ['prompt_articol','prompt_idei']) ? ($_POST[$k] ?? '') : sanitize($_POST[$k] ?? '');
    $existing = Database::fetchOne('SELECT id FROM settings WHERE key_name=?', [$k]);
    if ($existing) {
        Database::update('settings', ['value' => $val, 'updated_at' => date('Y-m-d H:i:s')], 'key_name=?', [$k]);
    } else {
        Database::insert('settings', ['key_name' => $k, 'value' => $val]);
    }
}

redirect('/admin/setari/?ok=1');
