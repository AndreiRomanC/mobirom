<?php
$pageTitle = 'Convertește imagini în PDF online gratuit — GhidRomânesc';
$metaDescription = 'Transformi mai multe imagini JPG, PNG sau WebP într-un singur PDF. Gratuit, direct în browser, fără upload.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/imagini-la-pdf/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">📄 Imagini → PDF</h1>
  <p class="page-subtitle">Transformi una sau mai multe imagini într-un singur PDF. Fiecare imagine = o pagină.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div id="drop-zone" style="border:2.5px dashed #cbd5e1;border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;margin-bottom:1.5rem;transition:border-color .2s" onclick="document.getElementById('img-input').click()">
    <div style="font-size:2.5rem;margin-bottom:.5rem">🖼️</div>
    <div style="font-weight:700;color:var(--blue-dark)">Selectează sau trage imaginile</div>
    <div style="font-size:.82rem;color:var(--text-muted);margin-top:.25rem">JPG, PNG, WebP — poți selecta mai multe</div>
    <input type="file" id="img-input" accept="image/jpeg,image/png,image/webp" multiple style="display:none">
  </div>

  <div id="controls" style="display:none">
    <div style="margin-bottom:1rem">
      <div style="font-weight:600;font-size:.85rem;color:var(--blue-dark);margin-bottom:.5rem">Imagini selectate <span style="font-weight:400;color:var(--text-muted)">(trage pentru reordonare)</span></div>
      <div id="img-list" style="display:flex;flex-direction:column;gap:.4rem"></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
      <div>
        <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Dimensiune pagină</label>
        <select id="page-size" class="form-control" style="margin-top:.3rem">
          <option value="auto">Auto (dimensiunea imaginii)</option>
          <option value="a4">A4 (210×297 mm)</option>
          <option value="a4-l">A4 Landscape (297×210 mm)</option>
          <option value="letter">Letter (216×279 mm)</option>
        </select>
      </div>
      <div>
        <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Margini</label>
        <select id="margins" class="form-control" style="margin-top:.3rem">
          <option value="0">Fără margini</option>
          <option value="20" selected>Mici (20px)</option>
          <option value="40">Medii (40px)</option>
        </select>
      </div>
    </div>

    <button onclick="convert()" id="btn-convert" class="btn btn-primary" style="width:100%">📄 Generează PDF</button>
    <div id="status" style="text-align:center;font-size:.875rem;margin-top:.75rem"></div>
  </div>
</div>
</div>

<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script>
var files = [];

var dz = document.getElementById('drop-zone');
dz.addEventListener('dragover',function(e){e.preventDefault();dz.style.borderColor='#457b9d';});
dz.addEventListener('dragleave',function(){dz.style.borderColor='#cbd5e1';});
dz.addEventListener('drop',function(e){e.preventDefault();dz.style.borderColor='#cbd5e1';addFiles(Array.from(e.dataTransfer.files));});
document.getElementById('img-input').addEventListener('change',function(e){addFiles(Array.from(e.target.files));});

function addFiles(newFiles) {
  newFiles.filter(function(f){return f.type.startsWith('image/');}).forEach(function(f){files.push(f);});
  renderList();
}

function renderList() {
  var html = files.map(function(f,i){
    return '<div style="display:flex;align-items:center;gap:.6rem;background:#f8fafc;border:1px solid var(--border);border-radius:8px;padding:.5rem .75rem">' +
      '<span style="color:#94a3b8;cursor:grab">⠿</span>' +
      '<span style="font-size:.82rem;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">🖼️ '+f.name+'</span>' +
      '<span style="font-size:.72rem;color:var(--text-muted)">'+(f.size/1024).toFixed(0)+'KB</span>' +
      '<button onclick="removeFile('+i+')" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:.9rem;padding:0">✕</button>' +
    '</div>';
  }).join('');
  document.getElementById('img-list').innerHTML = html;
  document.getElementById('controls').style.display = files.length ? '' : 'none';
}

function removeFile(i) { files.splice(i,1); renderList(); }

async function convert() {
  if (!files.length) return;
  var btn = document.getElementById('btn-convert');
  btn.disabled = true; btn.textContent = '⏳ Se procesează...';
  setStatus('⏳ Se procesează ' + files.length + ' imagini...', '#457b9d');
  try {
    var {PDFDocument} = PDFLib;
    var pdf = await PDFDocument.create();
    var pageSize = document.getElementById('page-size').value;
    var margin = parseInt(document.getElementById('margins').value);
    var A4 = [595.28, 841.89]; // points

    for (var i=0; i<files.length; i++) {
      var f = files[i];
      var buf = await f.arrayBuffer();
      var pdfImg;
      if (f.type === 'image/png') pdfImg = await pdf.embedPng(buf);
      else {
        // Convert to JPEG via canvas
        var img = new Image();
        await new Promise(function(res){
          img.onload=res;
          img.src=URL.createObjectURL(f);
        });
        var c=document.createElement('canvas'); c.width=img.naturalWidth; c.height=img.naturalHeight;
        c.getContext('2d').drawImage(img,0,0);
        var jpegBuf = await new Promise(function(res){c.toBlob(function(b){b.arrayBuffer().then(res);},'image/jpeg',0.92);});
        pdfImg = await pdf.embedJpg(jpegBuf);
      }

      var iw = pdfImg.width, ih = pdfImg.height;
      var pw, ph;

      if (pageSize === 'auto') {
        pw = iw + margin*2; ph = ih + margin*2;
      } else if (pageSize === 'a4') {
        pw = A4[0]; ph = A4[1];
      } else if (pageSize === 'a4-l') {
        pw = A4[1]; ph = A4[0];
      } else {
        pw = 612; ph = 792; // letter
      }

      var page = pdf.addPage([pw, ph]);
      var avW = pw - margin*2, avH = ph - margin*2;
      var scale = Math.min(avW/iw, avH/ih, 1);
      var dw = iw*scale, dh = ih*scale;
      var x = margin + (avW-dw)/2, y = margin + (avH-dh)/2;
      page.drawImage(pdfImg, {x, y, width: dw, height: dh});
    }

    var bytes = await pdf.save();
    var a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([bytes],{type:'application/pdf'}));
    a.download = 'imagini-ghidromanesc.pdf'; a.click();
    setStatus('✓ PDF cu ' + files.length + ' pagini descărcat!', '#16a34a');
  } catch(e) {
    setStatus('✕ Eroare: ' + e.message, '#dc2626');
  }
  btn.disabled = false; btn.textContent = '📄 Generează PDF';
}

function setStatus(m,c){var s=document.getElementById('status');s.textContent=m;s.style.color=c;}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
