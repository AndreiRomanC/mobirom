<?php
// Variabile disponibile: $pageTitle, $metaDescription, $ogImage, $canonicalUrl, $schemaJson, $noIndex
$siteTitle = SITE_NAME ?? 'GhidRomânesc';
$domain    = SITE_DOMAIN ?? 'https://ghidromanesc.ro';
$fullTitle = isset($pageTitle)
    ? (str_contains($pageTitle, $siteTitle) ? $pageTitle : $pageTitle . ' — ' . $siteTitle)
    : $siteTitle . ' — Ghiduri practice pentru români';
$desc      = $metaDescription ?? SITE_TAGLINE ?? 'Ghiduri simple, explicații utile și soluții practice pentru românii de pretutindeni.';
$og        = $ogImage ?? $domain . '/assets/img/og-default.jpg';
$canonical = $canonicalUrl ?? $domain . currentPath();
$currentPath = currentPath();

// Auto-schema pentru paginile de tooluri individuale
if (preg_match('#/tooluri/[^/]+/#', $canonical)) {
    $toolSchemaName = trim(preg_replace('/\s*—\s*' . preg_quote($siteTitle, '/') . '.*$/u', '', $pageTitle ?? ''));
    if (empty($schemaJson)) {
        $schemaJson = json_encode([
            '@context'            => 'https://schema.org',
            '@type'               => 'SoftwareApplication',
            'name'                => $toolSchemaName,
            'description'         => $desc,
            'url'                 => $canonical,
            'applicationCategory' => 'UtilityApplication',
            'operatingSystem'     => 'Web',
            'offers'              => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'RON'],
            'inLanguage'          => 'ro',
            'provider'            => ['@type' => 'Organization', 'name' => $siteTitle, 'url' => $domain],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    if (empty($breadcrumbSchema)) {
        $breadcrumbSchema = json_encode([
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Acasă',               'item' => $domain . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tooluri online',       'item' => $domain . '/tooluri/'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $toolSchemaName],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
?>
<!DOCTYPE html>
<html lang="ro" prefix="og: https://ogp.me/ns#">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php if (!empty($noIndex)): ?>
<meta name="robots" content="noindex, nofollow">
<?php else: ?>
<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
<?php endif; ?>

<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">

<!-- Open Graph -->
<meta property="og:type"        content="<?= isset($isArticle) ? 'article' : 'website' ?>">
<meta property="og:title"       content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url"         content="<?= e($canonical) ?>">
<meta property="og:image"       content="<?= e($og) ?>">
<meta property="og:site_name"   content="<?= e($siteTitle) ?>">
<meta property="og:locale"      content="ro_RO">

<!-- Twitter Card -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= e($fullTitle) ?>">
<meta name="twitter:description" content="<?= e($desc) ?>">
<meta name="twitter:image"       content="<?= e($og) ?>">

<!-- Favicon -->
<link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
<link rel="icon" type="image/png"     href="/assets/img/favicon.png" sizes="32x32">
<link rel="apple-touch-icon"          href="/assets/img/apple-touch-icon.png">

<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-KG1KJNDDV5"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-KG1KJNDDV5');
</script>

<!-- CSS -->
<link rel="stylesheet" href="/assets/css/style.css">

<!-- Schema.org -->
<?php if (!empty($schemaJson)): ?>
<script type="application/ld+json"><?= $schemaJson ?></script>
<?php endif; ?>

<!-- Schema Organization (pe toate paginile) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "GhidRomânesc",
  "url": "<?= e($domain) ?>",
  "logo": "<?= e($domain) ?>/assets/img/logo.png",
  "description": "<?= e(SITE_TAGLINE ?? '') ?>",
  "contactPoint": { "@type": "ContactPoint", "contactType": "customer support", "email": "<?= e(SITE_EMAIL ?? '') ?>" }
}
</script>

<?php if (!empty($breadcrumbSchema)): ?>
<script type="application/ld+json"><?= $breadcrumbSchema ?></script>
<?php endif; ?>
<?php if (!empty($faqSchema)): ?>
<script type="application/ld+json"><?= $faqSchema ?></script>
<?php endif; ?>
</head>
<body>

<!-- Skip navigation -->
<a class="sr-only" href="#main-content">Sari la conținut</a>

<header class="site-header" role="banner">
  <div class="container">
    <div class="header-inner">

      <a href="/" class="site-logo">Ghid<span>Românesc</span></a>

      <nav class="site-nav" role="navigation" aria-label="Navigare principală">
        <a href="/acte-institutii/" class="nav-link<?= isActivePath('/acte-institutii') ? ' active' : '' ?>">Acte și instituții</a>
        <a href="/digital-ai/"     class="nav-link<?= isActivePath('/digital-ai') ? ' active' : '' ?>">Digital și AI</a>
        <a href="/diaspora/"       class="nav-link<?= isActivePath('/diaspora') ? ' active' : '' ?>">Diaspora</a>
        <a href="/bani-taxe/"      class="nav-link<?= isActivePath('/bani-taxe') ? ' active' : '' ?>">Bani și taxe</a>
        <a href="/joburi-viata/"   class="nav-link<?= isActivePath('/joburi-viata') ? ' active' : '' ?>">Joburi</a>
        <a href="/modele-checklist/"class="nav-link<?= isActivePath('/modele-checklist') ? ' active' : '' ?>">Modele</a>
        <a href="/actualizari/"    class="nav-link<?= isActivePath('/actualizari') ? ' active' : '' ?>">Actualizări</a>
        <a href="/tooluri/"        class="nav-link<?= isActivePath('/tooluri') ? ' active' : '' ?>" style="color:var(--blue-mid);font-weight:700">⚡ Tooluri</a>
      </nav>

      <form class="header-search" action="/cauta/" method="get" role="search">
        <input type="search" name="q" class="header-search-input" placeholder="Caută ghiduri..." aria-label="Caută" value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>">
      </form>

      <a href="/sugereaza-subiect/" class="btn-suggest" aria-label="Sugerează un subiect">💡 Sugerează</a>

      <button class="nav-toggle" aria-label="Deschide meniu" aria-expanded="false">
        <span style="font-size:1.4rem;line-height:1">☰</span>
      </button>

    </div>
  </div>
</header>

<main id="main-content">
