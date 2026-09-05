<?php
$pageTitle = 'Calculator impozit PFA România 2026 — GhidRomânesc';
$metaDescription = 'Calculezi impozitele unui PFA în România 2026: CAS, CASS și impozit pe venit. Sistem real și norma de venit.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/calculator-pfa/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">💼 Calculator impozit PFA 2026</h1>
  <p class="page-subtitle">Calculezi CAS, CASS și impozitul pe venit al unui PFA. Sistem real de impunere.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Venit net anual (RON)</label>
      <input type="number" id="venit" class="form-control" value="60000" min="0" oninput="calc()" style="margin-top:.3rem;font-size:1rem;font-weight:700">
      <div style="font-size:.72rem;color:var(--text-muted);margin-top:.2rem">Venituri − cheltuieli deductibile</div>
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Salariu minim brut 2026 (RON)</label>
      <input type="number" id="smin" class="form-control" value="4050" min="1000" oninput="calc()" style="margin-top:.3rem">
      <div style="font-size:.72rem;color:var(--text-muted);margin-top:.2rem">Actualizează dacă s-a modificat</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Plătești CAS (pensie)?</label>
      <select id="cas-opt" class="form-control" onchange="calc()" style="margin-top:.3rem">
        <option value="da">Da — 25% × 12 × salariu minim</option>
        <option value="nu">Nu (sub 12 × salariu minim)</option>
      </select>
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Alte surse de CASS?</label>
      <select id="cass-alt" class="form-control" onchange="calc()" style="margin-top:.3rem">
        <option value="nu">Nu — PFA unica sursă</option>
        <option value="da">Da — salariat/pensionar (CASS 0)</option>
      </select>
    </div>
  </div>

  <!-- Rezultate -->
  <div style="background:#f0f4ff;border-radius:12px;padding:1.25rem;margin-bottom:1.25rem">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.6rem;text-align:center;margin-bottom:.875rem">
      <?php foreach([['r-cas','CAS (25%)','#dc2626'],['r-cass','CASS (10%)','#f59e0b'],['r-imp','Impozit (10%)','#b45309'],['r-net','Venit rămas','#16a34a']] as $c): ?>
      <div>
        <div style="font-size:.7rem;color:var(--text-muted);margin-bottom:.25rem"><?=$c[1]?></div>
        <div id="<?=$c[0]?>" style="font-size:1rem;font-weight:800;color:<?=$c[2]?>">—</div>
      </div>
      <?php endforeach; ?>
    </div>
    <div id="detalii" style="font-size:.78rem;color:var(--text-muted);border-top:1px solid #e2e8f0;padding-top:.65rem;display:flex;flex-direction:column;gap:.2rem"></div>
  </div>

  <div style="background:#f8fafc;border-radius:10px;padding:.875rem 1rem;font-size:.78rem;color:var(--text-muted)">
    <strong style="color:var(--blue-dark)">Rata efectivă totală de impozitare:</strong> <span id="rata-efectiva" style="font-weight:800;color:var(--blue-dark)">—</span>
  </div>
</div>

<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.5rem">
  <h2 style="font-size:.9rem;font-weight:700;color:var(--blue-dark);margin-bottom:.875rem">Reguli PFA sistem real 2026</h2>
  <div style="font-size:.82rem;color:var(--text-muted);display:flex;flex-direction:column;gap:.4rem">
    <div>📌 <strong>CAS</strong>: 25% dacă venitul net ≥ 12 × salariu minim. Baza = min(venit net, 24 × salariu minim)</div>
    <div>📌 <strong>CASS</strong>: 10% — plafon variabil în funcție de venit (6×, 12× sau 24× salariu minim)</div>
    <div>📌 <strong>Impozit venit</strong>: 10% × (venit net − CAS plătit)</div>
    <div>📌 <strong>Termenul de plată</strong>: trimestrial (25 ale lunii următoare trimestrului)</div>
    <div>📌 <strong>Dacă ești și salariat</strong>: CASS se plătește la angajator, nu se dublează la PFA</div>
  </div>
</div>
</div>

<script>
function fmt(v) { return Math.round(v).toLocaleString('ro') + ' RON'; }

function calc() {
  var venit = parseFloat(document.getElementById('venit').value) || 0;
  var smin  = parseFloat(document.getElementById('smin').value) || 4050;
  var casOpt  = document.getElementById('cas-opt').value;
  var cassAlt = document.getElementById('cass-alt').value;

  var cas = 0;
  if (casOpt === 'da' && venit >= 12 * smin) {
    var bazaCAS = Math.min(venit, 24 * smin);
    cas = bazaCAS * 0.25;
  }

  var cass = 0;
  if (cassAlt === 'nu') {
    var plaf6  = 6 * 12 * smin;
    var plaf12 = 12 * 12 * smin;
    var plaf24 = 24 * 12 * smin;
    if (venit < plaf6)       cass = plaf6 * 0.10;
    else if (venit < plaf12) cass = venit * 0.10;
    else if (venit < plaf24) cass = plaf12 * 0.10;
    else                     cass = plaf24 * 0.10;
    // Plafonare simplificată la 12 luni × smin
    var bazaCASS = Math.min(Math.max(venit, 6 * smin), 24 * smin) * 12;
    cass = Math.min(venit, Math.max(6 * smin, Math.min(venit, 24 * smin))) * 0.10;
    // Simplu: 10% × min(max(6×smin, min(venit, 24×smin)) × 12 / 12)
    var plafonCASS = Math.min(Math.max(venit, 6 * smin * 12), 24 * smin * 12);
    cass = plafonCASS * 0.10;
    // Real simplificat: dacă venit < 6×smin×12 → 10%×6×smin×12; dacă > 24×smin×12 → 10%×24×smin×12; altfel 10%×venit
    var v12 = venit; var p6 = 6*smin*12; var p24 = 24*smin*12;
    if (v12 < p6) cass = p6 * 0.10;
    else if (v12 > p24) cass = p24 * 0.10;
    else cass = v12 * 0.10;
  }

  var bazaImpozit = Math.max(0, venit - cas);
  var impozit = bazaImpozit * 0.10;
  var net = venit - cas - cass - impozit;
  var rataEf = venit > 0 ? ((cas + cass + impozit) / venit * 100).toFixed(1) : 0;

  document.getElementById('r-cas').textContent  = fmt(cas);
  document.getElementById('r-cass').textContent = fmt(cass);
  document.getElementById('r-imp').textContent  = fmt(impozit);
  document.getElementById('r-net').textContent  = fmt(net);
  document.getElementById('rata-efectiva').textContent = rataEf + '%';

  var linii = [
    '• Venit net: ' + fmt(venit),
    cas > 0 ? '• CAS 25% (baza: ' + fmt(Math.min(venit, 24*smin)) + '): −' + fmt(cas) : '• CAS: 0 RON (sub prag sau opțional)',
    cassAlt === 'da' ? '• CASS: 0 RON (plătit prin angajator)' : '• CASS 10% (plafon): −' + fmt(cass),
    '• Bază impozit (' + fmt(venit) + ' − ' + fmt(cas) + '): ' + fmt(bazaImpozit),
    '• Impozit 10%: −' + fmt(impozit),
    '• <strong>Venit rămas: ' + fmt(net) + '</strong>',
  ];
  document.getElementById('detalii').innerHTML = linii.map(function(l){ return '<div>' + l + '</div>'; }).join('');
}
calc();
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
