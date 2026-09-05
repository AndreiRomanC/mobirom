<?php
// Pagina articol individual
// Variabile primite de la router: $categorySlug, $articleSlug

$article = Article::getBySlug($categorySlug, $articleSlug);

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Pagina nu a fost găsită';
    require __DIR__ . '/../templates/header.php';
    echo '<div class="container" style="padding:4rem 0;text-align:center">
        <h1 style="font-size:3rem;color:#d1d5db">404</h1>
        <h2 style="margin-bottom:1rem">Pagina nu a fost găsită</h2>
        <p>Ghidul căutat nu există sau a fost mutat.</p>
        <a href="/" class="btn btn-primary" style="margin-top:1.5rem">Înapoi la pagina principală</a>
    </div>';
    require __DIR__ . '/../templates/footer.php';
    exit;
}

Article::incrementViews($article['id']);

$relatedArticles = Article::getRelated($article['id'], $article['category_id'], 4);

// Surse
$sources = Database::fetchAll('SELECT * FROM article_sources WHERE article_id = ?', [$article['id']]);

// Meta
$pageTitle       = $article['meta_title'] ?? $article['title'];
$metaDescription = $article['meta_description'] ?? truncate($article['excerpt'] ?? '', 160);
$ogImage         = $article['og_image'] ?? null;
$canonicalUrl    = SITE_DOMAIN . '/' . $article['category_slug'] . '/' . $article['slug'] . '/';
$isArticle       = true;

// Breadcrumb schema
$breadcrumbSchema = json_encode([
    "@context"        => "https://schema.org",
    "@type"           => "BreadcrumbList",
    "itemListElement" => [
        [ "@type" => "ListItem", "position" => 1, "name" => "Acasă",     "item" => SITE_DOMAIN . "/" ],
        [ "@type" => "ListItem", "position" => 2, "name" => $article['category_name'], "item" => SITE_DOMAIN . "/" . $article['category_slug'] . "/" ],
        [ "@type" => "ListItem", "position" => 3, "name" => $article['title'] ],
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Article schema
$articleSchema = json_encode([
    "@context"         => "https://schema.org",
    "@type"            => "Article",
    "headline"         => $article['title'],
    "description"      => $metaDescription,
    "datePublished"    => $article['published_at'] ?? $article['created_at'],
    "dateModified"     => $article['updated_at'],
    "author"           => ["@type" => "Person", "name" => $article['author_name'] ?? "GhidRomânesc"],
    "publisher"        => ["@type" => "Organization", "name" => "GhidRomânesc", "logo" => ["@type" => "ImageObject", "url" => SITE_DOMAIN . "/assets/img/logo.png"]],
    "mainEntityOfPage" => ["@type" => "WebPage", "@id" => $canonicalUrl],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$schemaJson = $articleSchema;

require __DIR__ . '/../templates/header.php';
?>

<div class="container">

  <!-- Breadcrumb -->
  <nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom:1.5rem">
    <a href="/">Acasă</a>
    <span class="breadcrumb-sep" aria-hidden="true">›</span>
    <a href="/<?= e($article['category_slug']) ?>/"><?= e($article['category_name']) ?></a>
    <span class="breadcrumb-sep" aria-hidden="true">›</span>
    <span aria-current="page"><?= e(truncate($article['title'], 60)) ?></span>
  </nav>

  <div class="article-layout">

    <!-- Conținut principal -->
    <article class="article-main">

      <header class="article-header">
        <div class="article-meta">
          <a href="/<?= e($article['category_slug']) ?>/" class="badge badge-blue"><?= e($article['category_name']) ?></a>
          <?php if ($article['published_at']): ?>
          <span>Publicat: <?= formatDate($article['published_at']) ?></span>
          <?php endif; ?>
          <?php if ($article['last_checked_at']): ?>
          <span>Verificat: <?= formatDate($article['last_checked_at']) ?></span>
          <?php endif; ?>
          <?php if ($article['author_name']): ?>
          <span>Autor: <?= e($article['author_name']) ?></span>
          <?php endif; ?>
          <span>👁 <?= number_format($article['views']) ?> vizualizări</span>
        </div>

        <h1 class="article-title"><?= e($article['title']) ?></h1>

        <?php if ($article['excerpt']): ?>
        <p class="article-excerpt"><?= e($article['excerpt']) ?></p>
        <?php endif; ?>
      </header>

      <!-- Disclaimer puternic pentru conținut sensibil (galben/rosu) -->
      <?php if ($article['needs_disclaimer'] || in_array($article['risk_level'], ['galben','rosu'])): ?>
      <div class="disclaimer-box">
        <strong>⚠️ Important:</strong> Acest articol are scop informativ și explicativ. Nu reprezintă consultanță juridică, fiscală, financiară sau medicală personalizată. Regulile, taxele și procedurile se pot schimba. <strong>Verifică întotdeauna la sursa oficială înainte de a lua o decizie sau de a depune documente.</strong>
      </div>
      <?php endif; ?>

      <!-- Conținut articol -->
      <div class="article-content">
        <?= $article['content'] ?>
      </div>

      <!-- Disclaimer legal universal — subtil, pe toate articolele -->
      <div style="margin-top:2rem;padding:.875rem 1rem;background:#f8fafc;border-left:3px solid #cbd5e1;border-radius:0 6px 6px 0;font-size:.8rem;color:#64748b;line-height:1.6">
        <strong style="color:#475569">ℹ️ Notă informativă:</strong>
        Conținutul acestui ghid are caracter general și orientativ. GhidRomânesc.ro nu este o instituție publică și nu oferă consultanță juridică, fiscală sau administrativă personalizată.
        Informațiile se pot modifica prin acte normative ulterioare.
        <?php if (!empty($sources)): ?>
        Consultați sursele oficiale indicate mai jos pentru datele actualizate și cu forță juridică.
        <?php else: ?>
        Consultați întotdeauna sursa oficială (gov.ro, anaf.ro, mae.ro etc.) pentru informații actualizate și cu forță juridică.
        <?php endif; ?>
      </div>

      <!-- Surse oficiale -->
      <?php if (!empty($sources)): ?>
      <div class="box-surse" style="margin-top:2rem">
        <h3>Surse oficiale</h3>
        <ul>
          <?php foreach ($sources as $src): ?>
          <li>
            <?php if ($src['url']): ?>
            <a href="<?= e($src['url']) ?>" target="_blank" rel="nofollow noopener"><?= e($src['title'] ?? $src['url']) ?></a>
            <?php else: ?>
            <?= e($src['title'] ?? '') ?>
            <?php endif; ?>
            <?php if ($src['institution']): ?>
            — <em><?= e($src['institution']) ?></em>
            <?php endif; ?>
            <?php if ($src['accessed_at']): ?>
            <small style="color:var(--text-muted)">(accesat <?= formatDate($src['accessed_at']) ?>)</small>
            <?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>


      <!-- Acțiuni -->
      <div class="article-actions">
        <button class="action-btn share-whatsapp" aria-label="Trimite pe WhatsApp">
          📲 Trimite pe WhatsApp
        </button>
        <button class="action-btn print-page" aria-label="Printează">
          🖨️ Printează
        </button>
        <button class="action-btn save-article" aria-label="Salvează ghidul">
          🔖 Salvează
        </button>
        <a href="/raporteaza-eroare/?url=<?= urlencode($canonicalUrl) ?>" class="action-btn">
          ⚠️ Raportează eroare
        </a>
      </div>

    </article>

    <!-- Sidebar -->
    <aside class="article-sidebar" aria-label="Sidebar">

      <?php if (!empty($relatedArticles)): ?>
      <div class="sidebar-widget">
        <div class="sidebar-widget-header">Ghiduri recomandate</div>
        <div class="sidebar-widget-body" style="padding:.5rem 0">
          <?php foreach ($relatedArticles as $rel): ?>
          <a href="/<?= e($rel['category_slug']) ?>/<?= e($rel['slug']) ?>/" class="related-article" style="padding:.75rem 1.1rem">
            <div>
              <span class="related-category"><?= e($rel['category_name']) ?></span>
              <span class="related-title"><?= e(truncate($rel['title'], 70)) ?></span>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Categorii quick nav -->
      <div class="sidebar-widget">
        <div class="sidebar-widget-header">Alte categorii</div>
        <div class="sidebar-widget-body" style="display:flex;flex-direction:column;gap:.35rem">
          <a href="/acte-institutii/"   style="font-size:.875rem;color:var(--blue-mid);font-weight:500">→ Acte și instituții</a>
          <a href="/digital-ai/"        style="font-size:.875rem;color:var(--blue-mid);font-weight:500">→ Digital și AI</a>
          <a href="/diaspora/"          style="font-size:.875rem;color:var(--blue-mid);font-weight:500">→ Diaspora</a>
          <a href="/bani-taxe/"         style="font-size:.875rem;color:var(--blue-mid);font-weight:500">→ Bani și taxe</a>
          <a href="/modele-checklist/"  style="font-size:.875rem;color:var(--blue-mid);font-weight:500">→ Modele și checklist-uri</a>
        </div>
      </div>

      <!-- Disclaimer widget -->
      <div class="sidebar-widget">
        <div class="sidebar-widget-header">Despre GhidRomânesc</div>
        <div class="sidebar-widget-body" style="font-size:.82rem;color:var(--text-muted);line-height:1.6">
          GhidRomânesc este o platformă informativă. Nu suntem instituție publică și nu oferim consultanță juridică, fiscală, financiară sau medicală personalizată.
          <a href="/despre/" style="display:block;margin-top:.5rem;font-weight:600;color:var(--blue-mid)">Află mai multe →</a>
        </div>
      </div>

    </aside>
  </div>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>
