<?php
$pageTitle       = 'Convertor valutar 2026 — Curs BNR oficial în timp real';
$metaDescription = 'Convertești lei (RON) în euro, dolari, lire, franci sau orice altă valută. Curs oficial BNR actualizat zilnic. Calculator valutar gratuit, fără publicitate.';
$canonicalUrl    = SITE_DOMAIN . '/tooluri/convertor-valutar/';
require __DIR__ . '/../../templates/header.php';

// Limbi principale pentru afișare
$currencyNames = [
    'RON' => ['🇷🇴', 'Leu românesc'],
    'EUR' => ['🇪🇺', 'Euro'],
    'USD' => ['🇺🇸', 'Dolar american'],
    'GBP' => ['🇬🇧', 'Liră sterlină'],
    'CHF' => ['🇨🇭', 'Franc elvețian'],
    'HUF' => ['🇭🇺', 'Forint maghiar'],
    'BGN' => ['🇧🇬', 'Leva bulgărească'],
    'MDL' => ['🇲🇩', 'Leu moldovenesc'],
    'UAH' => ['🇺🇦', 'Hryvnă ucraineană'],
    'PLN' => ['🇵🇱', 'Zlot polonez'],
    'CZK' => ['🇨🇿', 'Coroană cehă'],
    'TRY' => ['🇹🇷', 'Liră turcească'],
    'AUD' => ['🇦🇺', 'Dolar australian'],
    'CAD' => ['🇨🇦', 'Dolar canadian'],
    'SEK' => ['🇸🇪', 'Coroană suedeză'],
    'NOK' => ['🇳🇴', 'Coroană norvegiană'],
    'DKK' => ['🇩🇰', 'Coroană daneză'],
    'JPY' => ['🇯🇵', 'Yen japonez'],
    'CNY' => ['🇨🇳', 'Yuan chinezesc'],
    'AED' => ['🇦🇪', 'Dirham emiratez'],
    'RUB' => ['🇷🇺', 'Rublă rusească'],
    'NZD' => ['🇳🇿', 'Dolar neozeelandez'],
    'MXN' => ['🇲🇽', 'Peso mexican'],
    'ZAR' => ['🇿🇦', 'Rand sud-african'],
    'INR' => ['🇮🇳', 'Rupie indiană'],
    'BRL' => ['🇧🇷', 'Real brazilian'],
    'KRW' => ['🇰🇷', 'Won sud-coreean'],
    'SGD' => ['🇸🇬', 'Dolar singaporez'],
    'HKD' => ['🇭🇰', 'Dolar Hong Kong'],
    'ISK' => ['🇮🇸', 'Coroană islandeză'],
    'HRK' => ['🇭🇷', 'Kuna croată'],
    'THB' => ['🇹🇭', 'Baht tailandez'],
    'IDR' => ['🇮🇩', 'Rupie indonesiană'],
    'PHP' => ['🇵🇭', 'Peso filipinez'],
    'MYR' => ['🇲🇾', 'Ringgit malaiezian'],
    'TWD' => ['🇹🇼', 'Dolar taiwanez'],
    'XAU' => ['🥇', 'Aur (uncie troy)'],
    'SDR' => ['🌐', 'DST (FMI)'],
];

$popularCurrencies = ['EUR','USD','GBP','CHF','HUF','MDL','BGN','UAH','PLN','TRY'];
?>
<div class="page-header"><div class="container">
  <nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom:.5rem;color:rgba(255,255,255,.65);font-size:.85rem">
    <a href="/" style="color:inherit">Acasă</a>
    <span class="breadcrumb-sep" aria-hidden="true">›</span>
    <a href="/tooluri/" style="color:inherit">Tooluri</a>
    <span class="breadcrumb-sep" aria-hidden="true">›</span>
    <span aria-current="page">Convertor valutar</span>
  </nav>
  <h1 class="page-title">💱 Convertor valutar BNR</h1>
  <p class="page-subtitle">Curs oficial BNR actualizat zilnic — conversia se face instant în browser.</p>
</div></div>

<style>
.cv-card { background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.75rem;margin-bottom:1rem; }
.cv-card-title { font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:1.25rem;padding-bottom:.6rem;border-bottom:1px solid #f1f5f9; }

/* Calculator principal */
.cv-main { display:grid;grid-template-columns:1fr auto 1fr;gap:.75rem;align-items:end;margin-bottom:1.25rem; }
.cv-field label { display:block;font-size:.75rem;font-weight:700;color:var(--blue-dark);margin-bottom:.35rem; }
.cv-input-wrap { position:relative; }
.cv-amount { width:100%;padding:.75rem 1rem;border:2px solid #d1d5db;border-radius:10px;font-size:1.25rem;font-weight:700;font-family:inherit;color:#1f2937;background:#fff;transition:border-color .15s;box-sizing:border-box; }
.cv-amount:focus { outline:none;border-color:#457b9d;box-shadow:0 0 0 3px rgba(69,123,157,.12); }
.cv-select-wrap { position:relative;margin-top:.5rem; }
.cv-select { width:100%;padding:.65rem .9rem;border:1.5px solid #d1d5db;border-radius:9px;font-size:.9rem;font-family:inherit;color:#1f2937;background:#fff;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236b7280' stroke-width='1.5' fill='none'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .75rem center;padding-right:2rem;transition:border-color .15s;cursor:pointer; }
.cv-select:focus { outline:none;border-color:#457b9d; }

/* Swap */
.cv-swap-col { display:flex;flex-direction:column;align-items:center;justify-content:flex-end;padding-bottom:.1rem; }
.cv-swap { width:44px;height:44px;border-radius:50%;border:2px solid #457b9d;background:#fff;color:#457b9d;font-size:1.2rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;flex-shrink:0; }
.cv-swap:hover { background:#457b9d;color:#fff; }

/* Result */
.cv-result-box { background:linear-gradient(135deg,#1d3557,#457b9d);border-radius:12px;padding:1.25rem 1.5rem;text-align:center;margin-bottom:1.25rem;min-height:90px;display:flex;flex-direction:column;align-items:center;justify-content:center; }
.cv-result-amount { font-size:2.25rem;font-weight:900;color:#fff;letter-spacing:-.02em;line-height:1; }
.cv-result-currency { font-size:1rem;font-weight:600;color:rgba(255,255,255,.75);margin-top:.3rem; }
.cv-result-rate { font-size:.8rem;color:rgba(255,255,255,.55);margin-top:.5rem; }
.cv-result-placeholder { font-size:1rem;color:rgba(255,255,255,.5); }

/* Quick shortcuts */
.cv-quick-title { font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.6rem; }
.cv-quick-grid { display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.25rem; }
.cv-quick-btn { padding:.4rem .9rem;border:1.5px solid #d1d5db;border-radius:20px;background:#fff;font-size:.82rem;font-weight:600;color:#374151;cursor:pointer;transition:all .15s;font-family:inherit;white-space:nowrap; }
.cv-quick-btn:hover { border-color:#457b9d;color:#457b9d;background:#eff6ff; }
.cv-quick-btn.active { border-color:#457b9d;background:#457b9d;color:#fff; }

/* Source info */
.cv-source { display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:var(--text-muted);margin-top:.75rem;padding-top:.75rem;border-top:1px solid #f1f5f9; }
.cv-source a { color:#457b9d; }
.cv-dot { width:7px;height:7px;border-radius:50%;background:#22c55e;flex-shrink:0; }
.cv-dot.loading { background:#f59e0b;animation:pulse 1.2s infinite; }
.cv-dot.error { background:#ef4444; }
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.35} }

/* Tabel cursuri */
.cv-rates-table { width:100%;border-collapse:collapse;font-size:.875rem; }
.cv-rates-table th { background:#f8fafc;padding:.55rem .75rem;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left; }
.cv-rates-table td { padding:.5rem .75rem;border-bottom:1px solid #f3f4f6;vertical-align:middle; }
.cv-rates-table tr:last-child td { border-bottom:none; }
.cv-rates-table tr:hover td { background:#f8fafc; }
.cv-rates-table .td-flag { font-size:1.1rem;width:32px; }
.cv-rates-table .td-rate { font-weight:700;color:var(--blue-dark);text-align:right; }
.cv-rates-table .td-inv { color:var(--text-muted);font-size:.8rem;text-align:right; }
.cv-rates-table tr { cursor:pointer; }

@media (max-width:600px) {
  .cv-main { grid-template-columns:1fr;gap:.5rem; }
  .cv-swap-col { flex-direction:row;justify-content:center;padding-bottom:0; }
  .cv-swap { transform:rotate(90deg); }
  .cv-result-amount { font-size:1.75rem; }
  .cv-card { padding:1.1rem; }
}
</style>

<div class="container" style="max-width:760px;padding-top:1.5rem;padding-bottom:2rem">

  <!-- Card principal -->
  <div class="cv-card">
    <div class="cv-card-title">Calculator valutar</div>

    <!-- Suma și valute -->
    <div class="cv-main">
      <div class="cv-field">
        <label for="cv-from-amount">Sumă</label>
        <div class="cv-input-wrap">
          <input type="number" id="cv-from-amount" class="cv-amount" value="1" min="0" step="any" autocomplete="off" inputmode="decimal">
        </div>
        <div class="cv-select-wrap">
          <select id="cv-from-currency" class="cv-select"></select>
        </div>
      </div>

      <div class="cv-swap-col">
        <button class="cv-swap" id="cv-swap-btn" title="Inversează valutele" aria-label="Inversează valutele">⇄</button>
      </div>

      <div class="cv-field">
        <label for="cv-to-amount">Rezultat</label>
        <div class="cv-input-wrap">
          <input type="number" id="cv-to-amount" class="cv-amount" style="background:#f8fafc;border-color:#e5e7eb" readonly tabindex="-1">
        </div>
        <div class="cv-select-wrap">
          <select id="cv-to-currency" class="cv-select"></select>
        </div>
      </div>
    </div>

    <!-- Caseta rezultat mare -->
    <div class="cv-result-box" id="cv-result-box">
      <div class="cv-result-placeholder">Introduci suma și selectezi valutele</div>
    </div>

    <!-- Shortcut-uri valute populare -->
    <div class="cv-quick-title">Valute frecvente</div>
    <div class="cv-quick-grid" id="cv-quick-grid"></div>

    <!-- Sursă -->
    <div class="cv-source">
      <span class="cv-dot loading" id="cv-dot"></span>
      <span id="cv-source-text">Se încarcă cursurile BNR...</span>
      <a href="https://www.bnr.ro/Cursul-de-schimb--1224.aspx" target="_blank" rel="noopener noreferrer" style="margin-left:auto">bnr.ro →</a>
    </div>
  </div>

  <!-- Tabel cursuri față de RON -->
  <div class="cv-card">
    <div class="cv-card-title">Cursuri față de RON — <span id="cv-table-date">—</span></div>
    <div style="overflow-x:auto">
      <table class="cv-rates-table" id="cv-rates-table">
        <thead>
          <tr>
            <th class="td-flag"></th>
            <th>Valută</th>
            <th style="text-align:right">1 unitate → RON</th>
            <th style="text-align:right">1 RON →</th>
          </tr>
        </thead>
        <tbody id="cv-rates-body">
          <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:1.5rem">Se încarcă...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Notă SEO -->
  <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:1rem 1.25rem;font-size:.82rem;color:var(--text-muted);line-height:1.6">
    <strong style="color:#374151">ℹ️ Despre cursul BNR:</strong>
    Cursul de schimb afișat este cursul de referință publicat de Banca Națională a României (BNR).
    Acesta este actualizat în fiecare zi lucrătoare în jurul orei 13:00 și este valabil pentru ziua respectivă.
    Cursul BNR este folosit pentru contabilitate, declarații fiscale și calcule oficiale — nu reprezintă cursul la care schimbi efectiv bani la casă.
  </div>

</div>

<script>
(function () {
  var NAMES = <?= json_encode($currencyNames, JSON_UNESCAPED_UNICODE) ?>;
  var POPULAR = <?= json_encode($popularCurrencies) ?>;

  var rates = null;     // { CURRENCY: { rate, multiplier } }  rate = X RON per `multiplier` units
  var bnrDate = '';

  var fromSel  = document.getElementById('cv-from-currency');
  var toSel    = document.getElementById('cv-to-currency');
  var fromAmt  = document.getElementById('cv-from-amount');
  var toAmt    = document.getElementById('cv-to-amount');
  var resultBox = document.getElementById('cv-result-box');
  var quickGrid = document.getElementById('cv-quick-grid');
  var dot       = document.getElementById('cv-dot');
  var sourceText = document.getElementById('cv-source-text');
  var tableDate  = document.getElementById('cv-table-date');
  var ratesBody  = document.getElementById('cv-rates-body');

  // Returnează rata în RON pentru 1 unitate din valuta dată
  function toRon(currency) {
    if (currency === 'RON') return 1;
    var r = rates[currency];
    if (!r) return null;
    return r.rate / r.multiplier;
  }

  function convert(amount, from, to) {
    if (!rates) return null;
    var fromRate = toRon(from);
    var toRate   = toRon(to);
    if (!fromRate || !toRate) return null;
    return (amount * fromRate) / toRate;
  }

  function fmt(n, currency) {
    if (n === null || isNaN(n)) return '—';
    // Ajustăm zecimalele: JPY, HUF, IDR → 0-2; restul 2-6
    var bigInt = ['JPY','IDR','KRW','HUF','ISK'];
    var decimals = bigInt.indexOf(currency) !== -1 ? 2 : 4;
    if (Math.abs(n) >= 100) decimals = 2;
    if (Math.abs(n) >= 1000) decimals = 2;
    return n.toLocaleString('ro-RO', {minimumFractionDigits:2, maximumFractionDigits:decimals});
  }

  function currencyLabel(code) {
    var info = NAMES[code];
    return info ? info[0] + ' ' + code + ' — ' + info[1] : code;
  }

  function buildSelects(allCodes) {
    // Ordonăm: populare primele, restul alfabetic
    var rest = allCodes.filter(function(c){ return POPULAR.indexOf(c) === -1 && c !== 'RON'; });
    rest.sort();
    var ordered = ['RON'].concat(POPULAR.filter(function(c){ return allCodes.indexOf(c) !== -1; })).concat(rest);

    [fromSel, toSel].forEach(function(sel) {
      sel.innerHTML = '';
      ordered.forEach(function(code) {
        var opt = document.createElement('option');
        opt.value = code;
        opt.textContent = currencyLabel(code);
        sel.appendChild(opt);
      });
    });

    fromSel.value = 'RON';
    toSel.value   = 'EUR';
  }

  function buildQuickGrid() {
    quickGrid.innerHTML = '';
    POPULAR.forEach(function(code) {
      if (!rates[code]) return;
      var btn = document.createElement('button');
      btn.className = 'cv-quick-btn';
      btn.textContent = (NAMES[code] ? NAMES[code][0] + ' ' : '') + code;
      btn.dataset.code = code;
      btn.addEventListener('click', function() {
        // Dacă FROM=RON sau dacă TO este deja setată la altceva, schimbăm TO
        if (fromSel.value === 'RON' || toSel.value === 'RON') {
          // Setăm valuta "cealaltă" (non-RON)
          if (fromSel.value === 'RON') toSel.value = code;
          else fromSel.value = code;
        } else {
          toSel.value = code;
        }
        updateQuickActive();
        doConvert();
      });
      quickGrid.appendChild(btn);
    });
    updateQuickActive();
  }

  function updateQuickActive() {
    var active = toSel.value !== 'RON' ? toSel.value : fromSel.value;
    quickGrid.querySelectorAll('.cv-quick-btn').forEach(function(btn) {
      btn.classList.toggle('active', btn.dataset.code === active);
    });
  }

  function buildRatesTable() {
    var show = POPULAR.concat(Object.keys(rates).filter(function(c){
      return c !== 'RON' && POPULAR.indexOf(c) === -1;
    }).sort());

    var html = '';
    show.forEach(function(code) {
      if (!rates[code]) return;
      var info = NAMES[code] || ['', code];
      var ronRate = toRon(code);
      var invRate = 1 / ronRate;
      html += '<tr onclick="selectCurrency(\'' + code + '\')">' +
        '<td class="td-flag">' + info[0] + '</td>' +
        '<td><strong>' + code + '</strong> <span style="color:var(--text-muted);font-size:.8rem">' + info[1] + '</span></td>' +
        '<td class="td-rate">' + fmt(ronRate, 'RON') + ' RON</td>' +
        '<td class="td-inv">' + fmt(invRate, code) + ' ' + code + '</td>' +
        '</tr>';
    });
    ratesBody.innerHTML = html;
    tableDate.textContent = bnrDate;
  }

  window.selectCurrency = function(code) {
    if (fromSel.value === 'RON') toSel.value = code;
    else if (toSel.value === 'RON') fromSel.value = code;
    else toSel.value = code;
    updateQuickActive();
    doConvert();
    window.scrollTo({top: 0, behavior: 'smooth'});
  };

  function doConvert() {
    if (!rates) return;
    var amount = parseFloat(fromAmt.value);
    var from   = fromSel.value;
    var to     = toSel.value;

    if (isNaN(amount) || amount === 0) {
      toAmt.value = '';
      resultBox.innerHTML = '<div class="cv-result-placeholder">Introduci suma</div>';
      return;
    }

    var result = convert(amount, from, to);
    if (result === null) {
      toAmt.value = '—';
      resultBox.innerHTML = '<div class="cv-result-placeholder">Valută indisponibilă</div>';
      return;
    }

    toAmt.value = fmt(result, to);

    var fromInfo = NAMES[from] || ['', from];
    var toInfo   = NAMES[to]   || ['', to];
    var rate1 = convert(1, from, to);

    resultBox.innerHTML =
      '<div class="cv-result-amount">' + fmt(result, to) + '</div>' +
      '<div class="cv-result-currency">' + toInfo[0] + ' ' + to + ' — ' + toInfo[1] + '</div>' +
      '<div class="cv-result-rate">1 ' + from + ' = ' + fmt(rate1, to) + ' ' + to +
        (from !== 'RON' && to !== 'RON' ? ' &nbsp;|&nbsp; 1 ' + to + ' = ' + fmt(1/rate1, from) + ' ' + from : '') +
      '</div>';

    updateQuickActive();
  }

  // Reverse: dacă utilizatorul editează câmpul rezultat
  toAmt.removeAttribute('readonly');
  toAmt.addEventListener('input', function() {
    if (!rates) return;
    var amount = parseFloat(toAmt.value);
    var from   = fromSel.value;
    var to     = toSel.value;
    if (isNaN(amount)) { fromAmt.value = ''; return; }
    var result = convert(amount, to, from);
    if (result !== null) fromAmt.value = fmt(result, from).replace(/\./g,'').replace(',','.');
    // Actualizăm result box
    doConvert();
  });
  toAmt.setAttribute('readonly', '');

  fromAmt.addEventListener('input', doConvert);
  fromSel.addEventListener('change', function(){ updateQuickActive(); doConvert(); });
  toSel.addEventListener('change',   function(){ updateQuickActive(); doConvert(); });

  document.getElementById('cv-swap-btn').addEventListener('click', function() {
    var tmp = fromSel.value;
    fromSel.value = toSel.value;
    toSel.value   = tmp;
    updateQuickActive();
    doConvert();
  });

  // Fetch rates
  fetch('/api/bnr-rates.php')
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (data.error) throw new Error(data.error);
      rates   = data.rates;
      bnrDate = data.date;

      dot.className = 'cv-dot';
      sourceText.textContent = 'Curs BNR oficial — ' + data.date + ' (actualizat ' + data.updated + ')';

      var allCodes = Object.keys(rates);
      buildSelects(allCodes);
      buildQuickGrid();
      buildRatesTable();
      doConvert();
    })
    .catch(function(err) {
      dot.className = 'cv-dot error';
      sourceText.textContent = 'Eroare la încărcarea cursurilor BNR: ' + err.message;
      resultBox.innerHTML = '<div class="cv-result-placeholder" style="color:#fca5a5">Nu am putut contacta BNR. Încearcă din nou.</div>';
    });

})();
</script>

<?php require __DIR__ . '/../../templates/footer.php'; ?>
