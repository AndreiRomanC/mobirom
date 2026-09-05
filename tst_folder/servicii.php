<?php
$current_page = 'services';
$page_title   = 'Servicii';
$page_desc    = 'Meditații de Matematică și Română pentru clasa a V-a — tot cuprinsul programei, predat clar și eficient.';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$c = load_content('services');
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="page-hero-inner">
    <div class="container">
      <div class="badge white">Servicii</div>
      <h1><?= e(c($c,'hero_title','Servicii')) ?></h1>
      <p><?= e(c($c,'hero_subtitle')) ?></p>
    </div>
  </div>
</section>

<!-- ═══════════════════════ MATERIILE ═══════════════════════ -->
<section class="section">
  <div class="container">
    <div class="service-block">
      <!-- Matematică -->
      <div class="service-detail-card math fade-up">
        <div class="subj-bg-emoji">➗</div>
        <div class="subj-tag">Clasa a V-a</div>
        <h2><?= e(c($c,'math_title','Matematică')) ?></h2>
        <p><?= e(c($c,'math_desc')) ?></p>
        <ul class="service-topics">
          <?php for ($i=1; $i<=8; $i++): $v = c($c,"math_t$i"); if ($v): ?>
            <li><?= e($v) ?></li>
          <?php endif; endfor; ?>
        </ul>
      </div>

      <!-- Română -->
      <div class="service-detail-card ro fade-up d2">
        <div class="subj-bg-emoji">📖</div>
        <div class="subj-tag">Clasa a V-a</div>
        <h2><?= e(c($c,'ro_title','Limba Română')) ?></h2>
        <p><?= e(c($c,'ro_desc')) ?></p>
        <ul class="service-topics">
          <?php for ($i=1; $i<=8; $i++): $v = c($c,"ro_t$i"); if ($v): ?>
            <li><?= e($v) ?></li>
          <?php endif; endfor; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════ CUM FUNCȚIONEAZĂ ══════════════ -->
<?php if (visible('services_how')): ?>
<section class="section section-bg">
  <div class="container">
    <div class="section-header fade-up">
      <div class="badge"><?= e(c($c,'badge_how','Structura orei')) ?></div>
      <h2><?= e(c($c,'how_title','Cum funcționează o ședință')) ?></h2>
      <p><?= e(c($c,'how_subtitle')) ?></p>
    </div>
    <div class="how-grid">
      <?php
      $hows = [
        ['how1_title','how1_desc','1','d1','services_how1'],
        ['how2_title','how2_desc','2','d2','services_how2'],
        ['how3_title','how3_desc','3','d3','services_how3'],
      ];
      foreach ($hows as [$tk,$dk,$num,$delay,$vk]):
        if (!visible($vk)) continue;
      ?>
      <div class="how-card fade-up <?= $delay ?>">
        <div class="how-num"><?= $num ?></div>
        <h4><?= e(c($c,$tk)) ?></h4>
        <p><?= e(c($c,$dk)) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php endif; ?>

<!-- ═══════════════════════ CTA ════════════════════════════ -->
<?php if (visible('services_cta')): ?>
<div class="cta-wrap">
  <div class="container">
    <div class="cta-card fade-up">
      <h2><?= e(c($c,'cta_title','Gata să începem?')) ?></h2>
      <p><?= e(c($c,'cta_desc')) ?></p>
      <div class="cta-btns">
        <a href="<?= url('contact.php') ?>"  class="btn btn-white btn-lg">Contactează-ne</a>
        <a href="<?= url('program.php') ?>"   class="btn btn-white-outline btn-lg">Tarife & Program</a>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
