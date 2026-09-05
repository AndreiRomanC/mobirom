<?php
/**
 * GhidRomânesc — Script de instalare (SQLite)
 * Rulează o singură dată. ȘTERGE acest fișier după instalare!
 */

if (file_exists(__DIR__ . '/.installed')) {
    die('<h2 style="font-family:sans-serif;color:#c53030;padding:2rem">Site-ul este deja instalat. Șterge fișierul .installed pentru a reinstala.</h2>');
}

// ─── Creare tabele SQLite ─────────────────────────────────────────────────────
function createSchema(PDO $pdo): void {
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'autor',
            avatar TEXT, bio TEXT, is_active INTEGER NOT NULL DEFAULT 1,
            last_login TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
        )",
        "CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            description TEXT, icon TEXT, color TEXT,
            sort_order INTEGER DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
        )",
        "CREATE TABLE IF NOT EXISTS articles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL,
            author_id INTEGER, reviewer_id INTEGER,
            title TEXT NOT NULL, slug TEXT NOT NULL,
            excerpt TEXT, content TEXT,
            article_type TEXT DEFAULT 'ghid_complet',
            meta_title TEXT, meta_description TEXT,
            og_image TEXT, focus_keyword TEXT, tags TEXT,
            status TEXT NOT NULL DEFAULT 'draft',
            risk_level TEXT NOT NULL DEFAULT 'galben',
            published_at TEXT, scheduled_at TEXT,
            verified_by INTEGER, verified_at TEXT,
            review_date TEXT, last_checked_at TEXT,
            needs_disclaimer INTEGER DEFAULT 0,
            sources TEXT, internal_links TEXT,
            ai_generated INTEGER DEFAULT 0,
            ai_model TEXT, ai_prompt_used TEXT, ai_check_result TEXT,
            views INTEGER DEFAULT 0, shares INTEGER DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
            FOREIGN KEY (category_id) REFERENCES categories(id),
            FOREIGN KEY (author_id) REFERENCES users(id)
        )",
        "CREATE INDEX IF NOT EXISTS idx_articles_status ON articles(status)",
        "CREATE INDEX IF NOT EXISTS idx_articles_category ON articles(category_id)",
        "CREATE INDEX IF NOT EXISTS idx_articles_slug ON articles(slug)",
        "CREATE INDEX IF NOT EXISTS idx_articles_published_at ON articles(published_at)",
        "CREATE INDEX IF NOT EXISTS idx_articles_views ON articles(views)",
        "CREATE INDEX IF NOT EXISTS idx_articles_review_date ON articles(review_date)",
        "CREATE TABLE IF NOT EXISTS article_sources (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            article_id INTEGER NOT NULL,
            url TEXT, title TEXT, institution TEXT,
            accessed_at TEXT, trust_level TEXT DEFAULT 'oficial', notes TEXT,
            FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS newsletter (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE, name TEXT,
            is_active INTEGER DEFAULT 1,
            confirm_token TEXT, confirmed_at TEXT, unsubscribed_at TEXT,
            ip TEXT, created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
        )",
        "CREATE TABLE IF NOT EXISTS search_queries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            query TEXT NOT NULL UNIQUE,
            count INTEGER DEFAULT 1,
            last_searched TEXT DEFAULT (datetime('now','localtime'))
        )",
        "CREATE TABLE IF NOT EXISTS error_reports (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            article_id INTEGER, article_url TEXT,
            description TEXT NOT NULL, email TEXT,
            status TEXT DEFAULT 'new', ip TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
            FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS topic_suggestions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            subject TEXT NOT NULL, description TEXT, email TEXT,
            status TEXT DEFAULT 'new', ip TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
        )",
        "CREATE TABLE IF NOT EXISTS editorial_calendar (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL, category_id INTEGER, article_id INTEGER,
            assigned_to INTEGER, status TEXT DEFAULT 'idee',
            target_date TEXT, notes TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
            FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS trend_ideas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL, category TEXT, keyword TEXT,
            user_intent TEXT, article_type TEXT,
            risk_level TEXT DEFAULT 'galben',
            seo_difficulty TEXT DEFAULT 'medie',
            why_search TEXT, sources TEXT,
            recommendation TEXT DEFAULT 'pending',
            status TEXT DEFAULT 'idee',
            generated_at TEXT DEFAULT (datetime('now','localtime'))
        )",
        "CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key_name TEXT NOT NULL UNIQUE, value TEXT,
            updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
        )",
        "CREATE TABLE IF NOT EXISTS admin_sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL, ip TEXT, user_agent TEXT,
            logged_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
    ];

    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email   = trim($_POST['admin_email'] ?? '');
    $name    = trim($_POST['admin_name']  ?? 'Administrator');
    $pass    = $_POST['admin_pass'] ?? '';
    $apikey  = trim($_POST['api_key']     ?? '');
    $domain  = trim($_POST['site_domain'] ?? 'https://ghidromanesc.ro');

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email admin invalid.';
    if (strlen($pass) < 8) $errors[] = 'Parola trebuie să aibă minim 8 caractere.';

    if (empty($errors)) {
        try {
            // Creează directorul data/
            $dataDir = __DIR__ . '/data';
            if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);

            // Creează .htaccess pentru a proteja fișierul SQLite
            file_put_contents($dataDir . '/.htaccess', "Order Deny,Allow\nDeny from all\n");

            // Conectare SQLite și creare schema
            $dbPath = $dataDir . '/ghidromanesc.db';
            $pdo = new PDO('sqlite:' . $dbPath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->exec('PRAGMA journal_mode=WAL');
            $pdo->exec('PRAGMA foreign_keys=ON');
            $pdo->exec('PRAGMA encoding="UTF-8"');

            // Creează tabelele direct (mai fiabil decât parsarea SQL din fișier)
            createSchema($pdo);

            // Admin user (din formular)
            $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
            $pdo->prepare('INSERT INTO users (name, email, password, role, is_active, created_at, updated_at) VALUES (?, ?, ?, "administrator", 1, datetime("now","localtime"), datetime("now","localtime"))')
                ->execute([$name, $email, $hash]);

            // Admin default (dacă emailul din formular e diferit)
            $defaultEmail = 'andreic.roman@gmail.com';
            $defaultPass  = 'Andrei123@';
            if ($email !== $defaultEmail) {
                $defaultHash = password_hash($defaultPass, PASSWORD_BCRYPT, ['cost' => 12]);
                $pdo->prepare('INSERT OR IGNORE INTO users (name, email, password, role, is_active, created_at, updated_at) VALUES (?, ?, ?, "administrator", 1, datetime("now","localtime"), datetime("now","localtime"))')
                    ->execute(['Andrei', $defaultEmail, $defaultHash]);
            }

            // Categorii
            $categories = [
                ['acte-institutii',  'Acte și instituții',     'Ghiduri pentru pașaport, buletin, permis, cazier, primărie și ghișeul.ro.', 1],
                ['digital-ai',       'Digital și AI',          'ChatGPT, aplicații utile, scanare documente, PDF, emailuri, formulare online.', 2],
                ['diaspora',         'Diaspora',               'Consulate, acte românești în străinătate, programări, muncă, revenire.', 3],
                ['bani-taxe',        'Bani și taxe',           'Explicații despre ANAF, SPV, salariu, taxe locale, PFA și SRL.', 4],
                ['joburi-viata',     'Joburi și viață practică','CV, email profesional, interviu, contract de muncă explicat simplu.', 5],
                ['modele-checklist', 'Modele și checklist-uri','Modele emailuri, cereri, liste acte, checklist-uri pentru programări.', 6],
                ['actualizari',      'Actualizări importante', 'Schimbări care afectează practic oamenii: ce s-a schimbat și ce faci.', 7],
            ];
            $stmtCat = $pdo->prepare('INSERT INTO categories (slug, name, description, sort_order, created_at) VALUES (?, ?, ?, ?, datetime("now","localtime"))');
            foreach ($categories as $c) $stmtCat->execute($c);

            // Încarcă prompturile implicite din AI.php
            require_once __DIR__ . '/src/AI.php';
            $defaultPromptArticol = AI::getDefaultArticlePrompt();
            $defaultPromptIdei    = AI::getDefaultIdeasPrompt();

            // Setări
            $settings = [
                ['site_name',         'GhidRomânesc'],
                ['site_tagline',      'Ghiduri simple, explicații utile și soluții practice pentru românii de pretutindeni.'],
                ['site_domain',       $domain],
                ['admin_email',       $email],
                ['anthropic_api_key', $apikey],
                ['articles_per_page', '12'],
                ['enable_newsletter', '1'],
                ['newsletter_text',   'Primește ghiduri utile pe email. Fără spam, poți anula oricând.'],
                ['prompt_articol',    $defaultPromptArticol],
                ['prompt_idei',       $defaultPromptIdei],
            ];
            $stmtSet = $pdo->prepare('INSERT INTO settings (key_name, value, updated_at) VALUES (?, ?, datetime("now","localtime"))');
            foreach ($settings as $s) $stmtSet->execute($s);

            // Articole demo
            include __DIR__ . '/install/seed_articles.php';
            seedArticles($pdo);

            // Actualizează config.php
            $cfg = file_get_contents(__DIR__ . '/config.php');
            $cfg = str_replace(
                "define('SITE_DOMAIN', 'https://ghidromanesc.ro');",
                "define('SITE_DOMAIN', '$domain');",
                $cfg
            );
            if ($apikey) {
                $cfg = str_replace(
                    "define('ANTHROPIC_API_KEY', '');",
                    "define('ANTHROPIC_API_KEY', '" . addslashes($apikey) . "');",
                    $cfg
                );
            }
            file_put_contents(__DIR__ . '/config.php', $cfg);

            // Marchează instalarea
            file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));
            $success = true;

        } catch (Exception $e) {
            $errors[] = 'Eroare: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Instalare GhidRomânesc</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f0f4f8;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
  .card{background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.1);max-width:500px;width:100%;padding:2.5rem}
  h1{color:#1d3557;font-size:1.8rem;margin-bottom:.35rem}
  .subtitle{color:#64748b;margin-bottom:2rem;font-size:.9rem}
  .logo{font-size:1.5rem;font-weight:800;color:#1d3557;margin-bottom:1.25rem}
  .logo span{color:#2b6cb0}
  .section-title{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:1.5rem 0 .75rem;padding-bottom:.4rem;border-bottom:1px solid #e2e8f0}
  label{display:block;font-weight:600;color:#374151;margin-bottom:.35rem;font-size:.875rem}
  input{width:100%;padding:.7rem 1rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#1e293b;margin-bottom:1rem;transition:border-color .2s;font-family:inherit}
  input:focus{outline:none;border-color:#2b6cb0}
  .hint{font-size:.78rem;color:#94a3b8;margin-top:-.75rem;margin-bottom:.85rem}
  .btn{background:#1d3557;color:#fff;border:none;padding:.85rem 2rem;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;width:100%;margin-top:.5rem;transition:background .2s;font-family:inherit}
  .btn:hover{background:#2b6cb0}
  .error-box{background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:1rem;margin-bottom:1.5rem}
  .error-box p{color:#dc2626;font-size:.875rem;margin:.2rem 0}
  .success-box{background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:2rem;text-align:center}
  .success-box h2{color:#166534;font-size:1.4rem;margin-bottom:.75rem}
  .success-box p{color:#15803d;margin:.4rem 0;font-size:.9rem}
  .success-box .actions{display:flex;gap:.5rem;justify-content:center;margin-top:1.25rem}
  .success-box a{display:inline-block;background:#166534;color:#fff;padding:.7rem 1.5rem;border-radius:8px;text-decoration:none;font-weight:600;font-size:.9rem}
  .success-box a.secondary{background:#e2e8f0;color:#374151}
  .info-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:1rem;margin-bottom:1.5rem;font-size:.85rem;color:#1e40af}
</style>
</head>
<body>
<div class="card">
  <div class="logo">Ghid<span>Românesc</span></div>
  <h1>Instalare inițială</h1>
  <p class="subtitle">Completează datele de mai jos — durează 30 de secunde.</p>

  <?php if ($success): ?>
  <div class="success-box">
    <h2>✓ Instalare reușită!</h2>
    <p>Baza de date SQLite a fost creată.</p>
    <p>6 articole demo au fost adăugate.</p>
    <p><strong>Important:</strong> Șterge <code>install.php</code> pentru securitate.</p>
    <div class="actions">
      <a href="/">Mergi la site</a>
      <a href="/admin/" class="secondary">Admin panel</a>
    </div>
  </div>

  <?php else: ?>

  <?php if (!empty($errors)): ?>
  <div class="error-box">
    <?php foreach ($errors as $err): ?>
    <p>✕ <?= htmlspecialchars($err) ?></p>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="info-box">
    💡 <strong>Fără MySQL!</strong> GhidRomânesc folosește SQLite — baza de date este un singur fișier pe server. Nu trebuie să configurezi nimic în cPanel.
  </div>

  <form method="POST">
    <div class="section-title">Cont Administrator</div>
    <label>Nume</label>
    <input type="text" name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? '') ?>" placeholder="Administrator">

    <label>Email *</label>
    <input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>" required>

    <label>Parolă (minim 8 caractere) *</label>
    <input type="password" name="admin_pass" required>

    <div class="section-title">Configurare site</div>
    <label>Domeniu</label>
    <input type="url" name="site_domain" value="<?= htmlspecialchars($_POST['site_domain'] ?? 'https://ghidromanesc.ro') ?>">

    <label>Cheie API Anthropic (pentru AI Studio)</label>
    <input type="text" name="api_key" value="<?= htmlspecialchars($_POST['api_key'] ?? '') ?>" placeholder="sk-ant-...">
    <p class="hint">Opțional — o poți adăuga mai târziu din Admin → Setări.</p>

    <button type="submit" class="btn">Instalează →</button>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
