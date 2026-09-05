<?php
$pageTitle = 'Convertor format imagine JPG PNG WebP — GhidRomânesc';
$metaDescription = 'Convertești imagini între JPG, PNG și WebP gratuit, direct în browser. Fără upload, fără cont necesar.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/convertor-imagine/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">🔄 Convertor format imagine</h1>
  <p class="page-subtitle">Convertești orice imagine între JPG, PNG, WebP și GIF. Procesare locală — fără upload.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div id="drop-zone" style="border:2.5px dashed #cbd5e1;border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;margin-bottom:1.5rem" onclick="document.getElementById('img-input').click()">
    <div style="font-size:2.5rem;margin-bottom:.5rem">🖼️</div>
    <div style="font-weight:700;color:var(--blue-dark)">Selectează sau trage imaginea</div>
    <div style="font-size:.82rem;color:var(--text-muted);margin-top:.25rem">JPG, PNG, WebP, GIF, BMP, SVG</div>
    <input type="file" id="img-input" accept="image/*" multiple style="display:none">
  </div>

  <div id="controls" style="display:none">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
      <div>
        <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Convertește în</label>
        <select id="fmt-out" class="form-control" style="margin-top:.3rem" onchange="updateAll()">
          <option value="image/jpeg">JPEG (.jpg)</option>
          <option value="image/png">PNG (.png)</option>
          <option value="image/webp" selected>WebP (.webp) — cel mai mic</option>
        </select>
      </div>
      <div>
        <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Calitate</label>
        <input type="range" id="quality" min="10" max="100" value="85" oninput="document.getElementById('q-val').textContent=this.value+'%';updateAll()" style="width:100%;margin-top:.75rem;accent-color:var(--blue-mid)">
        <div style="text-align:right;font-size:.75rem;color:var(--text-muted)" id="q-val">85%</div>
      </div>
    </div>

    <div id="file-list" style="display:flex;flex-direction:column;gap:.6rem;margin-bottom:1.25rem"></div>

    <button onclick="downloadAll()" class="btn btn-primary" style="width:100%">📥 Descarcă toate imaginile convertite</button>
  </div>
</div>
</div>

<script>
var images = [];

var dz = document.getElementById('drop-zone');
dz.addEventListener('dragover', function(e){e.preventDefault();dz.style.borderColor='#457b9d';});
dz.addEventListener('dragleave', function(){dz.style.borderColor='#cbd5e1';});
dz.addEventListener('drop', function(e){e.preventDefault();dz.style.borderColor='#cbd5e1';loadFiles(Array.from(e.dataTransfer.files));});
document.getElementById('img-input').addEventListener('change', function(e){loadFiles(Array.from(e.target.files));});

function loadFiles(files) {
  files.filter(function(f){return f.type.startsWith('image/');}).forEach(function(f) {
    var img = new Image();
    var url = URL.createObjectURL(f);
    img.onload = function() {
      images.push({file: f, img: img, url: url});
      renderList();
    };
    img.src = url;
  });
}

function getExt(mime) {
  var m = {'image/jpeg':'jpg','image/png':'png','image/webp':'webp'};
  return m[mime] || 'jpg';
}

function renderList() {
  var fmt = document.getElementById('fmt-out').value;
  var q   = parseInt(document.getElementById('quality').value)/100;
  var ext = getExt(fmt);
  var html = images.map(function(item, i) {
    var c = document.createElement('canvas');
    c.width = item.img.naturalWidth; c.height = item.img.naturalHeight;
    c.getContext('2d').drawImage(item.img, 0, 0);
    var prevUrl = c.toDataURL('image/jpeg', 0.5);
    return '<div style="display:flex;align-items:center;gap:.85rem;background:#f8fafc;border:1px solid var(--border);border-radius:8px;padding:.6rem .85rem">' +
      '<img src="' + prevUrl + '" style="width:50px;height:40px;object-fit:cover;border-radius:4px;flex-shrink:0">' +
      '<div style="flex:1;min-width:0">' +
        '<div style="font-size:.82rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + item.file.name + '</div>' +
        '<div style="font-size:.72rem;color:var(--text-muted)">' + item.img.naturalWidth + '×' + item.img.naturalHeight + 'px · ' + (item.file.size/1024).toFixed(0) + 'KB</div>' +
      '</div>' +
      '<div style="font-size:.75rem;color:#457b9d;white-space:nowrap">→ .' + ext + '</div>' +
      '<button onclick="downloadOne(' + i + ')" class="btn btn-sm btn-secondary" style="flex-shrink:0">↓</button>' +
    '</div>';
  }).join('');
  document.getElementById('file-list').innerHTML = html || '';
  document.getElementById('controls').style.display = images.length ? '' : 'none';
}

function updateAll() { renderList(); }

function convertOne(item) {
  var fmt = document.getElementById('fmt-out').value;
  var q   = parseInt(document.getElementById('quality').value)/100;
  var c = document.createElement('canvas');
  c.width = item.img.naturalWidth; c.height = item.img.naturalHeight;
  c.getContext('2d').drawImage(item.img, 0, 0);
  return {canvas: c, fmt: fmt, q: q, ext: getExt(fmt), name: item.file.name.replace(/\.[^.]+$/, '')};
}

function downloadOne(i) {
  var r = convertOne(images[i]);
  r.canvas.toBlob(function(b){
    var a = document.createElement('a'); a.href = URL.createObjectURL(b); a.download = r.name + '.' + r.ext; a.click();
  }, r.fmt, r.q);
}

function downloadAll() {
  images.forEach(function(item, i) { setTimeout(function(){downloadOne(i);}, i*300); });
}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
