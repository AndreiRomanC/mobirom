<?php
// Homepage GhidRomânesc
$categories = Database::fetchAll(
    'SELECT c.*, COUNT(a.id) AS article_count
     FROM categories c
     LEFT JOIN articles a ON a.category_id = c.id AND a.status = "published"
     GROUP BY c.id
     ORDER BY c.sort_order, c.name'
);

$popularArticles = Article::getPopular(6);
$recentArticles  = Article::getRecent(6);
$diasporaArticles= Article::getDiaspora(4);
$digitalArticles = Article::getDigital(4);

$pageTitle       = null; // Va folosi titlul default
$metaDescription = SITE_TAGLINE ?? '';

// Schema Sitelinks Searchbox
$schemaJson = json_encode([
  "@context"        => "https://schema.org",
  "@type"           => "WebSite",
  "name"            => "GhidRomânesc",
  "url"             => SITE_DOMAIN,
  "description"     => $metaDescription,
  "inLanguage"      => "ro",
  "potentialAction" => [
    "@type"       => "SearchAction",
    "target"      => [ "@type" => "EntryPoint", "urlTemplate" => SITE_DOMAIN . "/cauta/?q={search_term_string}" ],
    "query-input" => "required name=search_term_string"
  ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

require __DIR__ . '/../templates/header.php';
?>

<!-- ── Hero ─────────────────────────────────────────────── -->
<section class="hero">
  <div class="container">
    <h1 class="hero-title">Ghiduri simple pentru români,<br>explicate pe înțelesul tuturor.</h1>
    <p class="hero-subtitle">Află rapid ce ai de făcut pentru acte, servicii digitale, diaspora, AI, bani și viață practică.</p>

    <form class="hero-search" method="get" action="/cauta/" role="search">
      <input type="search" name="q" class="hero-search-input" placeholder="Ce vrei să afli?" aria-label="Caută ghiduri" autocomplete="off">
      <button type="submit" class="hero-search-btn">Caută</button>
    </form>

    <div class="hero-tags" aria-label="Căutări populare">
      <span class="hero-tag">Pașaport</span>
      <span class="hero-tag">ChatGPT în română</span>
      <span class="hero-tag">Consulat Germania</span>
      <span class="hero-tag">SPV ANAF</span>
      <span class="hero-tag">Model email</span>
      <span class="hero-tag">Buletin de identitate</span>
      <span class="hero-tag">PDF din poză</span>
    </div>
  </div>
</section>

<!-- ── Categorii ─────────────────────────────────────────── -->
<section class="section">
  <div class="container">
    <h2 class="section-title">Ghiduri pe categorii</h2>
    <p class="section-subtitle">Alege categoria care te interesează</p>
    <div class="categories-grid">
      <?php foreach ($categories as $cat): ?>
      <a href="/<?= e($cat['slug']) ?>/" class="category-card" aria-label="Categoria <?= e($cat['name']) ?>">
        <div class="category-icon">
          <?= categoryIcon($cat['slug']) ?>
        </div>
        <span class="category-name"><?= e($cat['name']) ?></span>
        <?php if ($cat['article_count'] > 0): ?>
        <span class="category-count"><?= $cat['article_count'] ?> ghiduri</span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Cele mai căutate ───────────────────────────────────── -->
<?php if (!empty($popularArticles)): ?>
<section class="section section-alt">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Cele mai citite ghiduri</h2>
        <p class="section-subtitle">Ghidurile pe care le-au căutat cei mai mulți români</p>
      </div>
      <a href="/cauta/" class="see-all">Toate ghidurile →</a>
    </div>
    <div class="articles-grid">
      <?php foreach ($popularArticles as $art): ?>
      <?= articleCard($art) ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Ghiduri noi ────────────────────────────────────────── -->
<?php if (!empty($recentArticles)): ?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Ghiduri noi</h2>
        <p class="section-subtitle">Adăugate și actualizate recent</p>
      </div>
    </div>
    <div class="articles-grid">
      <?php foreach ($recentArticles as $art): ?>
      <?= articleCard($art) ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section-alt">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Actualizări importante</h2>
        <p class="section-subtitle">Schimbări practice care te afectează direct și pașii concreți de urmat.</p>
      </div>
      <a href="/actualizari/" class="see-all">Vezi actualizări →</a>
    </div>
    <div class="update-box" style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:18px;padding:1.5rem;">
      <p><strong>Pe GhidRomânesc găsești acum ghiduri dedicate numai actualizărilor care contează.</strong> Nu sunt știri generice, ci informații practice despre ce s-a schimbat și ce trebuie să faci efectiv.</p>
    </div>
  </div>
</section>

<!-- ── Diaspora ───────────────────────────────────────────── -->
<?php if (!empty($diasporaArticles)): ?>
<section class="section section-alt">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">🌍 Pentru românii din diaspora</h2>
        <p class="section-subtitle">Ghiduri pentru acte românești în străinătate, consulate, programări și revenire în țară</p>
      </div>
      <a href="/diaspora/" class="see-all">Toate ghidurile diaspora →</a>
    </div>
    <div class="articles-grid articles-grid-2">
      <?php foreach ($diasporaArticles as $art): ?>
      <?= articleCard($art) ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Digital & AI ───────────────────────────────────────── -->
<?php if (!empty($digitalArticles)): ?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">💻 AI și digital pe înțelesul tuturor</h2>
        <p class="section-subtitle">ChatGPT, aplicații utile, PDF, emailuri, formulare online — explicate simplu</p>
      </div>
      <a href="/digital-ai/" class="see-all">Toate ghidurile digitale →</a>
    </div>
    <div class="articles-grid articles-grid-2">
      <?php foreach ($digitalArticles as $art): ?>
      <?= articleCard($art) ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Trust bar ─────────────────────────────────────────── -->
<section class="section" style="background:var(--gray-50);border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:2rem;text-align:center">
      <div>
        <div style="font-size:2rem;font-weight:800;color:var(--blue-dark)">✓</div>
        <div style="font-weight:700;color:var(--blue-dark);margin-bottom:.3rem">Verificat editorial</div>
        <div style="font-size:.875rem;color:var(--text-muted)">Fiecare ghid este verificat înainte de publicare și are dată de reverificare.</div>
      </div>
      <div>
        <div style="font-size:2rem;font-weight:800;color:var(--blue-dark)">📋</div>
        <div style="font-weight:700;color:var(--blue-dark);margin-bottom:.3rem">Surse oficiale</div>
        <div style="font-size:.875rem;color:var(--text-muted)">Fiecare articol include sursele oficiale (gov.ro, anaf.ro, mae.ro, etc.).</div>
      </div>
      <div>
        <div style="font-size:2rem;font-weight:800;color:var(--blue-dark)">🇷🇴</div>
        <div style="font-weight:700;color:var(--blue-dark);margin-bottom:.3rem">Pentru toți românii</div>
        <div style="font-size:.875rem;color:var(--text-muted)">Ghiduri pentru România și pentru românii din diaspora, în aceeași platformă.</div>
      </div>
      <div>
        <div style="font-size:2rem;font-weight:800;color:var(--blue-dark)">⚡</div>
        <div style="font-weight:700;color:var(--blue-dark);margin-bottom:.3rem">Simplu și rapid</div>
        <div style="font-size:.875rem;color:var(--text-muted)">Fără jargon, fără informații inutile. Răspuns rapid la ce ai nevoie concret.</div>
      </div>
    </div>
  </div>
</section>

<?php
require __DIR__ . '/../templates/footer.php';

// Funcție helper pentru card articol (definită local pt pagina principală)
function articleCard(array $art): string {
    $typeLabels = [
        'ghid_complet'  => 'Ghid complet',
        'ghid_rapid'    => 'Ghid rapid',
        'checklist'     => 'Checklist',
        'faq'           => 'FAQ',
        'model_email'   => 'Model email',
        'tutorial'      => 'Tutorial',
        'actualizare'   => 'Actualizare',
        'explicatie'    => 'Explicație',
        'lista_aplicatii'=>'Listă aplicații',
        'ghid_diaspora' => 'Ghid diaspora',
    ];
    $typeLabel = $typeLabels[$art['article_type'] ?? ''] ?? 'Ghid';
    $url = '/' . e($art['category_slug'] ?? 'categorie') . '/' . e($art['slug']) . '/';
    $excerpt = truncate($art['excerpt'] ?? '', 140);
    $date = formatDate($art['published_at'] ?? $art['created_at'] ?? date('Y-m-d H:i:s'));

    ob_start();
    ?>
    <article class="article-card">
      <div class="article-card-body">
        <div class="article-card-category"><?= e($art['category_name'] ?? '') ?></div>
        <h3 class="article-card-title">
          <a href="<?= $url ?>"><?= e($art['title']) ?></a>
        </h3>
        <?php if ($excerpt): ?>
        <p class="article-card-excerpt"><?= e($excerpt) ?></p>
        <?php endif; ?>
      </div>
      <div class="article-card-footer">
        <span class="article-type-badge"><?= e($typeLabel) ?></span>
        <span><?= e($date) ?></span>
      </div>
    </article>
    <?php
    return ob_get_clean();
}
?>
