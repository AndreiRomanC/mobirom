<?php
$pageTitle       = 'Tooluri gratuite online — GhidRomânesc';
$metaDescription = 'Tooluri gratuite online pentru români: calculator salariu net, TVA, credit, verificator CNP, IBAN, PDF, SEO. Funcționează instant în browser, fără upload.';
$canonicalUrl    = SITE_DOMAIN . '/tooluri/';

// Schema breadcrumb + ItemList — setate înainte de header.php
$breadcrumbSchema = json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Acasă',               'item' => SITE_DOMAIN . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tooluri online gratuite'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$tools = [
  'pdf' => [
    'label'=>'PDF & Documente', 'icon'=>'📄', 'color'=>'#dbeafe',
    'items'=>[
      ['slug'=>'uneste-pdf',    'icon'=>'🔗','title'=>'Unește PDF-uri',          'desc'=>'Combină mai multe PDF-uri într-unul singur. Drag & drop pentru reordonare.'],
      ['slug'=>'imparte-pdf',   'icon'=>'✂️', 'title'=>'Împarte PDF',             'desc'=>'Extrage pagini sau intervale din orice PDF.'],
      ['slug'=>'protejare-pdf', 'icon'=>'🔒','title'=>'Protejează PDF cu parolă','desc'=>'Adaugă parolă de deschidere la orice PDF. 100% local.'],
      ['slug'=>'roteste-pdf',   'icon'=>'🔄','title'=>'Rotește pagini PDF',       'desc'=>'Corectezi orientarea paginilor cu 90°, 180° sau 270°.'],
      ['slug'=>'marcaj-pdf',    'icon'=>'💧','title'=>'Marcaj de apă PDF',        'desc'=>'Text diagonal (watermark) pe toate paginile unui PDF.'],
      ['slug'=>'numerotare-pdf','icon'=>'🔢','title'=>'Numerotare pagini PDF',    'desc'=>'Adaugi automat numere de pagină. Alegi poziția și formatul.'],
      ['slug'=>'numar-pagini',  'icon'=>'#️⃣','title'=>'Câte pagini are PDF-ul?', 'desc'=>'Verifici numărul de pagini fără să deschizi fișierul.'],
    ],
  ],
  'calcule' => [
    'label'=>'Calcule & Finanțe', 'icon'=>'💰', 'color'=>'#dcfce7',
    'items'=>[
      ['slug'=>'salariu-net',    'icon'=>'💰','title'=>'Calculator salariu net 2026','desc'=>'Calculezi net din brut sau invers. CAS, CASS, impozit, deducere personală.'],
      ['slug'=>'calculator-tva',   'icon'=>'🧮','title'=>'Calculator TVA 2026',       'desc'=>'Adaugi sau extragi TVA. Cote corecte 2026: 19%, 9%, 5% cu explicații.'],
      ['slug'=>'calculator-credit',   'icon'=>'🏦','title'=>'Calculator rată credit',       'desc'=>'Rata lunară, dobânda totală și grafic complet de amortizare.'],
      ['slug'=>'calculator-dobanda', 'icon'=>'📊','title'=>'Calculator dobândă',            'desc'=>'Dobândă simplă și compusă pentru depozite, economii sau împrumuturi.'],
      ['slug'=>'concediu-medical',   'icon'=>'🏥','title'=>'Calculator concediu medical',   'desc'=>'Estimezi indemnizația de concediu medical pe baza venitului din ultimele 6 luni.'],
      ['slug'=>'calculator-pfa',     'icon'=>'💼','title'=>'Calculator impozit PFA',        'desc'=>'CAS, CASS și impozit pe venit pentru PFA — sistem real de impunere.'],
      ['slug'=>'calculator-dividende','icon'=>'📈','title'=>'Calculator impozit dividende', 'desc'=>'Impozit 10% + CASS pe dividende distribuite. Include plafonare CASS.'],
      ['slug'=>'calculator-chirie',   'icon'=>'🏠','title'=>'Calculator impozit chirii 2026','desc'=>'Impozit 10%, CAS, CASS pe venituri din chirii. Cotă forfetară 20%. Include D212.'],
      ['slug'=>'generator-factura',   'icon'=>'🧾','title'=>'Generator factură PDF',         'desc'=>'Facturi PDF profesionale generate 100% în browser. Emitent, client, TVA, IBAN. Date salvate local.'],
      ['slug'=>'convertor-valutar',   'icon'=>'💱','title'=>'Convertor valutar BNR',            'desc'=>'Convertești RON în EUR, USD, GBP, CHF și orice altă valută. Curs oficial BNR actualizat zilnic.'],
    ],
  ],
  'acte' => [
    'label'=>'Acte & Verificări', 'icon'=>'🪪', 'color'=>'#fef9c3',
    'items'=>[
      ['slug'=>'verificator-cnp', 'icon'=>'🪪','title'=>'Verificator CNP',  'desc'=>'Validezi CNP-ul și afli data nașterii, sexul, județul și vârsta.'],
      ['slug'=>'verificator-iban','icon'=>'🏦','title'=>'Verificator IBAN', 'desc'=>'Verifici validitatea unui IBAN și identifici banca emitentă.'],
    ],
  ],
  'imagini' => [
    'label'=>'Imagini', 'icon'=>'🖼️', 'color'=>'#fce7f3',
    'items'=>[
      ['slug'=>'comprima-imagine',      'icon'=>'🗜️','title'=>'Comprimă imagine',             'desc'=>'Reduci dimensiunea JPG, PNG, WebP direct în browser. Fără upload.'],
      ['slug'=>'redimensionare-imagine','icon'=>'📐','title'=>'Redimensionează imagine',      'desc'=>'Schimbi lățimea și înălțimea. Dimensiuni preset sau personalizate.'],
      ['slug'=>'convertor-imagine',     'icon'=>'🔄','title'=>'Convertor format imagine',     'desc'=>'Convertești între JPG, PNG și WebP. Poți procesa mai multe simultan.'],
      ['slug'=>'rotire-imagine',        'icon'=>'↻', 'title'=>'Rotire și flip imagine',       'desc'=>'Rotești cu 90°/180°/270° sau întorci orizontal/vertical.'],
      ['slug'=>'decupare-imagine',      'icon'=>'✂️', 'title'=>'Decupează imagine',            'desc'=>'Decupezi zona dorită cu mouse-ul sau coordonate exacte.'],
      ['slug'=>'imagini-la-pdf',        'icon'=>'📄','title'=>'Imagini → PDF',                'desc'=>'Transformi mai multe imagini JPG/PNG/WebP într-un singur PDF.'],
    ],
  ],
  'seo' => [
    'label'=>'SEO & Conținut', 'icon'=>'🔍', 'color'=>'#ede9fe',
    'items'=>[
      ['slug'=>'preview-google',     'icon'=>'👁️', 'title'=>'Preview rezultat Google',  'desc'=>'Cum apare pagina ta în Google — desktop și mobile, live.'],
      ['slug'=>'generator-slug',     'icon'=>'🔗','title'=>'Generator slug URL',        'desc'=>'Transformă text românesc în URL curat, fără diacritice.'],
      ['slug'=>'analizor-seo',       'icon'=>'📊','title'=>'Analizor titlu & meta',     'desc'=>'Scor SEO, bare de progres, preview Google live.'],
      ['slug'=>'densitate-cuvinte',  'icon'=>'📝','title'=>'Densitate cuvinte cheie',   'desc'=>'Frecvența cuvintelor dintr-un text, cu bare vizuale.'],
      ['slug'=>'calculator-cuvinte', 'icon'=>'⏱️', 'title'=>'Calculator cuvinte & timp','desc'=>'Numeri cuvintele și estimezi timpul de citire.'],
    ],
  ],
];

// ItemList schema — construit din array-ul de tooluri
$itemListElements = [];
$pos = 1;
foreach ($tools as $cat) {
    foreach ($cat['items'] as $t) {
        $itemListElements[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => $t['title'], 'url' => SITE_DOMAIN . '/tooluri/' . $t['slug'] . '/'];
    }
}
$schemaJson = json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'ItemList',
    'name'            => 'Tooluri gratuite online — GhidRomânesc',
    'description'     => $metaDescription,
    'url'             => SITE_DOMAIN . '/tooluri/',
    'numberOfItems'   => count($itemListElements),
    'itemListElement' => $itemListElements,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

require __DIR__ . '/../templates/header.php';
?>

<div class="page-header">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom:.5rem;color:rgba(255,255,255,.65);font-size:.85rem">
      <a href="/" style="color:inherit">Acasă</a>
      <span class="breadcrumb-sep" aria-hidden="true">›</span>
      <span aria-current="page">Tooluri</span>
    </nav>
    <h1 class="page-title">Tooluri gratuite online</h1>
    <p class="page-subtitle">Calculatoare financiare, verificatoare acte, PDF, imagini și SEO — toate în browser, fără upload.</p>
  </div>
</div>

<div class="container" style="padding-top:1.5rem;padding-bottom:.5rem">
  <p style="color:var(--text-muted);font-size:.9rem;line-height:1.7;max-width:680px">
    GhidRomânesc pune la dispoziție <strong><?= count($itemListElements) ?> tooluri gratuite</strong> pentru români: de la calculatorul de salariu net 2026, TVA și credit, până la verificatorul de CNP și IBAN, instrumente PDF, convertor imagini și tooluri SEO. Toate funcționează direct în browser — niciun fișier sau date personale nu ajung pe server.
  </p>
</div>

<div class="container" style="padding-bottom:4rem">

<?php foreach ($tools as $catKey => $cat): ?>
<section id="<?= $catKey ?>" style="margin-bottom:2.5rem">
  <h2 style="font-size:1.1rem;font-weight:800;color:var(--blue-dark);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;padding-bottom:.5rem;border-bottom:2px solid var(--border)">
    <span style="background:<?= $cat['color'] ?>;padding:.2rem .5rem;border-radius:6px"><?= $cat['icon'] ?></span>
    <?= $cat['label'] ?>
    <span style="font-size:.78rem;font-weight:400;color:var(--text-muted);margin-left:.25rem">(<?= count($cat['items']) ?> tooluri)</span>
  </h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:.85rem">
    <?php foreach ($cat['items'] as $t): ?>
    <a href="/tooluri/<?= $t['slug'] ?>/" style="display:flex;gap:.85rem;padding:1rem 1.1rem;border:1.5px solid var(--border);border-radius:12px;text-decoration:none;background:#fff;align-items:flex-start;transition:border-color .15s,box-shadow .15s" onmouseover="this.style.borderColor='#457b9d';this.style.boxShadow='0 3px 12px rgba(0,0,0,.07)'" onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
      <div style="font-size:1.4rem;line-height:1;flex-shrink:0;margin-top:.1rem"><?= $t['icon'] ?></div>
      <div>
        <div style="font-weight:700;color:var(--blue-dark);font-size:.9rem;margin-bottom:.2rem"><?= e($t['title']) ?></div>
        <div style="font-size:.78rem;color:var(--text-muted);line-height:1.4"><?= e($t['desc']) ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endforeach; ?>

<div style="background:#f0f4ff;border-radius:14px;padding:1.25rem 1.75rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
  <div style="font-size:1.5rem">🔐</div>
  <div>
    <div style="font-weight:700;color:var(--blue-dark)">Confidențialitate garantată</div>
    <div style="font-size:.82rem;color:var(--text-muted)">Toate toolurile procesează datele în browserul tău. Niciun fișier, text sau informație personală nu este trimis pe serverele noastre.</div>
  </div>
</div>

</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>
