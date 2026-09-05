<?php
$pageTitle = 'Comprimă imaginea online gratuit — fără upload — GhidRomânesc';
$metaDescription = 'Reduci dimensiunea imaginilor JPG, PNG, WebP direct în browser. Fără upload, fără cont, 100% privat.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/comprima-imagine/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">🖼️ Comprimă imagine</h1>
  <p class="page-subtitle">Reduci dimensiunea oricărei imagini direct în browser. Niciun fișier nu ajunge pe server.</p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div id="drop-zone" style="border:2.5px dashed #cbd5e1;border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;margin-bottom:1.5rem;transition:border-color .2s" onclick="document.getElementById('img-input').click()">
    <div style="font-size:2.5rem;margin-bottom:.5rem">🖼️</div>
    <div style="font-weight:700;color:var(--blue-dark)">Selectează sau trage imaginea</div>
    <div style="font-size:.82rem;color:var(--text-muted);margin-top:.25rem">JPG, PNG, WebP — max 20MB</div>
    <input type="file" id="img-input" accept="image/jpeg,image/png,image/webp" style="display:none">
  </div>

  <div id="controls" style="display:none">
    <div class="form-group" style="margin-bottom:1rem">
      <label style="font-weight:700;color:var(--blue-dark)">Calitate: <span id="quality-val">80</span>%</label>
      <input type="range" id="quality" min="10" max="100" value="80" oninput="updateQuality()" style="width:100%;margin-top:.4rem;accent-color:var(--blue-mid)">
      <div style="display:flex;justify-content:space-between;font-size:.75rem;color:var(--text-muted)"><span>Compresie maximă</span><span>Calitate maximă</span></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
      <div>
        <label style="font-size:.8rem;font-weight:600;color:var(--blue-dark)">Format output</label>
        <select id="format" class="form-control" style="margin-top:.3rem">
          <option value="image/jpeg">JPEG</option>
          <option value="image/webp">WebP (mai mic)</option>
          <option value="image/png">PNG (fără pierderi)</option>
        </select>
      </div>
      <div>
        <label style="font-size:.8rem;font-weight:600;color:var(--blue-dark)">Lățime max (px)</label>
        <input type="number" id="max-width" class="form-control" placeholder="Original" min="100" style="margin-top:.3rem">
      </div>
    </div>

    <button onclick="compress()" class="btn btn-primary" style="width:100%;margin-bottom:1rem">🗜️ Comprimă și descarcă</button>

    <div id="stats" style="display:none;background:#f0f4ff;border-radius:10px;padding:1rem;display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;text-align:center">
      <div><div style="font-size:.72rem;color:var(--text-muted)">Original</div><div id="s-orig" style="font-weight:800;color:var(--blue-dark)"></div></div>
      <div><div style="font-size:.72rem;color:var(--text-muted)">Comprimat</div><div id="s-comp" style="font-weight:800;color:#16a34a"></div></div>
      <div><div style="font-size:.72rem;color:var(--text-muted)">Reducere</div><div id="s-pct" style="font-weight:800;color:#16a34a"></div></div>
    </div>
  </div>
</div>
</div>

<script>
var origFile=null, origSize=0;

var dz=document.getElementById('drop-zone');
dz.addEventListener('dragover',e=>{e.preventDefault();dz.style.borderColor='#457b9d';});
dz.addEventListener('dragleave',()=>dz.style.borderColor='#cbd5e1');
dz.addEventListener('drop',e=>{e.preventDefault();dz.style.borderColor='#cbd5e1';loadFile(e.dataTransfer.files[0]);});
document.getElementById('img-input').addEventListener('change',e=>loadFile(e.target.files[0]));

function loadFile(f){
  if(!f||!f.type.startsWith('image/'))return;
  origFile=f; origSize=f.size;
  document.getElementById('controls').style.display='';
  document.querySelector('#drop-zone div:nth-child(2)').textContent='📷 '+f.name+' ('+fmtSize(f.size)+')';
}

function updateQuality(){document.getElementById('quality-val').textContent=document.getElementById('quality').value;}

function fmtSize(b){if(b>1048576)return (b/1048576).toFixed(2)+' MB';return (b/1024).toFixed(1)+' KB';}

async function compress(){
  if(!origFile)return;
  var quality=parseInt(document.getElementById('quality').value)/100;
  var format=document.getElementById('format').value;
  var maxW=parseInt(document.getElementById('max-width').value)||99999;
  var img=new Image();
  var url=URL.createObjectURL(origFile);
  img.onload=function(){
    var w=Math.min(img.width,maxW), h=img.height*(w/img.width);
    var canvas=document.createElement('canvas');
    canvas.width=w; canvas.height=h;
    canvas.getContext('2d').drawImage(img,0,0,w,h);
    canvas.toBlob(function(blob){
      var ext=format.split('/')[1]==='jpeg'?'jpg':format.split('/')[1];
      var name=origFile.name.replace(/\.[^.]+$/,'')+'-comprimat.'+ext;
      var a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=name;a.click();
      var s=document.getElementById('stats');
      document.getElementById('s-orig').textContent=fmtSize(origSize);
      document.getElementById('s-comp').textContent=fmtSize(blob.size);
      var pct=Math.round((1-blob.size/origSize)*100);
      document.getElementById('s-pct').textContent=(pct>0?'-':'')+pct+'%';
      s.style.display='grid';
      URL.revokeObjectURL(url);
    },format,quality);
  };
  img.src=url;
}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
