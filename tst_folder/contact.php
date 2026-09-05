<?php
$current_page = 'contact';
$page_title   = 'Contact';
$page_desc    = 'Contactează Afterschool Claudia Muntean — suntem bucuroși să răspundem oricăror întrebări.';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$c = load_content('contact');

$msg_ok  = '';
$msg_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot
    if (!empty($_POST['website'])) { exit; }

    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $errors = [];
    if ($name === '')                              $errors[] = 'numele';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'adresa de email';
    if ($message === '')                            $errors[] = 'mesajul';

    if ($errors) {
        $msg_err = 'Te rugăm să completezi corect: ' . implode(', ', $errors) . '.';
    } else {
        // Save to admin submissions
        save_submission([
            'name' => $name, 'email' => $email,
            'phone' => $phone, 'subject' => $subject, 'message' => $message,
        ]);

        // Send notification email
        $to   = c($c, 'notifications_email', c($c, 'email', SITE_EMAIL));
        $subj = '=?UTF-8?B?' . base64_encode('Mesaj nou de pe site — ' . $name) . '?=';
        $body = "Nume: $name\nEmail: $email\nTelefon: $phone\nSubiect: $subject\n\nMesaj:\n$message";
        $headers  = "From: noreply@afterschoolclaudiamuntean.ro\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";
        @mail($to, $subj, $body, $headers);

        $msg_ok = 'Mesajul tău a fost trimis. Te vom contacta în cel mai scurt timp!';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="page-hero-inner">
    <div class="container">
      <div class="badge white">Contact</div>
      <h1><?= e(c($c,'hero_title','Contact')) ?></h1>
      <p><?= e(c($c,'hero_subtitle')) ?></p>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="contact-grid">

      <!-- Info -->
      <div class="fade-up">
        <div class="badge"><?= e(c($c,'badge_info','Date de contact')) ?></div>
        <h2><?= e(c($c,'info_title','Hai să vorbim')) ?></h2>
        <p><?= e(c($c,'info_desc')) ?></p>
        <div class="c-items">
          <?php if (visible('contact_address') && c($c,'address')): ?>
          <div class="c-item">
            <div class="c-item-icon">📍</div>
            <div>
              <div class="c-item-label">Adresă</div>
              <div class="c-item-val"><?= e(c($c,'address')) ?></div>
            </div>
          </div>
          <?php endif; ?>
          <?php if (visible('contact_phone') && c($c,'phone')): ?>
          <div class="c-item">
            <div class="c-item-icon">📞</div>
            <div>
              <div class="c-item-label">Telefon</div>
              <div class="c-item-val"><a href="tel:<?= e(str_replace(' ','',c($c,'phone'))) ?>"><?= e(c($c,'phone')) ?></a></div>
            </div>
          </div>
          <?php endif; ?>
          <?php if (visible('contact_email') && c($c,'email')): ?>
          <div class="c-item">
            <div class="c-item-icon">✉️</div>
            <div>
              <div class="c-item-label">Email</div>
              <div class="c-item-val"><a href="mailto:<?= e(c($c,'email')) ?>"><?= e(c($c,'email')) ?></a></div>
            </div>
          </div>
          <?php endif; ?>
          <?php if (visible('contact_hours') && c($c,'hours')): ?>
          <div class="c-item">
            <div class="c-item-icon">🕒</div>
            <div>
              <div class="c-item-label">Program</div>
              <div class="c-item-val"><?= e(c($c,'hours')) ?></div>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <?php if (visible('contact_map') && c($c,'map_embed')): ?>
        <div style="border-radius:var(--r-lg);overflow:hidden;height:260px;border:1px solid var(--border)">
          <iframe src="<?= e(c($c,'map_embed')) ?>" style="width:100%;height:100%;border:0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <?php endif; ?>
      </div>

      <!-- Form -->
      <div class="fade-up d2">
        <div class="contact-form-card">
          <h3><?= e(c($c,'form_title','Trimite-ne un mesaj')) ?></h3>
          <p><?= e(c($c,'form_desc')) ?></p>

          <?php if ($msg_ok): ?>
            <div class="form-msg success"><?= e($msg_ok) ?></div>
          <?php endif; ?>
          <?php if ($msg_err): ?>
            <div class="form-msg error"><?= e($msg_err) ?></div>
          <?php endif; ?>

          <form id="contact-form" method="POST" action="<?= url('contact.php') ?>">
            <!-- Honeypot -->
            <div class="hp-field" aria-hidden="true">
              <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Nume *</label>
                <input type="text" name="name" class="form-ctrl" placeholder="Maria Ionescu" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-ctrl" placeholder="maria@email.ro" required>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Telefon</label>
                <input type="tel" name="phone" class="form-ctrl" placeholder="+40 7xx xxx xxx">
              </div>
              <div class="form-group">
                <label class="form-label">Subiect</label>
                <select name="subject" class="form-ctrl">
                  <option value="">Selectează...</option>
                  <option>Înscriere grupă</option>
                  <option>Ședință individuală</option>
                  <option>Informații generale</option>
                  <option>Evaluare gratuită</option>
                  <option>Altceva</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Mesaj *</label>
              <textarea name="message" class="form-ctrl" placeholder="Spune-ne cum te putem ajuta…" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Trimite mesajul</button>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
