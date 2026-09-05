<?php
$pageTitle = 'Rotește și întoarce imaginea online gratuit — GhidRomânesc';
$metaDescription = 'Rotești imaginea cu 90°, 180°, 270° sau o întorci orizontal/vertical. Gratuit, direct în browser, fără upload.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/rotire-imagine/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">🔄 Rotire și flip imagine</h1>
  <p class="page-subtitle">Rotești sau întorci orice imagine. Procesare locală — fără upload pe server.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div id="drop-zone" style="border:2.5px dashed #cbd5e1;border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;margin-bottom:1.5rem" onclick="document.getElementById('img-input').click()">
    <div style="font-size:2.5rem;margin-bottom:.5rem">🖼️</div>
    <div style="font-weight:700;color:var(--blue-dark)">Selectează sau trage imaginea</div>
    <input type="file" id="img-input" accept="image/*" style="display:none">
  </div>

  <div id="controls" style="display:none">
    <!-- Butoane acțiuni -->
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;justify-content:center;margin-bottom:1.25rem">
      <?php
      $btns = [
        ['↺ 90°', 'rot(-90)', 'Rotire stânga 90°'],
        ['↻ 90°', 'rot(90)', 'Rotire dreapta 90°'],
        ['↕ 180°', 'rot(180)', 'Rotire 180°'],
        ['↔ Flip H', "flip('h')", 'Răstoarnă orizontal'],
        ['↕ Flip V', "flip('v')", 'Răstoarnă vertical'],
      ];
      foreach($btns as $b): ?>
      <button onclick="<?=$b[1]?>" class="btn btn-secondary" title="<?=$b[2]?>" style="min-width:90px"><?=$b[0]?></button>
      <?php endforeach; ?>
      <button onclick="resetImg()" class="btn btn-secondary" title="Resetează" style="color:#dc2626">✕ Reset</button>
    </div>

    <!-- Preview -->
    <div style="text-align:center;margin-bottom:1.25rem">
      <canvas id="preview" style="max-width:100%;max-height:400px;border-radius:8px;border:1px solid var(--border)"></canvas>
      <div id="dims" style="font-size:.75rem;color:var(--text-muted);margin-top:.4rem"></div>
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
      <div style="display:flex;align-items:flex-end">
        <button onclick="dlImg()" class="btn btn-primary" style="width:100%">📥 Descarcă</button>
      </div>
    </div>
  </div>
</div>
</div>

<script>
var origImg = new Image(), origFile = null;
var rotation = 0, flipH = false, flipV = false;

var dz = document.getElementById('drop-zone');
dz.addEventListener('dragover',function(e){e.preventDefault();dz.style.borderColor='#457b9d';});
dz.addEventListener('dragleave',function(){dz.style.borderColor='#cbd5e1';});
dz.addEventListener('drop',function(e){e.preventDefault();dz.style.borderColor='#cbd5e1';loadFile(e.dataTransfer.files[0]);});
document.getElementById('img-input').addEventListener('change',function(e){loadFile(e.target.files[0]);});

function loadFile(f) {
  if (!f || !f.type.startsWith('image/')) return;
  origFile = f; rotation = 0; flipH = false; flipV = false;
  var url = URL.createObjectURL(f);
  origImg.onload = function() {
    document.getElementById('controls').style.display = '';
    var fmt = document.getElementById('fmt');
    fmt.value = f.type === 'image/png' ? 'image/png' : f.type === 'image/webp' ? 'image/webp' : 'image/jpeg';
    render(); URL.revokeObjectURL(url);
  };
  origImg.src = url;
}

function rot(deg) { rotation = ((rotation + deg) % 360 + 360) % 360; render(); }
function flip(dir) { if(dir==='h') flipH=!flipH; else flipV=!flipV; render(); }
function resetImg() { rotation=0; flipH=false; flipV=false; render(); }

function render() {
  var rad = rotation * Math.PI / 180;
  var sw = origImg.naturalWidth, sh = origImg.naturalHeight;
  var rotated90 = rotation === 90 || rotation === 270;
  var cw = rotated90 ? sh : sw, ch = rotated90 ? sw : sh;

  var canvas = document.getElementById('preview');
  canvas.width = cw; canvas.height = ch;
  var ctx = canvas.getContext('2d');
  ctx.clearRect(0,0,cw,ch);
  ctx.translate(cw/2, ch/2);
  ctx.rotate(rad);
  ctx.scale(flipH ? -1 : 1, flipV ? -1 : 1);
  ctx.drawImage(origImg, -sw/2, -sh/2);
  ctx.setTransform(1,0,0,1,0,0);
  document.getElementById('dims').textContent = cw + '×' + ch + 'px · Rotire: ' + rotation + '°' + (flipH?' · Flip H':'') + (flipV?' · Flip V':'');
}

function dlImg() {
  var c = document.getElementById('preview');
  var fmt = document.getElementById('fmt').value;
  var ext = fmt.split('/')[1] === 'jpeg' ? 'jpg' : fmt.split('/')[1];
  var name = origFile ? origFile.name.replace(/\.[^.]+$/,'') : 'imagine';
  c.toBlob(function(b){
    var a = document.createElement('a'); a.href = URL.createObjectURL(b); a.download = name+'-rotit.'+ext; a.click();
  }, fmt, 0.92);
}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
