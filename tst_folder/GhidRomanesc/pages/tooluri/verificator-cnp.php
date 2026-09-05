<?php
$pageTitle       = 'Verificator și decodor CNP România — GhidRomânesc';
$metaDescription = 'Verifici dacă un CNP românesc este valid și afli data nașterii, sexul, județul și vârsta. Gratuit și instant.';
$canonicalUrl    = SITE_DOMAIN . '/tooluri/verificator-cnp/';

$toolFaq = [
    ['q' => 'Din ce este format CNP-ul românesc?',
     'a' => 'CNP are 13 cifre cu structura S-AA-LL-ZZ-JJ-NNN-C: S=sex și secol (1-9), AA=ultimele 2 cifre ale anului nașterii, LL=luna, ZZ=ziua, JJ=codul județului, NNN=număr de ordine, C=cifra de control calculată.'],
    ['q' => 'Ce înseamnă prima cifră din CNP?',
     'a' => '1=bărbat născut 1900-1999, 2=femeie 1900-1999, 3=bărbat 1800-1899, 4=femeie 1800-1899, 5=bărbat 2000-2099, 6=femeie 2000-2099, 7=bărbat rezident străin, 8=femeie rezident străin, 9=persoană cu CNP alocat special.'],
    ['q' => 'Cum se calculează cifra de control din CNP?',
     'a' => 'Se înmulțesc primele 12 cifre cu coeficienții [2,7,9,1,4,6,3,5,8,2,7,9], se adună produsele, se calculează restul împărțirii la 11. Dacă restul e 10, cifra de control e 1; altfel cifra de control e restul obținut.'],
    ['q' => 'Codul județului din CNP este același cu codul de pe mașini?',
     'a' => 'Nu. CNP-ul folosește un cod numeric propriu (01=Alba, 02=Arad, ..., 40=București, 41-46=Sectoarele 1-6), diferit de abrevierile de pe plăcuțele de înmatriculare (AB, AR, IF etc.).'],
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
    <span aria-current="page">Verificator CNP</span>
  </nav>
  <h1 class="page-title">🪪 Verificator CNP România</h1>
  <p class="page-subtitle">Verifici dacă un CNP este valid și afli informațiile conținute în el.</p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div class="form-group" style="margin-bottom:1.25rem">
    <label style="font-weight:700;color:var(--blue-dark)">CNP de verificat</label>
    <input type="text" id="cnp-input" class="form-control" maxlength="13" placeholder="ex: 1901215123456" oninput="verifyCNP()" style="margin-top:.4rem;font-size:1.25rem;font-family:monospace;letter-spacing:.15em">
    <div id="cnp-len" style="font-size:.78rem;color:var(--text-muted);margin-top:.3rem">0 / 13 cifre</div>
  </div>

  <div id="result" style="display:none">
    <div id="validity-box" style="border-radius:10px;padding:.85rem 1.1rem;margin-bottom:1.25rem;font-weight:700;font-size:1rem"></div>
    <div id="info-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem"></div>
  </div>
</div>

<div style="background:#f8fafc;border-radius:12px;padding:1.25rem 1.5rem;font-size:.85rem;color:var(--text-muted)">
  <strong style="color:var(--blue-dark)">Structura CNP:</strong> S-AA-LL-ZZ-JJ-NNN-C<br>
  S=sex/secol · AA=an naștere · LL=lună · ZZ=zi · JJ=județ · NNN=număr ordine · C=cifră control
</div>
</div>

<script>
var JUDETE = {
  '01':'Alba','02':'Arad','03':'Argeș','04':'Bacău','05':'Bihor','06':'Bistrița-Năsăud','07':'Botoșani','08':'Brașov','09':'Brăila','10':'Buzău',
  '11':'Caraș-Severin','12':'Cluj','13':'Constanța','14':'Covasna','15':'Dâmbovița','16':'Dolj','17':'Galați','18':'Gorj','19':'Harghita','20':'Hunedoara',
  '21':'Ialomița','22':'Iași','23':'Ilfov','24':'Maramureș','25':'Mehedinți','26':'Mureș','27':'Neamț','28':'Olt','29':'Prahova','30':'Satu Mare',
  '31':'Sălaj','32':'Sibiu','33':'Suceava','34':'Teleorman','35':'Timiș','36':'Tulcea','37':'Vaslui','38':'Vâlcea','39':'Vrancea','40':'București',
  '41':'Sector 1','42':'Sector 2','43':'Sector 3','44':'Sector 4','45':'Sector 5','46':'Sector 6',
  '51':'Călărași','52':'Giurgiu'
};

function verifyCNP() {
  var cnp = document.getElementById('cnp-input').value.replace(/\D/g,'');
  document.getElementById('cnp-len').textContent = cnp.length + ' / 13 cifre';
  if (cnp.length < 13) { document.getElementById('result').style.display='none'; return; }

  // Cifra de control
  var weights = [2,7,9,1,4,6,3,5,8,2,7,9];
  var sum = 0;
  for (var i=0;i<12;i++) sum += parseInt(cnp[i]) * weights[i];
  var ctrl = sum % 11;
  if (ctrl === 10) ctrl = 1;
  var valid = ctrl === parseInt(cnp[12]);

  // Decodare
  var s = parseInt(cnp[0]);
  var sex, century;
  if ([1,2].includes(s)) { sex=s===1?'Masculin':'Feminin'; century=1900; }
  else if ([3,4].includes(s)) { sex=s===3?'Masculin':'Feminin'; century=1800; }
  else if ([5,6].includes(s)) { sex=s===5?'Masculin':'Feminin'; century=2000; }
  else if ([7,8].includes(s)) { sex=s===7?'Masculin':'Feminin'; century=1900; }
  else if (s===9) { sex='Necunoscut'; century=2000; }
  else { sex='Necunoscut'; century=2000; }

  var an = century + parseInt(cnp.substring(1,3));
  var luna = parseInt(cnp.substring(3,5));
  var zi = parseInt(cnp.substring(5,7));
  var judetCode = cnp.substring(7,9);
  var judet = JUDETE[judetCode] || 'Necunoscut ('+judetCode+')';
  var nrOrdine = cnp.substring(9,12);

  var azi = new Date();
  var nastere = new Date(an, luna-1, zi);
  var varsta = azi.getFullYear() - an - (azi < new Date(azi.getFullYear(), luna-1, zi) ? 1 : 0);

  // Validare dată
  var dataValida = nastere.getFullYear()===an && nastere.getMonth()===luna-1 && nastere.getDate()===zi;

  var box = document.getElementById('validity-box');
  if (valid && dataValida) {
    box.style.background='#f0fdf4'; box.style.color='#15803d'; box.style.border='1.5px solid #bbf7d0';
    box.textContent='✓ CNP valid';
  } else {
    box.style.background='#fef2f2'; box.style.color='#dc2626'; box.style.border='1.5px solid #fecaca';
    box.textContent='✕ CNP invalid' + (!dataValida?' — data nașterii incorectă':'');
  }

  var luni = ['ianuarie','februarie','martie','aprilie','mai','iunie','iulie','august','septembrie','octombrie','noiembrie','decembrie'];
  var items = [
    {l:'Data nașterii', v: dataValida ? zi+' '+luni[luna-1]+' '+an : 'Dată invalidă'},
    {l:'Vârsta', v: dataValida ? varsta+' ani' : '—'},
    {l:'Sex', v: sex},
    {l:'Județ / Sector', v: judet},
    {l:'Nr. ordine', v: nrOrdine},
    {l:'Cifră control', v: cnp[12]+' '+(valid?'✓':'✕')},
  ];
  document.getElementById('info-grid').innerHTML = items.map(function(it){
    return '<div style="background:#f8fafc;border-radius:8px;padding:.65rem .85rem"><div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted);margin-bottom:.2rem">'+it.l+'</div><div style="font-weight:700;color:var(--blue-dark)">'+it.v+'</div></div>';
  }).join('');
  document.getElementById('result').style.display='';
}
</script>
<?php
// ── FAQ ──────────────────────────────────────────────────────────
?>
<div class="container-sm" style="padding-bottom:1.5rem">
  <h2 style="font-size:1.1rem;font-weight:800;color:var(--blue-dark);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid var(--border)">
    Întrebări frecvente — verificator CNP România
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
      ['slug'=>'verificator-iban',  'icon'=>'🏦','title'=>'Verificator IBAN'],
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
