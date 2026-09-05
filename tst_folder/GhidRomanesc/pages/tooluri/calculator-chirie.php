<?php
$pageTitle       = 'Calculator impozit chirii 2026 România — GhidRomânesc';
$metaDescription = 'Calculezi impozitul pe chirii în România 2026: impozit 10%, CAS, CASS, cotă forfetară 20%. Afli dacă trebuie să depui Declarația Unică D212. Gratuit.';
$canonicalUrl    = SITE_DOMAIN . '/tooluri/calculator-chirie/';

$toolFaq = [
    ['q' => 'Cum se calculează impozitul pe chirii în 2026?',
     'a' => 'Din chiria brută se scade cota forfetară de 20%, obținând venitul net. Impozitul este 10% aplicat la venitul net (efectiv 8% din chiria brută). Exemplu: 1.000 RON/lună × 12 luni = 12.000 RON brut → venit net 9.600 RON → impozit 960 RON/an.'],
    ['q' => 'Ce este cota forfetară de 20%?',
     'a' => 'Cota forfetară este o deducere fixă de 20% din venitul brut, care reprezintă cheltuielile aferente locuinței închiriate (uzură, reparații, etc.), fără a necesita documente justificative. Din 2024, cota a scăzut de la 40% la 20%.'],
    ['q' => 'Trebuie să plătesc CAS și CASS pe chirii?',
     'a' => 'Da, din 2024, chiriile sunt incluse la „alte venituri" care intră în calculul contribuțiilor sociale. CAS (24,75%) se datorează dacă venitul net anual depășește 12 × salariul minim brut (48.600 RON în 2026), calculat la plafonul respectiv. CASS (10%) se datorează de la 6 × salariul minim (24.300 RON), tot calculat la plafonul fix.'],
    ['q' => 'Ce este Declarația Unică D212 și când se depune?',
     'a' => 'D212 este declarația prin care îți raportezi veniturile din chirii și calculezi impozitele și contribuțiile datorate. Se depune online la ANAF (e-Declarații sau SPV) până pe 25 mai 2026, atât pentru veniturile din 2025 (realizate), cât și pentru cele estimate din 2026.'],
    ['q' => 'Dacă am și salariu, mai plătesc CAS și CASS pe chirii?',
     'a' => 'Dacă ești angajat, CAS este deja plătit prin angajator și nu îl datorezi din nou pe venituri din chirii. CASS se poate datora suplimentar dacă venitul din chirii, cumulat cu alte venituri extrasalariale, depășește pragurile. Situația exactă depinde de totalul veniturilor — consultați un contabil.'],
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
    <span aria-current="page">Calculator impozit chirii</span>
  </nav>
  <h1 class="page-title">🏠 Calculator impozit chirii 2026</h1>
  <p class="page-subtitle">Calculezi impozitul, CAS și CASS pe veniturile din chirii. Include D212.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">

<!-- Card inputuri -->
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.75rem 2rem;margin-bottom:1.25rem">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem" id="input-grid">

    <div class="form-group">
      <label style="font-weight:700;color:var(--blue-dark);display:block;margin-bottom:.4rem">
        Chirie lunară (RON)
      </label>
      <input type="number" id="chirie" class="form-control"
        value="1500" min="0" max="999999" step="50"
        oninput="calc()" style="font-size:1.15rem;font-weight:700">
    </div>

    <div class="form-group">
      <label style="font-weight:700;color:var(--blue-dark);display:block;margin-bottom:.4rem">
        Luni închiriate: <span id="luni-val" style="color:#457b9d">12</span>
      </label>
      <input type="range" id="luni" min="1" max="12" value="12"
        oninput="document.getElementById('luni-val').textContent=this.value;calc()"
        style="width:100%;height:6px;margin-top:.6rem;accent-color:#457b9d">
      <div style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--text-muted);margin-top:.2rem">
        <span>1 lună</span><span>6 luni</span><span>12 luni</span>
      </div>
    </div>

  </div>

  <label style="display:flex;align-items:flex-start;gap:.65rem;cursor:pointer;padding:.85rem 1rem;background:#fffbeb;border:1px solid #fcd34d;border-radius:10px;font-size:.875rem">
    <input type="checkbox" id="alte-venituri" onchange="calc()" style="margin-top:.15rem;width:16px;height:16px;flex-shrink:0;accent-color:#457b9d">
    <span>
      <strong style="color:var(--blue-dark)">Am și alte venituri extrasalariale</strong>
      <span style="display:block;color:var(--text-muted);font-size:.8rem;margin-top:.15rem">(PFA, dividende, drepturi autor, alte chirii etc.)</span>
    </span>
  </label>

  <div id="warn-alte" style="display:none;margin-top:.875rem;background:#fef3c7;border-left:4px solid #f59e0b;border-radius:0 8px 8px 0;padding:.75rem 1rem;font-size:.85rem;color:#92400e;line-height:1.6">
    ⚠️ <strong>Atenție:</strong> CAS și CASS se calculează cumulat din toate veniturile extrasalariale (chirii + PFA + dividende etc.). Calculatorul de față estimează <em>doar</em> contribuțiile pe veniturile din chirii introduse. Consultați un contabil pentru situația exactă.
  </div>

</div>

<!-- Rezultate -->
<div id="rezultate" style="background:#fff;border:1.5px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:1.25rem">

  <div style="background:linear-gradient(135deg,#1d3557,#2b4f76);padding:1rem 1.5rem;display:flex;align-items:center;gap:.75rem">
    <span style="font-size:1.1rem">📊</span>
    <span style="font-weight:800;color:#fff;font-size:.95rem">Calcul impozit chirii 2026</span>
  </div>

  <table style="width:100%;border-collapse:collapse;font-size:.9rem">
    <tbody>
    <tr style="background:#f8fafc">
      <td style="padding:.8rem 1.25rem;color:var(--text-muted);font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;width:55%">Venit brut anual (chirie × luni)</td>
      <td style="padding:.8rem 1.25rem;text-align:right;font-weight:700;color:var(--blue-dark)" id="r-brut">—</td>
    </tr>
    <tr>
      <td style="padding:.8rem 1.25rem;color:var(--text-muted);font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em">Cotă forfetară dedusă (20%)</td>
      <td style="padding:.8rem 1.25rem;text-align:right;color:#dc2626;font-weight:600" id="r-forfetara">—</td>
    </tr>
    <tr style="background:#f0f4ff">
      <td style="padding:.8rem 1.25rem;color:var(--blue-dark);font-weight:700;font-size:.875rem">Venit net anual impozabil</td>
      <td style="padding:.8rem 1.25rem;text-align:right;font-weight:800;color:var(--blue-dark);font-size:1rem" id="r-net">—</td>
    </tr>
    <tr style="border-top:2px solid #e5e7eb">
      <td style="padding:.8rem 1.25rem;color:var(--text-muted);font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em">Impozit venit 10% (din net)</td>
      <td style="padding:.8rem 1.25rem;text-align:right;font-weight:700;color:#dc2626" id="r-impozit">—</td>
    </tr>
    <tr>
      <td style="padding:.8rem 1.25rem;color:var(--text-muted);font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em">
        CAS 24,75%
        <span style="display:block;font-size:.72rem;font-weight:400;color:#94a3b8;text-transform:none;letter-spacing:0">Prag: 12 × 4.050 = 48.600 RON/an net</span>
      </td>
      <td style="padding:.8rem 1.25rem;text-align:right;font-weight:600" id="r-cas">—</td>
    </tr>
    <tr style="background:#f8fafc">
      <td style="padding:.8rem 1.25rem;color:var(--text-muted);font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em">
        CASS 10%
        <span style="display:block;font-size:.72rem;font-weight:400;color:#94a3b8;text-transform:none;letter-spacing:0">Prag: 6 × 4.050 = 24.300 RON/an net</span>
      </td>
      <td style="padding:.8rem 1.25rem;text-align:right;font-weight:600" id="r-cass">—</td>
    </tr>
    <tr style="background:#1d3557">
      <td style="padding:1rem 1.25rem;color:#93c5fd;font-weight:700;font-size:.9rem">TOTAL DE PLATĂ (impozit + contribuții)</td>
      <td style="padding:1rem 1.25rem;text-align:right;font-weight:900;color:#fff;font-size:1.25rem" id="r-total">—</td>
    </tr>
    </tbody>
  </table>

  <!-- D212 info -->
  <div id="d212-box" style="padding:1rem 1.25rem;background:#f0fdf4;border-top:2px solid #bbf7d0;display:flex;gap:.75rem;align-items:flex-start">
    <span style="font-size:1.1rem;flex-shrink:0">📋</span>
    <div style="font-size:.85rem;line-height:1.6">
      <strong style="color:#15803d;display:block;margin-bottom:.2rem">Declarația Unică D212 — obligatorie</strong>
      <span style="color:#166534">Trebuie să depui <strong>D212</strong> online la ANAF (e-Declarații / SPV) până pe <strong>25 mai 2026</strong> — atât pentru veniturile realizate în 2025, cât și pentru cele estimate din 2026.</span>
    </div>
  </div>
</div>

<!-- Rate lunare echivalente -->
<div style="background:#f8fafc;border:1px solid var(--border);border-radius:12px;padding:1.1rem 1.25rem;margin-bottom:1.25rem">
  <div style="font-size:.8rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem">Echivalent lunar</div>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;text-align:center">
    <div>
      <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.2rem">Impozit/lună</div>
      <div style="font-size:1.1rem;font-weight:800;color:#dc2626" id="r-imp-luna">—</div>
    </div>
    <div style="border-left:1px solid #e5e7eb;border-right:1px solid #e5e7eb">
      <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.2rem">Contribuții/lună</div>
      <div style="font-size:1.1rem;font-weight:800;color:#b45309" id="r-contrib-luna">—</div>
    </div>
    <div>
      <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.2rem">Total/lună</div>
      <div style="font-size:1.1rem;font-weight:800;color:var(--blue-dark)" id="r-total-luna">—</div>
    </div>
  </div>
</div>

<!-- Disclaimer -->
<div style="background:#fff8ed;border:1px solid #fcd34d;border-radius:10px;padding:.875rem 1rem;font-size:.8rem;color:#78350f;line-height:1.65">
  ⚠️ <strong>Disclaimer:</strong> Aceste calcule sunt estimative, bazate pe legislația fiscală în vigoare la data publicării (2026). Situația fiecărui contribuabil poate varia în funcție de tipul contractului de închiriere, statutul fiscal personal și alte venituri cumulate. <strong>Consultați un contabil autorizat sau site-ul oficial ANAF (anaf.ro) pentru situația dvs. exactă.</strong>
</div>

</div><!-- /.container-sm -->

<!-- FAQ -->
<div class="container-sm" style="padding-bottom:1.5rem">
  <h2 style="font-size:1.1rem;font-weight:800;color:var(--blue-dark);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid var(--border)">
    Întrebări frecvente — impozit chirii 2026
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
      ['slug'=>'calculator-pfa',       'icon'=>'💼','title'=>'Calculator impozit PFA'],
      ['slug'=>'calculator-dividende', 'icon'=>'📈','title'=>'Calculator dividende'],
      ['slug'=>'calculator-tva',       'icon'=>'🧮','title'=>'Calculator TVA'],
    ] as $r): ?>
    <a href="/tooluri/<?= $r['slug'] ?>/" style="display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;border:1.5px solid var(--border);border-radius:10px;text-decoration:none;background:#fff;font-size:.875rem;font-weight:600;color:var(--blue-dark);transition:border-color .15s" onmouseover="this.style.borderColor='#457b9d'" onmouseout="this.style.borderColor='var(--border)'">
      <span><?= $r['icon'] ?></span><?= e($r['title']) ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<script>
// ── Constante fiscale 2026 ────────────────────────────────────────────────────
var FORFETARA    = 0.20;        // cotă forfetară 20%
var IMPOZIT      = 0.10;        // impozit venit 10%
var CAS_COTA     = 0.2475;      // CAS 24,75%
var CASS_COTA    = 0.10;        // CASS 10%
var SAL_MIN      = 4050;        // salariu minim brut 2026 (RON/lună)
var CAS_PRAG     = 12 * SAL_MIN;  // 48.600 RON/an — prag CAS
var CASS_PRAG    = 6  * SAL_MIN;  // 24.300 RON/an — prag CASS

function fmt(v) {
  return v.toLocaleString('ro-RO', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' RON';
}

function fmtColor(v, colorIfPos) {
  return '<span style="color:' + (v > 0 ? colorIfPos : '#16a34a') + '">' + (v > 0 ? fmt(v) : '✓ Sub prag — 0 RON') + '</span>';
}

function calc() {
  var chirieL = parseFloat(document.getElementById('chirie').value) || 0;
  var luni    = parseInt(document.getElementById('luni').value) || 0;
  var alteV   = document.getElementById('alte-venituri').checked;

  // Avertisment alte venituri
  document.getElementById('warn-alte').style.display = alteV ? '' : 'none';

  if (chirieL <= 0 || luni < 1) return;

  // Calcul
  var brut      = chirieL * luni;
  var forfetara = brut * FORFETARA;
  var net       = brut - forfetara;           // = brut * 0.80
  var impozit   = net * IMPOZIT;              // = brut * 0.08

  var cas  = net >= CAS_PRAG  ? CAS_PRAG  * CAS_COTA  : 0;
  var cass = net >= CASS_PRAG ? CASS_PRAG * CASS_COTA : 0;
  var total = impozit + cas + cass;

  // Afișare tabel
  document.getElementById('r-brut').textContent      = fmt(brut);
  document.getElementById('r-forfetara').textContent = '− ' + fmt(forfetara);
  document.getElementById('r-net').textContent       = fmt(net);
  document.getElementById('r-impozit').textContent   = fmt(impozit);
  document.getElementById('r-cas').innerHTML  = fmtColor(cas,  '#dc2626');
  document.getElementById('r-cass').innerHTML = fmtColor(cass, '#b45309');
  document.getElementById('r-total').textContent     = fmt(total);

  // Echivalent lunar
  document.getElementById('r-imp-luna').textContent    = fmt(impozit / luni);
  document.getElementById('r-contrib-luna').textContent= fmt((cas + cass) / luni);
  document.getElementById('r-total-luna').textContent  = fmt(total / luni);
}

// Mobile: grid 1 coloană
if (window.innerWidth < 580) {
  document.getElementById('input-grid').style.gridTemplateColumns = '1fr';
}

// Calcul inițial
calc();
</script>

<?php require __DIR__ . '/../../templates/footer.php'; ?>
