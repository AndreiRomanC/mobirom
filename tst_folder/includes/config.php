<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-detect base path — works from root AND from any subdirectory
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$root_dir  = realpath(__DIR__ . '/..');
$doc_root  = realpath($_SERVER['DOCUMENT_ROOT'] ?? '/');

$base_path = '';
if ($root_dir && $doc_root && str_starts_with($root_dir, $doc_root)) {
    $base_path = str_replace('\\', '/', substr($root_dir, strlen($doc_root)));
    $base_path = rtrim($base_path, '/');
}

define('BASE_PATH', $base_path);          // e.g. '' or '/afterschool'
define('BASE_URL',  $protocol . '://' . $host . $base_path);
define('ROOT_DIR',  $root_dir);
define('CONTENT_DIR', $root_dir . '/content');

// ─── Admin credentials (SCHIMBĂ PAROLA DUPĂ INSTALARE!) ───────────────────────
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'claudia2025');   // <-- modifică această parolă!

// ─── Site info ────────────────────────────────────────────────────────────────
define('SITE_NAME',    'Afterschool Claudia Muntean');
define('SITE_TAGLINE', 'Meditații de calitate pentru clasa a V-a');
define('SITE_EMAIL',   'claudia@afterschoolclaudiamuntean.ro');
