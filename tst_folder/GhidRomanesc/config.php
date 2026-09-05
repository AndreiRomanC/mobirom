<?php
/**
 * GhidRomânesc — Configurație principală
 * Editează valorile de mai jos după instalare.
 */

// ─── Baza de date SQLite ───────────────────────────────────────────────────────
define('DB_PATH', __DIR__ . '/data/ghidromanesc.db');

// ─── Site ─────────────────────────────────────────────────────────────────────
define('SITE_NAME', 'GhidRomânesc');
define('SITE_DOMAIN', 'https://ghidromanesc.ro');
define('SITE_TAGLINE', 'Ghiduri simple, explicații utile și soluții practice pentru românii de pretutindeni.');
define('SITE_EMAIL', 'contact@ghidromanesc.ro');
define('ADMIN_EMAIL', 'admin@ghidromanesc.ro');
define('SITE_LANGUAGE', 'ro');

// ─── Chei API ─────────────────────────────────────────────────────────────────
define('ANTHROPIC_API_KEY', '');          // Completează din panoul Anthropic
define('ANTHROPIC_MODEL', 'claude-opus-4-8');

// ─── Securitate ───────────────────────────────────────────────────────────────
define('SECRET_KEY', 'schimba-aceasta-cheie-secreta-' . md5('ghidromanesc'));
define('SESSION_LIFETIME', 7200); // 2 ore

// ─── Paginare ─────────────────────────────────────────────────────────────────
define('ARTICLES_PER_PAGE', 12);
define('ADMIN_PER_PAGE', 20);

// ─── Upload ───────────────────────────────────────────────────────────────────
define('UPLOAD_DIR', __DIR__ . '/assets/uploads/');
define('UPLOAD_URL', '/assets/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// ─── Mediu ────────────────────────────────────────────────────────────────────
define('ENV', 'production'); // 'development' sau 'production'
define('DEBUG', ENV === 'development');

// ─── Timezone ─────────────────────────────────────────────────────────────────
date_default_timezone_set('Europe/Bucharest');

// ─── Autoload simple ──────────────────────────────────────────────────────────
spl_autoload_register(function($class) {
    $file = __DIR__ . '/src/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});

// ─── Sesiune ──────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_start();
}

// ─── Erori ────────────────────────────────────────────────────────────────────
if (DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
