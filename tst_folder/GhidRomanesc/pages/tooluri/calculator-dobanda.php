<?php
$pageTitle = 'Calculator dobândă simplă și compusă — GhidRomânesc';
$metaDescription = 'Calculezi dobânda simplă sau compusă pentru orice sumă, rată și perioadă. Util pentru economii, depozite și împrumuturi.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/calculator-dobanda/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">📊 Calculator dobândă</h1>
  <p class="page-subtitle">Calculezi dobânda simplă sau compusă — util pentru depozite, economii și împrumuturi.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div style="display:flex;gap:.4rem;background:#f1f5f9;padding:.3rem;border-radius:8px;margin-bottom:1.5rem">
    <button id="tab-simpla" onclick="setTab('simpla')" class="tab-btn active" style="flex:1;padding:.4rem;border:none;border-radius:6px;background:#fff;font-weight:700;font-size:.85rem;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.1)">Dobândă simplă</button>
    <button id="tab-compusa" onclick="setTab('compusa')" class="tab-btn" style="flex:1;padding:.4rem;border:none;border-radius:6px;background:none;font-weight:600;font-size:.85rem;cursor:pointer;color:var(--text-muted)">Dobândă compusă</button>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Capital inițial (RON)</label>
      <input type="number" id="capital" class="form-control" value="10000" min="0" oninput="calc()" style="margin-top:.3rem;font-size:1rem;font-weight:700">
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Rată anuală (%)</label>
      <input type="number" id="rata" class="form-control" value="6" min="0" max="100" step="0.1" oninput="calc()" style="margin-top:.3rem">
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Perioadă</label>
      <input type="number" id="perioada" class="form-control" value="3" min="1" oninput="calc()" style="margin-top:.3rem">
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Unitate perioadă</label>
      <select id="unitate" class="form-control" onchange="calc()" style="margin-top:.3rem">
        <option value="ani">Ani</option>
        <option value="luni">Luni</option>
        <option value="zile">Zile</option>
      </select>
    </div>
  </div>

  <div id="compusa-opts" style="display:none;margin-bottom:1rem">
    <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Capitalizare</label>
    <select id="cap-freq" class="form-control" onchange="calc()" style="margin-top:.3rem">
      <option value="1">Anuală</option>
      <option value="2">Semestrială</option>
      <option value="4">Trimestrială</option>
      <option value="12" selected>Lunară</option>
      <option value="365">Zilnică</option>
    </select>
  </div>

  <div style="background:#f0f4ff;border-radius:12px;padding:1.25rem;margin-bottom:1.25rem">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;text-align:center;margin-bottom:.75rem">
      <div>
        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.25rem">Capital inițial</div>
        <div id="r-cap" style="font-size:1rem;font-weight:800;color:var(--blue-dark)">—</div>
      </div>
      <div style="border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0">
        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.25rem">Dobândă câștigată</div>
        <div id="r-dob" style="font-size:1rem;font-weight:800;color:#16a34a">—</div>
      </div>
      <div>
        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.25rem">Total final</div>
        <div id="r-total" style="font-size:1.1rem;font-weight:900;color:#16a34a">—</div>
      </div>
    </div>
    <div id="formula" style="font-size:.78rem;color:var(--text-muted);border-top:1px solid #e2e8f0;padding-top:.6rem"></div>
  </div>

  <!-- Tabel evoluție ani -->
  <details style="border:1px solid var(--border);border-radius:10px;overflow:hidden">
    <summary style="padding:.75rem 1rem;cursor:pointer;font-weight:600;font-size:.85rem;color:var(--blue-dark);background:#f8fafc;user-select:none;list-style:none;display:flex;justify-content:space-between">
      <span>📋 Evoluție pe ani</span><span>▸</span>
    </summary>
    <div style="overflow-x:auto;max-height:300px;overflow-y:auto">
      <table style="width:100%;border-collapse:collapse;font-size:.82rem" id="evol-table">
        <thead><tr style="background:#f9fafb">
          <th style="padding:.5rem .75rem;text-align:left;border-bottom:1px solid #e5e7eb">An</th>
          <th style="padding:.5rem .75rem;text-align:right;border-bottom:1px solid #e5e7eb">Dobândă</th>
          <th style="padding:.5rem .75rem;text-align:right;border-bottom:1px solid #e5e7eb">Total</th>
        </tr></thead>
        <tbody id="evol-body"></tbody>
      </table>
    </div>
  </details>
</div>
</div>

<script>
var mode = 'simpla';

function setTab(t) {
  mode = t;
  ['simpla','compusa'].forEach(function(id) {
    var btn = document.getElementById('tab-' + id);
    btn.style.background = id === t ? '#fff' : 'none';
    btn.style.color = id === t ? 'inherit' : 'var(--text-muted)';
    btn.style.boxShadow = id === t ? '0 1px 3px rgba(0,0,0,.1)' : 'none';
  });
  document.getElementById('compusa-opts').style.display = t === 'compusa' ? '' : 'none';
  calc();
}

function toAni(val, unit) {
  if (unit === 'ani')  return val;
  if (unit === 'luni') return val / 12;
  return val / 365;
}

function fmt(v) { return Math.round(v).toLocaleString('ro') + ' RON'; }
function fmt2(v) { return v.toFixed(2).replace('.', ',') + ' RON'; }

function calc() {
  var P = parseFloat(document.getElementById('capital').value) || 0;
  var r = parseFloat(document.getElementById('rata').value) / 100;
  var t = parseFloat(document.getElementById('perioada').value) || 1;
  var unit = document.getElementById('unitate').value;
  var tAni = toAni(t, unit);
  var n = parseInt(document.getElementById('cap-freq').value) || 12;

  var total, dobanda;
  if (mode === 'simpla') {
    dobanda = P * r * tAni;
    total = P + dobanda;
    document.getElementById('formula').textContent = 'Formula: D = P × r × t = ' + P.toLocaleString('ro') + ' × ' + (r*100) + '% × ' + tAni.toFixed(2) + ' ani';
  } else {
    total = P * Math.pow(1 + r/n, n * tAni);
    dobanda = total - P;
    document.getElementById('formula').textContent = 'Formula: A = P × (1 + r/n)^(n×t) = ' + P.toLocaleString('ro') + ' × (1 + ' + (r*100) + '%/' + n + ')^(' + n + '×' + tAni.toFixed(2) + ')';
  }

  document.getElementById('r-cap').textContent   = fmt(P);
  document.getElementById('r-dob').textContent   = fmt2(dobanda);
  document.getElementById('r-total').textContent = fmt2(total);

  // Evoluție
  var maxAni = Math.min(Math.ceil(tAni), 30);
  var rows = '';
  for (var i = 1; i <= maxAni; i++) {
    var tI = i;
    var dI, totI;
    if (mode === 'simpla') { dI = P * r * tI; totI = P + dI; }
    else { totI = P * Math.pow(1 + r/n, n * tI); dI = totI - P; }
    rows += '<tr style="border-bottom:1px solid #f3f4f6"><td style="padding:.4rem .75rem;color:#6b7280">An ' + i + '</td><td style="padding:.4rem .75rem;text-align:right;color:#16a34a">+' + dI.toFixed(2) + '</td><td style="padding:.4rem .75rem;text-align:right;font-weight:600">' + totI.toFixed(2) + '</td></tr>';
  }
  document.getElementById('evol-body').innerHTML = rows;
}
calc();
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
