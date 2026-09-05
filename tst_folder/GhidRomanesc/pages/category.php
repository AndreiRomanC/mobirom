<?php
// Pagina categorie
// Variabile primite de la router: $categorySlug

$category = Database::fetchOne('SELECT * FROM categories WHERE slug = ?', [$categorySlug]);

if (!$category) {
    http_response_code(404);
    require __DIR__ . '/../templates/header.php';
    echo '<div class="container" style="padding:4rem 0;text-align:center"><h2>Categoria nu a fost găsită</h2><a href="/" class="btn btn-primary" style="margin-top:1rem">Acasă</a></div>';
    require __DIR__ . '/../templates/footer.php';
    exit;
}

$page    = max(1, (int)($_GET['p'] ?? 1));
$type    = sanitize($_GET['tip'] ?? '');

$articles = Article::getByCategory($categorySlug, $page, $type);

$total    = (int)Database::fetchColumn(
    'SELECT COUNT(*) FROM articles a
     LEFT JOIN categories c ON a.category_id = c.id
     WHERE c.slug = ? AND a.status = "published"' . ($type ? ' AND a.article_type = ?' : ''),
    $type ? [$categorySlug, $type] : [$categorySlug]
);
$totalPages = ceil($total / ARTICLES_PER_PAGE);

$pageTitle       = $category['name'] . ' — Ghiduri practice';
$metaDescription = $category['description'] ?? '';
$canonicalUrl    = SITE_DOMAIN . '/' . $category['slug'] . '/';

$breadcrumbSchema = json_encode([
    "@context"        => "https://schema.org",
    "@type"           => "BreadcrumbList",
    "itemListElement" => [
        [ "@type" => "ListItem", "position" => 1, "name" => "Acasă",  "item" => SITE_DOMAIN . "/" ],
        [ "@type" => "ListItem", "position" => 2, "name" => $category['name'] ],
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

require __DIR__ . '/../templates/header.php';
?>

<div class="page-header">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom:.75rem">
      <a href="/">Acasă</a>
      <span class="breadcrumb-sep">›</span>
      <span aria-current="page"><?= e($category['name']) ?></span>
    </nav>
    <h1 class="page-title">
      <?= categoryIcon($category['slug']) ?>
      <?= e($category['name']) ?>
    </h1>
    <?php if ($category['description']): ?>
    <p class="page-subtitle"><?= e($category['description']) ?></p>
    <?php endif; ?>
  </div>
</div>

<div class="container" style="padding-bottom:4rem">

  <!-- Filtre tip articol -->
  <div style="margin-bottom:1.5rem">
    <div class="filter-tabs" style="background:none;padding:0;border:none">
      <a href="/<?= e($categorySlug) ?>/" class="filter-tab<?= !$type ? ' active' : '' ?>">Toate</a>
      <a href="/<?= e($categorySlug) ?>/?tip=ghid_complet"    class="filter-tab<?= $type === 'ghid_complet'   ? ' active' : '' ?>">Ghiduri complete</a>
      <a href="/<?= e($categorySlug) ?>/?tip=checklist"       class="filter-tab<?= $type === 'checklist'      ? ' active' : '' ?>">Checklist-uri</a>
      <a href="/<?= e($categorySlug) ?>/?tip=faq"             class="filter-tab<?= $type === 'faq'            ? ' active' : '' ?>">FAQ</a>
      <a href="/<?= e($categorySlug) ?>/?tip=tutorial"        class="filter-tab<?= $type === 'tutorial'       ? ' active' : '' ?>">Tutoriale</a>
      <a href="/<?= e($categorySlug) ?>/?tip=model_email"     class="filter-tab<?= $type === 'model_email'    ? ' active' : '' ?>">Modele email</a>
    </div>
  </div>

  <?php if (empty($articles)): ?>
  <div class="empty-state">
    <div style="font-size:3rem;margin-bottom:.5rem">📋</div>
    <h3>Nu există ghiduri în această categorie încă</h3>
    <p>Revino în curând sau <a href="/sugereaza-subiect/">sugerează un subiect</a>.</p>
  </div>
  <?php else: ?>

  <div style="margin-bottom:1rem;color:var(--text-muted);font-size:.875rem"><?= $total ?> ghiduri</div>

  <div class="articles-grid">
    <?php foreach ($articles as $art): ?>
    <article class="article-card">
      <div class="article-card-body">
        <div class="article-card-category"><?= e($art['category_name'] ?? '') ?></div>
        <h2 class="article-card-title" style="font-size:1rem">
          <a href="/<?= e($art['category_slug'] ?? $categorySlug) ?>/<?= e($art['slug']) ?>/"><?= e($art['title']) ?></a>
        </h2>
        <?php if ($art['excerpt']): ?>
        <p class="article-card-excerpt"><?= e(truncate($art['excerpt'], 140)) ?></p>
        <?php endif; ?>
      </div>
      <div class="article-card-footer">
        <span class="article-type-badge"><?= e(str_replace('_', ' ', $art['article_type'] ?? '')) ?></span>
        <span><?= formatDate($art['published_at'] ?? $art['created_at']) ?></span>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

  <!-- Paginare -->
  <?php if ($totalPages > 1): ?>
  <nav class="pagination" aria-label="Pagini">
    <?php if ($page > 1): ?>
    <a href="/<?= e($categorySlug) ?>/?p=<?= $page-1 ?><?= $type ? '&tip='.e($type) : '' ?>" class="page-link" aria-label="Pagina anterioară">‹</a>
    <?php endif; ?>
    <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
    <a href="/<?= e($categorySlug) ?>/?p=<?= $i ?><?= $type ? '&tip='.e($type) : '' ?>" class="page-link<?= $i===$page?' active':'' ?>" <?= $i===$page?'aria-current="page"':'' ?>><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
    <a href="/<?= e($categorySlug) ?>/?p=<?= $page+1 ?><?= $type ? '&tip='.e($type) : '' ?>" class="page-link" aria-label="Pagina următoare">›</a>
    <?php endif; ?>
  </nav>
  <?php endif; ?>

  <?php endif; ?>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>
