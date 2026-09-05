<?php
// ── Configurare pagină ────────────────────────────────────────────────────────
// NOTĂ: API-ul ANAF webservicesp.anaf.ro a fost dezactivat în 2025.
// Toolul folosește acum openapi.ro (CORS activ, tier gratuit).
// Cheie gratuită: https://openapi.ro/docs — înregistrare în 2 minute.

$pageTitle       = 'Verificator CUI România — date firmă — GhidRomânesc';
$metaDescription = 'Verifici orice firmă din România după CUI: denumire, adresă, TVA, nr. Registrul Comerțului. Date actualizate, gratuit.';
$canonicalUrl    = SITE_DOMAIN . '/tooluri/verificator-cui/';

$toolFaq = [
    ['q' => 'Ce informații aflu dintr-un CUI?',
     'a' => 'CUI (Codul Unic de Înregistrare) identifică unic orice firmă din România. Prin verificare afli: denumirea completă, adresa sediului social, numărul din Registrul Comerțului (J.../RA...), data înregistrării, codul CAEN principal și statutul de plătitor TVA.'],
    ['q' => 'De ce este necesară o cheie API?',
     'a' => 'API-ul public ANAF (webservicesp.anaf.ro) a fost dezactivat în 2025. Toolul folosește acum openapi.ro, un serviciu comercial cu date din ANAF și Registrul Comerțului. Tier-ul gratuit oferă ~100 cereri/zi, suficient pentru uz personal. Înregistrarea este gratuită la openapi.ro.'],
    ['q' => 'CUI și CIF sunt același lucru?',
     'a' => 'Da. CUI (Cod Unic de Înregistrare) și CIF (Cod de Identificare Fiscală) se referă la același număr. Firmele plătitoare de TVA au prefixul RO pe facturi (ex: RO12345678), dar numărul propriu-zis este identic.'],
    ['q' => 'Butonul „Copiază date pentru factură" la ce folosește?',
     'a' => 'Copiază în clipboard datele firmei în formatul standard pentru facturi: Denumire, CUI, Registrul Comerțului, Adresă, TVA. Poți lipi direct în Word, Excel sau orice soft de facturare.'],
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
    <span aria-current="page">Verificator CUI</span>
  </nav>
  <h1 class="page-title">🏢 Verificator CUI România</h1>
  <p class="page-subtitle">Date despre orice firmă din România. Necesită cheie gratuită openapi.ro.</p>
</div></div>

<style>
.cui-search-card { background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.75rem 2rem;margin-bottom:1.25rem; }
.cui-input-row { display:flex;gap:.6rem;margin:.5rem 0 .4rem; }
.cui-input-row input { flex:1;font-size:1.15rem;font-weight:600;letter-spacing:.02em;padding:.7rem 1rem;border:2px solid var(--border);border-radius:10px;outline:none;font-family:inherit;color:var(--blue-dark);transition:border-color .15s; }
.cui-input-row input:focus { border-color:#457b9d; }
.cui-input-row input::placeholder { color:#94a3b8;font-weight:400; }
.cui-search-btn { padding:.7rem 1.5rem;background:var(--blue-dark);color:#fff;border:none;border-radius:10px;font-size:.95rem;font-weight:700;cursor:pointer;white-space:nowrap;font-family:inherit;transition:background .15s;display:flex;align-items:center;gap:.4rem; }
.cui-search-btn:hover { background:#457b9d; }
.cui-search-btn:disabled { background:#94a3b8;cursor:not-allowed; }
.cui-hint { font-size:.8rem;color:var(--text-muted); }
.cui-loading { text-align:center;padding:2.5rem 1rem;color:var(--text-muted); }
.cui-spinner { display:inline-block;width:32px;height:32px;border:3px solid #e2e8f0;border-top-color:#457b9d;border-radius:50%;animation:spin .8s linear infinite;margin-bottom:.75rem; }
@keyframes spin { to { transform:rotate(360deg); } }
.cui-error { background:#fef2f2;border:1.5px solid #fca5a5;border-radius:12px;padding:1.1rem 1.25rem;display:flex;gap:.75rem;align-items:flex-start; }
.cui-error-msg { font-size:.9rem;color:#b91c1c;line-height:1.5; }
.company-card { background:#fff;border:1.5px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:1.25rem; }
.company-header { padding:1.25rem 1.5rem;background:linear-gradient(135deg,#1d3557 0%,#2b4f76 100%);display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap; }
.company-name { font-size:1.2rem;font-weight:800;color:#fff;line-height:1.3;flex:1;min-width:0; }
.company-badges { display:flex;gap:.4rem;flex-wrap:wrap;flex-shrink:0; }
.cui-badge { padding:.25rem .7rem;border-radius:20px;font-size:.72rem;font-weight:700;letter-spacing:.03em;white-space:nowrap; }
.badge-active   { background:#bbf7d0;color:#15803d; }
.badge-inactive { background:#fecaca;color:#b91c1c; }
.badge-tva      { background:#bfdbfe;color:#1d4ed8; }
.company-body { padding:1.25rem 1.5rem; }
.company-grid { display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem; }
.cui-field { display:block; }
.cui-field-label { font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem; }
.cui-field-value { font-size:.92rem;font-weight:600;color:var(--blue-dark);line-height:1.4;word-break:break-word; }
.cui-field-full { margin-bottom:1rem; }
.cui-field-full .cui-field-value { font-size:.88rem;font-weight:400;color:#374151; }
.cui-tva-row { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:.75rem 1rem;margin-bottom:1.1rem;display:flex;align-items:center;gap:.6rem;font-size:.88rem; }
.cui-tva-row.no-tva { background:#f9fafb;border-color:#e5e7eb; }
.cui-tva-label { font-weight:700;color:var(--blue-dark);margin-right:.25rem; }
.company-actions { display:flex;gap:.6rem;flex-wrap:wrap;border-top:1px solid #f1f5f9;padding:1rem 1.5rem;background:#fafafa; }
.btn-copy-invoice { flex:1;min-width:200px;padding:.7rem 1.25rem;background:#1d3557;color:#fff;border:none;border-radius:10px;font-size:.9rem;font-weight:700;cursor:pointer;font-family:inherit;transition:background .15s;display:flex;align-items:center;justify-content:center;gap:.4rem; }
.btn-copy-invoice:hover { background:#457b9d; }
.btn-new-search { padding:.7rem 1.25rem;background:#fff;color:var(--blue-dark);border:1.5px solid var(--border);border-radius:10px;font-size:.88rem;font-weight:600;cursor:pointer;font-family:inherit;transition:border-color .15s,background .15s; }
.btn-new-search:hover { border-color:#457b9d;background:#f0f4ff; }
/* API key setup */
.api-setup { background:#fffbeb;border:1.5px solid #fcd34d;border-radius:14px;padding:1.5rem 1.75rem;margin-bottom:1.25rem; }
.api-key-input-row { display:flex;gap:.6rem;margin-top:.75rem; }
.api-key-input-row input { flex:1;padding:.55rem .85rem;border:1.5px solid #d1d5db;border-radius:8px;font-family:monospace;font-size:.875rem; }
.api-key-input-row input:focus { outline:none;border-color:#457b9d; }
@media (max-width:600px) {
  .cui-search-card { padding:1.25rem; }
  .cui-input-row { flex-direction:column; }
  .cui-search-btn { justify-content:center; }
  .company-grid { grid-template-columns:1fr; }
  .company-header,.company-body,.company-actions { padding:1rem 1.1rem; }
  .company-name { font-size:1.05rem; }
  .btn-copy-invoice { min-width:unset; }
}
</style>

<div class="container-sm" style="padding-top:1.5rem;padding-bottom:4rem">

  <!-- API Key Setup (vizibil doar fără cheie) -->
  <div class="api-setup" id="api-setup-box">
    <div style="display:flex;align-items:flex-start;gap:.75rem">
      <span style="font-size:1.3rem;flex-shrink:0">🔑</span>
      <div>
        <strong style="color:#92400e;font-size:.95rem">Necesită cheie API gratuită openapi.ro</strong>
        <p style="color:#78350f;font-size:.85rem;margin:.4rem 0 0;line-height:1.6">
          API-ul public ANAF a fost <strong>dezactivat în 2025</strong>. Toolul folosește acum <strong>openapi.ro</strong>, care oferă date din ANAF + Registrul Comerțului.<br>
          <strong>Tier gratuit:</strong> ~100 cereri/zi — suficient pentru uz personal.
          <a href="https://openapi.ro/docs" target="_blank" rel="noopener" style="color:#b45309;font-weight:700">Obține cheie gratuită →</a>
        </p>
        <div class="api-key-input-row">
          <input type="password" id="api-key-input" placeholder="Lipește cheia API openapi.ro here..." autocomplete="off">
          <button onclick="saveApiKey()" class="cui-search-btn" style="white-space:nowrap;padding:.55rem 1rem;font-size:.875rem">💾 Salvează</button>
          <button onclick="testApiKey()" class="btn-new-search" style="font-size:.82rem;padding:.5rem .85rem">🧪 Testează</button>
        </div>
        <div id="key-status" style="font-size:.8rem;margin-top:.4rem;min-height:1.2em"></div>
      </div>
    </div>
  </div>

  <!-- Căutare -->
  <div class="cui-search-card">
    <label for="cui-input" style="font-weight:700;color:var(--blue-dark);font-size:.9rem">CUI sau CIF firmă</label>
    <div class="cui-input-row">
      <input type="text" id="cui-input" inputmode="numeric"
        placeholder="ex: 12345678 sau RO12345678"
        autocomplete="off" spellcheck="false" maxlength="12"
        aria-label="Introdu CUI-ul firmei">
      <button class="cui-search-btn" id="search-btn" type="button">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        Verifică
      </button>
    </div>
    <p class="cui-hint">Acceptă cu sau fără prefixul RO · ex: 4266570 sau RO4266570</p>
  </div>

  <!-- Loading -->
  <div id="state-loading" style="display:none">
    <div class="cui-loading">
      <div class="cui-spinner"></div>
      <div style="font-weight:600;color:var(--blue-dark)">Se caută firma...</div>
    </div>
  </div>

  <!-- Eroare -->
  <div id="state-error" style="display:none">
    <div class="cui-error">
      <div style="font-size:1.2rem;flex-shrink:0">⚠️</div>
      <div>
        <div style="font-weight:700;margin-bottom:.25rem">Eroare</div>
        <div class="cui-error-msg" id="error-msg">—</div>
      </div>
    </div>
  </div>

  <!-- Rezultat -->
  <div id="state-result" style="display:none">
    <div class="company-card" role="region" aria-label="Date firmă">
      <div class="company-header">
        <div class="company-name" id="r-name">—</div>
        <div class="company-badges">
          <span class="cui-badge badge-active" id="r-stare-badge">ACTIV</span>
          <span class="cui-badge badge-tva" id="r-tva-badge" style="display:none">Plătitor TVA</span>
        </div>
      </div>
      <div class="company-body">
        <div class="company-grid">
          <div class="cui-field"><div class="cui-field-label">CUI / CIF</div><div class="cui-field-value" id="r-cui">—</div></div>
          <div class="cui-field"><div class="cui-field-label">Nr. Registrul Comerțului</div><div class="cui-field-value" id="r-regcom">—</div></div>
          <div class="cui-field"><div class="cui-field-label">Data înregistrare</div><div class="cui-field-value" id="r-data">—</div></div>
          <div class="cui-field"><div class="cui-field-label">Cod CAEN</div><div class="cui-field-value" id="r-caen">—</div></div>
        </div>
        <div class="cui-field cui-field-full">
          <div class="cui-field-label">Adresă sediu social</div>
          <div class="cui-field-value" id="r-adresa">—</div>
        </div>
        <div class="cui-tva-row" id="r-tva-row">
          <span id="r-tva-icon">✓</span>
          <span><span class="cui-tva-label">TVA:</span><span id="r-tva-text">—</span></span>
        </div>
      </div>
      <div class="company-actions">
        <button class="btn-copy-invoice" id="copy-btn" type="button">📋 Copiază date pentru factură</button>
        <button class="btn-new-search" id="new-search-btn" type="button">🔍 Altă firmă</button>
      </div>
    </div>
    <p style="font-size:.75rem;color:var(--text-muted);text-align:center">
      Date via openapi.ro · <span id="r-timestamp"></span>
    </p>
  </div>

</div><!-- /.container-sm -->

<!-- FAQ + Related tools (PHP) -->
<div class="container-sm" style="padding-bottom:1.5rem">
  <h2 style="font-size:1.1rem;font-weight:800;color:var(--blue-dark);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid var(--border)">
    Întrebări frecvente — verificator CUI România
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
      ['slug'=>'verificator-cnp',  'icon'=>'🪪','title'=>'Verificator CNP'],
      ['slug'=>'verificator-iban', 'icon'=>'🏦','title'=>'Verificator IBAN'],
      ['slug'=>'calculator-tva',   'icon'=>'🧮','title'=>'Calculator TVA'],
      ['slug'=>'generator-factura','icon'=>'🧾','title'=>'Generator factură PDF'],
    ] as $r): ?>
    <a href="/tooluri/<?= $r['slug'] ?>/" style="display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;border:1.5px solid var(--border);border-radius:10px;text-decoration:none;background:#fff;font-size:.875rem;font-weight:600;color:var(--blue-dark);transition:border-color .15s" onmouseover="this.style.borderColor='#457b9d'" onmouseout="this.style.borderColor='var(--border)'">
      <span><?= $r['icon'] ?></span><?= e($r['title']) ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<script>
'use strict';

var LS_KEY = 'openapi-ro-key-v1';
var firmData = null, firmCui = '';

// ── API Key management ────────────────────────────────────────────────────────
function loadApiKey() {
  var k = '';
  try { k = localStorage.getItem(LS_KEY) || ''; } catch(e) {}
  if (k) {
    document.getElementById('api-key-input').value = k;
    document.getElementById('api-setup-box').style.display = 'none';
  }
  return k;
}

function saveApiKey() {
  var k = document.getElementById('api-key-input').value.trim();
  if (!k) { keyStatus('Introdu cheia API.', 'error'); return; }
  try { localStorage.setItem(LS_KEY, k); } catch(e) {}
  document.getElementById('api-setup-box').style.display = 'none';
  keyStatus('✓ Cheie salvată!', 'ok');
}

function clearApiKey() {
  try { localStorage.removeItem(LS_KEY); } catch(e) {}
  document.getElementById('api-key-input').value = '';
  document.getElementById('api-setup-box').style.display = '';
  showState('');
}

async function testApiKey() {
  var k = document.getElementById('api-key-input').value.trim();
  if (!k) { keyStatus('Introdu mai întâi cheia.', 'error'); return; }
  keyStatus('Se testează...', 'neutral');
  try {
    var res = await fetch('https://api.openapi.ro/api/companies/4266570', {
      headers: { 'x-api-key': k, 'Accept': 'application/json' }
    });
    if (res.status === 200) {
      keyStatus('✓ Cheie validă! Poți căuta firme acum.', 'ok');
      try { localStorage.setItem(LS_KEY, k); } catch(e) {}
      document.getElementById('api-setup-box').style.display = 'none';
    } else if (res.status === 401 || res.status === 403) {
      keyStatus('✕ Cheie invalidă sau expirată. Verifică la openapi.ro.', 'error');
    } else {
      keyStatus('Răspuns neașteptat HTTP ' + res.status, 'error');
    }
  } catch(e) {
    keyStatus('Eroare conexiune: ' + e.message, 'error');
  }
}

function keyStatus(msg, type) {
  var el = document.getElementById('key-status');
  el.textContent = msg;
  el.style.color = type === 'ok' ? '#15803d' : type === 'error' ? '#dc2626' : '#6b7280';
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmtDate(d) {
  if (!d || d === 'null') return '—';
  var p = String(d).split(/[-\/]/);
  if (p.length === 3) return (p[2].length === 4 ? p[0]+'.'+p[1]+'.'+p[2] : p[2]+'.'+p[1]+'.'+p[0]);
  return d;
}

function showState(s) {
  ['loading','error','result'].forEach(function(n) {
    var el = document.getElementById('state-' + n);
    if (el) el.style.display = n === s ? '' : 'none';
  });
}

function showError(msg) {
  document.getElementById('error-msg').innerHTML = msg;
  showState('error');
  setSearching(false);
}

function setSearching(on) {
  var btn = document.getElementById('search-btn');
  btn.disabled = on;
  if (on) {
    btn.innerHTML = 'Se caută...';
  } else {
    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg> Verifică';
  }
}

// ── Lookup ────────────────────────────────────────────────────────────────────
async function lookup() {
  var raw = document.getElementById('cui-input').value.trim();
  var cui = raw.replace(/^RO\s*/i, '').replace(/[^0-9]/g, '');

  if (!cui || cui.length < 2 || cui.length > 9) {
    showError('CUI invalid. Trebuie să conțină 2–9 cifre (cu sau fără prefixul RO).');
    return;
  }

  var apiKey = '';
  try { apiKey = localStorage.getItem(LS_KEY) || ''; } catch(e) {}

  if (!apiKey) {
    document.getElementById('api-setup-box').style.display = '';
    document.getElementById('api-setup-box').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    return;
  }

  showState('loading');
  setSearching(true);

  try {
    var res = await fetch('https://api.openapi.ro/api/companies/' + encodeURIComponent(cui), {
      headers: { 'x-api-key': apiKey, 'Accept': 'application/json' }
    });

    if (res.status === 404) {
      showError('Firma cu CUI ' + cui + ' nu a fost găsită. Verifică dacă ai introdus corect numărul.');
      return;
    }
    if (res.status === 401 || res.status === 403) {
      showError('Cheie API invalidă sau expirată. <a href="#" onclick="clearApiKey();return false;" style="color:#b91c1c">Resetează cheia →</a>');
      return;
    }
    if (!res.ok) {
      showError('Eroare API (' + res.status + '). Încearcă din nou.');
      return;
    }

    var json = await res.json();
    if (!json || json.error) {
      showError(json?.error || 'Răspuns neașteptat de la API.');
      return;
    }

    firmData = json;
    firmCui  = cui;
    renderResult();
    showState('result');

  } catch(e) {
    showError('Eroare de conexiune: ' + e.message);
    console.error('[CUI]', e);
  } finally {
    setSearching(false);
  }
}

// ── Render ────────────────────────────────────────────────────────────────────
function renderResult() {
  var d = firmData;

  // Normalizăm câmpurile din openapi.ro (format poate varia ușor)
  var name    = d.denumire || d.name || '—';
  var address = d.adresa   || d.address || d.sediu || '—';
  var regcom  = d.nr_reg_com || d.nrRegCom || d.numar_reg_com || '—';
  var caen    = d.cod_CAEN   || d.codCaen  || d.caen || '—';
  var dataInreg = d.data_inregistrare || d.dataInregistrare || '—';
  var isActive  = String(d.stare || d.status || 'ACTIV').toUpperCase().includes('ACTIV');
  var hasTva    = d.platitor_tva === true || d.tva?.activ === true || d.tva_platitor === true;
  var tvaSince  = d.data_inregistrare_tva || d.tva?.data_inregistrare || d.dataTvaActiv || null;
  var cui       = d.cod_unic || d.cui || firmCui;

  document.getElementById('r-name').textContent = name;
  document.getElementById('r-cui').textContent  = hasTva ? 'RO' + cui : String(cui);

  var sb = document.getElementById('r-stare-badge');
  sb.textContent = isActive ? 'ACTIV' : 'INACTIV';
  sb.className   = 'cui-badge ' + (isActive ? 'badge-active' : 'badge-inactive');

  var tb = document.getElementById('r-tva-badge');
  tb.style.display = hasTva ? '' : 'none';

  document.getElementById('r-regcom').textContent = regcom;
  document.getElementById('r-data').textContent   = fmtDate(dataInreg);
  document.getElementById('r-caen').textContent   = caen;
  document.getElementById('r-adresa').textContent = address;

  var tvaRow  = document.getElementById('r-tva-row');
  var tvaTxt  = document.getElementById('r-tva-text');
  var tvaIcon = document.getElementById('r-tva-icon');
  if (hasTva) {
    tvaTxt.textContent = tvaSince ? ' Înregistrat din ' + fmtDate(tvaSince) : ' Înregistrat în scopuri de TVA';
    tvaRow.className   = 'cui-tva-row';
    tvaIcon.textContent = '✓';
  } else {
    tvaTxt.textContent = ' Neplătitor TVA';
    tvaRow.className   = 'cui-tva-row no-tva';
    tvaIcon.textContent = '—';
  }

  document.getElementById('r-timestamp').textContent =
    'Interogat ' + new Date().toLocaleDateString('ro-RO', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});

  // Stocăm pentru copiere
  firmData._cuiFmt  = hasTva ? 'RO' + cui : String(cui);
  firmData._name    = name;
  firmData._address = address;
  firmData._regcom  = regcom;
  firmData._tvaTxt  = hasTva ? 'Plătitor' + (tvaSince ? ' (din ' + fmtDate(tvaSince) + ')' : '') : 'Neplătitor';
}

// ── Copy ──────────────────────────────────────────────────────────────────────
function copyInvoice() {
  if (!firmData) return;
  var text = [
    firmData._name,
    'CUI: ' + firmData._cuiFmt,
    'Reg. Com.: ' + firmData._regcom,
    'Adresă: ' + firmData._address,
    'TVA: ' + firmData._tvaTxt,
  ].join('\n');

  var btn = document.getElementById('copy-btn');
  function flash(ok) {
    btn.textContent = ok ? '✓ Date copiate!' : '✕ Eroare copiere';
    btn.style.background = ok ? '#16a34a' : '#dc2626';
    setTimeout(function() { btn.textContent = '📋 Copiază date pentru factură'; btn.style.background = ''; }, 2200);
  }

  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(text).then(function() { flash(true); }).catch(function() { flash(false); });
  } else {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;opacity:0;pointer-events:none';
    document.body.appendChild(ta);
    ta.focus(); ta.select();
    try { document.execCommand('copy'); flash(true); } catch(e) { flash(false); }
    document.body.removeChild(ta);
  }
}

function newSearch() {
  firmData = null; firmCui = '';
  showState('');
  var inp = document.getElementById('cui-input');
  inp.value = '';
  inp.focus();
}

// ── Events ────────────────────────────────────────────────────────────────────
document.getElementById('search-btn').addEventListener('click', lookup);
document.getElementById('cui-input').addEventListener('keydown', function(e) { if (e.key === 'Enter') lookup(); });
document.getElementById('cui-input').addEventListener('input', function() {
  var v = this.value.toUpperCase();
  if (v.startsWith('RO')) this.value = 'RO' + v.slice(2).replace(/\D/g,'').slice(0,9);
  else this.value = v.replace(/[^0-9RO]/g,'');
});
document.getElementById('copy-btn').addEventListener('click', copyInvoice);
document.getElementById('new-search-btn').addEventListener('click', newSearch);
document.getElementById('api-key-input').addEventListener('keydown', function(e) { if (e.key === 'Enter') saveApiKey(); });

// Init
loadApiKey();
document.getElementById('cui-input').focus();
</script>

<?php require __DIR__ . '/../../templates/footer.php'; ?>
