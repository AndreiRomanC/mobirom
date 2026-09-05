
<?php
$cf = load_content('footer');
$cc = load_content('contact');
$footer_brand_desc     = c($cf, 'brand_desc',      'Meditații profesionale de Matematică și Limba Română pentru elevii de clasa a V-a.');
$footer_col_nav        = c($cf, 'col_nav_title',    'Navigare');
$footer_col_contact    = c($cf, 'col_contact_title','Contact rapid');
$footer_phone          = c($cc, 'phone',            '+40 724 219 222');
$footer_email          = c($cc, 'email',            SITE_EMAIL);
$footer_hours          = c($cc, 'hours',            'Luni – Vineri: 13:00 – 17:00');
$footer_phone_href     = 'tel:' . preg_replace('/\s+/', '', $footer_phone);
?>
<footer>
  <div class="container">
    <div class="footer-inner">
      <div class="footer-brand">
        <a href="<?= url('index.php') ?>" class="footer-logo">
          <div class="footer-logo-icon">CM</div>
          <span class="footer-logo-name"><?= SITE_NAME ?></span>
        </a>
        <p><?= e($footer_brand_desc) ?></p>
      </div>

      <div class="footer-col">
        <h4><?= e($footer_col_nav) ?></h4>
        <ul class="footer-links">
          <li><a href="<?= url('index.php') ?>">Acasă</a></li>
          <li><a href="<?= url('despre.php') ?>">Despre</a></li>
          <li><a href="<?= url('servicii.php') ?>">Servicii</a></li>
          <li><a href="<?= url('program.php') ?>">Program & Tarife</a></li>
          <li><a href="<?= url('contact.php') ?>">Contact</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4><?= e($footer_col_contact) ?></h4>
        <ul class="footer-links">
          <li><a href="<?= e($footer_phone_href) ?>">📞 <?= e($footer_phone) ?></a></li>
          <li><a href="mailto:<?= e($footer_email) ?>"><?= e($footer_email) ?></a></li>
          <li><span style="color:rgba(255,255,255,.45);font-size:.88rem"><?= e($footer_hours) ?></span></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p class="footer-copy">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Toate drepturile rezervate.</p>
      <a href="<?= url('admin/') ?>" class="footer-admin-link">Admin</a>
    </div>
  </div>
</footer>

<script src="<?= asset('js/scripts.js') ?>"></script>
</body>
</html>
