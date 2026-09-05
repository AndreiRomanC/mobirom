<?php
$pageTitle       = 'Verificator IBAN România — validare și decodare gratuit — GhidRomânesc';
$metaDescription = 'Verifici dacă un IBAN este valid și afli banca emitentă pentru IBAN-urile românești. Gratuit și instant.';
$canonicalUrl    = SITE_DOMAIN . '/tooluri/verificator-iban/';

$toolFaq = [
    ['q' => 'Cum verifici dacă un IBAN românesc este valid?',
     'a' => 'Un IBAN românesc valid are exact 24 de caractere: RO (2 litere țară) + 2 cifre de control + 4 litere cod bancă + 16 cifre cont. Validarea matematică folosește algoritmul MOD 97 conform standardului ISO 13616.'],
    ['q' => 'Ce înseamnă cele 4 litere din IBAN-ul românesc?',
     'a' => 'Literele de pe pozițiile 5-8 reprezintă codul SWIFT al băncii. Exemple: BTRL = Banca Transilvania, INGB = ING Bank, BBRD = BRD, RNCB sau AAAA = BCR, RZBL = Raiffeisen, CECB = CEC Bank, TREZ = Trezorerie.'],
    ['q' => 'Câte caractere are IBAN-ul românesc?',
     'a' => 'Exact 24 de caractere. IBAN-ul României este printre cele mai scurte din Europa (de exemplu, cel german are 22, cel francez 27, cel britanic 22).'],
    ['q' => 'Un IBAN validat matematic garantează că există contul?',
     'a' => 'Nu. Validatorul verifică corectitudinea structurii și a cifrei de control conform algoritmului MOD 97, dar nu verifică existența reală a contului în baza de date a băncii.'],
];

$faqSchema = json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type'          => 'Question',
        'name'           => $f['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ], $toolFaq),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom:.5rem;color:rgba(255,255,255,.65);font-size:.85rem">
    <a href="/" style="color:inherit">Acasă</a>
    <span class="breadcrumb-sep" aria-hidden="true">›</span>
    <a href="/tooluri/" style="color:inherit">Tooluri</a>
    <span class="breadcrumb-sep" aria-hidden="true">›</span>
    <span aria-current="page">Verificator IBAN</span>
  </nav>
  <h1 class="page-title">🏦 Verificator IBAN România</h1>
  <p class="page-subtitle">Verifici dacă un IBAN este valid și identifici banca emitentă pentru România.</p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">
  <div class="form-group" style="margin-bottom:1.25rem">
    <label style="font-weight:700;color:var(--blue-dark)">IBAN</label>
    <input type="text" id="iban-input" class="form-control" maxlength="34" placeholder="ex: RO49AAAA1B31007593840000" oninput="verifyIBAN()" style="margin-top:.4rem;font-size:1rem;font-family:monospace;letter-spacing:.1em;text-transform:uppercase">
  </div>
  <div id="result"></div>
</div>
<div style="background:#f8fafc;border-radius:12px;padding:1.25rem 1.5rem;font-size:.85rem;color:var(--text-muted)">
  <strong style="color:var(--blue-dark)">Format IBAN România:</strong> RO + 2 cifre control + 4 litere cod bancă + 16 cifre cont = 24 caractere total
</div>
</div>

<script>
var BANCI = {
  'AAAA':'BCR — Banca Comercială Română','BACX':'Unicredit Bank','BBRD':'BRD — Groupe Société Générale',
  'BRDE':'BRD — Groupe Société Générale','BTRL':'Banca Transilvania','CARP':'Patria Bank',
  'CECB':'CEC Bank','DAFB':'Alpha Bank România','EXIM':'Exim Banca Românească','FNNB':'First Bank',
  'HVBL':'Banca Transilvania (vechi)','INGB':'ING Bank România','MIND':'Mindbank',
  'NBOM':'Marfin Bank','OTPV':'OTP Bank România','PIRB':'First Bank (Piraeus)',
  'PORL':'Porsche Bank România','RNCB':'BCR — Banca Comercială Română','RNBK':'Romanian National Bank',
  'RZBL':'Raiffeisen Bank','TREZ':'Trezorerie','UGBI':'Garanti BBVA Bank','WBAN':'Idea::Bank',
};

function mod97(str) {
  var remainder = '';
  for (var i=0;i<str.length;i++) {
    remainder = (remainder + str[i]);
    if (remainder.length > 9) remainder = (parseInt(remainder) % 97).toString();
  }
  return parseInt(remainder) % 97;
}

function verifyIBAN() {
  var raw = document.getElementById('iban-input').value.replace(/\s/g,'').toUpperCase();
  document.getElementById('iban-input').value = raw;
  var res = document.getElementById('result');
  if (raw.length < 4) { res.innerHTML=''; return; }

  // Rearanjare și conversie
  var rearranged = raw.substring(4) + raw.substring(0,4);
  var numeric = '';
  for (var i=0;i<rearranged.length;i++) {
    var c = rearranged.charCodeAt(i);
    if (c >= 65 && c <= 90) numeric += (c - 55).toString();
    else numeric += rearranged[i];
  }

  var valid = raw.length >= 15 && mod97(numeric) === 1;
  var country = raw.substring(0,2);
  var bankCode = raw.substring(4,8);
  var banca = raw.startsWith('RO') ? (BANCI[bankCode] || 'Bancă neidentificată ('+bankCode+')') : null;

  var box, info='';
  if (valid) {
    box = '<div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:.85rem 1.1rem;margin-bottom:1rem;font-weight:700;color:#15803d">✓ IBAN valid</div>';
    var items = [
      {l:'Țară', v: country === 'RO' ? '🇷🇴 România' : country},
      {l:'Lungime', v: raw.length + ' caractere'},
    ];
    if (banca) items.push({l:'Bancă', v: banca});
    items.push({l:'IBAN formatat', v: raw.match(/.{1,4}/g).join(' ')});
    info = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">'+items.map(function(it){return '<div style="background:#f8fafc;border-radius:8px;padding:.6rem .85rem"><div style="font-size:.72rem;text-transform:uppercase;color:var(--text-muted);margin-bottom:.15rem">'+it.l+'</div><div style="font-weight:700;color:var(--blue-dark);font-size:.9rem">'+it.v+'</div></div>';}).join('')+'</div>';
  } else {
    box = '<div style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;padding:.85rem 1.1rem;font-weight:700;color:#dc2626">✕ IBAN invalid' + (raw.length < 15 ? ' — prea scurt' : ' — cifra de control greșită') + '</div>';
  }
  res.innerHTML = box + info;
}
</script>
<?php
// ── FAQ ─────���────────────────────────────────────────────────────
?>
<div class="container-sm" style="padding-bottom:1.5rem">
  <h2 style="font-size:1.1rem;font-weight:800;color:var(--blue-dark);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid var(--border)">
    Întrebări frecvente — verificator IBAN România
  </h2>
  <div style="display:flex;flex-direction:column;gap:.5rem">
    <?php foreach ($toolFaq as $item): ?>
    <details style="border:1px solid var(--border);border-radius:10px;overflow:hidden">
      <summary style="padding:.875rem 1rem;cursor:pointer;font-weight:600;font-size:.9rem;color:var(--blue-dark);list-style:none;display:flex;justify-content:space-between;align-items:center;user-select:none">
        <?= e($item['q']) ?><span style="font-size:.75rem;color:var(--text-muted);margin-left:.5rem;flex-shrink:0">▸</span>
      </summary>
      <div style="padding:.875rem 1rem;font-size:.875rem;color:var(--text-muted);line-height:1.7;border-top:1px solid var(--border);background:#fafafa">
        <?= e($item['a']) ?>
      </div>
    </details>
    <?php endforeach; ?>
  </div>
</div>

<div class="container-sm" style="padding-bottom:3rem">
  <h2 style="font-size:1rem;font-weight:700;color:var(--blue-dark);margin-bottom:.875rem">Tooluri similare</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem">
    <?php foreach ([
      ['slug'=>'verificator-cnp',   'icon'=>'🪪','title'=>'Verificator CNP'],
      ['slug'=>'salariu-net',       'icon'=>'💰','title'=>'Calculator salariu net'],
      ['slug'=>'calculator-credit', 'icon'=>'🏦','title'=>'Calculator rată credit'],
      ['slug'=>'calculator-tva',    'icon'=>'🧮','title'=>'Calculator TVA'],
    ] as $r): ?>
    <a href="/tooluri/<?= $r['slug'] ?>/" style="display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;border:1.5px solid var(--border);border-radius:10px;text-decoration:none;background:#fff;font-size:.875rem;font-weight:600;color:var(--blue-dark);transition:border-color .15s" onmouseover="this.style.borderColor='#457b9d'" onmouseout="this.style.borderColor='var(--border)'">
      <span><?= $r['icon'] ?></span><?= e($r['title']) ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/../../templates/footer.php'; ?>
