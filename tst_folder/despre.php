<?php
$current_page = 'about';
$page_title   = 'Despre';
$page_desc    = 'Profesori cu experiență, dedicați succesului fiecărui elev de clasa a V-a.';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$c = load_content('about');
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="page-hero-inner">
    <div class="container">
      <div class="badge white">Despre noi</div>
      <h1><?= e(c($c,'hero_title','Echipa noastră')) ?></h1>
      <p><?= e(c($c,'hero_subtitle')) ?></p>
    </div>
  </div>
</section>

<!-- ═══════════════════ BIO ════════════════════════════════ -->
<section class="section">
  <div class="container">
    <div class="about-bio-grid">
      <div class="about-bio fade-up">
        <div class="badge"><?= e(c($c,'badge_bio','Povestea noastră')) ?></div>
        <h2><?= e(c($c,'bio_title')) ?></h2>
        <?php if (visible('about_bio_p1')): ?><p><?= e(c($c,'bio_p1')) ?></p><?php endif; ?>
        <?php if (visible('about_bio_p2')): ?><p><?= e(c($c,'bio_p2')) ?></p><?php endif; ?>
        <?php if (visible('about_bio_p3')): ?><p><?= e(c($c,'bio_p3')) ?></p><?php endif; ?>
        <div style="margin-top:2rem;display:flex;gap:1rem;flex-wrap:wrap">
          <a href="<?= url('contact.php') ?>" class="btn btn-primary">Contactează-ne</a>
          <a href="<?= url('program.php') ?>"  class="btn btn-outline">Vezi tarifele</a>
        </div>
      </div>

      <div class="about-aside fade-up d2">
        <?php if (c($c,'team_image')): ?>
          <img src="<?= asset('img/pages/' . e(c($c,'team_image'))) ?>" alt="Echipa noastră" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:var(--r-xl);margin-bottom:1.5rem;border:1px solid var(--border)">
        <?php else: ?>
          <div class="about-img-placeholder" style="aspect-ratio:1;border-radius:var(--r-xl);font-size:5rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--bg),#dde3ff);border:1px solid var(--border)">👩‍🏫</div>
        <?php endif; ?>
        <div class="aside-card">
          <h4><?= e(c($c,'stats_title','Câteva cifre')) ?></h4>
          <?php
          $stats = [
            ['stat1_num','stat1_lbl','about_stat1'],
            ['stat2_num','stat2_lbl','about_stat2'],
            ['stat3_num','stat3_lbl','about_stat3'],
            ['stat4_num','stat4_lbl',null],
          ];
          foreach ($stats as [$nk,$lk,$vk]):
            if ($vk && !visible($vk)) continue;
            if (!c($c,$nk)) continue;
          ?>
          <div class="aside-stat">
            <div class="aside-stat-num"><?= e(c($c,$nk)) ?></div>
            <div class="aside-stat-lbl"><?= e(c($c,$lk)) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════ METODA ═════════════════════════════ -->
<?php if (visible('about_method')): ?>
<section class="section section-bg">
  <div class="container">
    <div class="section-header fade-up">
      <div class="badge"><?= e(c($c,'badge_method','Metodologie')) ?></div>
      <h2><?= e(c($c,'method_title','Metoda noastră de predare')) ?></h2>
      <p><?= e(c($c,'method_subtitle')) ?></p>
    </div>
    <div class="method-grid">
      <?php
      $methods = [
        ['method1_title','method1_desc','1','d1','about_method1'],
        ['method2_title','method2_desc','2','d2','about_method2'],
        ['method3_title','method3_desc','3','d3','about_method3'],
        ['method4_title','method4_desc','4','d4','about_method4'],
      ];
      foreach ($methods as [$tk,$dk,$num,$delay,$vk]):
        if (!visible($vk)) continue;
      ?>
      <div class="method-card fade-up <?= $delay ?>">
        <div class="method-step"><?= $num ?></div>
        <h4><?= e(c($c,$tk)) ?></h4>
        <p><?= e(c($c,$dk)) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php endif; ?>

<!-- ═══════════════════ CTA ════════════════════════════════ -->
<div class="cta-wrap">
  <div class="container">
    <div class="cta-card fade-up">
      <h2><?= e(c($c,'cta_title','Hai să discutăm despre copilul tău')) ?></h2>
      <p><?= e(c($c,'cta_desc')) ?></p>
      <div class="cta-btns">
        <a href="<?= url('contact.php') ?>" class="btn btn-white btn-lg"><?= e(c($c,'cta_btn','Programează evaluarea gratuită')) ?></a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
