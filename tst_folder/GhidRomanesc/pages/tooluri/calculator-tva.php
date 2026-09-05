<?php
$pageTitle       = 'Calculator TVA România 2026 — cotele 21% și 11% — GhidRomânesc';
$metaDescription = 'Calculator TVA România 2026: cotă standard 21%, cotă redusă 11% (alimente, medicamente, HoReCa). Adaugi sau extragi TVA instant. Date oficiale ANAF.';
$canonicalUrl    = SITE_DOMAIN . '/tooluri/calculator-tva/';

$toolFaq = [
    ['q' => 'Care sunt cotele TVA în România în 2026?',
     'a' => 'Din 1 august 2025, România aplică două cote principale: 21% cota standard (majoritate bunuri și servicii) și 11% cota redusă (alimente, apă, medicamente, HoReCa, cazare, cărți, bilete, lemne de foc). Cotele de 19%, 9% și 5% au fost eliminate. Se menține temporar 9% pentru locuințe noi cu condiții stricte, până la 31 iulie 2026.'],
    ['q' => 'De ce s-a schimbat TVA-ul din 2025?',
     'a' => 'Prin Legea 141/2025 (în vigoare din 1 august 2025), România a simplificat sistemul de TVA: cota standard a crescut de la 19% la 21%, iar cotele reduse de 9% și 5% au fost unificate într-o singură cotă redusă de 11%. Modificarea face parte dintr-un pachet de ajustări fiscale.'],
    ['q' => 'Cum adaugi TVA la un preț fără TVA?',
     'a' => 'Înmulțești prețul fără TVA cu (1 + cota/100). Exemplu cotă 21%: 100 RON × 1,21 = 121 RON cu TVA. Exemplu cotă 11%: 100 RON × 1,11 = 111 RON cu TVA.'],
    ['q' => 'Cum extragi TVA dintr-un preț cu TVA inclus?',
     'a' => 'Împarți prețul cu (1 + cota/100). Exemplu cotă 21%: 121 RON ÷ 1,21 = 100 RON fără TVA, TVA = 21 RON. Exemplu cotă 11%: 111 RON ÷ 1,11 = 100 RON fără TVA, TVA = 11 RON.'],
    ['q' => 'TVA la restaurant și alimente în 2026?',
     'a' => 'Restaurantele (HoReCa cu servire la masă), alimentele de bază, apa potabilă și minerală intră la cota redusă de 11%. Băuturile alcoolice și băuturile răcoritoare cu zahăr adăugat rămân la cota standard de 21%.'],
    ['q' => 'Care este pragul de înregistrare în scopuri de TVA în 2026?',
     'a' => 'Din septembrie 2025, firmele cu cifră de afaceri anuală sub 395.000 RON (echivalent ~88.500 EUR) pot aplica regimul de scutire TVA (anterior pragul era 300.000 RON).'],
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
    <span aria-current="page">Calculator TVA</span>
  </nav>
  <h1 class="page-title">🧮 Calculator TVA România 2026</h1>
  <p class="page-subtitle">Adaugi sau extragi TVA instant. Cote în vigoare: 21% standard, 11% redusă.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">

<!-- Notificare schimbare cote -->
<div style="background:#fff7ed;border:1.5px solid #fb923c;border-radius:12px;padding:.875rem 1.1rem;margin-bottom:1.5rem;font-size:.85rem;line-height:1.6">
  ⚠️ <strong>Modificare majoră:</strong> Din <strong>1 august 2025</strong> (Legea 141/2025), cotele TVA s-au schimbat:
  <strong>19% → 21%</strong> (standard) și <strong>9%/5% → 11%</strong> (redusă unificată).
</div>

<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Cotă TVA</label>
      <select id="cota" class="form-control" onchange="calc()" style="margin-top:.3rem">
        <option value="21">21% — cotă standard</option>
        <option value="11">11% — cotă redusă</option>
        <option value="9">9% — locuințe (temp. până 31.07.2026)</option>
      </select>
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Operație</label>
      <select id="op" class="form-control" onchange="updateLabel();calc()" style="margin-top:.3rem">
        <option value="add">Adaugă TVA (fără → cu TVA)</option>
        <option value="extract">Extrage TVA (cu → fără TVA)</option>
      </select>
    </div>
  </div>

  <div class="form-group" style="margin-bottom:1.5rem">
    <label style="font-weight:700;color:var(--blue-dark)" id="input-label">Sumă fără TVA (RON)</label>
    <input type="number" id="suma" class="form-control" value="100" min="0" step="0.01" oninput="calc()" style="margin-top:.4rem;font-size:1.25rem;font-weight:700">
  </div>

  <div style="background:#f0f4ff;border-radius:12px;padding:1.25rem;display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;text-align:center;margin-bottom:1.25rem">
    <div>
      <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.3rem">Fără TVA</div>
      <div id="r-fara" style="font-size:1.25rem;font-weight:800;color:var(--blue-dark)">100,00 RON</div>
    </div>
    <div style="border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0">
      <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.3rem">Valoare TVA</div>
      <div id="r-tva" style="font-size:1.25rem;font-weight:800;color:#dc2626">21,00 RON</div>
    </div>
    <div>
      <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.3rem">Cu TVA</div>
      <div id="r-cu" style="font-size:1.25rem;font-weight:800;color:#16a34a">121,00 RON</div>
    </div>
  </div>

  <div id="cota-info" style="background:#f8fafc;border-radius:10px;padding:.875rem 1rem;font-size:.82rem;color:var(--text-muted)"></div>
</div>

<!-- Tabel cote -->
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.5rem">
  <h2 style="font-size:.95rem;font-weight:700;color:var(--blue-dark);margin-bottom:1rem">Cotele TVA în vigoare — România 2026 (din 1 aug. 2025)</h2>
  <div style="display:flex;flex-direction:column;gap:.6rem;font-size:.85rem">

    <div style="padding:.75rem .875rem;border-radius:8px;background:#fef2f2;border-left:3px solid #dc2626">
      <strong style="color:#dc2626">21% — Cotă standard</strong><br>
      <span style="color:var(--text-muted)">Electronice, îmbrăcăminte, mobilă, servicii profesionale, construcții, electricitate, panouri solare, alcool, băuturi răcoritoare cu zahăr, suplimente alimentare, tutun, veterinare, petreceri și evenimente.</span>
    </div>

    <div style="padding:.75rem .875rem;border-radius:8px;background:#fff7ed;border-left:3px solid #f59e0b">
      <strong style="color:#b45309">11% — Cotă redusă</strong><br>
      <span style="color:var(--text-muted)">Alimente de bază (fără alcool), apă potabilă și minerală, medicamente pentru uz uman, produse ortopedice, servicii HoReCa (restaurant, catering), cazare hoteluri și camping, cărți și manuale, ziare și reviste, bilete spectacole/muzee/cinema/sport, lemne de foc, energie termică (nov–mar), îngrășăminte și pesticide agricole.</span>
    </div>

    <div style="padding:.75rem .875rem;border-radius:8px;background:#f0fdf4;border-left:3px solid #16a34a">
      <strong style="color:#15803d">9% — Temporar pentru locuințe (până la 31 iulie 2026)</strong><br>
      <span style="color:var(--text-muted)">Se aplică exclusiv pentru livrarea de locuințe noi cu suprafață utilă ≤ 120 m² și valoare ≤ 600.000 lei, cu contract semnat înainte de 1 august 2025. <strong>Nu se mai aplică</strong> altor categorii (alimente, medicamente etc.).</span>
    </div>

  </div>

  <div style="margin-top:1rem;padding:.75rem .875rem;background:#eff6ff;border-radius:8px;font-size:.82rem;color:#1e40af">
    📋 <strong>Prag înregistrare TVA 2026:</strong> Cifra de afaceri anuală sub <strong>395.000 RON</strong> (~88.500 EUR) — regim de scutire disponibil (anterior era 300.000 RON).
  </div>
</div>
</div>

<script>
var INFOS = {
  '21': '📌 Cotă standard 21% — se aplică tuturor bunurilor și serviciilor care nu sunt incluse explicit în cota redusă.',
  '11': '📌 Cotă redusă 11% — alimente, apă, medicamente, HoReCa, cazare, cărți, bilete cultură/sport, lemne de foc.',
  '9':  '📌 Cotă temporară 9% — exclusiv locuințe noi ≤ 120 m² și ≤ 600.000 lei cu condiții stricte, până la 31 iulie 2026.'
};

function updateLabel() {
  var op = document.getElementById('op').value;
  document.getElementById('input-label').textContent = op === 'add' ? 'Sumă fără TVA (RON)' : 'Sumă cu TVA (RON)';
}

function fmt(v) { return v.toFixed(2).replace('.', ',') + ' RON'; }

function calc() {
  var suma = parseFloat(document.getElementById('suma').value) || 0;
  var cota = parseFloat(document.getElementById('cota').value) / 100;
  var op = document.getElementById('op').value;
  var fara, tva, cu;
  if (op === 'add') {
    fara = suma; cu = suma * (1 + cota); tva = cu - fara;
  } else {
    cu = suma; fara = cu / (1 + cota); tva = cu - fara;
  }
  document.getElementById('r-fara').textContent = fmt(fara);
  document.getElementById('r-tva').textContent  = fmt(tva);
  document.getElementById('r-cu').textContent   = fmt(cu);
  document.getElementById('cota-info').textContent = INFOS[document.getElementById('cota').value];
}
calc();
</script>

<!-- FAQ -->
<div class="container-sm" style="padding-bottom:1.5rem">
  <h2 style="font-size:1.1rem;font-weight:800;color:var(--blue-dark);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid var(--border)">
    Întrebări frecvente — calculator TVA România 2026
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
      ['slug'=>'salariu-net',          'icon'=>'💰','title'=>'Calculator salariu net'],
      ['slug'=>'calculator-credit',    'icon'=>'🏦','title'=>'Calculator rată credit'],
      ['slug'=>'calculator-pfa',       'icon'=>'💼','title'=>'Calculator impozit PFA'],
      ['slug'=>'calculator-dividende', 'icon'=>'📈','title'=>'Calculator dividende'],
    ] as $r): ?>
    <a href="/tooluri/<?= $r['slug'] ?>/" style="display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;border:1.5px solid var(--border);border-radius:10px;text-decoration:none;background:#fff;font-size:.875rem;font-weight:600;color:var(--blue-dark);transition:border-color .15s" onmouseover="this.style.borderColor='#457b9d'" onmouseout="this.style.borderColor='var(--border)'">
      <span><?= $r['icon'] ?></span><?= e($r['title']) ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/../../templates/footer.php'; ?>
