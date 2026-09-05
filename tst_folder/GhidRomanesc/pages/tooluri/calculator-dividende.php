<?php
$pageTitle = 'Calculator impozit dividende România 2026 — GhidRomânesc';
$metaDescription = 'Calculezi impozitul pe dividende și CASS datorat în România 2026. Cota 10% + CASS 10% în funcție de plafon.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/calculator-dividende/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">📈 Calculator impozit dividende 2026</h1>
  <p class="page-subtitle">Calculezi impozitul pe dividende și CASS de plătit în România.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Dividende brute (RON)</label>
      <input type="number" id="div" class="form-control" value="50000" min="0" oninput="calc()" style="margin-top:.3rem;font-size:1rem;font-weight:700">
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Salariu minim brut 2026 (RON)</label>
      <input type="number" id="smin" class="form-control" value="4050" min="1000" oninput="calc()" style="margin-top:.3rem">
    </div>
  </div>

  <div class="form-group" style="margin-bottom:1.25rem">
    <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Ești și salariat/pensionar?</label>
    <select id="alt-cass" class="form-control" onchange="calc()" style="margin-top:.3rem">
      <option value="nu">Nu — dividendele sunt singura sursă de venit</option>
      <option value="da">Da — CASS se plătește prin angajator (nu se dublează)</option>
    </select>
  </div>

  <div style="background:#f0f4ff;border-radius:12px;padding:1.25rem;margin-bottom:1rem">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;text-align:center;margin-bottom:.75rem">
      <div>
        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.25rem">Impozit dividende (10%)</div>
        <div id="r-imp" style="font-size:1.1rem;font-weight:800;color:#dc2626">—</div>
      </div>
      <div style="border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0">
        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.25rem">CASS datorat</div>
        <div id="r-cass" style="font-size:1.1rem;font-weight:800;color:#b45309">—</div>
      </div>
      <div>
        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.25rem">Dividend net</div>
        <div id="r-net" style="font-size:1.1rem;font-weight:800;color:#16a34a">—</div>
      </div>
    </div>
    <div id="detalii" style="font-size:.78rem;color:var(--text-muted);border-top:1px solid #e2e8f0;padding-top:.65rem;display:flex;flex-direction:column;gap:.2rem"></div>
  </div>

  <div id="plafon-info" style="background:#f8fafc;border-radius:10px;padding:.875rem 1rem;font-size:.82rem;color:var(--text-muted)"></div>
</div>

<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.5rem">
  <h2 style="font-size:.9rem;font-weight:700;color:var(--blue-dark);margin-bottom:.875rem">Reguli dividende 2026</h2>
  <div style="font-size:.82rem;color:var(--text-muted);display:flex;flex-direction:column;gap:.4rem">
    <div>📌 <strong>Impozit pe dividende</strong>: 10% aplicat la suma brută a dividendelor distribuite</div>
    <div>📌 <strong>CASS pe dividende</strong>: 10% — se aplică la plafon (6×, 12× sau 24× salariu minim lunar × 12)</div>
    <div>📌 <strong>Dacă ești salariat</strong>: CASS nu se datorează pe dividende (plafon atins prin salariu)</div>
    <div>📌 <strong>Termenul</strong>: impozitul se reține la sursă de firmă; CASS se declară prin D212 până pe 25 mai</div>
    <div>📌 <strong>Dividendele pot fi distribuite</strong> doar după aprobarea situațiilor financiare anuale</div>
  </div>
</div>
</div>

<script>
function fmt(v) { return Math.round(v).toLocaleString('ro') + ' RON'; }

function calc() {
  var div   = parseFloat(document.getElementById('div').value) || 0;
  var smin  = parseFloat(document.getElementById('smin').value) || 4050;
  var altCASS = document.getElementById('alt-cass').value;

  var impozit = div * 0.10;
  var cass = 0;
  var plafonLabel = '';

  if (altCASS === 'nu') {
    var p6  = 6 * smin * 12;
    var p12 = 12 * smin * 12;
    var p24 = 24 * smin * 12;

    if (div < p6) {
      cass = p6 * 0.10;
      plafonLabel = 'Plafon 6 × ' + smin + ' × 12 = ' + fmt(p6) + ' (CASS minim chiar dacă dividendele sunt mai mici)';
    } else if (div < p12) {
      cass = p6 * 0.10;
      plafonLabel = 'Plafon aplicat: 6 × salariu minim anual (' + fmt(p6) + ')';
    } else if (div < p24) {
      cass = p12 * 0.10;
      plafonLabel = 'Plafon aplicat: 12 × salariu minim anual (' + fmt(p12) + ')';
    } else {
      cass = p24 * 0.10;
      plafonLabel = 'Plafon maxim: 24 × salariu minim anual (' + fmt(p24) + ')';
    }
  } else {
    plafonLabel = 'CASS = 0 RON — plătit prin angajator, nu se datorează pe dividende.';
  }

  var net = div - impozit - cass;

  document.getElementById('r-imp').textContent  = fmt(impozit);
  document.getElementById('r-cass').textContent = fmt(cass);
  document.getElementById('r-net').textContent  = fmt(net);
  document.getElementById('plafon-info').textContent = plafonLabel;

  document.getElementById('detalii').innerHTML = [
    '• Dividende brute: ' + fmt(div),
    '• Impozit 10%: −' + fmt(impozit),
    altCASS === 'nu' ? '• CASS 10% (plafon): −' + fmt(cass) : '• CASS: 0 RON (salariat)',
    '• <strong>Dividend net: ' + fmt(net) + '</strong> (' + (div > 0 ? (net/div*100).toFixed(1) : 0) + '% din brut)',
  ].map(function(l){ return '<div>' + l + '</div>'; }).join('');
}
calc();
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
