<?php
$pageTitle       = 'Calculator salariu net din brut România 2026 — GhidRomânesc';
$metaDescription = 'Calculezi salariul net din brut în România 2026. Include CAS 25%, CASS 10%, impozit 10% și deducere personală. Rapid și gratuit.';
$canonicalUrl    = SITE_DOMAIN . '/tooluri/salariu-net/';

$toolFaq = [
    ['q' => 'Cum se calculează salariul net din brut în România 2026?',
     'a' => 'Din salariul brut se scad: CAS (contribuție pensie) 25%, CASS (sănătate) 10% și impozit pe venit 10% aplicat la baza impozabilă (brut minus CAS, CASS și deducerea personală). Exemplu: 5.000 RON brut → ~3.240 RON net.'],
    ['q' => 'Ce este deducerea personală în 2026?',
     'a' => 'Deducerea personală este de 300 RON/lună pentru salarii brute sub 3.000 RON și scade liniar la 0 pentru salarii brute de 5.000 RON sau mai mari. Reduce baza de calcul a impozitului.'],
    ['q' => 'Angajații IT mai sunt scutiți de impozit pe venit în 2026?',
     'a' => 'Nu. Scutirea de impozit pe venit pentru angajații din IT a fost eliminată începând cu 2026 (prin modificările fiscale introduse în a doua jumătate a anului 2024). Din 2026, angajații IT plătesc impozit pe venit 10% la fel ca orice alt angajat.'],
    ['q' => 'Care este salariul minim net în 2026?',
     'a' => 'Salariul minim brut în 2026 este de 4.050 RON, echivalent cu aproximativ 2.685 RON net (pentru angajat normal, fără deducere personală completă).'],
    ['q' => 'Cum calculez brut din net?',
     'a' => 'Calculatorul nostru include și calculul invers (Net → Brut): selectezi tab-ul „Net → Brut", introduci suma netă și primești salariul brut estimat, calculat prin iterație numerică.'],
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
    <span aria-current="page">Calculator salariu net</span>
  </nav>
  <h1 class="page-title">💰 Calculator salariu net România 2026</h1>
  <p class="page-subtitle">Calculezi salariul net din brut sau invers. Include toate reținerile legale 2026.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div style="display:flex;gap:.5rem;background:#f1f5f9;padding:.3rem;border-radius:8px;margin-bottom:1.5rem">
    <button id="tab-brut" onclick="setTab('brut')" style="flex:1;padding:.45rem;border:none;border-radius:6px;background:#fff;font-weight:700;font-size:.85rem;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.1)">Brut → Net</button>
    <button id="tab-net" onclick="setTab('net')" style="flex:1;padding:.45rem;border:none;border-radius:6px;background:none;font-weight:600;font-size:.85rem;cursor:pointer;color:var(--text-muted)">Net → Brut</button>
  </div>

  <div class="form-group" style="margin-bottom:1rem">
    <label style="font-weight:700;color:var(--blue-dark)" id="main-label">Salariu brut (RON)</label>
    <input type="number" id="main-input" class="form-control" value="5000" min="0" oninput="calculate()" style="margin-top:.4rem;font-size:1.1rem;font-weight:700">
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
    <div>
      <label style="font-size:.8rem;font-weight:600;color:var(--blue-dark)">An fiscal</label>
      <select id="an" class="form-control" onchange="calculate()" style="margin-top:.3rem">
        <option value="2026" selected>2026</option>
        <option value="2025">2025</option>
      </select>
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:600;color:var(--blue-dark)">Tip angajat</label>
      <select id="tip" class="form-control" onchange="calculate()" style="margin-top:.3rem">
        <option value="normal">Normal</option>
        <option value="constructii">Construcții</option>
      </select>
    </div>
  </div>

  <!-- Rezultat -->
  <div id="result" style="background:#f0f4ff;border-radius:12px;padding:1.25rem;margin-bottom:1.25rem">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
      <span style="font-size:.9rem;color:var(--text-muted)" id="result-label">Salariu net</span>
      <span id="result-value" style="font-size:2rem;font-weight:900;color:var(--blue-dark)">0 RON</span>
    </div>
    <div style="display:flex;flex-direction:column;gap:.35rem" id="breakdown"></div>
  </div>

  <div style="background:#f8fafc;border-radius:10px;padding:1rem;font-size:.78rem;color:var(--text-muted)">
    <strong style="color:var(--blue-dark)">Rate 2026:</strong> CAS angajat 25% · CASS angajat 10% · Impozit venit 10% · Deducere personală: 300 RON (sub 3.000 RON brut), 0 RON (peste 5.000 RON brut)<br>
    <strong style="color:var(--blue-dark)">Salariu minim 2026:</strong> 4.050 RON brut / ~2.685 RON net · <strong style="color:#dc2626">Notă:</strong> scutirea de impozit pentru IT a fost eliminată din 2026.
  </div>
</div>
</div>

<script>
var mode = 'brut';

function setTab(t) {
  mode = t;
  document.getElementById('tab-brut').style.background = t==='brut'?'#fff':'none';
  document.getElementById('tab-brut').style.color = t==='brut'?'inherit':'var(--text-muted)';
  document.getElementById('tab-net').style.background = t==='net'?'#fff':'none';
  document.getElementById('tab-net').style.color = t==='net'?'inherit':'var(--text-muted)';
  document.getElementById('main-label').textContent = t==='brut'?'Salariu brut (RON)':'Salariu net (RON)';
  document.getElementById('result-label').textContent = t==='brut'?'Salariu net':'Salariu brut estimat';
  calculate();
}

function getDeductie(brut) {
  if (brut <= 3000) return 300;
  if (brut >= 5000) return 0;
  return Math.round(300 * (5000 - brut) / 2000);
}

function calcNet(brut, tip) {
  var cas, cass, ded, baza, impozit, net;
  if (tip === 'constructii') {
    cas = Math.round(brut * 0.2125);
    cass = Math.round(brut * 0.10);
    impozit = 0;
  } else {
    cas = Math.round(brut * 0.25);
    cass = Math.round(brut * 0.10);
    ded = getDeductie(brut);
    baza = Math.max(0, brut - cas - cass - (ded || 0));
    impozit = Math.round(baza * 0.10);
  }
  ded = ded || 0;
  net = brut - cas - cass - impozit;
  return { brut: brut, cas: cas, cass: cass, ded: ded, impozit: impozit, net: net };
}

function calculate() {
  var val = parseFloat(document.getElementById('main-input').value) || 0;
  var tip = document.getElementById('tip').value;
  var r;

  if (mode === 'brut') {
    r = calcNet(val, tip);
  } else {
    // Aproximare brut din net prin iteratie
    var brut = val / 0.65;
    for (var i = 0; i < 20; i++) {
      var test = calcNet(brut, tip);
      if (Math.abs(test.net - val) < 0.5) break;
      brut = brut + (val - test.net) * 0.9;
    }
    r = calcNet(Math.round(brut), tip);
  }

  var mainVal = mode === 'brut' ? r.net : r.brut;
  document.getElementById('result-value').textContent = mainVal.toLocaleString('ro') + ' RON';

  var pct = function(v, base) { return base > 0 ? ' (' + (v/base*100).toFixed(1) + '%)' : ''; };
  var rows = [
    {l: 'Salariu brut', v: r.brut, c: '#1d3557'},
    {l: '— CAS angajat (25%)', v: -r.cas, c: '#dc2626'},
    {l: '— CASS angajat (10%)', v: -r.cass, c: '#dc2626'},
    {l: '— Impozit venit (10%)', v: -r.impozit, c: '#dc2626'},
    {l: '✓ Deducere personală', v: r.ded, c: '#16a34a'},
    {l: '= Salariu net', v: r.net, c: '#16a34a', bold: true},
  ];
  document.getElementById('breakdown').innerHTML = rows.map(function(row) {
    return '<div style="display:flex;justify-content:space-between;font-size:.85rem;padding:.2rem 0;border-top:1px solid #e2e8f0;' + (row.bold?'font-weight:800;':'') + '">' +
      '<span style="color:' + (row.bold?'var(--blue-dark)':'var(--text-muted)') + '">' + row.l + '</span>' +
      '<span style="color:' + row.c + '">' + (row.v>0?'+':'') + Math.round(row.v).toLocaleString('ro') + ' RON</span></div>';
  }).join('');
}
calculate();
</script>
<?php
// ── FAQ ──────────────────────────────────────────────────────────
?>
<div class="container-sm" style="padding-bottom:1.5rem">
  <h2 style="font-size:1.1rem;font-weight:800;color:var(--blue-dark);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid var(--border)">
    Întrebări frecvente — calculator salariu net
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
      ['slug'=>'calculator-tva',       'icon'=>'🧮','title'=>'Calculator TVA 2026'],
      ['slug'=>'concediu-medical',     'icon'=>'🏥','title'=>'Calculator concediu medical'],
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
