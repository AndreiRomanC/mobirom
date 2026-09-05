<?php
$pageTitle = 'Calculator concediu medical România 2026 — GhidRomânesc';
$metaDescription = 'Calculezi indemnizația de concediu medical în România 2026. Include baza de calcul, cota aplicabilă și suma netă estimată.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/concediu-medical/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">🏥 Calculator concediu medical 2026</h1>
  <p class="page-subtitle">Estimezi indemnizația de concediu medical pe baza venitului brut din ultimele 6 luni.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Venit brut lunar mediu (RON)</label>
      <input type="number" id="venit" class="form-control" value="5000" min="0" oninput="calc()" style="margin-top:.3rem;font-size:1rem">
      <div style="font-size:.72rem;color:var(--text-muted);margin-top:.2rem">Media ultimelor 6 luni</div>
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Număr zile concediu</label>
      <input type="number" id="zile" class="form-control" value="7" min="1" max="183" oninput="calc()" style="margin-top:.3rem;font-size:1rem">
    </div>
  </div>

  <div class="form-group" style="margin-bottom:1.25rem">
    <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Tip diagnostic</label>
    <select id="tip" class="form-control" onchange="calc()" style="margin-top:.3rem">
      <option value="75">75% — Boală obișnuită (răceală, gripă etc.)</option>
      <option value="80">80% — Accident de muncă / boală profesională</option>
      <option value="85">85% — Spitalizare</option>
      <option value="100">100% — TBC, cancer, urgențe, boală copil < 3 ani</option>
    </select>
  </div>

  <div style="background:#f0f4ff;border-radius:12px;padding:1.25rem;margin-bottom:1.25rem">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;text-align:center;margin-bottom:.75rem">
      <div>
        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.25rem">Bază zilnică</div>
        <div id="r-baza" style="font-size:1.1rem;font-weight:800;color:var(--blue-dark)">—</div>
      </div>
      <div style="border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0">
        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.25rem">Indemnizație brută</div>
        <div id="r-brut" style="font-size:1.1rem;font-weight:800;color:#b45309">—</div>
      </div>
      <div>
        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.25rem">Indemnizație netă est.</div>
        <div id="r-net" style="font-size:1.1rem;font-weight:800;color:#16a34a">—</div>
      </div>
    </div>
    <div id="detalii" style="font-size:.78rem;color:var(--text-muted);border-top:1px solid #e2e8f0;padding-top:.65rem;display:flex;flex-direction:column;gap:.2rem"></div>
  </div>

  <div id="platitor-box" style="background:#fff7ed;border:1px solid #fcd34d;border-radius:10px;padding:.875rem 1rem;font-size:.82rem;color:#92400e"></div>
</div>

<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.5rem">
  <h2 style="font-size:.9rem;font-weight:700;color:var(--blue-dark);margin-bottom:.875rem">Cum se calculează concediul medical</h2>
  <div style="font-size:.83rem;color:var(--text-muted);display:flex;flex-direction:column;gap:.5rem">
    <div>📌 <strong>Baza de calcul</strong> = suma veniturilor brute din ultimele 6 luni ÷ numărul de zile calendaristice (182 sau 183)</div>
    <div>📌 <strong>Indemnizație brută</strong> = baza zilnică × cota % × număr zile calendaristice concediu</div>
    <div>📌 <strong>Cine plătește</strong>: angajatorul plătește primele 5 zile lucătoare; FNUASS plătește restul</div>
    <div>📌 <strong>Rețineri din indemnizație</strong>: CASS 10% + impozit 10% (CAS nu se reține din indemnizație)</div>
    <div>📌 <strong>Condiție</strong>: minim 6 luni de stagiu de asigurare în ultimele 12 luni (cu excepții pentru urgențe)</div>
  </div>
</div>
</div>

<script>
function calc() {
  var venit = parseFloat(document.getElementById('venit').value) || 0;
  var zile  = parseInt(document.getElementById('zile').value) || 1;
  var cota  = parseInt(document.getElementById('tip').value) / 100;

  // Baza zilnică = venit brut lunar mediu × 6 / 182 zile
  var bazaLunara6 = venit * 6;
  var bazaZilnica = bazaLunara6 / 182;

  var brutTotal = bazaZilnica * cota * zile;
  var cass = brutTotal * 0.10;
  var impozit = (brutTotal - cass) * 0.10;
  var net = brutTotal - cass - impozit;

  var fmt = function(v) { return v.toFixed(2) + ' RON'; };

  document.getElementById('r-baza').textContent  = fmt(bazaZilnica) + '/zi';
  document.getElementById('r-brut').textContent  = fmt(brutTotal);
  document.getElementById('r-net').textContent   = fmt(net);

  var platitorText = '';
  var zileLucratoare = Math.min(5, zile);
  var zileFNUASS = Math.max(0, zile - 5);
  if (zile <= 5) {
    platitorText = '💼 Toate ' + zile + ' zilele sunt plătite de angajator.';
  } else {
    platitorText = '💼 Primele 5 zile lucătoare: plătite de angajator (' + fmt(bazaZilnica * cota * 5) + '). ' +
      '🏛️ Restul de ' + zileFNUASS + ' zile: plătite de FNUASS (' + fmt(bazaZilnica * cota * zileFNUASS) + ').';
  }
  document.getElementById('platitor-box').textContent = platitorText;

  document.getElementById('detalii').innerHTML = [
    '• Bază totală 6 luni: ' + fmt(bazaLunara6),
    '• Bază zilnică (' + fmt(bazaLunara6) + ' ÷ 182 zile): ' + fmt(bazaZilnica),
    '• Indemnizație brută (' + (cota*100) + '% × ' + fmt(bazaZilnica) + ' × ' + zile + ' zile): ' + fmt(brutTotal),
    '• CASS reținut (10%): −' + fmt(cass),
    '• Impozit reținut (10% din ' + fmt(brutTotal - cass) + '): −' + fmt(impozit),
    '• Net estimat: ' + fmt(net),
  ].map(function(l){ return '<div>' + l + '</div>'; }).join('');
}
calc();
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
