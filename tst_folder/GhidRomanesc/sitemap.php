<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
$domain = SITE_DOMAIN;
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">

  <!-- Homepage -->
  <url>
    <loc><?= $domain ?>/</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <!-- Categorii -->
  <?php
  $categories = Database::fetchAll('SELECT slug FROM categories ORDER BY slug');
  foreach ($categories as $cat):
  ?>
  <url>
    <loc><?= $domain ?>/<?= htmlspecialchars($cat['slug']) ?>/</loc>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <?php endforeach; ?>

  <!-- Pagini statice -->
  <?php
  $staticPages = ['despre','politica-editoriala','surse-verificare','limitarea-responsabilitatii','confidentialitate','cookies','contact','raporteaza-eroare','sugereaza-subiect'];
  foreach ($staticPages as $page):
  ?>
  <url>
    <loc><?= $domain ?>/<?= $page ?>/</loc>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <?php endforeach; ?>

  <!-- Articole -->
  <?php
  $articles = Database::fetchAll(
    'SELECT a.slug, a.updated_at, a.published_at, c.slug AS cat_slug
     FROM articles a
     LEFT JOIN categories c ON a.category_id=c.id
     WHERE a.status="published"
     ORDER BY a.published_at DESC'
  );
  foreach ($articles as $art):
  $lastmod = $art['updated_at'] ?? $art['published_at'] ?? date('Y-m-d');
  ?>
  <url>
    <loc><?= $domain ?>/<?= htmlspecialchars($art['cat_slug']) ?>/<?= htmlspecialchars($art['slug']) ?>/</loc>
    <lastmod><?= substr($lastmod, 0, 10) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <?php endforeach; ?>

</urlset>
