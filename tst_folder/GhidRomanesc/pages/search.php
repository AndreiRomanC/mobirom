<?php
$query = sanitize($_GET['q'] ?? '');
$page  = max(1, (int)($_GET['p'] ?? 1));

$results    = [];
$totalCount = 0;
$totalPages = 0;

if (strlen($query) >= 2) {
    Article::logSearch($query);
    $results    = Article::search($query, $page);
    $totalCount = Article::countSearch($query);
    $totalPages = ceil($totalCount / ARTICLES_PER_PAGE);
}

$pageTitle       = $query ? 'Rezultate pentru "' . $query . '"' : 'Caută ghiduri';
$metaDescription = 'Caută ghiduri practice pe GhidRomânesc.ro';
$noIndex         = true;

require __DIR__ . '/../templates/header.php';
?>

<div class="container" style="padding:2.5rem 0 4rem;max-width:860px">

  <h1 style="font-size:1.75rem;font-weight:800;color:var(--blue-dark);margin-bottom:1.5rem">
    <?= $query ? 'Rezultate pentru „' . e($query) . '"' : 'Caută ghiduri' ?>
  </h1>

  <!-- Căutare mare -->
  <form class="search-bar" action="/cauta/" method="get" role="search" style="margin-bottom:2rem">
    <input type="search" name="q" class="search-input" value="<?= e($query) ?>" placeholder="Ce vrei să afli?" aria-label="Caută" autofocus>
    <button type="submit" class="search-submit">Caută</button>
  </form>

  <?php if ($query && strlen($query) < 2): ?>
  <div class="alert alert-warning">Introdu cel puțin 2 caractere pentru a căuta.</div>

  <?php elseif ($query && empty($results)): ?>
  <div class="empty-state">
    <div style="font-size:3rem;margin-bottom:.5rem">🔍</div>
    <h3>Niciun rezultat pentru „<?= e($query) ?>"</h3>
    <p>Încearcă alte cuvinte-cheie sau <a href="/sugereaza-subiect/?subiect=<?= urlencode($query) ?>">sugerează un subiect nou</a>.</p>
  </div>

  <?php elseif (!empty($results)): ?>
  <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.5rem"><?= $totalCount ?> rezultate găsite pentru „<?= e($query) ?>"</p>

  <div style="display:flex;flex-direction:column;gap:1rem">
    <?php foreach ($results as $art): ?>
    <article style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;transition:box-shadow var(--transition)">
      <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--blue-mid);margin-bottom:.3rem"><?= e($art['category_name'] ?? '') ?></div>
      <h2 style="font-size:1.05rem;font-weight:700;margin-bottom:.4rem">
        <a href="/<?= e($art['category_slug'] ?? '') ?>/<?= e($art['slug']) ?>/" style="color:var(--text)"><?= e($art['title']) ?></a>
      </h2>
      <?php if ($art['excerpt']): ?>
      <p style="font-size:.875rem;color:var(--text-muted)"><?= e(truncate($art['excerpt'], 180)) ?></p>
      <?php endif; ?>
      <div style="font-size:.8rem;color:var(--text-muted);margin-top:.5rem"><?= formatDate($art['published_at'] ?? $art['created_at']) ?></div>
    </article>
    <?php endforeach; ?>
  </div>

  <!-- Paginare -->
  <?php if ($totalPages > 1): ?>
  <nav class="pagination" aria-label="Pagini">
    <?php if ($page > 1): ?>
    <a href="/cauta/?q=<?= urlencode($query) ?>&p=<?= $page-1 ?>" class="page-link">‹</a>
    <?php endif; ?>
    <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
    <a href="/cauta/?q=<?= urlencode($query) ?>&p=<?= $i ?>" class="page-link<?= $i===$page?' active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
    <a href="/cauta/?q=<?= urlencode($query) ?>&p=<?= $page+1 ?>" class="page-link">›</a>
    <?php endif; ?>
  </nav>
  <?php endif; ?>

  <?php else: ?>
  <!-- Pagina de căutare fără query — sugestii -->
  <div>
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem">Categorii populare</h2>
    <div class="categories-grid" style="grid-template-columns:repeat(auto-fill,minmax(150px,1fr))">
      <?php
      $cats = Database::fetchAll('SELECT * FROM categories ORDER BY sort_order, name');
      foreach ($cats as $cat):
      ?>
      <a href="/<?= e($cat['slug']) ?>/" class="category-card">
        <div class="category-icon"><?= categoryIcon($cat['slug']) ?></div>
        <span class="category-name"><?= e($cat['name']) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>
