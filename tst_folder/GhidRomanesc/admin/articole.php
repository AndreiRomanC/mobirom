<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Article.php';
Auth::requireAdmin();

$actiune = sanitize($_GET['actiune'] ?? '');
$id      = (int)($_GET['id'] ?? 0);
$status  = sanitize($_GET['status'] ?? '');

// ─── Salvare / creare ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title'            => sanitize($_POST['title'] ?? ''),
        'slug'             => slug($_POST['slug'] ?? $_POST['title'] ?? ''),
        'category_id'      => (int)($_POST['category_id'] ?? 0),
        'article_type'     => sanitize($_POST['article_type'] ?? 'ghid_complet'),
        'excerpt'          => sanitize($_POST['excerpt'] ?? ''),
        'content'          => $_POST['content'] ?? '',  // HTML allowed
        'meta_title'       => sanitize($_POST['meta_title'] ?? ''),
        'meta_description' => sanitize($_POST['meta_description'] ?? ''),
        'focus_keyword'    => sanitize($_POST['focus_keyword'] ?? ''),
        'tags'             => sanitize($_POST['tags'] ?? ''),
        'risk_level'       => sanitize($_POST['risk_level'] ?? 'galben'),
        'needs_disclaimer' => isset($_POST['needs_disclaimer']) ? 1 : 0,
        'review_date'      => $_POST['review_date'] ?? null,
        'author_id'        => Auth::user()['id'],
    ];

    $newStatus = sanitize($_POST['save_action'] ?? 'draft');

    // Validare
    $errors = [];
    if (!$data['title']) $errors[] = 'Titlul este obligatoriu.';
    if (!$data['category_id']) $errors[] = 'Selectează o categorie.';

    // Verificare auto-publish — admins pot publica orice
    $isAdmin = in_array($_SESSION['user_role'] ?? '', ['administrator', 'editor']);
    if ($newStatus === 'published' && $data['risk_level'] !== 'verde' && !$isAdmin) {
        $errors[] = 'Articolele galbene și roșii nu se pot publica automat. Alege "Trimite spre verificare".';
    }

    if (empty($errors)) {
        $articleId = null;
        try {
            $data['status'] = match($newStatus) {
                'published' => 'published',
                'pending'   => 'pending',
                'scheduled' => 'scheduled',
                'blocked'   => 'blocked',
                default     => 'draft',
            };
            // Setează published_at la prima publicare (articol nou SAU articol care nu era publicat)
            if ($data['status'] === 'published') {
                $prevStatus = $id ? (Database::fetchColumn('SELECT status FROM articles WHERE id=?', [$id]) ?: '') : '';
                if (!$id || $prevStatus !== 'published') {
                    $data['published_at'] = date('Y-m-d H:i:s');
                }
            }
            if ($data['status'] === 'scheduled') {
                $data['scheduled_at'] = sanitize($_POST['scheduled_at'] ?? '');
            }

            if ($id) {
                Database::update('articles', $data, 'id = ?', [$id]);
                $articleId = $id;
                $successMsg = 'Articolul a fost actualizat.';
            } else {
                $articleId = Database::insert('articles', $data);
                $successMsg = 'Articolul a fost creat.';
            }

            // Surse
            if (!empty($_POST['sources'])) {
                Database::delete('article_sources', 'article_id = ?', [$articleId]);
                foreach ($_POST['sources'] as $src) {
                    if (empty($src['url']) && empty($src['title'])) continue;
                    Database::insert('article_sources', [
                        'article_id'  => $articleId,
                        'url'         => sanitize($src['url'] ?? ''),
                        'title'       => sanitize($src['title'] ?? ''),
                        'institution' => sanitize($src['institution'] ?? ''),
                        'accessed_at' => $src['accessed_at'] ?? null,
                        'trust_level' => sanitize($src['trust_level'] ?? 'oficial'),
                    ]);
                }
            }

            redirect('/admin/articole/?actiune=editeaza&id=' . $articleId . '&ok=' . urlencode($successMsg));

        } catch (\Throwable $e) {
            // Log eroare pentru diagnosticare
            $logMsg = date('Y-m-d H:i:s') . ' | articole.php | ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() . "\n";
            @file_put_contents(__DIR__ . '/../data/error.log', $logMsg, FILE_APPEND);

            if ($articleId) {
                // Articolul a fost creat/salvat, redirecționăm la el cu avertizare
                redirect('/admin/articole/?actiune=editeaza&id=' . $articleId . '&eroare=' . urlencode('Articolul a fost salvat, dar a apărut o eroare secundară: ' . $e->getMessage()));
            } else {
                $errors[] = 'Eroare la salvare: ' . $e->getMessage();
            }
        }
    }
}

// ─── Ștergere ───────────────────────────────────────────────
if ($actiune === 'sterge' && $id) {
    Auth::requireRole('administrator', 'editor');
    Database::delete('articles', 'id = ?', [$id]);
    redirect('/admin/articole/?deleted=1');
}

// ─── Depublicare ────────────────────────────────────────────
if ($actiune === 'depublica' && $id) {
    Auth::requireRole('administrator', 'editor');
    Database::update('articles', ['status' => 'draft'], 'id=?', [$id]);
    redirect('/admin/articole/?ok=' . urlencode('Articolul a fost mutat în draft.'));
}

// ─── Publicare rapidă ───────────────────────────────────────
if ($actiune === 'publica' && $id) {
    $art = Database::fetchOne('SELECT * FROM articles WHERE id=?', [$id]);
    $isAdmin = Auth::can('publish') || in_array($_SESSION['user_role'] ?? '', ['administrator', 'editor']);
    if ($art && ($art['risk_level'] === 'verde' || $isAdmin)) {
        Database::update('articles', ['status' => 'published', 'published_at' => date('Y-m-d H:i:s')], 'id=?', [$id]);
        redirect('/admin/articole/?ok=' . urlencode('Articolul a fost publicat.'));
    }
    redirect('/admin/articole/?eroare=' . urlencode('Nu ai permisiunea să publici acest articol.'));
}

// ─── Formular creare / editare ──────────────────────────────
if ($actiune === 'nou' || $actiune === 'editeaza') {
    $article  = $id ? Database::fetchOne('SELECT * FROM articles WHERE id=?', [$id]) : [];
    $sources  = $id ? Database::fetchAll('SELECT * FROM article_sources WHERE article_id=?', [$id]) : [];
    $categories = Database::fetchAll('SELECT * FROM categories ORDER BY name');
    $pageTitle = $actiune === 'nou' ? 'Articol nou' : 'Editează articol';
    require __DIR__ . '/../templates/admin-layout.php';
    ?>

  <?php if (isset($_GET['ok'])): ?><div class="admin-notice success">✓ <?= e($_GET['ok']) ?></div><?php endif; ?>
  <?php if (isset($_GET['eroare'])): ?><div class="admin-notice error">⚠️ <?= e($_GET['eroare']) ?></div><?php endif; ?>
  <?php if (!empty($errors)): ?><div class="admin-notice error"><ul style="margin:0;padding-left:1.2rem"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <form method="POST" id="article-form">
    <div class="admin-form-grid">

      <!-- ── Main ── -->
      <div class="admin-form-main" style="display:flex;flex-direction:column;gap:1rem">

        <div class="admin-card">
          <div class="admin-card-body">
            <div class="form-group" style="margin-bottom:.75rem">
              <label>Titlu articol *</label>
              <input type="text" id="article-title" name="title" class="form-control" value="<?= e($article['title'] ?? '') ?>" required style="font-size:1.1rem;font-weight:600">
            </div>
            <div class="form-group" style="margin-bottom:.75rem">
              <label>Slug URL</label>
              <div style="display:flex;gap:.5rem;align-items:center">
                <span style="color:var(--text-muted);font-size:.875rem">/categorie/</span>
                <input type="text" id="slug" name="slug" class="form-control" value="<?= e($article['slug'] ?? '') ?>" style="flex:1">
                <span style="color:var(--text-muted);font-size:.875rem">/</span>
              </div>
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label>Rezumat (excerpt)</label>
              <textarea name="excerpt" class="form-control" rows="2"><?= e($article['excerpt'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <div class="admin-card">
          <div class="admin-card-header">
            <span class="admin-card-title">Conținut articol</span>
            <div style="display:flex;gap:.5rem">
              <button type="button" id="btn-check-risk" class="btn btn-sm btn-secondary">🔍 Verifică riscul</button>
              <button type="button" id="btn-generate-seo" class="btn btn-sm btn-secondary">✨ SEO automat</button>
            </div>
          </div>
          <div class="admin-card-body" style="padding-bottom:.5rem">
            <textarea id="article-content" name="content" class="form-control" rows="20" style="font-family:'Courier New',monospace;font-size:.875rem"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
            <p style="font-size:.78rem;color:var(--text-muted);margin-top:.4rem">Poți folosi HTML pentru formatare (h2, h3, ul, ol, strong, a, div class="box-scurt" etc.)</p>
          </div>
          <div id="risk-output" style="padding:.75rem 1.25rem;font-size:.875rem"></div>
        </div>

        <!-- Surse -->
        <div class="admin-card">
          <div class="admin-card-header">
            <span class="admin-card-title">Surse oficiale</span>
            <button type="button" class="btn btn-sm btn-secondary" onclick="addSource()">+ Adaugă sursă</button>
          </div>
          <div class="admin-card-body" id="sources-container">
            <?php
            $srcList = !empty($sources) ? $sources : [['url'=>'','title'=>'','institution'=>'','accessed_at'=>'','trust_level'=>'oficial']];
            foreach ($srcList as $i => $src): ?>
            <div class="source-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 120px auto;gap:.5rem;margin-bottom:.5rem;align-items:end">
              <div><label style="font-size:.78rem">URL sursă</label><input type="url" name="sources[<?=$i?>][url]" class="form-control" value="<?= e($src['url'] ?? '') ?>" placeholder="https://"></div>
              <div><label style="font-size:.78rem">Titlu / Descriere</label><input type="text" name="sources[<?=$i?>][title]" class="form-control" value="<?= e($src['title'] ?? '') ?>"></div>
              <div><label style="font-size:.78rem">Instituție</label><input type="text" name="sources[<?=$i?>][institution]" class="form-control" value="<?= e($src['institution'] ?? '') ?>"></div>
              <div><label style="font-size:.78rem">Data accesării</label><input type="date" name="sources[<?=$i?>][accessed_at]" class="form-control" value="<?= e($src['accessed_at'] ?? '') ?>"></div>
              <div style="padding-bottom:.1rem"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.source-row').remove()">✕</button></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

      <!-- ── Sidebar ── -->
      <div class="admin-form-sidebar">

        <!-- Publicare -->
        <div class="admin-card">
          <div class="admin-card-header"><span class="admin-card-title">Publicare</span></div>
          <div class="admin-card-body">
            <div class="form-group">
              <label>Status curent</label>
              <?= statusBadge($article['status'] ?? 'draft') ?>
            </div>
            <div style="display:flex;flex-direction:column;gap:.5rem;margin-top:.75rem">
              <button type="submit" name="save_action" value="draft" class="btn btn-secondary">Salvează draft</button>
              <button type="submit" name="save_action" value="pending" class="btn btn-secondary" style="border-color:#b45309;color:#b45309">Trimite spre verificare</button>
              <?php
              $canPublish = ($article['risk_level'] ?? 'galben') === 'verde'
                         || in_array($_SESSION['user_role'] ?? '', ['administrator', 'editor']);
              ?>
              <button type="submit" name="save_action" value="published"
                class="btn btn-success<?= $canPublish ? '' : ' disabled-look' ?>"
                <?= $canPublish ? '' : 'disabled title="Doar articolele verzi se pot publica direct (sau schimbă rolul)"' ?>>
                ✓ Publică acum<?= (!$canPublish) ? ' (doar verde)' : '' ?>
              </button>
              <button type="submit" name="save_action" value="scheduled" class="btn btn-secondary">📅 Programează</button>
            </div>
            <?php if ($id && ($article['status'] ?? '') === 'published'): ?>
            <a href="/<?= e($article['category_slug'] ?? '') ?>/<?= e($article['slug'] ?? '') ?>/" target="_blank" class="btn btn-sm btn-secondary" style="margin-top:.5rem;width:100%;text-align:center">↗ Vedere articol</a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Categorie & tip -->
        <div class="admin-card">
          <div class="admin-card-header"><span class="admin-card-title">Categorie & tip</span></div>
          <div class="admin-card-body">
            <div class="form-group">
              <label>Categorie *</label>
              <select name="category_id" class="form-control" required>
                <option value="">— Selectează —</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($article['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Tip articol</label>
              <select name="article_type" class="form-control">
                <?php
                $types = ['ghid_complet'=>'Ghid complet','ghid_rapid'=>'Ghid rapid','checklist'=>'Checklist','faq'=>'FAQ','model_email'=>'Model email','tutorial'=>'Tutorial','actualizare'=>'Actualizare','explicatie'=>'Explicație','lista_aplicatii'=>'Listă aplicații','ghid_diaspora'=>'Ghid diaspora'];
                foreach ($types as $k => $v):
                ?>
                <option value="<?=$k?>" <?= ($article['article_type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- Risc -->
        <div class="admin-card">
          <div class="admin-card-header"><span class="admin-card-title">Nivel de risc editorial</span></div>
          <div class="admin-card-body">
            <div class="risk-selector">
              <?php foreach (['verde','galben','rosu'] as $r): ?>
              <label class="risk-option <?= $r ?><?= ($article['risk_level'] ?? 'galben') === $r ? ' selected' : '' ?>">
                <input type="radio" name="risk_level" value="<?=$r?>" <?= ($article['risk_level'] ?? 'galben') === $r ? 'checked' : '' ?>>
                <?= ucfirst($r) ?>
              </label>
              <?php endforeach; ?>
            </div>
            <p style="font-size:.78rem;color:var(--text-muted);margin-top:.75rem">Verde = publicare automată OK<br>Galben = necesită verificare<br>Roșu = blocat, verificare specială</p>
            <div class="form-group" style="margin-top:.75rem">
              <label>
                <input type="checkbox" name="needs_disclaimer" <?= !empty($article['needs_disclaimer']) ? 'checked' : '' ?>>
                Adaugă disclaimer automat
              </label>
            </div>
          </div>
        </div>

        <!-- SEO -->
        <div class="admin-card">
          <div class="admin-card-header"><span class="admin-card-title">SEO</span></div>
          <div class="admin-card-body">
            <div class="form-group">
              <label>Meta title <span id="meta_title_count" style="font-weight:400;color:var(--text-muted)"></span></label>
              <input type="text" id="meta_title" name="meta_title" class="form-control" value="<?= e($article['meta_title'] ?? '') ?>" maxlength="80">
            </div>
            <div class="form-group">
              <label>Meta description <span id="meta_description_count" style="font-weight:400;color:var(--text-muted)"></span></label>
              <textarea id="meta_description" name="meta_description" class="form-control" rows="3"><?= e($article['meta_description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
              <label>Cuvânt-cheie principal</label>
              <input type="text" id="focus_keyword" name="focus_keyword" class="form-control" value="<?= e($article['focus_keyword'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Taguri (separate prin virgulă)</label>
              <input type="text" name="tags" class="form-control" value="<?= e($article['tags'] ?? '') ?>">
            </div>
          </div>
        </div>

        <!-- Data reverificare -->
        <div class="admin-card">
          <div class="admin-card-header"><span class="admin-card-title">Verificare</span></div>
          <div class="admin-card-body">
            <div class="form-group">
              <label>Data reverificare recomandată</label>
              <input type="date" name="review_date" class="form-control" value="<?= e($article['review_date'] ?? '') ?>">
              <p class="form-hint">Verde: 6-12 luni | Galben: 3-6 luni | Roșu: 1-3 luni</p>
            </div>
            <?php if ($id): ?>
            <a href="/admin/articole/?actiune=sterge&id=<?=$id?>" class="btn btn-sm btn-danger btn-block" data-confirm="Ești sigur că vrei să ștergi acest articol? Acțiunea este ireversibilă.">🗑 Șterge articolul</a>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </form>

  <script>
  let srcIndex = <?= count($srcList) ?>;
  function addSource() {
    const c = document.getElementById('sources-container');
    const d = document.createElement('div');
    d.className = 'source-row';
    d.style = 'display:grid;grid-template-columns:2fr 1fr 1fr 120px auto;gap:.5rem;margin-bottom:.5rem;align-items:end';
    d.innerHTML = `
      <div><label style="font-size:.78rem">URL sursă</label><input type="url" name="sources[${srcIndex}][url]" class="form-control" placeholder="https://"></div>
      <div><label style="font-size:.78rem">Titlu</label><input type="text" name="sources[${srcIndex}][title]" class="form-control"></div>
      <div><label style="font-size:.78rem">Instituție</label><input type="text" name="sources[${srcIndex}][institution]" class="form-control"></div>
      <div><label style="font-size:.78rem">Data accesării</label><input type="date" name="sources[${srcIndex}][accessed_at]" class="form-control"></div>
      <div style="padding-bottom:.1rem"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.source-row').remove()">✕</button></div>`;
    c.appendChild(d);
    srcIndex++;
  }
  </script>

    <?php
    require __DIR__ . '/../templates/admin-footer.php';
    exit;
}

// ─── Listă articole ─────────────────────────────────────────
$filterStatus = sanitize($_GET['status'] ?? '');
$filterCat    = sanitize($_GET['categorie'] ?? '');
$filterRisk   = sanitize($_GET['risc'] ?? '');
$search       = sanitize($_GET['cauta'] ?? '');
$page         = max(1, (int)($_GET['p'] ?? 1));

$where   = ['1=1'];
$params  = [];
if ($filterStatus) { $where[] = 'a.status = ?';      $params[] = $filterStatus; }
if ($filterCat)    { $where[] = 'c.slug = ?';         $params[] = $filterCat; }
if ($filterRisk)   { $where[] = 'a.risk_level = ?';   $params[] = $filterRisk; }
if ($search)       { $where[] = 'a.title LIKE ?';     $params[] = '%'.$search.'%'; }

$whereStr  = implode(' AND ', $where);
$total     = (int)Database::fetchColumn("SELECT COUNT(*) FROM articles a LEFT JOIN categories c ON a.category_id=c.id WHERE $whereStr", $params);
$totalPages= ceil($total / ADMIN_PER_PAGE);
$offset    = ($page-1) * ADMIN_PER_PAGE;

$articles = Database::fetchAll(
    "SELECT a.*, c.name AS category_name, c.slug AS cat_slug, u.name AS author_name
     FROM articles a
     LEFT JOIN categories c ON a.category_id=c.id
     LEFT JOIN users u ON a.author_id=u.id
     WHERE $whereStr
     ORDER BY a.updated_at DESC
     LIMIT " . ADMIN_PER_PAGE . " OFFSET $offset",
    $params
);
$categories = Database::fetchAll('SELECT * FROM categories ORDER BY name');

$pageTitle = 'Articole';
require __DIR__ . '/../templates/admin-layout.php';
?>

<?php if (isset($_GET['ok'])): ?><div class="admin-notice success">✓ <?= e($_GET['ok']) ?></div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="admin-notice success">✓ Articolul a fost șters.</div><?php endif; ?>
<?php if (isset($_GET['eroare'])): ?><div class="admin-notice error">⚠️ <?= e($_GET['eroare']) ?></div><?php endif; ?>

<!-- Toolbar -->
<div class="admin-toolbar">
  <div class="admin-toolbar-left">
    <form style="display:flex;gap:.5rem;flex-wrap:wrap">
      <input type="search" name="cauta" value="<?= e($search) ?>" placeholder="Caută după titlu..." class="form-control" style="width:220px">
      <select name="status" class="form-control" style="width:160px">
        <option value="">Toate statusurile</option>
        <?php foreach (['published','draft','pending','scheduled','blocked','needs_update'] as $s): ?>
        <option value="<?=$s?>" <?= $filterStatus===$s?'selected':'' ?>><?= statusLabel($s) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="categorie" class="form-control" style="width:180px">
        <option value="">Toate categoriile</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= e($cat['slug']) ?>" <?= $filterCat===$cat['slug']?'selected':'' ?>><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="risc" class="form-control" style="width:130px">
        <option value="">Orice risc</option>
        <option value="verde"  <?= $filterRisk==='verde'?'selected':'' ?>>Verde</option>
        <option value="galben" <?= $filterRisk==='galben'?'selected':'' ?>>Galben</option>
        <option value="rosu"   <?= $filterRisk==='rosu'?'selected':'' ?>>Roșu</option>
      </select>
      <button type="submit" class="btn btn-secondary">Filtrează</button>
    </form>
  </div>
  <div class="admin-toolbar-right">
    <a href="/admin/articole/?actiune=nou" class="btn btn-primary">+ Articol nou</a>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <span class="admin-card-title"><?= $total ?> articole</span>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Titlu</th>
          <th>Categorie</th>
          <th>Tip</th>
          <th>Risc</th>
          <th>Status</th>
          <th>Vizual.</th>
          <th>Reverif.</th>
          <th>Acțiuni</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($articles as $art): ?>
        <tr>
          <td class="col-title">
            <a href="/admin/articole/?actiune=editeaza&id=<?= $art['id'] ?>"><?= e(truncate($art['title'], 60)) ?></a>
            <?php if ($art['ai_generated']): ?><span style="font-size:.7rem;color:#9ca3af;margin-left:.3rem">AI</span><?php endif; ?>
          </td>
          <td style="font-size:.8rem"><?= e($art['category_name'] ?? '—') ?></td>
          <td style="font-size:.78rem;color:var(--text-muted)"><?= str_replace('_',' ', $art['article_type'] ?? '') ?></td>
          <td><?= riskBadge($art['risk_level']) ?></td>
          <td><?= statusBadge($art['status']) ?></td>
          <td style="font-size:.875rem"><?= number_format($art['views']) ?></td>
          <td style="font-size:.8rem;color:<?= ($art['review_date'] && $art['review_date'] <= date('Y-m-d')) ? '#dc2626' : 'var(--text-muted)' ?>">
            <?= $art['review_date'] ? formatDate($art['review_date']) : '—' ?>
          </td>
          <td>
            <div style="display:flex;gap:.3rem">
              <a href="/admin/articole/?actiune=editeaza&id=<?= $art['id'] ?>" class="btn btn-sm btn-secondary">✏️</a>
              <?php if ($art['status'] === 'published'): ?>
              <a href="/<?= e($art['cat_slug'] ?? '') ?>/<?= e($art['slug']) ?>/" target="_blank" class="btn btn-sm btn-secondary">↗</a>
              <?php endif; ?>
              <?php if ($art['status'] !== 'published' && in_array($_SESSION['user_role']??'', ['administrator','editor'])): ?>
              <a href="/admin/articole/?actiune=publica&id=<?= $art['id'] ?>" class="btn btn-sm btn-success" title="Publică">✓ Publică</a>
              <?php endif; ?>
              <?php if ($art['status'] === 'published' && in_array($_SESSION['user_role']??'', ['administrator','editor'])): ?>
              <a href="/admin/articole/?actiune=depublica&id=<?= $art['id'] ?>" class="btn btn-sm btn-secondary" title="Mută în draft" onclick="return confirm('Depublici articolul?')">⏸</a>
              <?php endif; ?>
              <a href="/admin/articole/?actiune=sterge&id=<?= $art['id'] ?>" class="btn btn-sm btn-danger" data-confirm="Ștergi articolul «<?= htmlspecialchars(addslashes($art['title'])) ?>»?">🗑</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($articles)): ?>
        <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted)">Niciun articol găsit.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Paginare -->
<?php if ($totalPages > 1): ?>
<nav class="pagination" style="margin-top:1.25rem">
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
  <a href="?p=<?=$i?>&status=<?= urlencode($filterStatus) ?>&categorie=<?= urlencode($filterCat) ?>&cauta=<?= urlencode($search) ?>"
     class="page-link<?= $i===$page?' active':'' ?>"><?= $i ?></a>
  <?php endfor; ?>
</nav>
<?php endif; ?>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
