<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$allowed = ['home','about','services','program','contact','footer'];
$page    = $_GET['page'] ?? '';
if (!in_array($page, $allowed, true)) {
    header('Location: ' . BASE_PATH . '/admin/index.php');
    exit;
}

$data       = load_content($page);
$vis_file   = CONTENT_DIR . '/visibility.json';
$visibility = file_exists($vis_file) ? (json_decode(file_get_contents($vis_file), true) ?? []) : [];

// ── Sections + subsections per page ───────────────────────────────────────
// section:    id (visibility key or null), label, fields[], subsections[]
// subsection: id (visibility key), label, fields[]
$page_sections = [
    'home' => [
        [
            'id' => null, 'label' => 'Hero',
            'fields' => ['hero_title','hero_title_accent','hero_subtitle','hero_cta','hero_cta2',
                         'stat1_num','stat1_lbl','stat2_num','stat2_lbl','stat3_num','stat3_lbl'],
        ],
        [
            'id' => 'home_features', 'label' => 'De ce noi',
            'fields' => ['badge_features','features_title','features_subtitle'],
            'subsections' => [
                ['id'=>'home_feat1','label'=>'Card 1','fields'=>['feat1_icon','feat1_title','feat1_desc']],
                ['id'=>'home_feat2','label'=>'Card 2','fields'=>['feat2_icon','feat2_title','feat2_desc']],
                ['id'=>'home_feat3','label'=>'Card 3','fields'=>['feat3_icon','feat3_title','feat3_desc']],
                ['id'=>'home_feat4','label'=>'Card 4','fields'=>['feat4_icon','feat4_title','feat4_desc']],
            ],
        ],
        [
            'id' => 'home_subjects', 'label' => 'Materii',
            'fields' => ['badge_subjects','subjects_title','subjects_subtitle'],
        ],
        [
            'id' => 'home_about', 'label' => 'Despre noi',
            'fields' => ['badge_about','about_name','about_title','about_link','about_image'],
            'subsections' => [
                ['id'=>'home_about_p1','label'=>'Paragraf 1','fields'=>['about_desc1']],
                ['id'=>'home_about_p2','label'=>'Paragraf 2','fields'=>['about_desc2']],
                ['id'=>'home_cred1','label'=>'Credențial 1','fields'=>['cred1']],
                ['id'=>'home_cred2','label'=>'Credențial 2','fields'=>['cred2']],
                ['id'=>'home_cred3','label'=>'Credențial 3','fields'=>['cred3']],
            ],
        ],
        [
            'id' => 'home_testimonials', 'label' => 'Testimoniale',
            'fields' => ['badge_testimonials','testimonials_title','testimonials_subtitle'],
            'subsections' => [
                ['id'=>'home_test1','label'=>'Testimonial 1','fields'=>['test1_name','test1_role','test1_text']],
                ['id'=>'home_test2','label'=>'Testimonial 2','fields'=>['test2_name','test2_role','test2_text']],
                ['id'=>'home_test3','label'=>'Testimonial 3','fields'=>['test3_name','test3_role','test3_text']],
            ],
        ],
        [
            'id' => 'home_cta', 'label' => 'Banner CTA',
            'fields' => ['cta_title','cta_desc','cta_btn','cta_btn2'],
        ],
    ],
    'about' => [
        [
            'id' => null, 'label' => 'Hero pagină',
            'fields' => ['hero_title','hero_subtitle'],
        ],
        [
            'id' => null, 'label' => 'Cine suntem',
            'fields' => ['badge_bio','bio_title'],
            'subsections' => [
                ['id'=>'about_bio_p1','label'=>'Paragraf 1','fields'=>['bio_p1']],
                ['id'=>'about_bio_p2','label'=>'Paragraf 2','fields'=>['bio_p2']],
                ['id'=>'about_bio_p3','label'=>'Paragraf 3','fields'=>['bio_p3']],
            ],
        ],
        [
            'id' => null, 'label' => 'Statistici & Imagine',
            'fields' => ['stats_title','team_image'],
            'subsections' => [
                ['id'=>'about_stat1','label'=>'Stat 1','fields'=>['stat1_num','stat1_lbl']],
                ['id'=>'about_stat2','label'=>'Stat 2','fields'=>['stat2_num','stat2_lbl']],
                ['id'=>'about_stat3','label'=>'Stat 3','fields'=>['stat3_num','stat3_lbl']],
            ],
        ],
        [
            'id' => 'about_method', 'label' => 'Metoda de predare',
            'fields' => ['badge_method','method_title','method_subtitle'],
            'subsections' => [
                ['id'=>'about_method1','label'=>'Pasul 1','fields'=>['method1_title','method1_desc']],
                ['id'=>'about_method2','label'=>'Pasul 2','fields'=>['method2_title','method2_desc']],
                ['id'=>'about_method3','label'=>'Pasul 3','fields'=>['method3_title','method3_desc']],
                ['id'=>'about_method4','label'=>'Pasul 4','fields'=>['method4_title','method4_desc']],
            ],
        ],
        [
            'id' => null, 'label' => 'Banner CTA',
            'fields' => ['cta_title','cta_desc','cta_btn'],
        ],
    ],
    'services' => [
        [
            'id' => null, 'label' => 'Hero pagină',
            'fields' => ['hero_title','hero_subtitle'],
        ],
        [
            'id' => null, 'label' => 'Matematică',
            'fields' => ['math_title','math_desc',
                         'math_t1','math_t2','math_t3','math_t4',
                         'math_t5','math_t6','math_t7','math_t8'],
        ],
        [
            'id' => null, 'label' => 'Limba Română',
            'fields' => ['ro_title','ro_desc',
                         'ro_t1','ro_t2','ro_t3','ro_t4',
                         'ro_t5','ro_t6','ro_t7','ro_t8'],
        ],
        [
            'id' => 'services_how', 'label' => 'Structura orei',
            'fields' => ['badge_how','how_title','how_subtitle'],
            'subsections' => [
                ['id'=>'services_how1','label'=>'Pasul 1','fields'=>['how1_title','how1_desc']],
                ['id'=>'services_how2','label'=>'Pasul 2','fields'=>['how2_title','how2_desc']],
                ['id'=>'services_how3','label'=>'Pasul 3','fields'=>['how3_title','how3_desc']],
            ],
        ],
        [
            'id' => 'services_cta', 'label' => 'Banner CTA',
            'fields' => ['cta_title','cta_desc'],
        ],
    ],
    'program' => [
        [
            'id' => null, 'label' => 'Hero pagină',
            'fields' => ['hero_title','hero_subtitle'],
        ],
        [
            'id' => 'program_prices', 'label' => 'Orar & Tarife',
            'fields' => ['badge_schedule','schedule_title','schedule_note',
                         'monday','tuesday','wednesday','thursday','friday','saturday','sunday',
                         'badge_price','price_title','price_subtitle',
                         'price1_title','price1_amount','price1_period','price1_desc',
                         'price1_f1','price1_f2','price1_f3','price1_f4'],
        ],
        [
            'id' => 'program_enroll', 'label' => 'Cum te înscrii',
            'fields' => ['badge_enroll','enroll_title','enroll_subtitle'],
            'subsections' => [
                ['id'=>'program_step1','label'=>'Pasul 1','fields'=>['step1_title','step1_desc']],
                ['id'=>'program_step2','label'=>'Pasul 2','fields'=>['step2_title','step2_desc']],
                ['id'=>'program_step3','label'=>'Pasul 3','fields'=>['step3_title','step3_desc']],
                ['id'=>'program_step4','label'=>'Pasul 4','fields'=>['step4_title','step4_desc']],
            ],
        ],
    ],
    'contact' => [
        [
            'id' => null, 'label' => 'Hero pagină',
            'fields' => ['hero_title','hero_subtitle'],
        ],
        [
            'id' => null, 'label' => 'Informații contact',
            'fields' => ['badge_info','info_title','info_desc','notifications_email'],
            'subsections' => [
                ['id'=>'contact_address','label'=>'Adresă','fields'=>['address']],
                ['id'=>'contact_phone','label'=>'Telefon','fields'=>['phone']],
                ['id'=>'contact_email','label'=>'Email public','fields'=>['email']],
                ['id'=>'contact_hours','label'=>'Program','fields'=>['hours']],
                ['id'=>'contact_map','label'=>'Hartă','fields'=>['map_embed']],
            ],
        ],
        [
            'id' => null, 'label' => 'Formular',
            'fields' => ['form_title','form_desc'],
        ],
    ],
    'footer' => [
        [
            'id' => null, 'label' => 'Brand',
            'fields' => ['brand_desc'],
        ],
        [
            'id' => null, 'label' => 'Titluri coloane',
            'fields' => ['col_nav_title','col_contact_title'],
        ],
    ],
];

// ── Labels ────────────────────────────────────────────────────────────────
$labels = [
    'hero_title'=>'Titlu pagină','hero_title_accent'=>'Cuvânt colorat',
    'hero_subtitle'=>'Subtitlu','hero_cta'=>'Buton principal','hero_cta2'=>'Buton secundar',
    'stat1_num'=>'Stat 1 — val','stat1_lbl'=>'Stat 1 — text',
    'stat2_num'=>'Stat 2 — val','stat2_lbl'=>'Stat 2 — text',
    'stat3_num'=>'Stat 3 — val','stat3_lbl'=>'Stat 3 — text',
    'badge_features'=>'Etichetă','features_title'=>'Titlu','features_subtitle'=>'Subtitlu',
    'feat1_icon'=>'Emoji','feat1_title'=>'Titlu','feat1_desc'=>'Text',
    'feat2_icon'=>'Emoji','feat2_title'=>'Titlu','feat2_desc'=>'Text',
    'feat3_icon'=>'Emoji','feat3_title'=>'Titlu','feat3_desc'=>'Text',
    'feat4_icon'=>'Emoji','feat4_title'=>'Titlu','feat4_desc'=>'Text',
    'badge_subjects'=>'Etichetă','subjects_title'=>'Titlu','subjects_subtitle'=>'Subtitlu',
    'badge_about'=>'Etichetă','about_name'=>'Titlu','about_title'=>'Subtitlu',
    'about_desc1'=>'Paragraf 1','about_desc2'=>'Paragraf 2',
    'cred1'=>'Credențial 1','cred2'=>'Credențial 2','cred3'=>'Credențial 3',
    'about_link'=>'Text buton','about_image'=>'Imagine',
    'badge_testimonials'=>'Etichetă','testimonials_title'=>'Titlu','testimonials_subtitle'=>'Subtitlu',
    'test1_name'=>'Nume','test1_role'=>'Rol','test1_text'=>'Text',
    'test2_name'=>'Nume','test2_role'=>'Rol','test2_text'=>'Text',
    'test3_name'=>'Nume','test3_role'=>'Rol','test3_text'=>'Text',
    'cta_title'=>'Titlu','cta_desc'=>'Text','cta_btn'=>'Buton principal','cta_btn2'=>'Buton secundar',
    'badge_bio'=>'Etichetă','bio_title'=>'Titlu',
    'bio_p1'=>'Paragraf 1','bio_p2'=>'Paragraf 2','bio_p3'=>'Paragraf 3',
    'stats_title'=>'Titlu card statistici','team_image'=>'Imagine echipă',
    'badge_method'=>'Etichetă','method_title'=>'Titlu','method_subtitle'=>'Subtitlu',
    'method1_title'=>'Titlu','method1_desc'=>'Descriere',
    'method2_title'=>'Titlu','method2_desc'=>'Descriere',
    'method3_title'=>'Titlu','method3_desc'=>'Descriere',
    'method4_title'=>'Titlu','method4_desc'=>'Descriere',
    'math_title'=>'Titlu','math_desc'=>'Descriere',
    'math_t1'=>'Capitol 1','math_t2'=>'Capitol 2','math_t3'=>'Capitol 3','math_t4'=>'Capitol 4',
    'math_t5'=>'Capitol 5','math_t6'=>'Capitol 6','math_t7'=>'Capitol 7','math_t8'=>'Capitol 8',
    'ro_title'=>'Titlu','ro_desc'=>'Descriere',
    'ro_t1'=>'Capitol 1','ro_t2'=>'Capitol 2','ro_t3'=>'Capitol 3','ro_t4'=>'Capitol 4',
    'ro_t5'=>'Capitol 5','ro_t6'=>'Capitol 6','ro_t7'=>'Capitol 7','ro_t8'=>'Capitol 8',
    'badge_how'=>'Etichetă','how_title'=>'Titlu','how_subtitle'=>'Subtitlu',
    'how1_title'=>'Titlu','how1_desc'=>'Descriere',
    'how2_title'=>'Titlu','how2_desc'=>'Descriere',
    'how3_title'=>'Titlu','how3_desc'=>'Descriere',
    'badge_schedule'=>'Etichetă orar','schedule_title'=>'Titlu orar','schedule_note'=>'Notă',
    'monday'=>'Luni','tuesday'=>'Marți','wednesday'=>'Miercuri',
    'thursday'=>'Joi','friday'=>'Vineri','saturday'=>'Sâmbătă','sunday'=>'Duminică',
    'badge_price'=>'Etichetă tarife','price_title'=>'Titlu tarife','price_subtitle'=>'Subtitlu tarife',
    'price1_title'=>'Titlu tarif','price1_amount'=>'Preț (cifra)','price1_period'=>'Perioadă',
    'price1_desc'=>'Descriere tarif',
    'price1_f1'=>'Detaliu 1','price1_f2'=>'Detaliu 2','price1_f3'=>'Detaliu 3','price1_f4'=>'Detaliu 4',
    'badge_enroll'=>'Etichetă','enroll_title'=>'Titlu','enroll_subtitle'=>'Subtitlu',
    'step1_title'=>'Titlu','step1_desc'=>'Descriere',
    'step2_title'=>'Titlu','step2_desc'=>'Descriere',
    'step3_title'=>'Titlu','step3_desc'=>'Descriere',
    'step4_title'=>'Titlu','step4_desc'=>'Descriere',
    'badge_info'=>'Etichetă','info_title'=>'Titlu','info_desc'=>'Text intro',
    'notifications_email'=>'📬 Email notificări','address'=>'Adresă','phone'=>'Telefon',
    'email'=>'Email public','hours'=>'Program','map_embed'=>'URL embed hartă',
    'form_title'=>'Titlu formular','form_desc'=>'Subtitlu formular',
    'brand_desc'=>'Descriere brand','col_nav_title'=>'Titlu coloană Navigare','col_contact_title'=>'Titlu coloană Contact',
];

function is_textarea_field(string $key, string $val): bool {
    foreach (['_subtitle','_desc','bio_p','schedule_note','map_embed','price1_desc','info_desc'] as $t) {
        if (str_contains($key, $t)) return true;
    }
    return strlen($val) > 80;
}
function is_image_key(string $key): bool { return str_ends_with($key, '_image'); }

$page_titles = [
    'home'=>'Pagina principală','about'=>'Despre',
    'services'=>'Servicii','program'=>'Program & Tarife','contact'=>'Contact','footer'=>'Footer',
];
$page_name = $page_titles[$page] ?? $page;
$sections  = $page_sections[$page] ?? [];
$saved = isset($_GET['saved']);
$err   = isset($_GET['err']);

// Field renderer — inline closure to avoid code duplication
$render_field = function(string $key) use ($data, $labels) {
    if (!array_key_exists($key, $data)) return;
    $value = $data[$key];
    if (!is_string($value)) return;
    $label    = $labels[$key] ?? ucwords(str_replace('_',' ',$key));
    $is_img   = is_image_key($key);
    $is_ta    = !$is_img && is_textarea_field($key, $value);
    $full_col = $is_img || $is_ta;
    $fid      = 'f_' . htmlspecialchars($key);
    echo '<div class="ae-field' . ($full_col ? ' ae-full' : '') . '">';
    echo '<label class="ae-label" for="' . $fid . '">' . htmlspecialchars($label) . '</label>';
    if ($is_img) {
        if ($value) {
            echo '<div class="ae-img-preview">';
            echo '<img src="' . BASE_PATH . '/assets/img/pages/' . htmlspecialchars($value) . '" alt="" data-ae-preview>';
            echo '<label class="ae-img-clear"><input type="checkbox" name="clear_image[' . htmlspecialchars($key) . ']" value="1"> șterge</label>';
            echo '</div>';
        } else {
            echo '<p class="ae-img-empty">Nicio imagine.</p>';
        }
        echo '<input type="file" name="img[' . htmlspecialchars($key) . ']" id="' . $fid . '" class="admin-input ae-file" accept="image/jpeg,image/png,image/webp" data-ae-img-key="' . htmlspecialchars($key) . '">';
        echo '<span class="ae-hint">JPG · PNG · WebP · max 5 MB</span>';
    } elseif ($is_ta) {
        $rows = strlen($value) > 200 ? 4 : 2;
        echo '<textarea name="content[' . htmlspecialchars($key) . ']" id="' . $fid . '" class="admin-input" rows="' . $rows . '">' . htmlspecialchars($value) . '</textarea>';
    } else {
        echo '<input type="text" name="content[' . htmlspecialchars($key) . ']" id="' . $fid . '" class="admin-input" value="' . htmlspecialchars($value) . '">';
    }
    echo '</div>';
};
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editare: <?= $page_name ?> — Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
</head>
<body>
<div class="admin-wrap">
  <header class="admin-header">
    <span class="admin-logo">⚙️ &nbsp;<?= htmlspecialchars($page_name) ?></span>
    <div style="display:flex;gap:1rem;align-items:center">
      <a href="<?= BASE_PATH ?>/admin/index.php" style="font-size:.85rem;color:var(--text-3)">← Dashboard</a>
      <a href="<?= BASE_PATH ?>/admin/logout.php" class="btn btn-outline btn-sm">Deconectare</a>
    </div>
  </header>

  <div class="admin-content">
    <?php if ($saved): ?>
      <div class="admin-alert ok">✅ &nbsp;Modificările au fost salvate.</div>
    <?php endif; ?>
    <?php if ($err): ?>
      <div class="admin-alert err">❌ &nbsp;Eroare la salvare. Verifică permisiunile <code>content/</code>.</div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_PATH ?>/admin/save.php" enctype="multipart/form-data">
      <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">

      <?php foreach ($sections as $sec): ?>
        <div class="ae-section">
          <div class="ae-sec-head">
            <span class="ae-sec-title"><?= htmlspecialchars($sec['label']) ?></span>
            <?php if ($sec['id']): $vk = $sec['id']; ?>
              <label class="ae-vis-toggle" title="Vizibil pe site">
                <input type="checkbox" name="visibility[<?= $vk ?>]" value="1"<?= ($visibility[$vk] ?? true) ? ' checked' : '' ?>>
                <span class="ae-vis-label">vizibil</span>
              </label>
            <?php endif; ?>
          </div>

          <div class="ae-fields">
            <?php foreach ($sec['fields'] as $key): $render_field($key); endforeach; ?>

            <?php foreach ($sec['subsections'] ?? [] as $sub): ?>
              <div class="ae-subsec-head ae-full">
                <span class="ae-subsec-title"><?= htmlspecialchars($sub['label']) ?></span>
                <?php $svk = $sub['id']; ?>
                <label class="ae-vis-toggle">
                  <input type="checkbox" name="visibility[<?= $svk ?>]" value="1"<?= ($visibility[$svk] ?? true) ? ' checked' : '' ?>>
                  <span class="ae-vis-label">vizibil</span>
                </label>
              </div>
              <?php foreach ($sub['fields'] as $key): $render_field($key); endforeach; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="ae-save-bar">
        <button type="submit" class="btn btn-primary">💾 &nbsp;Salvează</button>
        <a href="<?= BASE_PATH ?>/admin/index.php" class="btn btn-outline">Anulează</a>
      </div>
    </form>
  </div>
</div>

<script>
// Client-side image preview when a file is selected
document.querySelectorAll('.ae-file').forEach(function(inp) {
  inp.addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var field = this.closest('.ae-field');
    var reader = new FileReader();
    reader.onload = function(e) {
      // Find or create preview container
      var preview = field.querySelector('.ae-img-preview');
      if (!preview) {
        preview = document.createElement('div');
        preview.className = 'ae-img-preview';
        inp.parentNode.insertBefore(preview, inp);
        var empty = field.querySelector('.ae-img-empty');
        if (empty) empty.remove();
      }
      // Find or create img tag
      var img = preview.querySelector('[data-ae-preview]');
      if (!img) {
        img = document.createElement('img');
        img.setAttribute('data-ae-preview', '');
        preview.prepend(img);
      }
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });
});
</script>
</body>
</html>
