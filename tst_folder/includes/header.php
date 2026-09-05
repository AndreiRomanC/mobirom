<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title ?? SITE_NAME) ?> <?= isset($page_title) ? '— ' . SITE_NAME : '' ?></title>
  <meta name="description" content="<?= e($page_desc ?? 'Meditații profesionale de Matematică și Limba Română pentru clasa a V-a. Grupe mici, rezultate garantate — Claudia Muntean.') ?>">
  <!-- Fonts: self-hosted, preloaded — zero external request, zero swap, zero CLS -->
  <link rel="preload" as="font" type="font/woff2" href="<?= asset('fonts/pjs-latin.woff2') ?>" crossorigin>
  <link rel="preload" as="font" type="font/woff2" href="<?= asset('fonts/pjs-latin-ext.woff2') ?>" crossorigin>
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <link rel="icon" type="image/png" href="<?= asset('img/logo.png') ?>">
</head>
<body class="page-<?= e($current_page ?? 'default') ?>">

<?php
$nav_links = [
    'home'     => ['url' => url('index.php'),    'label' => 'Acasă'],
    'about'    => ['url' => url('despre.php'),   'label' => 'Despre'],
    'services' => ['url' => url('servicii.php'), 'label' => 'Servicii'],
    'program'  => ['url' => url('program.php'),  'label' => 'Program & Tarife'],
    'contact'  => ['url' => url('contact.php'),  'label' => 'Contact'],
];
$current = $current_page ?? '';
?>

<header class="nav" id="nav">
  <div class="container">
    <div class="nav-inner">
      <a href="<?= url('index.php') ?>" class="nav-logo">
        <img src="<?= asset('img/logo.png') ?>" alt="CM logo" class="nav-logo-icon">
        <div class="nav-logo-text">
          <span class="nav-logo-sub">Afterschool · Clasa a V-a</span>
          <span class="nav-logo-name">Claudia Muntean</span>
        </div>
      </a>

      <nav>
        <ul class="nav-links" id="nav-links">
          <?php foreach ($nav_links as $key => $link): ?>
            <li><a href="<?= e($link['url']) ?>" class="<?= $current === $key ? 'active' : '' ?>"><?= e($link['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <div class="nav-right">
        <a href="<?= url('contact.php') ?>" class="btn btn-primary btn-sm nav-cta-btn">Înscrie-te</a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Meniu">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile dropdown -->
  <div class="nav-mobile" id="nav-mobile">
    <div class="container">
      <ul class="nav-mobile-links">
        <?php foreach ($nav_links as $key => $link): ?>
          <li><a href="<?= e($link['url']) ?>" class="<?= $current === $key ? 'active' : '' ?>"><?= e($link['label']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?= url('contact.php') ?>" class="btn btn-primary" style="margin-top:.5rem">Înscrie-te acum</a></li>
      </ul>
    </div>
  </div>
</header>
