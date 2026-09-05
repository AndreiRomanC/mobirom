<?php
$pageTitle = 'Calculator rată credit și dobândă — GhidRomânesc';
$metaDescription = 'Calculezi rata lunară, dobânda totală și costul total al oricărui credit. Grafic de amortizare complet. Gratuit și instant.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/calculator-credit/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">🏦 Calculator rată credit</h1>
  <p class="page-subtitle">Calculezi rata lunară, dobânda totală și costul real al oricărui credit sau împrumut.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Sumă credit (RON)</label>
      <input type="number" id="suma" class="form-control" value="50000" min="0" oninput="calc()" style="margin-top:.3rem;font-size:1rem;font-weight:700">
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Dobândă anuală (%)</label>
      <input type="number" id="dob" class="form-control" value="8.5" min="0" max="100" step="0.1" oninput="calc()" style="margin-top:.3rem;font-size:1rem">
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Perioadă (luni)</label>
      <input type="number" id="luni" class="form-control" value="60" min="1" max="360" oninput="calc()" style="margin-top:.3rem;font-size:1rem">
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Tip credit</label>
      <select id="tip" class="form-control" onchange="calc()" style="margin-top:.3rem">
        <option value="anuitate">Anuitate (rată egală)</option>
        <option value="principal">Principal constant</option>
      </select>
    </div>
  </div>

  <!-- Rezultate principale -->
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-bottom:1.5rem">
    <div style="background:#e0f2fe;border-radius:12px;padding:1rem;text-align:center">
      <div style="font-size:.72rem;font-weight:700;color:#0369a1;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.3rem">Rată lunară</div>
      <div id="r-rata" style="font-size:1.5rem;font-weight:900;color:#0369a1">—</div>
      <div style="font-size:.72rem;color:#0369a1;margin-top:.2rem;opacity:.75" id="r-rata-sub"></div>
    </div>
    <div style="background:#fef9c3;border-radius:12px;padding:1rem;text-align:center">
      <div style="font-size:.72rem;font-weight:700;color:#a16207;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.3rem">Dobândă totală</div>
      <div id="r-dob" style="font-size:1.5rem;font-weight:900;color:#a16207">—</div>
    </div>
    <div style="background:#f0fdf4;border-radius:12px;padding:1rem;text-align:center">
      <div style="font-size:.72rem;font-weight:700;color:#15803d;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.3rem">Total de plătit</div>
      <div id="r-total" style="font-size:1.5rem;font-weight:900;color:#15803d">—</div>
    </div>
  </div>

  <!-- Grafic simplificat -->
  <div style="margin-bottom:1.25rem">
    <div style="font-size:.82rem;font-weight:600;color:var(--blue-dark);margin-bottom:.5rem">Structura plăților</div>
    <div style="height:20px;border-radius:10px;overflow:hidden;display:flex">
      <div id="bar-principal" style="background:#0369a1;transition:width .3s"></div>
      <div id="bar-dobanda" style="background:#fcd34d;flex:1"></div>
    </div>
    <div style="display:flex;gap:1rem;margin-top:.4rem;font-size:.78rem">
      <span><span style="display:inline-block;width:10px;height:10px;background:#0369a1;border-radius:2px;margin-right:.3rem"></span>Principal: <strong id="leg-principal"></strong></span>
      <span><span style="display:inline-block;width:10px;height:10px;background:#fcd34d;border-radius:2px;margin-right:.3rem"></span>Dobândă: <strong id="leg-dobanda"></strong></span>
    </div>
  </div>

  <!-- Grafic amortizare (primele 12 luni) -->
  <details style="border:1px solid var(--border);border-radius:10px;overflow:hidden">
    <summary style="padding:.75rem 1rem;cursor:pointer;font-weight:600;font-size:.85rem;color:var(--blue-dark);background:#f8fafc;user-select:none;list-style:none;display:flex;justify-content:space-between">
      <span>📋 Grafic de amortizare complet</span><span style="color:var(--text-muted)">▸</span>
    </summary>
    <div style="overflow-x:auto;max-height:400px;overflow-y:auto">
      <table style="width:100%;border-collapse:collapse;font-size:.8rem" id="amort-table">
        <thead><tr style="background:#f9fafb;position:sticky;top:0">
          <th style="padding:.5rem .75rem;text-align:left;border-bottom:1px solid #e5e7eb;white-space:nowrap">Luna</th>
          <th style="padding:.5rem .75rem;text-align:right;border-bottom:1px solid #e5e7eb;white-space:nowrap">Rată</th>
          <th style="padding:.5rem .75rem;text-align:right;border-bottom:1px solid #e5e7eb;white-space:nowrap">Principal</th>
          <th style="padding:.5rem .75rem;text-align:right;border-bottom:1px solid #e5e7eb;white-space:nowrap">Dobândă</th>
          <th style="padding:.5rem .75rem;text-align:right;border-bottom:1px solid #e5e7eb;white-space:nowrap">Sold rămas</th>
        </tr></thead>
        <tbody id="amort-body"></tbody>
      </table>
    </div>
  </details>
</div>
</div>

<script>
function fmt(v) { return Math.round(v).toLocaleString('ro') + ' RON'; }
function fmt2(v) { return v.toFixed(2).replace('.', ',') + ' RON'; }

function calc() {
  var P = parseFloat(document.getElementById('suma').value) || 0;
  var r = parseFloat(document.getElementById('dob').value) / 100 / 12;
  var n = parseInt(document.getElementById('luni').value) || 1;
  var tip = document.getElementById('tip').value;

  if (P <= 0 || n <= 0) return;

  var totalDob = 0, rows = [], rataLunara;

  if (tip === 'anuitate') {
    if (r === 0) {
      rataLunara = P / n;
    } else {
      rataLunara = P * r * Math.pow(1+r, n) / (Math.pow(1+r, n) - 1);
    }
    var sold = P;
    for (var i = 1; i <= n; i++) {
      var dob = sold * r;
      var princ = rataLunara - dob;
      sold -= princ;
      totalDob += dob;
      rows.push([i, rataLunara, princ, dob, Math.max(0, sold)]);
    }
    document.getElementById('r-rata').textContent = fmt2(rataLunara);
    document.getElementById('r-rata-sub').textContent = 'egală în fiecare lună';
  } else {
    var princConst = P / n;
    var sold2 = P;
    var rataMax = 0;
    for (var j = 1; j <= n; j++) {
      var dob2 = sold2 * r;
      var rata2 = princConst + dob2;
      if (rata2 > rataMax) rataMax = rata2;
      totalDob += dob2;
      sold2 -= princConst;
      rows.push([j, rata2, princConst, dob2, Math.max(0, sold2)]);
    }
    document.getElementById('r-rata').textContent = fmt2(rataMax);
    document.getElementById('r-rata-sub').textContent = 'prima rată (descrescătoare)';
  }

  var total = P + totalDob;
  document.getElementById('r-dob').textContent = fmt(totalDob);
  document.getElementById('r-total').textContent = fmt(total);

  var pctP = Math.round(P / total * 100);
  document.getElementById('bar-principal').style.width = pctP + '%';
  document.getElementById('leg-principal').textContent = fmt(P) + ' (' + pctP + '%)';
  document.getElementById('leg-dobanda').textContent = fmt(totalDob) + ' (' + (100-pctP) + '%)';

  var tbody = document.getElementById('amort-body');
  tbody.innerHTML = rows.map(function(r) {
    return '<tr style="border-bottom:1px solid #f3f4f6">' +
      '<td style="padding:.4rem .75rem;color:#6b7280">L' + r[0] + '</td>' +
      '<td style="padding:.4rem .75rem;text-align:right;font-weight:600">' + r[1].toFixed(2) + '</td>' +
      '<td style="padding:.4rem .75rem;text-align:right;color:#0369a1">' + r[2].toFixed(2) + '</td>' +
      '<td style="padding:.4rem .75rem;text-align:right;color:#b45309">' + r[3].toFixed(2) + '</td>' +
      '<td style="padding:.4rem .75rem;text-align:right">' + Math.max(0,r[4]).toFixed(2) + '</td>' +
      '</tr>';
  }).join('');
}
calc();
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
