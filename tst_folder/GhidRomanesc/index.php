<?php
/**
 * GhidRomânesc — Router principal
 * Gestionează toate URL-urile publice ale site-ului.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Article.php';

$path = currentPath();

// Log vizită cu detectare bot
if (!str_starts_with($path, '/admin') && !str_starts_with($path, '/api')) {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '')[0]);

    // Scoring bot detection
    $score = 0; $botReason = [];
    $uaLow = strtolower($ua);
    $knownBots = ['googlebot','bingbot','slurp','duckduckbot','baiduspider','yandexbot','semrushbot','ahrefsbot','mj12bot','dotbot','rogerbot','ia_archiver','facebot','applebot','petalbot','gptbot','claudebot','bytespider'];
    foreach ($knownBots as $b) { if (str_contains($uaLow,$b)) { $score=-100; $botReason[]="known:$b"; break; } }
    if ($score > -100) {
        $botKw = ['bot','crawler','spider','scraper','wget','curl','python','java/','go-http','ruby','axios','libwww','nutch','httrack','archive.org_bot','dataforseo','semrush','ahrefs'];
        foreach ($botKw as $k) { if (str_contains($uaLow,$k)) { $score-=60; $botReason[]="kw:$k"; break; } }
        if (strlen($ua)<30)  { $score-=40; $botReason[]='short_ua'; }
        if (empty($ua))      { $score-=80; $botReason[]='no_ua'; }
        if (str_contains($uaLow,'mozilla/5.0')) $score+=30;
        if (preg_match('/(chrome|firefox|safari|edge|opr)\/[\d.]+/i',$ua)) $score+=30;
        if (preg_match('/(android|iphone|ipad|mobile)/i',$ua)) $score+=15;
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $score+=15;
        if (!empty($_SERVER['HTTP_ACCEPT'])) $score+=10;
    }
    $isBot = $score < 0;

    Database::query(
        'INSERT INTO visits (ip,page,referer,user_agent,is_bot,bot_score,bot_reason) VALUES (?,?,?,?,?,?,?)',
        [$ip,$path,$_SERVER['HTTP_REFERER']??null,$ua,$isBot?1:0,$score,implode(',',$botReason)?:null]
    );
}

// Elimină trailing slash (normalizare, dar nu redirecționa inutile)
$path = rtrim($path, '/') ?: '/';

// ─── Rute statice ─────────────────────────────────────────────
$staticRoutes = [
    '/'                              => 'pages/home.php',
    '/tooluri'                       => 'pages/tooluri.php',
    '/cauta'                         => 'pages/search.php',
    '/despre'                        => 'pages/about.php',
    '/contact'                       => 'pages/contact.php',
    '/raporteaza-eroare'             => 'pages/report.php',
    '/sugereaza-subiect'             => 'pages/suggest.php',
    '/politica-editoriala'           => ['pages/legal.php', ['legalPage' => 'politica-editoriala']],
    '/surse-verificare'              => ['pages/legal.php', ['legalPage' => 'surse-verificare']],
    '/limitarea-responsabilitatii'   => ['pages/legal.php', ['legalPage' => 'limitarea-responsabilitatii']],
    '/confidentialitate'             => ['pages/legal.php', ['legalPage' => 'confidentialitate']],
    '/cookies'                       => ['pages/legal.php', ['legalPage' => 'cookies']],
];

// Verifică rute statice (cu și fără trailing slash)
foreach ($staticRoutes as $route => $handler) {
    if ($path === $route || $path === $route . '/') {
        if (is_array($handler)) {
            [$file, $vars] = $handler;
            foreach ($vars as $k => $v) $$k = $v;
            require __DIR__ . '/' . $file;
        } else {
            require __DIR__ . '/' . $handler;
        }
        exit;
    }
}

// ─── Rute dinamice ────────────────────────────────────────────

// Categorii valide
$validCategories = [
    'acte-institutii',
    'digital-ai',
    'diaspora',
    'bani-taxe',
    'joburi-viata',
    'modele-checklist',
    'actualizari',
];

// Tooluri individuale
if (preg_match('#^/tooluri/([a-z0-9-]+)/?$#', $path, $m)) {
    $toolSlug = $m[1];
    require __DIR__ . '/pages/tool.php';
    exit;
}

// Pattern: /categorie/
if (preg_match('#^/([a-z0-9-]+)/?$#', $path, $m)) {
    $slug = $m[1];
    if (in_array($slug, $validCategories)) {
        $categorySlug = $slug;
        require __DIR__ . '/pages/category.php';
        exit;
    }
    // Poate fi o categorie din DB
    $catCheck = Database::fetchOne('SELECT slug FROM categories WHERE slug=?', [$slug]);
    if ($catCheck) {
        $categorySlug = $slug;
        require __DIR__ . '/pages/category.php';
        exit;
    }
}

// Pattern: /categorie/slug-articol/
if (preg_match('#^/([a-z0-9-]+)/([a-z0-9-]+)/?$#', $path, $m)) {
    $categorySlug = $m[1];
    $articleSlug  = $m[2];
    require __DIR__ . '/pages/article.php';
    exit;
}

// Newsletter confirm
if (preg_match('#^/newsletter/confirm/?#', $path)) {
    $token = sanitize($_GET['token'] ?? '');
    if ($token) {
        $sub = Database::fetchOne('SELECT id FROM newsletter WHERE confirm_token=?', [$token]);
        if ($sub) {
            Database::update('newsletter', ['confirmed_at' => date('Y-m-d H:i:s'), 'confirm_token' => null], 'id=?', [$sub['id']]);
            $pageTitle = 'Abonare confirmată';
            require __DIR__ . '/templates/header.php';
            echo '<div class="container" style="padding:4rem 0;text-align:center"><h1>✓ Abonare confirmată</h1><p style="margin:1rem 0">Ești abonat la newsletter-ul GhidRomânesc.</p><a href="/" class="btn btn-primary">Înapoi la ghiduri</a></div>';
            require __DIR__ . '/templates/footer.php';
        } else {
            http_response_code(400);
            $pageTitle = 'Token invalid';
            require __DIR__ . '/templates/header.php';
            echo '<div class="container" style="padding:4rem 0;text-align:center"><h2>Token invalid sau expirat.</h2><a href="/" class="btn btn-primary" style="margin-top:1rem">Acasă</a></div>';
            require __DIR__ . '/templates/footer.php';
        }
    }
    exit;
}

// Newsletter unsubscribe
if (preg_match('#^/newsletter/dezabonare/?#', $path)) {
    $token = sanitize($_GET['token'] ?? '');
    if ($token) {
        Database::update('newsletter', ['is_active' => 0, 'unsubscribed_at' => date('Y-m-d H:i:s')], 'confirm_token=? OR email=?', [$token, $token]);
    }
    $pageTitle = 'Dezabonat';
    require __DIR__ . '/templates/header.php';
    echo '<div class="container" style="padding:4rem 0;text-align:center"><h2>Ai fost dezabonat.</h2><p>Nu vei mai primi emailuri de la GhidRomânesc.</p><a href="/" class="btn btn-primary" style="margin-top:1rem">Acasă</a></div>';
    require __DIR__ . '/templates/footer.php';
    exit;
}

// ─── 404 ──────────────────────────────────────────────────────
http_response_code(404);
$pageTitle = 'Pagina nu a fost găsită — GhidRomânesc';
require __DIR__ . '/templates/header.php';
?>
<div class="container" style="padding:5rem 0;text-align:center">
  <div style="font-size:4rem;font-weight:900;color:#e2e8f0;line-height:1">404</div>
  <h1 style="font-size:1.75rem;font-weight:800;color:var(--blue-dark);margin:.75rem 0 .5rem">Pagina nu a fost găsită</h1>
  <p style="color:var(--text-muted);margin-bottom:2rem">Ghidul pe care îl cauți nu există sau a fost mutat.</p>
  <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
    <a href="/" class="btn btn-primary">Acasă</a>
    <a href="/cauta/" class="btn btn-secondary">Caută ghiduri</a>
  </div>
</div>
<?php require __DIR__ . '/templates/footer.php'; ?>
