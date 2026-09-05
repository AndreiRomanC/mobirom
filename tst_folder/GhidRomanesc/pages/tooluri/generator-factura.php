<?php
$pageTitle       = 'Generator factură PDF 2026 România — GhidRomânesc';
$metaDescription = 'Generezi facturi PDF profesionale direct în browser, fără server. Câmpuri complete: emitent, client, TVA, tabel servicii, IBAN. Date salvate local.';
$canonicalUrl    = SITE_DOMAIN . '/tooluri/generator-factura/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom:.5rem;color:rgba(255,255,255,.65);font-size:.85rem">
    <a href="/" style="color:inherit">Acasă</a>
    <span class="breadcrumb-sep" aria-hidden="true">›</span>
    <a href="/tooluri/" style="color:inherit">Tooluri</a>
    <span class="breadcrumb-sep" aria-hidden="true">›</span>
    <span aria-current="page">Generator factură PDF</span>
  </nav>
  <h1 class="page-title">🧾 Generator factură PDF</h1>
  <p class="page-subtitle">Factură profesională generată 100% local în browser. Zero date trimise pe server.</p>
</div></div>

<style>
.inv-section { background:#fff;border:1.5px solid var(--border);border-radius:14px;padding:1.5rem;margin-bottom:1rem; }
.inv-section-title { font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid #f1f5f9; }
.inv-grid2 { display:grid;grid-template-columns:1fr 1fr;gap:.85rem; }
.inv-grid3 { display:grid;grid-template-columns:1fr 1fr 1fr;gap:.85rem; }
.inv-label { font-size:.78rem;font-weight:600;color:var(--blue-dark);display:block;margin-bottom:.25rem; }
.inv-input { width:100%;padding:.45rem .65rem;border:1px solid #d1d5db;border-radius:7px;font-size:.875rem;font-family:inherit;color:#1f2937;background:#fff;transition:border-color .15s; }
.inv-input:focus { outline:none;border-color:#457b9d;box-shadow:0 0 0 2px rgba(69,123,157,.12); }
.inv-select { appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236b7280' stroke-width='1.5' fill='none'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .6rem center;padding-right:1.75rem; }

/* Products table */
#products-table { width:100%;border-collapse:collapse;font-size:.85rem; }
#products-table th { background:#f8fafc;padding:.5rem .65rem;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;border-bottom:1px solid #e5e7eb;white-space:nowrap; }
#products-table td { padding:.4rem .5rem;border-bottom:1px solid #f3f4f6;vertical-align:middle; }
#products-table input, #products-table select { padding:.3rem .45rem;border:1px solid #e5e7eb;border-radius:5px;font-size:.83rem;font-family:inherit;width:100%;background:#fff; }
#products-table input:focus, #products-table select:focus { outline:none;border-color:#457b9d; }
.td-desc { min-width:160px; }
.td-qty  { width:60px; }
.td-price{ width:95px; }
.td-tva  { width:75px; }
.td-total{ width:90px;text-align:right;font-weight:700;color:var(--blue-dark); }
.td-del  { width:32px; }

/* Totals */
.inv-totals { margin-left:auto;min-width:260px;border-collapse:collapse;font-size:.875rem; }
.inv-totals td { padding:.4rem .75rem; }
.inv-totals .row-total { background:#1d3557;color:#fff;font-size:1rem; }
.inv-totals .row-total td { padding:.65rem .75rem;font-weight:800; }

/* Actions bar */
.inv-actions { display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;padding:1rem 1.5rem;background:#f8fafc;border-radius:12px;margin-top:.5rem; }
.btn-gen { padding:.8rem 1.75rem;background:#1d3557;color:#fff;border:none;border-radius:10px;font-size:.95rem;font-weight:700;cursor:pointer;font-family:inherit;transition:background .15s;display:flex;align-items:center;gap:.5rem; }
.btn-gen:hover { background:#457b9d; }
.btn-gen:disabled { background:#94a3b8;cursor:not-allowed; }

/* Notice */
.inv-notice { display:flex;align-items:center;gap:.6rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:.6rem 1rem;font-size:.8rem;color:#1e40af;margin-bottom:1rem; }

@media (max-width:640px) {
  .inv-grid2,.inv-grid3 { grid-template-columns:1fr; }
  #products-table .td-desc { min-width:110px; }
}
</style>

<div class="container-sm" style="padding-top:1.25rem;padding-bottom:4rem">

<div class="inv-notice">
  🔒 <span>Nicio dată nu este trimisă pe server. Totul se procesează în browser. Datele emitentului sunt salvate local (localStorage).</span>
</div>

<!-- ── EMITENT ──────────────────────────────────────────── -->
<div class="inv-section">
  <div class="inv-section-title">🏢 Emitent (salvat automat)</div>
  <div class="inv-grid2" style="margin-bottom:.85rem">
    <div>
      <label class="inv-label" for="e-den">Denumire firmă / Nume PFA *</label>
      <input class="inv-input" id="e-den" type="text" placeholder="ex: Alpha Tech SRL" oninput="saveEmitent()">
    </div>
    <div>
      <label class="inv-label" for="e-cui">CUI / CIF *</label>
      <input class="inv-input" id="e-cui" type="text" placeholder="ex: RO12345678" oninput="saveEmitent()">
    </div>
  </div>
  <div style="margin-bottom:.85rem">
    <label class="inv-label" for="e-adr">Adresă sediu</label>
    <input class="inv-input" id="e-adr" type="text" placeholder="ex: Str. Exemplu nr. 1, București" oninput="saveEmitent()">
  </div>
  <div class="inv-grid3" style="margin-bottom:.85rem">
    <div>
      <label class="inv-label" for="e-iban">IBAN</label>
      <input class="inv-input" id="e-iban" type="text" placeholder="RO49BTRL..." oninput="saveEmitent()" style="font-family:monospace;font-size:.82rem">
    </div>
    <div>
      <label class="inv-label" for="e-tel">Telefon</label>
      <input class="inv-input" id="e-tel" type="text" placeholder="0722 000 000" oninput="saveEmitent()">
    </div>
    <div>
      <label class="inv-label" for="e-email">Email</label>
      <input class="inv-input" id="e-email" type="email" placeholder="contact@firma.ro" oninput="saveEmitent()">
    </div>
  </div>
  <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;cursor:pointer;font-weight:600;color:var(--blue-dark)">
    <input type="checkbox" id="e-tva" onchange="saveEmitent()" style="width:16px;height:16px;accent-color:#457b9d">
    Plătitor TVA
  </label>
</div>

<!-- ── CLIENT ──────────────────────────────────────────── -->
<div class="inv-section">
  <div class="inv-section-title">👤 Client</div>
  <div class="inv-grid2" style="margin-bottom:.85rem">
    <div>
      <label class="inv-label" for="c-den">Denumire client *</label>
      <input class="inv-input" id="c-den" type="text" placeholder="ex: Beta Systems SRL">
    </div>
    <div>
      <label class="inv-label" for="c-cui">CUI / CIF (opțional)</label>
      <input class="inv-input" id="c-cui" type="text" placeholder="ex: RO87654321">
    </div>
  </div>
  <div>
    <label class="inv-label" for="c-adr">Adresă client</label>
    <input class="inv-input" id="c-adr" type="text" placeholder="ex: Bd. Unirii nr. 10, Cluj-Napoca">
  </div>
</div>

<!-- ── DATE FACTURĂ ─────────────────────────────────────── -->
<div class="inv-section">
  <div class="inv-section-title">📋 Detalii factură</div>
  <div class="inv-grid3">
    <div>
      <label class="inv-label" for="f-serie">Serie-Număr</label>
      <input class="inv-input" id="f-serie" type="text" value="GR-001" placeholder="GR-001">
    </div>
    <div>
      <label class="inv-label" for="f-data">Data emiterii</label>
      <input class="inv-input" id="f-data" type="date">
    </div>
    <div>
      <label class="inv-label" for="f-scad">Data scadenței</label>
      <input class="inv-input" id="f-scad" type="date">
    </div>
  </div>
</div>

<!-- ── PRODUSE / SERVICII ──────────────────────────────── -->
<div class="inv-section">
  <div class="inv-section-title">📦 Produse / Servicii</div>
  <div style="overflow-x:auto;margin-bottom:1rem">
    <table id="products-table">
      <thead>
        <tr>
          <th class="td-desc">Descriere</th>
          <th class="td-qty">Cant.</th>
          <th class="td-price">Preț/un. (RON)</th>
          <th class="td-tva">TVA %</th>
          <th class="td-total">Total (RON)</th>
          <th class="td-del"></th>
        </tr>
      </thead>
      <tbody id="rows-body">
        <!-- Rânduri dinamice -->
      </tbody>
    </table>
  </div>
  <button onclick="addRow()" class="btn btn-secondary btn-sm">+ Adaugă rând</button>

  <!-- Totaluri -->
  <div style="display:flex;justify-content:flex-end;margin-top:1.25rem">
    <table class="inv-totals">
      <tr>
        <td style="color:var(--text-muted)">Subtotal (fără TVA)</td>
        <td style="text-align:right;font-weight:600" id="sum-subtotal">0,00 RON</td>
      </tr>
      <tr id="tva-breakdown-rows"></tr>
      <tr class="row-total">
        <td>TOTAL DE PLATĂ</td>
        <td style="text-align:right;font-size:1.1rem" id="sum-total">0,00 RON</td>
      </tr>
    </table>
  </div>
</div>

<!-- ── MENȚIUNI ─────────────────────────────────────────── -->
<div class="inv-section">
  <div class="inv-section-title">📝 Mențiuni (opțional)</div>
  <textarea class="inv-input" id="f-note" rows="2" placeholder="ex: Plata în 30 zile de la emitere. Mulțumim!" style="resize:vertical"></textarea>
</div>

<!-- ── ACȚIUNI ──────────────────────────────────────────── -->
<div class="inv-actions">
  <button class="btn-gen" id="btn-pdf" onclick="generatePDF()">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
    Descarcă PDF
  </button>
  <span style="font-size:.8rem;color:var(--text-muted)" id="gen-status"></span>
  <span style="font-size:.8rem;color:var(--text-muted);margin-left:auto">
    Necesită conexiune la internet pentru bibliotecile jsPDF și html2canvas.
  </span>
</div>

</div><!-- /.container-sm -->

<!-- ── TEMPLATE FACTURĂ (ascuns, folosit pentru PDF) ──── -->
<div id="invoice-tpl" style="position:fixed;left:-9999px;top:0;width:794px;background:#fff;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#222;padding:42px 52px;box-sizing:border-box;z-index:-1"></div>

<!-- jsPDF + html2canvas CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
'use strict';

// ── LocalStorage ──────────────────────────────────────────────────────────────
var LS_KEY = 'ghidro-emitent-v1';

function saveEmitent() {
  try {
    localStorage.setItem(LS_KEY, JSON.stringify({
      den:   v('e-den'), cui:   v('e-cui'), adr:   v('e-adr'),
      iban:  v('e-iban'), tel: v('e-tel'), email: v('e-email'),
      tva:   document.getElementById('e-tva').checked,
    }));
  } catch(e) {}
}

function loadEmitent() {
  try {
    var d = JSON.parse(localStorage.getItem(LS_KEY) || '{}');
    ['den','cui','adr','iban','tel','email'].forEach(function(k) {
      var el = document.getElementById('e-' + k);
      if (el && d[k]) el.value = d[k];
    });
    if (d.tva !== undefined) document.getElementById('e-tva').checked = !!d.tva;
  } catch(e) {}
}

// ── Rows ──────────────────────────────────────────────────────────────────────
var rowId = 0;

function addRow(desc, qty, price, tvaRate) {
  rowId++;
  var id = rowId;
  var tr = document.createElement('tr');
  tr.id = 'row-' + id;
  tr.innerHTML =
    '<td class="td-desc"><input class="desc" type="text" value="' + esc(desc||'') + '" placeholder="Descriere serviciu/produs" oninput="calc()"></td>' +
    '<td class="td-qty"><input class="qty" type="number" value="' + (qty||1) + '" min="0" step="any" oninput="calc()"></td>' +
    '<td class="td-price"><input class="price" type="number" value="' + (price||0) + '" min="0" step="any" oninput="calc()"></td>' +
    '<td class="td-tva"><select class="tva-sel" onchange="calc()">' +
      ['0','5','9','19'].map(function(r){ return '<option value="'+r+'"'+(r===(String(tvaRate||19))?'selected':'')+'>'+r+'%</option>'; }).join('') +
    '</select></td>' +
    '<td class="td-total" id="rtotal-'+id+'">0,00</td>' +
    '<td class="td-del"><button type="button" onclick="removeRow('+id+')" style="border:none;background:none;cursor:pointer;color:#dc2626;font-size:1rem;padding:.15rem .3rem;line-height:1" title="Șterge rând">✕</button></td>';
  document.getElementById('rows-body').appendChild(tr);
  calc();
}

function removeRow(id) {
  var tr = document.getElementById('row-' + id);
  if (tr) tr.remove();
  calc();
}

// ── Calcule ───────────────────────────────────────────────────────────────────
function calc() {
  var subtotal = 0, tvaMap = {};
  document.querySelectorAll('#rows-body tr').forEach(function(tr) {
    var qty   = parseFloat(tr.querySelector('.qty')?.value || 0) || 0;
    var price = parseFloat(tr.querySelector('.price')?.value || 0) || 0;
    var tva   = parseFloat(tr.querySelector('.tva-sel')?.value || 0) || 0;
    var base  = qty * price;
    var tvAmt = base * tva / 100;
    subtotal += base;
    tvaMap[tva] = (tvaMap[tva] || 0) + tvAmt;
    var cell = tr.querySelector('[id^="rtotal-"]');
    if (cell) cell.textContent = fmtN(base + tvAmt);
  });

  var totalTva = Object.values(tvaMap).reduce(function(a,b){return a+b;}, 0);
  var total = subtotal + totalTva;

  document.getElementById('sum-subtotal').textContent = fmtN(subtotal) + ' RON';
  document.getElementById('sum-total').textContent    = fmtN(total) + ' RON';

  // TVA breakdown
  var bk = document.getElementById('tva-breakdown-rows');
  bk.innerHTML = '';
  Object.entries(tvaMap).forEach(function(entry) {
    var rate = entry[0], amt = entry[1];
    if (amt > 0.001) {
      bk.innerHTML += '<td style="color:var(--text-muted);padding:.3rem .75rem">TVA ' + rate + '%</td><td style="text-align:right;padding:.3rem .75rem;color:#dc2626">' + fmtN(amt) + ' RON</td>';
    }
  });
}

// ── PDF ───────────────────────────────────────────────────────────────────────
async function generatePDF() {
  var btn = document.getElementById('btn-pdf');
  var status = document.getElementById('gen-status');
  btn.disabled = true;
  btn.childNodes[1].textContent = ' Se generează...';
  status.textContent = '';

  try {
    // Colectăm datele
    var emitent = {
      den: v('e-den'), cui: v('e-cui'), adr: v('e-adr'),
      iban: v('e-iban'), tel: v('e-tel'), email: v('e-email'),
      tva: document.getElementById('e-tva').checked,
    };
    var client = { den: v('c-den'), cui: v('c-cui'), adr: v('c-adr') };
    var factura = { serie: v('f-serie'), data: v('f-data'), scad: v('f-scad'), note: v('f-note') };
    saveEmitent();

    // Construim tabelul de produse
    var rows = [], subtotal = 0, tvaMap = {};
    document.querySelectorAll('#rows-body tr').forEach(function(tr, idx) {
      var desc  = tr.querySelector('.desc')?.value || '';
      var qty   = parseFloat(tr.querySelector('.qty')?.value || 0) || 0;
      var price = parseFloat(tr.querySelector('.price')?.value || 0) || 0;
      var tva   = parseFloat(tr.querySelector('.tva-sel')?.value || 0) || 0;
      var base  = qty * price;
      var tvAmt = base * tva / 100;
      subtotal += base;
      tvaMap[tva] = (tvaMap[tva] || 0) + tvAmt;
      rows.push({idx:idx+1, desc:desc, qty:qty, price:price, tva:tva, total:base+tvAmt});
    });
    var totalTva = Object.values(tvaMap).reduce(function(a,b){return a+b;},0);
    var total = subtotal + totalTva;

    // Generăm HTML pentru invoice
    var rowsHtml = rows.map(function(r) {
      return '<tr style="border-bottom:1px solid #e5e7eb">' +
        '<td style="padding:6px 8px">' + r.idx + '</td>' +
        '<td style="padding:6px 8px">' + esc(r.desc) + '</td>' +
        '<td style="padding:6px 8px;text-align:center">' + r.qty + '</td>' +
        '<td style="padding:6px 8px;text-align:right">' + fmtN(r.price) + '</td>' +
        '<td style="padding:6px 8px;text-align:center">' + r.tva + '%</td>' +
        '<td style="padding:6px 8px;text-align:right;font-weight:700">' + fmtN(r.total) + '</td>' +
      '</tr>';
    }).join('');

    var tvaBreak = Object.entries(tvaMap).filter(function(e){return e[1]>0.001;}).map(function(e) {
      return '<tr><td style="padding:4px 10px;color:#6b7280;font-size:11px">TVA ' + e[0] + '%</td>' +
             '<td style="padding:4px 10px;text-align:right;font-size:11px;color:#dc2626">' + fmtN(e[1]) + ' RON</td></tr>';
    }).join('');

    var dataFmt = factura.data ? new Date(factura.data).toLocaleDateString('ro-RO') : '';
    var scadFmt = factura.scad ? new Date(factura.scad).toLocaleDateString('ro-RO') : '';

    var html = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#222;width:694px">' +

    // ── HEADER ──
    '<div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:18px;border-bottom:3px solid #1d3557;margin-bottom:20px">' +
      '<div>' +
        '<div style="font-size:18px;font-weight:900;color:#1d3557;margin-bottom:6px">' + esc(emitent.den) + '</div>' +
        (emitent.cui   ? '<div style="margin-bottom:2px"><b>CUI:</b> ' + esc(emitent.cui) + '</div>' : '') +
        (emitent.adr   ? '<div style="margin-bottom:2px">' + esc(emitent.adr) + '</div>' : '') +
        (emitent.iban  ? '<div style="margin-bottom:2px"><b>IBAN:</b> <span style="font-family:Courier,monospace">' + esc(emitent.iban) + '</span></div>' : '') +
        (emitent.tel   ? '<div style="margin-bottom:2px">&#128222; ' + esc(emitent.tel) + '</div>' : '') +
        (emitent.email ? '<div style="margin-bottom:2px">&#9993; ' + esc(emitent.email) + '</div>' : '') +
        (emitent.tva   ? '<div style="color:#15803d;font-weight:700;margin-top:4px">&#10003; Plătitor TVA</div>' : '') +
      '</div>' +
      '<div style="text-align:right">' +
        '<div style="font-size:26px;font-weight:900;color:#1d3557;letter-spacing:.04em">FACTURĂ</div>' +
        '<div style="font-size:15px;font-weight:700;color:#457b9d">' + esc(factura.serie) + '</div>' +
        (dataFmt ? '<div style="margin-top:8px"><b>Data:</b> ' + dataFmt + '</div>' : '') +
        (scadFmt ? '<div><b>Scadent:</b> ' + scadFmt + '</div>' : '') +
      '</div>' +
    '</div>' +

    // ── CLIENT ──
    '<div style="background:#f0f4ff;border-left:4px solid #457b9d;padding:10px 14px;margin-bottom:20px;border-radius:0 6px 6px 0">' +
      '<div style="font-size:9px;font-weight:700;text-transform:uppercase;color:#6b7280;margin-bottom:4px;letter-spacing:.08em">Facturată către</div>' +
      '<div style="font-size:14px;font-weight:800;color:#1d3557">' + esc(client.den) + '</div>' +
      (client.cui ? '<div><b>CUI:</b> ' + esc(client.cui) + '</div>' : '') +
      (client.adr ? '<div>' + esc(client.adr) + '</div>' : '') +
    '</div>' +

    // ── TABEL PRODUSE ──
    '<table style="width:100%;border-collapse:collapse;margin-bottom:16px">' +
      '<thead><tr style="background:#1d3557;color:#fff">' +
        '<th style="padding:7px 8px;text-align:left;width:24px">#</th>' +
        '<th style="padding:7px 8px;text-align:left">Descriere</th>' +
        '<th style="padding:7px 8px;text-align:center;width:45px">Cant.</th>' +
        '<th style="padding:7px 8px;text-align:right;width:90px">Preț/un.</th>' +
        '<th style="padding:7px 8px;text-align:center;width:45px">TVA</th>' +
        '<th style="padding:7px 8px;text-align:right;width:90px">Total RON</th>' +
      '</tr></thead>' +
      '<tbody>' + rowsHtml + '</tbody>' +
    '</table>' +

    // ── TOTALURI ──
    '<div style="display:flex;justify-content:flex-end;margin-bottom:18px">' +
      '<table style="border-collapse:collapse;min-width:260px">' +
        '<tr><td style="padding:4px 10px;color:#555">Subtotal (fără TVA)</td><td style="padding:4px 10px;text-align:right">' + fmtN(subtotal) + ' RON</td></tr>' +
        tvaBreak +
        '<tr style="background:#1d3557;color:#fff">' +
          '<td style="padding:9px 10px;font-weight:800;font-size:13px">TOTAL DE PLATĂ</td>' +
          '<td style="padding:9px 10px;text-align:right;font-weight:900;font-size:16px">' + fmtN(total) + ' RON</td>' +
        '</tr>' +
      '</table>' +
    '</div>' +

    // ── MENȚIUNI & IBAN ──
    (emitent.iban ? '<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:8px 12px;margin-bottom:10px"><b>Plata în cont:</b> ' + esc(emitent.iban) + '</div>' : '') +
    (factura.note ? '<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:6px;padding:8px 12px;margin-bottom:10px"><b>Mențiuni:</b> ' + esc(factura.note) + '</div>' : '') +

    // ── FOOTER ──
    '<div style="border-top:1px solid #e5e7eb;padding-top:10px;font-size:9px;color:#9ca3af;text-align:center">' +
      'Factura a fost emisă conform Legii nr. 227/2015 privind Codul Fiscal. Document generat electronic.' +
    '</div>' +

    '</div>';

    // Injectăm în template-ul ascuns
    var tpl = document.getElementById('invoice-tpl');
    tpl.innerHTML = html;
    tpl.style.left = '-9999px';
    tpl.style.display = 'block';

    // html2canvas → jsPDF
    var canvas = await html2canvas(tpl, {
      scale: 2,
      useCORS: true,
      backgroundColor: '#ffffff',
      logging: false,
      width: 794,
    });

    tpl.style.display = 'none';

    var imgData = canvas.toDataURL('image/jpeg', 0.96);
    var jsPDF = window.jspdf.jsPDF;
    var pdf = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });
    var pW = pdf.internal.pageSize.getWidth();
    var pH = pdf.internal.pageSize.getHeight();
    var imgH = canvas.height * pW / canvas.width;

    var y = 0;
    while (y < imgH) {
      if (y > 0) pdf.addPage();
      pdf.addImage(imgData, 'JPEG', 0, -y, pW, imgH);
      y += pH;
    }

    var filename = 'factura-' + (factura.serie || 'GR-001').replace(/[^a-zA-Z0-9-]/g, '-') + '.pdf';
    pdf.save(filename);
    status.textContent = '✓ PDF generat cu succes!';
    setTimeout(function(){ status.textContent = ''; }, 3000);

  } catch(err) {
    console.error(err);
    document.getElementById('gen-status').textContent = '✕ Eroare: ' + err.message + '. Verifică că ești online (bibliotecile se încarcă din CDN).';
  } finally {
    btn.disabled = false;
    btn.childNodes[1].textContent = ' Descarcă PDF';
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function v(id) {
  var el = document.getElementById(id);
  return el ? el.value.trim() : '';
}

function esc(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fmtN(n) {
  return Number(n).toFixed(2).replace('.', ',');
}

// ── Init ──────────────────────────────────────────────────────────────────────
loadEmitent();

// Date implicite
var today = new Date();
document.getElementById('f-data').value = today.toISOString().slice(0,10);
var due = new Date(today); due.setDate(due.getDate() + 30);
document.getElementById('f-scad').value = due.toISOString().slice(0,10);

// Rând inițial
addRow('', 1, 0, 19);
</script>

<?php require __DIR__ . '/../../templates/footer.php'; ?>
