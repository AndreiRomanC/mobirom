<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::requireAdmin();
$pageTitle = 'Trafic & Vizitatori';

// ── Parsare sursă trafic din referrer ─────────────────────────────────────────
function parseReferrer(?string $ref): array {
    if (!$ref) return ['sursa' => 'Direct', 'cautare' => null, 'icon' => '🔗'];
    $host = strtolower(parse_url($ref, PHP_URL_HOST) ?? '');
    $query = parse_url($ref, PHP_URL_QUERY) ?? '';
    parse_str($query, $params);

    $engines = [
        'google'      => ['icon'=>'🔍','param'=>'q'],
        'bing'        => ['icon'=>'🔍','param'=>'q'],
        'yahoo'       => ['icon'=>'🔍','param'=>'p'],
        'duckduckgo'  => ['icon'=>'🔍','param'=>'q'],
        'yandex'      => ['icon'=>'🔍','param'=>'text'],
        'baidu'       => ['icon'=>'🔍','param'=>'wd'],
    ];
    foreach ($engines as $name => $cfg) {
        if (str_contains($host, $name)) {
            $path = strtolower(parse_url($ref, PHP_URL_PATH) ?? '');
            $term = $params[$cfg['param']] ?? null;
            // Google /url?q=... este un REDIRECT: parametrul „q" conține URL-ul
            // destinație, nu termenul căutat. Ignorăm și orice valoare care e un URL.
            if ($term !== null) {
                $term = trim($term);
                if (str_starts_with($path, '/url') || preg_match('#^https?://#i', $term) || $term === '') {
                    $term = null;
                }
            }
            return ['sursa' => ucfirst($name), 'cautare' => $term, 'icon' => $cfg['icon']];
        }
    }

    $social = ['facebook'=>'👥','instagram'=>'📸','twitter'=>'🐦','x.com'=>'🐦','linkedin'=>'💼','tiktok'=>'🎵','youtube'=>'▶️','reddit'=>'🟠','whatsapp'=>'💬','t.me'=>'✈️'];
    foreach ($social as $name => $icon) {
        if (str_contains($host, $name)) return ['sursa' => ucfirst(explode('.',$name)[0]), 'cautare' => null, 'icon' => $icon];
    }

    if (str_contains($host, SITE_DOMAIN) || str_contains($host, 'ghidromanesc')) {
        return ['sursa' => 'Intern', 'cautare' => null, 'icon' => '↩️'];
    }

    $domain = preg_replace('/^www\./', '', $host);
    return ['sursa' => $domain ?: 'Altul', 'cautare' => null, 'icon' => '🌐'];
}

// ── Filtre ────────────────────────────────────────────────────────────────────
$zile         = (int)($_GET['zile'] ?? 30);
if (!in_array($zile, [7, 30, 90, 365])) $zile = 30;
$dataDe       = sanitize($_GET['data_de'] ?? '');
$dataPana     = sanitize($_GET['data_pana'] ?? '');
$filtruTara   = sanitize($_GET['tara'] ?? '');
$filtruOras   = sanitize($_GET['oras'] ?? '');
$filtruIsp    = sanitize($_GET['isp'] ?? '');
$filtruPagina = sanitize($_GET['pagina'] ?? '');
$filtruBot    = sanitize($_GET['tip'] ?? 'human');
$viewMode     = in_array($_GET['view'] ?? '', ['ip','lista']) ? $_GET['view'] : 'lista';
$filtruOrganic = ($_GET['organic'] ?? '') === '1';

// Condiție timp
if ($dataDe && $dataPana) {
    $whereTime    = "v.created_at >= '$dataDe 00:00:00' AND v.created_at <= '$dataPana 23:59:59'";
    $labelPeriada = "$dataDe → $dataPana";
} else {
    $whereTime    = "v.created_at >= DATE('now', '-{$zile} days')";
    $labelPeriada = "ultimele {$zile} zile";
}

// Condiție bot
$whereBot = match($filtruBot) {
    'bot'   => 'AND v.is_bot=1',
    'human' => 'AND v.is_bot=0',
    default => '',
};

// Condiții geo + organic
$db = Database::get();
$whereGeo = [];
if ($filtruTara)    $whereGeo[] = 'g.country = ' . $db->quote($filtruTara);
if ($filtruOras)    $whereGeo[] = 'g.city = ' . $db->quote($filtruOras);
if ($filtruIsp)     $whereGeo[] = 'g.isp LIKE ' . $db->quote('%'.$filtruIsp.'%');
if ($filtruPagina)  $whereGeo[] = 'v.page LIKE ' . $db->quote('%'.$filtruPagina.'%');
if ($filtruOrganic) $whereGeo[] = "(v.referer LIKE '%google.%' OR v.referer LIKE '%bing.com%' OR v.referer LIKE '%yahoo.%' OR v.referer LIKE '%duckduckgo.%' OR v.referer LIKE '%yandex.%')";
$extraWhere = $whereGeo ? 'AND ' . implode(' AND ', $whereGeo) : '';

// ── Geo batch lookup ──────────────────────────────────────────────────────────
$ipsNoi = Database::fetchAll(
    "SELECT DISTINCT v.ip FROM visits v LEFT JOIN ip_geo g ON v.ip=g.ip
     WHERE g.ip IS NULL AND v.ip!='' AND v.ip!='::1' AND v.ip NOT LIKE '127.%' LIMIT 100"
);
if (!empty($ipsNoi)) {
    $payload = json_encode(array_map(fn($r) => ['query'=>$r['ip'],'fields'=>'query,country,countryCode,regionName,city,isp,lat,lon,status'], $ipsNoi));
    $ch = curl_init('http://ip-api.com/batch');
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>5]);
    $resp = curl_exec($ch); unset($ch);
    foreach (json_decode($resp??'[]',true)??[] as $r) {
        if (($r['status']??'')!=='success') { Database::query('INSERT OR IGNORE INTO ip_geo (ip) VALUES (?)',[$r['query']]); continue; }
        Database::query('INSERT OR REPLACE INTO ip_geo (ip,country,country_code,region,city,isp,lat,lon,cached_at) VALUES (?,?,?,?,?,?,?,?,datetime("now","localtime"))',
            [$r['query'],$r['country'],$r['countryCode'],$r['regionName'],$r['city'],$r['isp'],$r['lat'],$r['lon']]);
    }
}

// ── Statistici ────────────────────────────────────────────────────────────────
$totaleRaw = Database::fetchOne(
    "SELECT COUNT(*) AS total, SUM(is_bot=0) AS human, SUM(is_bot=1) AS bot, COUNT(DISTINCT CASE WHEN is_bot=0 THEN ip END) AS ip_unice
     FROM visits v WHERE $whereTime"
);
$total=$totaleRaw['total']??0; $human=$totaleRaw['human']??0; $bot=$totaleRaw['bot']??0; $ipUnice=$totaleRaw['ip_unice']??0;
$botPct = $total>0 ? round($bot/$total*100) : 0;

$totalFiltrat  = (int)Database::fetchColumn("SELECT COUNT(DISTINCT v.ip) FROM visits v LEFT JOIN ip_geo g ON v.ip=g.ip WHERE $whereTime $whereBot $extraWhere");
$viziteFiltrat = (int)Database::fetchColumn("SELECT COUNT(*) FROM visits v LEFT JOIN ip_geo g ON v.ip=g.ip WHERE $whereTime $whereBot $extraWhere");

$perZi = Database::fetchAll(
    "SELECT DATE(v.created_at) AS zi, COUNT(DISTINCT CASE WHEN v.is_bot=0 THEN v.ip END) AS unici_human, COUNT(DISTINCT v.ip) AS unici_total, SUM(v.is_bot=0) AS vizite_human, COUNT(*) AS vizite_total
     FROM visits v LEFT JOIN ip_geo g ON v.ip=g.ip WHERE $whereTime $extraWhere GROUP BY DATE(v.created_at) ORDER BY zi DESC"
);
$topPagini = Database::fetchAll(
    "SELECT v.page, COUNT(DISTINCT v.ip) AS unici, COUNT(*) AS vizite, SUM(v.is_bot=0) AS human
     FROM visits v LEFT JOIN ip_geo g ON v.ip=g.ip WHERE $whereTime $whereBot $extraWhere
     GROUP BY v.page ORDER BY vizite DESC LIMIT 20"
);
$topTari = Database::fetchAll(
    "SELECT g.country, COUNT(DISTINCT v.ip) AS unici FROM visits v JOIN ip_geo g ON v.ip=g.ip
     WHERE $whereTime $whereBot AND g.country IS NOT NULL $extraWhere
     GROUP BY g.country ORDER BY unici DESC LIMIT 10"
);
$topIsp = Database::fetchAll(
    "SELECT g.isp, COUNT(DISTINCT v.ip) AS unici FROM visits v JOIN ip_geo g ON v.ip=g.ip
     WHERE $whereTime AND v.is_bot=0 AND g.isp IS NOT NULL GROUP BY g.isp ORDER BY unici DESC LIMIT 8"
);
$botReasons = Database::fetchAll(
    "SELECT v.bot_reason, COUNT(*) AS cnt FROM visits v WHERE $whereTime AND v.is_bot=1 AND v.bot_reason IS NOT NULL
     GROUP BY v.bot_reason ORDER BY cnt DESC LIMIT 10"
);
$recente = Database::fetchAll(
    "SELECT v.ip,v.page,v.referer,v.created_at,v.is_bot,v.bot_score,g.city,g.country,g.isp
     FROM visits v LEFT JOIN ip_geo g ON v.ip=g.ip
     WHERE $whereTime $whereBot $extraWhere ORDER BY v.id DESC LIMIT 100"
);
$peIP = Database::fetchAll(
    "SELECT v.ip,g.city,g.country,g.isp,COUNT(*) AS vizite,COUNT(DISTINCT v.page) AS pagini_unice,
            MIN(v.created_at) AS prima,MAX(v.created_at) AS ultima,
            MAX(v.bot_score) AS scor,GROUP_CONCAT(DISTINCT v.page) AS pagini,
            GROUP_CONCAT(v.referer) AS referrers
     FROM visits v LEFT JOIN ip_geo g ON v.ip=g.ip
     WHERE $whereTime $whereBot $extraWhere GROUP BY v.ip ORDER BY ultima DESC LIMIT 200"
);

$topSurse = [];
foreach (Database::fetchAll("SELECT v.referer FROM visits v WHERE $whereTime AND v.is_bot=0 AND v.referer IS NOT NULL") as $row) {
    $parsed = parseReferrer($row['referer']);
    $topSurse[$parsed['sursa']] = ($topSurse[$parsed['sursa']] ?? 0) + 1;
}
arsort($topSurse);
$topSurse = array_slice($topSurse, 0, 10, true);

// ── Căutări organice ──────────────────────────────────────────────────────────
$organicKeywords = []; $searchEngineStats = [];
foreach (Database::fetchAll("SELECT v.referer FROM visits v WHERE $whereTime AND v.is_bot=0
    AND (v.referer LIKE '%google.%' OR v.referer LIKE '%bing.com%'
      OR v.referer LIKE '%yahoo.%' OR v.referer LIKE '%duckduckgo.%'
      OR v.referer LIKE '%yandex.%') LIMIT 5000") as $row) {
    $p = parseReferrer($row['referer']);
    $searchEngineStats[$p['sursa']] = ($searchEngineStats[$p['sursa']] ?? 0) + 1;
    if ($p['cautare']) {
        $key = mb_strtolower(trim($p['cautare']));
        $organicKeywords[$key] = ($organicKeywords[$key] ?? 0) + 1;
    }
}
arsort($organicKeywords); arsort($searchEngineStats);
$organicKeywords = array_slice($organicKeywords, 0, 30, true);
$totalOrganic    = array_sum($searchEngineStats);

$baseUrl = '?'.http_build_query(array_filter(['zile'=>$zile,'data_de'=>$dataDe,'data_pana'=>$dataPana,'tara'=>$filtruTara,'oras'=>$filtruOras,'isp'=>$filtruIsp,'pagina'=>$filtruPagina,'tip'=>$filtruBot,'organic'=>$filtruOrganic?'1':'']));

require __DIR__ . '/../templates/admin-layout.php';
?>
<style>
/* ── Tab navigation ─────────────────────────────────────── */
.viz-tab-nav {
  display:flex; gap:0; border-bottom:2px solid #e5e7eb;
  margin-bottom:1.25rem; overflow-x:auto; -webkit-overflow-scrolling:touch;
}
.viz-tab {
  padding:.65rem 1.1rem; font-size:.85rem; font-weight:600;
  color:#6b7280; border:none; background:none; cursor:pointer;
  border-bottom:2px solid transparent; margin-bottom:-2px;
  white-space:nowrap; font-family:inherit; transition:color .15s,border-color .15s;
}
.viz-tab:hover { color:#1d3557; }
.viz-tab.active { color:#1d3557; border-bottom-color:#1d3557; }
.viz-tab .vtab-badge {
  display:inline-block; background:#e0f2fe; color:#0369a1;
  font-size:.68rem; padding:.05rem .35rem; border-radius:8px;
  margin-left:.3rem; font-weight:700;
}

/* ── Filter bar ─────────────────────────────────────────── */
.viz-filter-bar {
  background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px;
  padding:.75rem 1rem; margin-bottom:1.25rem;
  display:flex; flex-wrap:wrap; gap:.6rem; align-items:center;
}
.viz-filter-bar select, .viz-filter-bar input[type="text"], .viz-filter-bar input[type="date"] {
  font-size:.82rem; padding:.35rem .6rem; border:1px solid #d1d5db;
  border-radius:6px; background:#fff; font-family:inherit; color:#374151;
  height:32px;
}
.viz-filter-bar .filter-sep {
  width:1px; height:24px; background:#e5e7eb; flex-shrink:0;
}
.viz-organic-toggle {
  display:flex; align-items:center; gap:.4rem; font-size:.82rem;
  font-weight:600; color:#1d3557; cursor:pointer; padding:.3rem .6rem;
  border:1.5px solid; border-radius:6px; transition:all .15s; height:32px;
}
.viz-organic-toggle.on  { border-color:#16a34a; background:#f0fdf4; color:#15803d; }
.viz-organic-toggle.off { border-color:#d1d5db; background:#fff; }

/* ── Score bar ──────────────────────────────────────────── */
.score-bar { display:inline-block;height:6px;border-radius:3px;vertical-align:middle;margin-right:.3rem }

/* ── Keyword tag ─────────────────────────────────────────── */
.kw-tag {
  display:inline-block; padding:.2rem .55rem; background:#f1f5f9;
  border-radius:4px; font-size:.78rem; color:#334155; margin:.15rem;
  border:1px solid #e2e8f0; cursor:default;
}

/* ── Mobile accordion toggle pentru filtre ───────────────── */
.viz-filter-toggle {
  display:none; width:100%; align-items:center; justify-content:space-between;
  padding:.7rem 1rem; background:#f8fafc; border:1.5px solid #e5e7eb;
  border-radius:10px; font-size:.875rem; font-weight:600; color:#1d3557;
  cursor:pointer; font-family:inherit; margin-bottom:.75rem;
}
.viz-filter-badge {
  display:inline-flex; align-items:center; justify-content:center;
  background:#1d3557; color:#fff; font-size:.65rem; font-weight:700;
  border-radius:8px; padding:.05rem .4rem; margin-left:.4rem;
}
.viz-filter-arrow { transition:transform .2s; display:inline-block; flex-shrink:0; }
.viz-filter-toggle.open .viz-filter-arrow { transform:rotate(180deg); }
/* Sumar selecții active afișat în butonul strâns (mobil) */
.vft-summary { display:flex; flex-wrap:wrap; align-items:center; gap:.3rem; text-align:left; }
.vft-label   { font-weight:700; }
.vft-chip {
  display:inline-block; background:#1d3557; color:#fff;
  font-size:.7rem; font-weight:600; padding:.1rem .45rem;
  border-radius:6px; white-space:nowrap;
}

/* ── Responsive mobil ────────────────────────────────────── */
@media (max-width: 768px) {
  /* Layout coloane filtre pe mobil (display e controlat de JS) */
  .viz-filter-bar {
    flex-direction:column; gap:.75rem;
    border-radius:0 0 10px 10px; border-top:none; padding-top:1rem;
    margin-top:-.75rem;
  }
  .viz-filter-bar select,
  .viz-filter-bar input[type="text"],
  .viz-filter-bar input[type="date"] {
    width:100%; height:40px; font-size:.875rem;
  }
  .viz-filter-bar .filter-sep { display:none; }
  .viz-organic-toggle { width:100%; height:40px; justify-content:center; }
  .viz-filter-bar .btn { width:100%; min-height:40px; }

  /* Tab-uri → grid 2×2 cu border */
  .viz-tab-nav {
    display:grid !important; grid-template-columns:1fr 1fr;
    border-bottom:none; gap:.4rem; margin-bottom:1rem;
    overflow:visible;
  }
  .viz-tab {
    border:1.5px solid #e5e7eb; border-radius:8px;
    padding:.6rem .5rem; font-size:.78rem; min-height:44px;
    text-align:center; white-space:normal; margin-bottom:0;
    display:flex; align-items:center; justify-content:center; gap:.25rem;
  }
  .viz-tab.active {
    background:#1d3557; color:#fff; border-color:#1d3557;
  }
  .viz-tab.active .vtab-badge { background:rgba(255,255,255,.25); color:#fff; }

  /* Grids din tab-uri → o coloană */
  #tc-sumar > div[style*="grid-template-columns"],
  #tc-pagini > div[style*="grid-template-columns"],
  #tc-cautari > div[style*="grid-template-columns"] {
    display:block !important;
  }
  #tc-sumar > div[style*="grid-template-columns"] > .admin-card,
  #tc-pagini > div[style*="grid-template-columns"] > .admin-card,
  #tc-cautari > div[style*="grid-template-columns"] > .admin-card {
    margin-bottom:.75rem;
  }
}
</style>

<!-- ── KPI bar ──────────────────────────────────────────── -->
<div class="stats-grid" style="margin-bottom:1rem">
  <div class="stat-card">
    <div class="stat-value"><?=number_format($ipUnice)?></div>
    <div class="stat-label">Vizitatori unici (<?=$zile?>z)</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?=number_format($human)?></div>
    <div class="stat-label">Vizite umane</div>
  </div>
  <div class="stat-card">
    <div class="stat-value" style="color:<?=$botPct>30?'#dc2626':($botPct>10?'#b45309':'inherit')?>"><?=$botPct?>%</div>
    <div class="stat-label">Trafic bot (<?=number_format($bot)?>)</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?=number_format($totalOrganic)?></div>
    <div class="stat-label">Vizite organice</div>
  </div>
</div>

<!-- ── Filter bar ──────────────────────────────────────────── -->
<?php
$filtruActiv = $filtruTara||$filtruPagina||$dataDe||$filtruBot!=='human'||$filtruOrganic;
$tipLabels = ['human'=>'👤 Oameni','bot'=>'🤖 Boți','all'=>'📊 Toți'];
$selectii  = [$labelPeriada, $tipLabels[$filtruBot] ?? '👤 Oameni'];
if($filtruTara)    $selectii[] = '🌍 '.$filtruTara;
if($filtruPagina)  $selectii[] = '📄 '.$filtruPagina;
if($filtruOrganic) $selectii[] = '🔍 organic';
?>
<button type="button" class="viz-filter-toggle" id="viz-filter-btn">
  <span class="vft-summary">
    <span class="vft-label">🔍 Filtre</span>
    <?php foreach($selectii as $s): ?><span class="vft-chip"><?=e($s)?></span><?php endforeach; ?>
  </span>
  <span class="viz-filter-arrow">▾</span>
</button>
<form method="GET" id="fform">
<div class="viz-filter-bar" id="viz-filter-bar">

  <!-- Perioadă -->
  <select name="zile" onchange="document.getElementById('fform').submit()" title="Perioadă">
    <?php foreach([7=>'7 zile',30=>'30 zile',90=>'90 zile',365=>'1 an'] as $v=>$l): ?>
    <option value="<?=$v?>" <?=$zile===$v&&!$dataDe?'selected':''?>><?=$l?></option>
    <?php endforeach; ?>
  </select>
  <input type="date" name="data_de"   value="<?=e($dataDe)?>"  title="De la">
  <input type="date" name="data_pana" value="<?=e($dataPana)?>" title="Până la">

  <div class="filter-sep"></div>

  <!-- Tip vizitator -->
  <select name="tip" onchange="document.getElementById('fform').submit()" title="Tip vizitator">
    <option value="human" <?=$filtruBot==='human'?'selected':''?>>👤 Oameni</option>
    <option value="bot"   <?=$filtruBot==='bot'  ?'selected':''?>>🤖 Boți</option>
    <option value="all"   <?=$filtruBot==='all'  ?'selected':''?>>📊 Toți</option>
  </select>

  <div class="filter-sep"></div>

  <!-- Organic toggle -->
  <a href="<?=$baseUrl?>&organic=<?=$filtruOrganic?'0':'1'?>&view=<?=$viewMode?>"
     class="viz-organic-toggle <?=$filtruOrganic?'on':'off'?>"
     title="Afișează doar vizitele venite din motoare de căutare">
    🔍 Doar organic<?php if($filtruOrganic): ?> ✓<?php endif; ?>
  </a>

  <div class="filter-sep"></div>

  <!-- Filtre geo/pagina -->
  <select name="tara" title="Țară">
    <option value="">🌍 Toate țările</option>
    <?php foreach($filtruTara?Database::fetchAll("SELECT DISTINCT country FROM ip_geo WHERE country IS NOT NULL ORDER BY country"):Database::fetchAll("SELECT DISTINCT country FROM ip_geo WHERE country IS NOT NULL ORDER BY country LIMIT 80") as $t): ?>
    <option value="<?=e($t['country'])?>" <?=$filtruTara===$t['country']?'selected':''?>><?=e($t['country'])?></option>
    <?php endforeach; ?>
  </select>
  <input type="text" name="pagina" value="<?=e($filtruPagina)?>" placeholder="Filtrează pagina..." style="width:160px">

  <input type="hidden" name="view" value="<?=e($viewMode)?>">
  <button type="submit" class="btn btn-primary btn-sm">Aplică</button>
  <?php if($filtruTara||$filtruPagina||$dataDe||$filtruBot!=='human'||$filtruOrganic): ?>
  <a href="?zile=<?=$zile?>&tip=human&view=<?=$viewMode?>" class="btn btn-secondary btn-sm">✕ Reset</a>
  <?php endif; ?>
  <span style="font-size:.75rem;color:var(--text-muted);margin-left:auto">
    <strong><?=e($labelPeriada)?></strong> · <?=number_format($totalFiltrat)?> IP · <?=number_format($viziteFiltrat)?> vizite
    <?php if($filtruOrganic): ?> · <span style="color:#15803d">🔍 Organic activ</span><?php endif; ?>
  </span>
</div>
</form>

<!-- ── Tab navigation ──────────────────────────────────── -->
<div class="viz-tab-nav">
  <button class="viz-tab" id="vtab-sumar"      onclick="showTab('sumar')">
    📊 Sumar
  </button>
  <button class="viz-tab" id="vtab-pagini"     onclick="showTab('pagini')">
    📄 Pagini
    <span class="vtab-badge"><?=count($topPagini)?></span>
  </button>
  <button class="viz-tab" id="vtab-cautari"    onclick="showTab('cautari')">
    🔍 Căutări organice
    <?php if($totalOrganic): ?><span class="vtab-badge"><?=number_format($totalOrganic)?></span><?php endif; ?>
  </button>
  <button class="viz-tab" id="vtab-vizitatori" onclick="showTab('vizitatori')">
    👁 Vizitatori
    <span class="vtab-badge"><?=count($recente)?></span>
  </button>
</div>

<!-- ══════════════════════════════════════════════════════
     TAB 1: SUMAR
     ══════════════════════════════════════════════════════ -->
<div id="tc-sumar">
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.1rem;margin-bottom:1.1rem">

  <!-- Per zi -->
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">📅 Vizitatori pe zi</span></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Data</th><th>👤 Unici</th><th>Total</th></tr></thead>
        <tbody>
          <?php foreach(array_slice($perZi,0,15) as $r): ?>
          <tr>
            <td><?=formatDate($r['zi'],'d.m')?></td>
            <td><strong><?=$r['unici_human']?></strong></td>
            <td style="color:var(--text-muted);font-size:.8rem"><?=$r['vizite_total']?></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($perZi)): ?><tr><td colspan="3" style="text-align:center;padding:1.5rem;color:var(--text-muted)">Nicio vizită.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top țări -->
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">🌍 Top țări</span></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Țară</th><th>IP unice</th></tr></thead>
        <tbody>
          <?php foreach($topTari as $t): ?>
          <tr>
            <td><a href="<?=$baseUrl?>&tara=<?=urlencode($t['country'])?>" style="text-decoration:none"><?=e($t['country'])?></a></td>
            <td><strong><?=$t['unici']?></strong></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($topTari)): ?><tr><td colspan="2" style="text-align:center;padding:1.5rem;color:var(--text-muted)">În procesare...</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Surse -->
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">🔗 Surse trafic</span></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Sursă</th><th>Vizite</th></tr></thead>
        <tbody>
          <?php foreach($topSurse as $sursa=>$cnt): ?>
          <tr><td style="font-size:.85rem"><?=e($sursa)?></td><td><strong><?=$cnt?></strong></td></tr>
          <?php endforeach; ?>
          <?php if(empty($topSurse)): ?><tr><td colspan="2" style="text-align:center;padding:1.5rem;color:var(--text-muted)">Nicio vizită.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Top ISP -->
<div class="admin-card">
  <div class="admin-card-header"><span class="admin-card-title">📡 Top ISP (oameni)</span></div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Provider</th><th>Unici</th></tr></thead>
      <tbody>
        <?php foreach($topIsp as $i): ?>
        <tr>
          <td style="font-size:.82rem"><?=e(truncate($i['isp'],35))?></td>
          <td><strong><?=$i['unici']?></strong></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($topIsp)): ?><tr><td colspan="2" style="text-align:center;padding:1.5rem;color:var(--text-muted)">Date geo în procesare.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</div><!-- /tc-sumar -->


<!-- ══════════════════════════════════════════════════════
     TAB 2: PAGINI
     ══════════════════════════════════════════════════════ -->
<div id="tc-pagini" style="display:none">
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.1rem">

  <!-- Top pagini -->
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">📄 Top pagini</span></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Pagina</th><th>Unici</th><th>Vizite</th><th>Bot%</th></tr></thead>
        <tbody>
          <?php foreach($topPagini as $p):
            $bPct = $p['vizite']>0 ? round(($p['vizite']-$p['human'])/$p['vizite']*100) : 0; ?>
          <tr>
            <td style="font-size:.8rem">
              <a href="<?=e($p['page'])?>" target="_blank"><?=e(truncate($p['page'],40))?></a>
              <a href="<?=$baseUrl?>&pagina=<?=urlencode($p['page'])?>" style="font-size:.7rem;margin-left:.3rem;color:var(--text-muted)" title="Filtrează">▶</a>
            </td>
            <td><strong><?=$p['unici']?></strong></td>
            <td><?=$p['vizite']?></td>
            <td><span style="font-size:.75rem;color:<?=$bPct>50?'#dc2626':($bPct>20?'#b45309':'#16a34a')?>"><?=$bPct?>%</span></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($topPagini)): ?><tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--text-muted)">Nicio vizită.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Bot reasons -->
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">🤖 Detectare boți</span></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Motiv</th><th>Nr.</th></tr></thead>
        <tbody>
          <?php foreach($botReasons as $br): ?>
          <tr>
            <td style="font-size:.78rem;font-family:monospace"><?=e(truncate($br['bot_reason'],28))?></td>
            <td><?=$br['cnt']?></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($botReasons)): ?><tr><td colspan="2" style="padding:1.5rem;text-align:center;color:var(--text-muted)">Nicio dată.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div><!-- /tc-pagini -->


<!-- ══════════════════════════════════════════════════════
     TAB 3: CĂUTĂRI ORGANICE
     ══════════════════════════════════════════════════════ -->
<div id="tc-cautari" style="display:none">

<?php if($totalOrganic === 0): ?>
<div class="admin-card">
  <div class="admin-card-body" style="text-align:center;padding:2.5rem;color:var(--text-muted)">
    <div style="font-size:2rem;margin-bottom:.5rem">🔍</div>
    <div>Nicio vizită organică înregistrată în această perioadă.</div>
    <div style="font-size:.8rem;margin-top:.4rem">Datele organice apar când utilizatorii ajung pe site din Google, Bing, etc.</div>
  </div>
</div>
<?php else: ?>

<!-- Motoare de căutare -->
<div style="display:grid;grid-template-columns:1fr 2fr;gap:1.1rem;margin-bottom:1.1rem">
  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">🔍 Motoare de căutare</span></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Motor</th><th>Vizite</th><th>%</th></tr></thead>
        <tbody>
          <?php foreach($searchEngineStats as $engine=>$cnt): $pct=round($cnt/$totalOrganic*100); ?>
          <tr>
            <td style="font-weight:600"><?=e($engine)?></td>
            <td><strong><?=$cnt?></strong></td>
            <td>
              <div style="display:flex;align-items:center;gap:.4rem">
                <div style="background:#dbeafe;border-radius:3px;height:6px;width:<?=min($pct,100)?>px"></div>
                <span style="font-size:.75rem;color:var(--text-muted)"><?=$pct?>%</span>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top cuvinte cheie -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">🔑 Cuvinte cheie organice</span>
      <span style="font-size:.78rem;color:var(--text-muted)"><?=count($organicKeywords)?> termeni unici</span>
    </div>
    <?php if(!empty($organicKeywords)): ?>
    <div style="padding:.75rem 1rem .4rem">
      <input type="text" id="kw-filter" placeholder="Caută în cuvinte cheie..."
        oninput="filterKeywords(this.value)"
        style="width:100%;font-size:.82rem;padding:.35rem .6rem;border:1px solid #d1d5db;border-radius:6px;font-family:inherit">
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table" id="kw-table">
        <thead><tr><th>#</th><th>Cuvânt cheie</th><th>Vizite</th></tr></thead>
        <tbody>
          <?php $i=1; foreach($organicKeywords as $kw=>$cnt): ?>
          <tr class="kw-row">
            <td style="color:var(--text-muted);font-size:.78rem"><?=$i++?></td>
            <td style="font-size:.85rem"><span class="kw-tag"><?=e($kw)?></span></td>
            <td><strong><?=$cnt?></strong></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="admin-card-body" style="color:var(--text-muted);font-size:.85rem">
      Există vizite organice dar fără cuvinte cheie vizibile (Google criptează de obicei query-ul).<br>
      <small>Notă: Google trimite din ce în ce mai rar termenul de căutare în referrer.</small>
    </div>
    <?php endif; ?>
  </div>

</div>

<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:10px;padding:.75rem 1rem;font-size:.8rem;color:#78350f">
  ℹ️ <strong>De ce lipsesc unele cuvinte cheie?</strong> Google nu mai transmite termenul de căutare în referrer pentru utilizatorii autentificați sau în HTTPS (politics „not provided"). Termenii vizibili provin din DuckDuckGo, Yahoo sau sesiuni neautentificate.
</div>
<?php endif; ?>

</div><!-- /tc-cautari -->


<!-- ══════════════════════════════════════════════════════
     TAB 4: VIZITATORI
     ══════════════════════════════════════════════════════ -->
<div id="tc-vizitatori" style="display:none">

<!-- Toggle vizualizare -->
<div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.875rem">
  <span style="font-size:.82rem;font-weight:600;color:var(--text-muted)">Vizualizare:</span>
  <a href="<?=$baseUrl?>&view=lista" class="btn btn-sm <?=$viewMode==='lista'?'btn-primary':'btn-secondary'?>">🕐 Cronologic</a>
  <a href="<?=$baseUrl?>&view=ip"    class="btn btn-sm <?=$viewMode==='ip'?'btn-primary':'btn-secondary'?>">👤 Grupat pe IP</a>
</div>

<?php if($viewMode==='ip'): ?>
<div class="admin-card">
  <div class="admin-card-header">
    <span class="admin-card-title">👤 Grupat pe IP</span>
    <span style="font-size:.78rem;color:var(--text-muted)"><?=count($peIP)?> IP-uri</span>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>IP</th><th>Loc</th><th>ISP</th><th>Surse</th><th>Vizite</th><th>Scor</th><th>Pagini</th><th>Prima</th><th>Ultima</th></tr></thead>
      <tbody>
        <?php foreach($peIP as $v):
          $pagini=array_unique(array_filter(explode(',',$v['pagini']??'')));
          $scor=(int)$v['scor']; $isBot=$scor<0;
          $refs = array_filter(explode(',',$v['referrers']??''));
          $surseIP = [];
          foreach($refs as $r) { $p=parseReferrer($r); $surseIP[$p['sursa']]=($surseIP[$p['sursa']]??0)+1; }
          arsort($surseIP);
        ?>
        <tr style="<?=$isBot?'opacity:.6':''?>">
          <td style="font-family:monospace;font-size:.75rem"><?=e($v['ip'])?></td>
          <td style="font-size:.78rem"><?=e($v['city']??'—')?>, <a href="<?=$baseUrl?>&tara=<?=urlencode($v['country']??'')?>" style="text-decoration:none;color:var(--text-muted)"><?=e($v['country']??'—')?></a></td>
          <td style="font-size:.72rem;color:var(--text-muted)"><?=e(truncate($v['isp']??'—',20))?></td>
          <td style="font-size:.75rem">
            <?php foreach(array_slice($surseIP,0,2,true) as $s=>$n): ?>
            <span><?=e($s)?> <strong><?=$n?></strong></span><br>
            <?php endforeach; ?>
            <?php if(empty($surseIP)): ?>Direct<?php endif; ?>
          </td>
          <td><strong><?=$v['vizite']?></strong></td>
          <td>
            <span style="font-size:.75rem;font-weight:700;color:<?=$scor>=30?'#16a34a':($scor>=0?'#b45309':'#dc2626')?>"><?=$scor>=0?'+':''?><?=$scor?></span>
            <?=$isBot?'🤖':'👤'?>
          </td>
          <td style="font-size:.72rem;max-width:180px">
            <?php foreach(array_slice($pagini,0,3) as $pg): ?>
            <a href="<?=e($pg)?>" target="_blank" style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?=e($pg)?></a>
            <?php endforeach; ?>
            <?php if(count($pagini)>3): ?><span style="color:var(--text-muted)">+<?=count($pagini)-3?></span><?php endif; ?>
          </td>
          <td style="font-size:.72rem;color:var(--text-muted)"><?=substr($v['prima'],0,16)?></td>
          <td style="font-size:.72rem"><?=substr($v['ultima'],0,16)?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($peIP)): ?><tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--text-muted)">Nicio vizită.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php else: ?>
<div class="admin-card">
  <div class="admin-card-header">
    <span class="admin-card-title">🕐 Vizite recente</span>
    <span style="font-size:.78rem;color:var(--text-muted)"><?=count($recente)?> rezultate</span>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>IP</th><th>Loc</th><th>Sursă</th><th>Căutare organică</th><th>Scor</th><th>Pagina</th><th>Data</th></tr></thead>
      <tbody>
        <?php foreach($recente as $v):
          $scor=(int)$v['bot_score']; $isBot=$v['is_bot'];
          $ref = parseReferrer($v['referer']);
        ?>
        <tr style="<?=$isBot?'opacity:.55':''?>">
          <td style="font-family:monospace;font-size:.75rem"><?=e($v['ip'])?></td>
          <td style="font-size:.78rem">
            <?php if($v['city']): ?><a href="<?=$baseUrl?>&oras=<?=urlencode($v['city'])?>" style="text-decoration:none"><?=e($v['city'])?></a>, <?php endif; ?>
            <?php if($v['country']): ?><a href="<?=$baseUrl?>&tara=<?=urlencode($v['country'])?>" style="text-decoration:none;color:var(--text-muted)"><?=e($v['country'])?></a><?php else: ?>—<?php endif; ?>
          </td>
          <td style="font-size:.78rem"><?=$ref['icon']?> <?=e($ref['sursa'])?></td>
          <td style="font-size:.78rem;color:#1d3557;font-style:<?=$ref['cautare']?'italic':'normal'?>">
            <?=$ref['cautare']?'🔍 '.e($ref['cautare']):'<span style="color:var(--text-muted)">—</span>'?>
          </td>
          <td>
            <span style="font-size:.75rem;font-weight:700;color:<?=$scor>=30?'#16a34a':($scor>=0?'#b45309':'#dc2626')?>"><?=$scor>=0?'+':''?><?=$scor?></span>
            <?=$isBot?'🤖':'👤'?>
          </td>
          <td style="font-size:.78rem"><a href="<?=e($v['page'])?>" target="_blank"><?=e(truncate($v['page'],30))?></a></td>
          <td style="font-size:.72rem;white-space:nowrap"><?=substr($v['created_at'],0,16)?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($recente)): ?><tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">Nicio vizită.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

</div><!-- /tc-vizitatori -->

<script>
// ── Tab logic ─────────────────────────────────────────────────────────────────
var TABS = ['sumar','pagini','cautari','vizitatori'];

function showTab(name) {
  TABS.forEach(function(t) {
    document.getElementById('tc-'+t).style.display    = t === name ? '' : 'none';
    document.getElementById('vtab-'+t).classList.toggle('active', t === name);
  });
  try { localStorage.setItem('viz-active-tab', name); } catch(e) {}
}

// Restore saved tab
(function() {
  var saved = '';
  try { saved = localStorage.getItem('viz-active-tab') || ''; } catch(e) {}
  showTab(TABS.indexOf(saved) >= 0 ? saved : 'sumar');
})();

// ── Mobile filter accordion ───────────────────────────────────────────────────
(function() {
  var bar = document.getElementById('viz-filter-bar');
  var btn = document.getElementById('viz-filter-btn');
  if (!bar || !btn) return;

  var MOBILE = window.innerWidth <= 768;

  if (MOBILE) {
    btn.style.display = 'flex';
    bar.style.display = 'none';
  }

  function openFilter() {
    bar.style.display = 'flex';
    btn.classList.add('open');
  }
  function closeFilter() {
    bar.style.display = 'none';
    btn.classList.remove('open');
  }

  btn.addEventListener('click', function() {
    if (btn.classList.contains('open')) closeFilter(); else openFilter();
  });

  // Auto-deschide dacă filtru e activ
  <?php if($filtruActiv): ?>if (MOBILE) openFilter();<?php endif; ?>
})();

// ── Keyword filter ────────────────────────────────────────────────────────────
function filterKeywords(q) {
  q = q.toLowerCase().trim();
  document.querySelectorAll('#kw-table .kw-row').forEach(function(tr) {
    var kw = tr.querySelector('.kw-tag');
    tr.style.display = (!q || (kw && kw.textContent.toLowerCase().includes(q))) ? '' : 'none';
  });
}
</script>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
