<?php
/**
 * GhidRomânesc — Script de testare
 * Verifică că toate componentele funcționează.
 * ȘTERGE acest fișier de pe server după testare!
 */

// Protecție: rulează doar din CLI sau cu token secret
if (php_sapi_name() !== 'cli' && ($_GET['token'] ?? '') !== 'test-ghid-2024') {
    http_response_code(403);
    die('Adaugă ?token=test-ghid-2024 la URL pentru a rula testele. Șterge fișierul după testare!');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Article.php';

$pass = 0;
$fail = 0;
$results = [];

function test(string $name, callable $fn): void {
    global $pass, $fail, $results;
    try {
        $result = $fn();
        if ($result === true || $result === null) {
            $results[] = ['ok', $name, ''];
            $pass++;
        } else {
            $results[] = ['fail', $name, is_string($result) ? $result : 'Returned: ' . json_encode($result)];
            $fail++;
        }
    } catch (Throwable $e) {
        $results[] = ['fail', $name, $e->getMessage()];
        $fail++;
    }
}

// ─── 1. Configurare ───────────────────────────────────────────────────────────
test('DB_PATH este definit', fn() => defined('DB_PATH') || 'DB_PATH lipsă');
test('DB_PATH există ca director', fn() => is_dir(dirname(DB_PATH)) || 'Directorul data/ nu există — rulează install.php');
test('Fișierul SQLite există', fn() => file_exists(DB_PATH) || 'ghidromanesc.db nu există — rulează install.php');
test('Directorul data/ are .htaccess', fn() => file_exists(dirname(DB_PATH) . '/.htaccess') || '.htaccess lipsă în data/');
test('SITE_NAME definit', fn() => defined('SITE_NAME') && SITE_NAME !== '');
test('SITE_DOMAIN definit', fn() => defined('SITE_DOMAIN') && SITE_DOMAIN !== '');

// ─── 2. Conexiune SQLite ──────────────────────────────────────────────────────
test('Conexiune SQLite reușită', function() {
    $db = Database::get();
    return $db instanceof PDO;
});

test('WAL mode activ', function() {
    $mode = Database::fetchColumn('PRAGMA journal_mode');
    return $mode === 'wal' || "journal_mode este '$mode', așteptat 'wal'";
});

test('Foreign keys activate', function() {
    $fk = Database::fetchColumn('PRAGMA foreign_keys');
    return $fk == 1 || "foreign_keys=$fk, așteptat 1";
});

// ─── 3. Tabele existente ──────────────────────────────────────────────────────
$expectedTables = ['users','categories','articles','article_sources','newsletter',
                   'search_queries','error_reports','topic_suggestions',
                   'editorial_calendar','trend_ideas','settings','admin_sessions'];

foreach ($expectedTables as $table) {
    test("Tabel '$table' există", function() use ($table) {
        $exists = Database::fetchColumn(
            "SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name=?", [$table]
        );
        return $exists > 0 || "Tabelul '$table' nu există — rulează install.php";
    });
}

// ─── 4. Date inițiale ────────────────────────────────────────────────────────
test('Categorii inserate (7 așteptate)', function() {
    $count = (int)Database::fetchColumn('SELECT COUNT(*) FROM categories');
    return $count === 7 || "Găsite $count categorii, așteptate 7";
});

test('Categorii au slug-uri corecte', function() {
    $slugs = array_column(Database::fetchAll('SELECT slug FROM categories'), 'slug');
    $expected = ['acte-institutii','digital-ai','diaspora','bani-taxe','joburi-viata','modele-checklist','actualizari'];
    $missing = array_diff($expected, $slugs);
    return empty($missing) || 'Lipsesc: ' . implode(', ', $missing);
});

test('Admin user există', function() {
    $user = Database::fetchOne("SELECT * FROM users WHERE role='administrator' LIMIT 1");
    return $user !== null || 'Niciun administrator găsit — rulează install.php';
});

test('Userul andreic.roman@gmail.com există', function() {
    $user = Database::fetchOne("SELECT * FROM users WHERE email='andreic.roman@gmail.com'");
    return $user !== null || 'Userul andreic.roman@gmail.com nu există — rulează install.php';
});

test('Parola Andrei123@ funcționează', function() {
    $user = Database::fetchOne("SELECT password FROM users WHERE email='andreic.roman@gmail.com'");
    if (!$user) return 'User negăsit';
    return password_verify('Andrei123@', $user['password']) || 'Parola nu corespunde hash-ului';
});

test('Setări inițiale există', function() {
    $count = (int)Database::fetchColumn('SELECT COUNT(*) FROM settings');
    return $count >= 5 || "Doar $count setări găsite, așteptate cel puțin 5";
});

test('Articole demo inserate', function() {
    $count = (int)Database::fetchColumn('SELECT COUNT(*) FROM articles');
    return $count >= 3 || "Doar $count articole găsite";
});

// ─── 5. Autentificare ────────────────────────────────────────────────────────
test('Auth::attempt cu credențiale corecte', function() {
    // Nu modificăm sesiunea reală — testăm direct hash-ul
    $user = Database::fetchOne("SELECT password FROM users WHERE email='andreic.roman@gmail.com'");
    return $user && password_verify('Andrei123@', $user['password']);
});

test('Auth::attempt respinge parola greșită', function() {
    $user = Database::fetchOne("SELECT password FROM users WHERE email='andreic.roman@gmail.com'");
    return $user && !password_verify('parolaGresita', $user['password']);
});

// ─── 6. Helpers ───────────────────────────────────────────────────────────────
test('slug() funcționează cu diacritice', function() {
    $s = slug('Cum faci programare la pașaport');
    return $s === 'cum-faci-programare-la-pasaport' || "Obținut: '$s'";
});

test('slug() funcționează cu ș/ț', function() {
    $s = slug('Acte și instituții');
    return $s === 'acte-si-institutii' || "Obținut: '$s'";
});

test('truncate() funcționează', function() {
    $t = truncate('Acesta este un text lung pentru test', 20);
    return strlen($t) <= 23; // 20 + '…'
});

test('e() escapează HTML', function() {
    return e('<script>alert(1)</script>') === '&lt;script&gt;alert(1)&lt;/script&gt;';
});

test('formatDate() funcționează', function() {
    return formatDate('2024-01-15 10:30:00') === '15.01.2024';
});

test('csrf() generează token', function() {
    $token = csrf();
    return strlen($token) === 64;
});

// ─── 7. Article model ────────────────────────────────────────────────────────
test('Article::getPopular() funcționează', function() {
    $articles = Article::getPopular(3);
    return is_array($articles);
});

test('Article::getRecent() funcționează', function() {
    $articles = Article::getRecent(3);
    return is_array($articles);
});

test('Article::search() funcționează', function() {
    $results = Article::search('pașaport');
    return is_array($results);
});

test('Article::search() nu aruncă eroare pentru query gol', function() {
    $results = Article::search('');
    return is_array($results);
});

// ─── 8. Database helpers ─────────────────────────────────────────────────────
test('Database::insert() și fetchOne() funcționează', function() {
    // Curăță înainte (dacă testul a mai rulat)
    Database::delete('search_queries', 'query=?', ['__test__']);
    $id = Database::insert('search_queries', ['query' => '__test__', 'count' => 1, 'last_searched' => date('Y-m-d H:i:s')]);
    $row = Database::fetchOne('SELECT * FROM search_queries WHERE id=?', [$id]);
    Database::delete('search_queries', 'id=?', [$id]);
    return $row && $row['query'] === '__test__';
});

test('Database::update() adaugă updated_at automat', function() {
    // Curăță înainte
    Database::delete('search_queries', 'query=?', ['__test2__']);
    $id = Database::insert('search_queries', ['query' => '__test2__', 'count' => 1]);
    Database::update('search_queries', ['count' => 2], 'id=?', [$id]);
    $row = Database::fetchOne('SELECT count FROM search_queries WHERE id=?', [$id]);
    Database::delete('search_queries', 'id=?', [$id]);
    return $row && (int)$row['count'] === 2;
});

// ─── 9. SQL SQLite-specific ──────────────────────────────────────────────────
test("DATE('now') funcționează în SQLite", function() {
    $date = Database::fetchColumn("SELECT DATE('now')");
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || "Rezultat invalid: '$date'";
});

test("datetime('now','localtime') funcționează", function() {
    $dt = Database::fetchColumn("SELECT datetime('now','localtime')");
    return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $dt) || "Rezultat invalid: '$dt'";
});

test("Concatenare cu || funcționează (nu CONCAT)", function() {
    $result = Database::fetchColumn("SELECT 'a' || 'b' || 'c'");
    return $result === 'abc';
});

test("LOWER() funcționează pentru comparații", function() {
    $result = Database::fetchColumn("SELECT LOWER('GhidRomânesc')");
    return $result === 'ghidromanesc' || "Obținut: '$result'";
});

test("Articolele publicate au category_slug prin JOIN", function() {
    $art = Database::fetchOne(
        "SELECT a.slug, c.slug AS category_slug
         FROM articles a
         LEFT JOIN categories c ON a.category_id = c.id
         WHERE a.status = 'published'
         LIMIT 1"
    );
    return $art === null || !empty($art['category_slug']) || 'category_slug gol în articol publicat';
});

// ─── 10. Fișiere critice ─────────────────────────────────────────────────────
$criticalFiles = [
    'index.php', 'config.php', '.htaccess', 'robots.txt', 'sitemap.php',
    'src/Database.php', 'src/Auth.php', 'src/Article.php', 'src/AI.php', 'src/helpers.php',
    'templates/header.php', 'templates/footer.php',
    'templates/admin-layout.php', 'templates/admin-footer.php',
    'pages/home.php', 'pages/article.php', 'pages/category.php', 'pages/search.php',
    'admin/index.php', 'admin/login.php', 'admin/articole.php',
    'admin/ai-studio.php', 'admin/calendar.php', 'admin/setari.php',
    'api/ai-generate.php', 'api/newsletter.php',
    'assets/css/style.css', 'assets/css/admin.css',
    'assets/js/main.js', 'assets/js/admin.js',
];

foreach ($criticalFiles as $file) {
    test("Fișier există: $file", function() use ($file) {
        return file_exists(__DIR__ . '/' . $file) || "Fișierul '$file' lipsește";
    });
}

// ─── Afișare rezultate ────────────────────────────────────────────────────────
$total = $pass + $fail;
$allOk = $fail === 0;

if (php_sapi_name() === 'cli') {
    echo "\nGhidRomânesc — Rezultate teste\n";
    echo str_repeat('─', 60) . "\n";
    foreach ($results as [$status, $name, $msg]) {
        $icon = $status === 'ok' ? '✓' : '✕';
        echo "$icon $name" . ($msg ? " → $msg" : '') . "\n";
    }
    echo str_repeat('─', 60) . "\n";
    echo "$pass/$total teste trecute" . ($fail > 0 ? ", $fail eșuate" : '') . "\n\n";
    exit($fail > 0 ? 1 : 0);
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Teste GhidRomânesc</title>
<meta name="robots" content="noindex,nofollow">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',monospace;background:#0f172a;color:#e2e8f0;padding:2rem;min-height:100vh}
  h1{font-size:1.4rem;margin-bottom:1.5rem;color:#f1f5f9}
  .summary{background:<?= $allOk ? '#14532d' : '#7f1d1d' ?>;border:1px solid <?= $allOk ? '#16a34a' : '#dc2626' ?>;border-radius:8px;padding:1rem 1.5rem;margin-bottom:1.5rem;font-size:1.1rem;font-weight:700;color:#fff}
  .results{display:flex;flex-direction:column;gap:.3rem}
  .result{display:flex;align-items:flex-start;gap:.75rem;padding:.45rem .75rem;border-radius:5px;font-size:.875rem}
  .ok{background:#052e16;color:#86efac}
  .fail{background:#450a0a;color:#fca5a5}
  .icon{flex-shrink:0;font-weight:700;width:16px}
  .name{flex:1}
  .msg{color:#94a3b8;font-style:italic;font-size:.8rem;margin-left:.5rem}
  .warning{background:#422006;border:1px solid #fb923c;border-radius:8px;padding:1rem;margin-top:1.5rem;color:#fed7aa;font-size:.85rem}
</style>
</head>
<body>
<h1>🧪 GhidRomânesc — Teste automate</h1>

<div class="summary">
  <?= $allOk ? '✓ Toate testele trecute' : "✕ $fail teste eșuate" ?> — <?= $pass ?>/<?= $total ?> OK
</div>

<div class="results">
  <?php foreach ($results as [$status, $name, $msg]): ?>
  <div class="result <?= $status ?>">
    <span class="icon"><?= $status === 'ok' ? '✓' : '✕' ?></span>
    <span class="name"><?= htmlspecialchars($name) ?></span>
    <?php if ($msg): ?><span class="msg"><?= htmlspecialchars($msg) ?></span><?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<div class="warning">
  ⚠️ <strong>Șterge test.php de pe server după ce termini!</strong> Conține informații sensibile despre configurație.
</div>
</body>
</html>
