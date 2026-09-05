<?php
$pageTitle = 'Redimensionează imagine online gratuit — GhidRomânesc';
$metaDescription = 'Redimensionezi orice imagine la dimensiunile dorite. JPG, PNG, WebP. Fără upload, procesare locală în browser.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/redimensionare-imagine/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">📐 Redimensionează imagine</h1>
  <p class="page-subtitle">Schimbi dimensiunile oricărei imagini. Procesare locală — fără upload pe server.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div id="drop-zone" style="border:2.5px dashed #cbd5e1;border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;margin-bottom:1.5rem;transition:border-color .2s" onclick="document.getElementById('img-input').click()">
    <div style="font-size:2.5rem;margin-bottom:.5rem">🖼️</div>
    <div style="font-weight:700;color:var(--blue-dark)">Selectează sau trage imaginea</div>
    <div style="font-size:.82rem;color:var(--text-muted);margin-top:.25rem">JPG, PNG, WebP, GIF</div>
    <input type="file" id="img-input" accept="image/*" style="display:none">
  </div>

  <div id="controls" style="display:none">
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem">
      🖼️ <strong id="file-info"></strong>
    </div>

    <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:.75rem;align-items:flex-end;margin-bottom:.75rem">
      <div>
        <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Lățime (px)</label>
        <input type="number" id="new-w" class="form-control" min="1" oninput="syncDim('w')" style="margin-top:.3rem">
      </div>
      <div style="padding-bottom:.5rem;font-size:1.2rem;color:var(--text-muted);text-align:center">×</div>
      <div>
        <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Înălțime (px)</label>
        <input type="number" id="new-h" class="form-control" min="1" oninput="syncDim('h')" style="margin-top:.3rem">
      </div>
    </div>

    <div style="display:flex;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap">
      <label style="display:flex;align-items:center;gap:.35rem;font-size:.85rem;cursor:pointer">
        <input type="checkbox" id="lock-ratio" checked> Păstrează proporțiile
      </label>
      <div style="display:flex;gap:.4rem;flex-wrap:wrap">
        <?php foreach(['320×240'=>[320,240],'640×480'=>[640,480],'800×600'=>[800,600],'1280×720'=>[1280,720],'1920×1080'=>[1920,1080]] as $label=>$dims): ?>
        <button onclick="setSize(<?=$dims[0]?>,<?=$dims[1]?>)" class="btn btn-sm btn-secondary" style="font-size:.72rem"><?=$label?></button>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
      <div>
        <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Format output</label>
        <select id="fmt" class="form-control" style="margin-top:.3rem">
          <option value="image/jpeg">JPEG</option>
          <option value="image/png">PNG</option>
          <option value="image/webp">WebP</option>
        </select>
      </div>
      <div>
        <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Calitate (JPEG/WebP)</label>
        <input type="range" id="quality" min="10" max="100" value="90" style="width:100%;margin-top:.75rem;accent-color:var(--blue-mid)">
        <div style="text-align:right;font-size:.75rem;color:var(--text-muted)" id="q-val">90%</div>
      </div>
    </div>

    <!-- Preview -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem">
      <div style="text-align:center">
        <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.4rem">Original</div>
        <canvas id="prev-orig" style="max-width:100%;border-radius:6px;border:1px solid var(--border)"></canvas>
        <div id="orig-dims" style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem"></div>
      </div>
      <div style="text-align:center">
        <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.4rem">Rezultat</div>
        <canvas id="prev-new" style="max-width:100%;border-radius:6px;border:1px solid var(--border)"></canvas>
        <div id="new-dims" style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem"></div>
      </div>
    </div>

    <button onclick="download()" class="btn btn-primary" style="width:100%">📥 Descarcă imaginea redimensionată</button>
    <div id="status" style="text-align:center;font-size:.85rem;margin-top:.75rem"></div>
  </div>
</div>
</div>

<script>
var img = new Image(), origW = 0, origH = 0, origFile = null;

var dz = document.getElementById('drop-zone');
dz.addEventListener('dragover', function(e){e.preventDefault();dz.style.borderColor='#457b9d';});
dz.addEventListener('dragleave', function(){dz.style.borderColor='#cbd5e1';});
dz.addEventListener('drop', function(e){e.preventDefault();dz.style.borderColor='#cbd5e1';loadFile(e.dataTransfer.files[0]);});
document.getElementById('img-input').addEventListener('change', function(e){loadFile(e.target.files[0]);});
document.getElementById('quality').addEventListener('input', function(){document.getElementById('q-val').textContent=this.value+'%'; updatePreview();});

function loadFile(f) {
  if (!f || !f.type.startsWith('image/')) return;
  origFile = f;
  var url = URL.createObjectURL(f);
  img.onload = function() {
    origW = img.naturalWidth; origH = img.naturalHeight;
    document.getElementById('new-w').value = origW;
    document.getElementById('new-h').value = origH;
    document.getElementById('file-info').textContent = f.name + ' — ' + origW + '×' + origH + 'px — ' + (f.size/1024).toFixed(0) + 'KB';
    // Set format based on file type
    var fmtSel = document.getElementById('fmt');
    if (f.type === 'image/png') fmtSel.value = 'image/png';
    else if (f.type === 'image/webp') fmtSel.value = 'image/webp';
    else fmtSel.value = 'image/jpeg';
    document.getElementById('controls').style.display = '';
    drawPreview('prev-orig', origW, origH, 120);
    updatePreview();
    URL.revokeObjectURL(url);
  };
  img.src = url;
}

function drawPreview(canvasId, w, h, maxSize) {
  var c = document.getElementById(canvasId);
  var scale = Math.min(maxSize/w, maxSize/h, 1);
  c.width = Math.round(w*scale); c.height = Math.round(h*scale);
  c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
  document.getElementById(canvasId.replace('prev-','')+'dims').textContent = w + '×' + h + 'px';
}

function syncDim(changed) {
  if (!document.getElementById('lock-ratio').checked) { updatePreview(); return; }
  var w = parseInt(document.getElementById('new-w').value)||1;
  var h = parseInt(document.getElementById('new-h').value)||1;
  var ratio = origW / origH;
  if (changed === 'w') document.getElementById('new-h').value = Math.round(w / ratio);
  else document.getElementById('new-w').value = Math.round(h * ratio);
  updatePreview();
}

function setSize(w, h) {
  document.getElementById('new-w').value = w;
  document.getElementById('new-h').value = h;
  updatePreview();
}

function updatePreview() {
  var w = parseInt(document.getElementById('new-w').value)||1;
  var h = parseInt(document.getElementById('new-h').value)||1;
  drawPreview('prev-new', w, h, 120);
}

function download() {
  var w = parseInt(document.getElementById('new-w').value)||1;
  var h = parseInt(document.getElementById('new-h').value)||1;
  var fmt = document.getElementById('fmt').value;
  var q = parseInt(document.getElementById('quality').value)/100;
  var ext = fmt.split('/')[1] === 'jpeg' ? 'jpg' : fmt.split('/')[1];
  var c = document.createElement('canvas'); c.width = w; c.height = h;
  c.getContext('2d').drawImage(img, 0, 0, w, h);
  c.toBlob(function(blob){
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = (origFile ? origFile.name.replace(/\.[^.]+$/, '') : 'imagine') + '-' + w + 'x' + h + '.' + ext;
    a.click();
    document.getElementById('status').textContent = '✓ Descărcat!';
    document.getElementById('status').style.color = '#16a34a';
  }, fmt, q);
}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
