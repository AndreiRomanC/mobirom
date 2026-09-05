<?php
$pageTitle = 'Analizor SEO titlu și meta description — GhidRomânesc';
$metaDescription = 'Verifici dacă titlul și meta description-ul paginii tale sunt optime pentru SEO. Feedback instant cu sugestii concrete.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/analizor-seo/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">📊 Analizor SEO titlu & meta</h1>
  <p class="page-subtitle">Verifici și optimizezi titlul și meta description-ul paginilor tale pentru Google.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div class="form-group" style="margin-bottom:1.25rem">
    <label style="font-weight:700;color:var(--blue-dark)">Titlu pagină (Title tag)</label>
    <input type="text" id="f-title" class="form-control" placeholder="ex: Cum îți faci pașaportul în 2026 — GhidRomânesc" oninput="analyze()" style="margin-top:.4rem;font-size:.95rem">
    <div style="display:flex;justify-content:space-between;margin-top:.3rem">
      <span id="t-count" style="font-size:.78rem;color:var(--text-muted)">0 caractere</span>
      <span id="t-px" style="font-size:.78rem;color:var(--text-muted)">~0px</span>
    </div>
    <div id="t-bar-wrap" style="height:6px;background:#e2e8f0;border-radius:3px;margin-top:.3rem;overflow:hidden">
      <div id="t-bar" style="height:6px;border-radius:3px;background:#16a34a;width:0;transition:width .2s,background .2s"></div>
    </div>
    <div id="t-feedback" style="margin-top:.5rem;font-size:.82rem"></div>
  </div>

  <div class="form-group" style="margin-bottom:1.25rem">
    <label style="font-weight:700;color:var(--blue-dark)">Meta description</label>
    <textarea id="f-desc" class="form-control" rows="3" placeholder="ex: Ghid complet pas cu pas pentru obținerea pașaportului simplu sau electronic în România. Acte necesare, costuri și programare online." oninput="analyze()" style="margin-top:.4rem;font-size:.9rem"></textarea>
    <div style="display:flex;justify-content:space-between;margin-top:.3rem">
      <span id="d-count" style="font-size:.78rem;color:var(--text-muted)">0 caractere</span>
      <span id="d-words" style="font-size:.78rem;color:var(--text-muted)">0 cuvinte</span>
    </div>
    <div id="d-bar-wrap" style="height:6px;background:#e2e8f0;border-radius:3px;margin-top:.3rem;overflow:hidden">
      <div id="d-bar" style="height:6px;border-radius:3px;background:#16a34a;width:0;transition:width .2s,background .2s"></div>
    </div>
    <div id="d-feedback" style="margin-top:.5rem;font-size:.82rem"></div>
  </div>

  <div class="form-group" style="margin-bottom:1.5rem">
    <label style="font-weight:700;color:var(--blue-dark)">Cuvânt cheie principal (opțional)</label>
    <input type="text" id="f-kw" class="form-control" placeholder="ex: pașaport România" oninput="analyze()" style="margin-top:.4rem">
    <div id="kw-feedback" style="margin-top:.5rem;font-size:.82rem"></div>
  </div>

  <!-- Scor general -->
  <div id="score-box" style="display:none;background:#f8fafc;border-radius:12px;padding:1.25rem;margin-bottom:1.25rem">
    <div style="display:flex;align-items:center;gap:1rem">
      <div id="score-circle" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:900;flex-shrink:0;border:4px solid"></div>
      <div style="flex:1">
        <div id="score-label" style="font-weight:700;color:var(--blue-dark);margin-bottom:.25rem"></div>
        <div id="score-tips" style="display:flex;flex-direction:column;gap:.25rem"></div>
      </div>
    </div>
  </div>

  <!-- Preview Google -->
  <div id="preview-box" style="display:none">
    <div style="font-weight:700;font-size:.85rem;color:var(--blue-dark);margin-bottom:.5rem">👁️ Preview Google</div>
    <div style="background:#fff;border-radius:8px;padding:1rem;box-shadow:0 1px 4px rgba(0,0,0,.1);font-family:Arial,sans-serif">
      <div style="font-size:13px;color:#202124;margin-bottom:2px">ghidromanesc.ro › pagina-ta</div>
      <div id="prev-title" style="font-size:19px;color:#1a0dab;margin-bottom:3px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"></div>
      <div id="prev-desc" style="font-size:14px;color:#4d5156;line-height:1.55;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"></div>
    </div>
  </div>

</div>
</div>

<script>
function analyze() {
  var title = document.getElementById('f-title').value;
  var desc  = document.getElementById('f-desc').value;
  var kw    = document.getElementById('f-kw').value.trim().toLowerCase();
  var tLen  = title.length, dLen = desc.length;
  var dWords = desc.trim() ? desc.trim().split(/\s+/).length : 0;

  // Titlu
  document.getElementById('t-count').textContent = tLen + ' caractere';
  document.getElementById('t-px').textContent = '~' + Math.round(tLen * 6.5) + 'px';
  var tPct = Math.min(100, tLen / 60 * 100);
  var tColor = tLen === 0 ? '#e2e8f0' : tLen < 30 ? '#f59e0b' : tLen <= 60 ? '#16a34a' : '#dc2626';
  setBar('t-bar', Math.min(100, tLen/70*100), tColor);
  var tTips = [];
  if (tLen === 0) tTips.push({t:'Adaugă un titlu.',c:'#94a3b8'});
  else if (tLen < 30) tTips.push({t:'⚠️ Titlu prea scurt — adaugă mai mult context (min. 30 car.)',c:'#b45309'});
  else if (tLen <= 60) tTips.push({t:'✓ Lungime ideală pentru titlu (30–60 car.)',c:'#16a34a'});
  else if (tLen <= 70) tTips.push({t:'⚠️ Titlu puțin prea lung — Google poate trunchia (max ~60 car.)',c:'#b45309'});
  else tTips.push({t:'✕ Titlu prea lung (' + tLen + ' car.) — Google îl va trunchia în rezultate',c:'#dc2626'});
  if (kw && tLen > 0 && title.toLowerCase().indexOf(kw) === -1) tTips.push({t:'💡 Cuvântul cheie „' + kw + '" nu apare în titlu',c:'#457b9d'});
  else if (kw && title.toLowerCase().indexOf(kw) >= 0) tTips.push({t:'✓ Cuvântul cheie apare în titlu',c:'#16a34a'});
  document.getElementById('t-feedback').innerHTML = tTips.map(function(t){return '<div style="color:'+t.c+'">'+t.t+'</div>';}).join('');

  // Meta description
  document.getElementById('d-count').textContent = dLen + ' caractere';
  document.getElementById('d-words').textContent = dWords + ' cuvinte';
  var dColor = dLen === 0 ? '#e2e8f0' : dLen < 70 ? '#f59e0b' : dLen <= 160 ? '#16a34a' : '#dc2626';
  setBar('d-bar', Math.min(100, dLen/180*100), dColor);
  var dTips = [];
  if (dLen === 0) dTips.push({t:'Adaugă o meta description.',c:'#94a3b8'});
  else if (dLen < 70) dTips.push({t:'⚠️ Meta description prea scurtă — ideal 140–160 car.',c:'#b45309'});
  else if (dLen <= 160) dTips.push({t:'✓ Lungime ideală pentru meta description (70–160 car.)',c:'#16a34a'});
  else dTips.push({t:'✕ Meta description prea lungă (' + dLen + ' car.) — Google trunchiază la ~160',c:'#dc2626'});
  if (kw && dLen > 0 && desc.toLowerCase().indexOf(kw) === -1) dTips.push({t:'💡 Cuvântul cheie nu apare în meta description',c:'#457b9d'});
  document.getElementById('d-feedback').innerHTML = dTips.map(function(t){return '<div style="color:'+t.c+'">'+t.t+'</div>';}).join('');

  // Scor
  if (tLen > 0 || dLen > 0) {
    var score = 0;
    if (tLen >= 30 && tLen <= 60) score += 40;
    else if (tLen > 0) score += 15;
    if (dLen >= 70 && dLen <= 160) score += 40;
    else if (dLen > 0) score += 15;
    if (kw && title.toLowerCase().indexOf(kw) >= 0) score += 10;
    if (kw && desc.toLowerCase().indexOf(kw) >= 0) score += 10;
    var sc = document.getElementById('score-circle');
    var color = score >= 80 ? '#16a34a' : score >= 50 ? '#f59e0b' : '#dc2626';
    sc.textContent = score;
    sc.style.color = color;
    sc.style.borderColor = color;
    document.getElementById('score-label').textContent = score >= 80 ? 'SEO excelent' : score >= 50 ? 'SEO acceptabil — mai ai de lucru' : 'SEO slab — necesită îmbunătățiri';
    document.getElementById('score-box').style.display = '';
  } else {
    document.getElementById('score-box').style.display = 'none';
  }

  // Preview
  if (tLen > 0 || dLen > 0) {
    document.getElementById('prev-title').textContent = title || '(titlu lipsă)';
    document.getElementById('prev-desc').textContent = desc || '(meta description lipsă)';
    document.getElementById('preview-box').style.display = '';
  } else {
    document.getElementById('preview-box').style.display = 'none';
  }
}

function setBar(id, pct, color) {
  var el = document.getElementById(id);
  el.style.width = pct + '%';
  el.style.background = color;
}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
