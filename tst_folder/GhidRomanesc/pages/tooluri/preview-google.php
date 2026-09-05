<?php
$pageTitle = 'Preview rezultat Google — cum apare pagina ta în căutări — GhidRomânesc';
$metaDescription = 'Vezi cum arată pagina ta în rezultatele Google înainte să o publici. Verifici titlul, URL-ul și meta description-ul în timp real.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/preview-google/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">👁️ Preview rezultat Google</h1>
  <p class="page-subtitle">Verifici cum apare pagina ta în Google înainte să o publici.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.5rem">
  <div class="form-group" style="margin-bottom:1rem">
    <label style="font-weight:700;font-size:.85rem;color:var(--blue-dark)">URL pagină</label>
    <input id="f-url" class="form-control" type="url" placeholder="https://exemplu.ro/pagina-mea/" oninput="update()" style="margin-top:.3rem">
  </div>
  <div class="form-group" style="margin-bottom:1rem">
    <label style="font-weight:700;font-size:.85rem;color:var(--blue-dark)">Titlu SEO <span id="c-title" style="font-weight:400;color:var(--text-muted)"></span></label>
    <input id="f-title" class="form-control" placeholder="Titlul paginii — ideal 50-60 caractere" oninput="update()" style="margin-top:.3rem">
  </div>
  <div class="form-group">
    <label style="font-weight:700;font-size:.85rem;color:var(--blue-dark)">Meta description <span id="c-desc" style="font-weight:400;color:var(--text-muted)"></span></label>
    <textarea id="f-desc" class="form-control" rows="3" placeholder="Descrierea paginii — ideal 140-160 caractere" oninput="update()" style="margin-top:.3rem"></textarea>
  </div>
</div>

<div>
  <div style="font-weight:700;font-size:.85rem;color:var(--blue-dark);margin-bottom:.75rem">Preview Desktop Google</div>
  <div style="background:#fff;border-radius:12px;padding:1.25rem;font-family:Arial,sans-serif;box-shadow:0 2px 8px rgba(0,0,0,.1)">
    <div id="prev-url" style="font-size:13px;color:#202124;margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">ghidromanesc.ro › pagina-mea</div>
    <div id="prev-title" style="font-size:20px;color:#1a0dab;line-height:1.3;margin-bottom:4px;cursor:pointer;overflow:hidden;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical">Titlul paginii tale</div>
    <div id="prev-desc" style="font-size:14px;color:#4d5156;line-height:1.58;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">Descrierea paginii apare aici. Completează câmpul Meta description pentru a vedea previzualizarea.</div>
  </div>

  <div style="font-weight:700;font-size:.85rem;color:var(--blue-dark);margin-top:1.25rem;margin-bottom:.75rem">Preview Mobile Google</div>
  <div style="background:#fff;border-radius:12px;padding:1rem;font-family:Arial,sans-serif;box-shadow:0 2px 8px rgba(0,0,0,.1);max-width:360px">
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.4rem">
      <div style="width:28px;height:28px;background:#e8f0fe;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px">G</div>
      <div>
        <div id="prev-url-mob" style="font-size:12px;color:#202124">ghidromanesc.ro</div>
        <div style="font-size:11px;color:#5f6368">▾</div>
      </div>
    </div>
    <div id="prev-title-mob" style="font-size:18px;color:#1a0dab;line-height:1.3;margin-bottom:3px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">Titlul paginii tale</div>
    <div id="prev-desc-mob" style="font-size:13px;color:#4d5156;line-height:1.5;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical">Descrierea paginii apare aici.</div>
  </div>

  <div id="seo-tips" style="margin-top:1rem;display:flex;flex-direction:column;gap:.4rem"></div>
</div>

</div>
</div>

<script>
function update() {
  const url = document.getElementById('f-url').value || 'https://ghidromanesc.ro/pagina-mea/';
  const title = document.getElementById('f-title').value || 'Titlul paginii tale';
  const desc = document.getElementById('f-desc').value || 'Descrierea paginii apare aici. Completează câmpul Meta description pentru a vedea previzualizarea.';
  const tLen = title.length, dLen = desc.length;

  document.getElementById('c-title').textContent = `(${tLen}/60 car.)`;
  document.getElementById('c-desc').textContent = `(${dLen}/160 car.)`;

  let cleanUrl = url.replace(/^https?:\/\//,'').replace(/\/$/,'');
  document.getElementById('prev-url').textContent = cleanUrl;
  document.getElementById('prev-url-mob').textContent = cleanUrl.split('/')[0];
  ['prev-title','prev-title-mob'].forEach(id => document.getElementById(id).textContent = title);
  ['prev-desc','prev-desc-mob'].forEach(id => document.getElementById(id).textContent = desc);

  const tips = [];
  if (tLen > 60) tips.push({t:`⚠️ Titlul are ${tLen} caractere — Google trunchiază la ~60`,c:'#b45309'});
  else if (tLen < 30) tips.push({t:'💡 Titlul e scurt — încearcă 50-60 caractere pentru impact maxim',c:'#457b9d'});
  else tips.push({t:'✓ Lungime titlu OK',c:'#16a34a'});
  if (dLen > 160) tips.push({t:`⚠️ Meta description are ${dLen} car. — Google trunchiază la ~160`,c:'#b45309'});
  else if (dLen < 70 && dLen > 0) tips.push({t:'💡 Meta description e scurtă — încearcă 140-160 caractere',c:'#457b9d'});
  else if (dLen > 0) tips.push({t:'✓ Lungime meta description OK',c:'#16a34a'});

  document.getElementById('seo-tips').innerHTML = tips.map(t=>`<div style="font-size:.82rem;color:${t.c};padding:.35rem .6rem;background:#f8fafc;border-radius:6px">${t.t}</div>`).join('');
}
update();
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
