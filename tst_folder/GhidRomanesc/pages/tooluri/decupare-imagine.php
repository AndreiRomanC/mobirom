<?php
$pageTitle = 'Decupează imagine online gratuit — GhidRomânesc';
$metaDescription = 'Decupezi orice imagine direct în browser. Poți seta coordonate exacte sau trage zona de decupare. Fără upload pe server.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/decupare-imagine/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">✂️ Decupează imagine</h1>
  <p class="page-subtitle">Decupezi zona dorită dintr-o imagine. Poți introduce coordonate exacte sau trage zona cu mouse-ul.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div id="drop-zone" style="border:2.5px dashed #cbd5e1;border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;margin-bottom:1.5rem" onclick="document.getElementById('img-input').click()">
    <div style="font-size:2.5rem;margin-bottom:.5rem">🖼️</div>
    <div style="font-weight:700;color:var(--blue-dark)">Selectează sau trage imaginea</div>
    <input type="file" id="img-input" accept="image/*" style="display:none">
  </div>

  <div id="controls" style="display:none">
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem">
      🖼️ <strong id="file-info"></strong>
    </div>

    <!-- Canvas de crop -->
    <div style="position:relative;display:inline-block;max-width:100%;margin-bottom:1.25rem;cursor:crosshair;user-select:none" id="canvas-wrap">
      <canvas id="canvas-main" style="display:block;max-width:100%;border-radius:8px;border:1px solid var(--border)"></canvas>
      <div id="crop-overlay" style="position:absolute;border:2px solid #457b9d;background:rgba(69,123,157,.1);display:none;pointer-events:none"></div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;margin-bottom:1rem">
      <?php foreach(['X'=>'cx','Y'=>'cy','Lățime'=>'cw','Înălțime'=>'ch'] as $label=>$id): ?>
      <div>
        <label style="font-size:.75rem;font-weight:700;color:var(--blue-dark)"><?=$label?></label>
        <input type="number" id="<?=$id?>" class="form-control" min="0" oninput="updateFromInputs()" style="margin-top:.2rem">
      </div>
      <?php endforeach; ?>
    </div>

    <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap">
      <button onclick="cropRatio(1,1)" class="btn btn-sm btn-secondary">1:1 pătrat</button>
      <button onclick="cropRatio(16,9)" class="btn btn-sm btn-secondary">16:9</button>
      <button onclick="cropRatio(4,3)" class="btn btn-sm btn-secondary">4:3</button>
      <button onclick="cropRatio(3,2)" class="btn btn-sm btn-secondary">3:2</button>
      <button onclick="selectAll()" class="btn btn-sm btn-secondary">Selectează tot</button>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
      <div>
        <label style="font-size:.8rem;font-weight:700;color:var(--blue-dark)">Format</label>
        <select id="fmt" class="form-control" style="margin-top:.3rem">
          <option value="image/jpeg">JPEG</option>
          <option value="image/png">PNG</option>
          <option value="image/webp">WebP</option>
        </select>
      </div>
      <div style="display:flex;align-items:flex-end">
        <button onclick="cropAndDownload()" class="btn btn-primary" style="width:100%">✂️ Decupează și descarcă</button>
      </div>
    </div>
    <div id="status" style="text-align:center;font-size:.85rem"></div>
  </div>
</div>
</div>

<script>
var img = new Image(), origFile = null, scale = 1;
var cropX=0, cropY=0, cropW=0, cropH=0;
var isDragging=false, dragStart={x:0,y:0};

var dz=document.getElementById('drop-zone');
dz.addEventListener('dragover',function(e){e.preventDefault();});
dz.addEventListener('drop',function(e){e.preventDefault();loadFile(e.dataTransfer.files[0]);});
document.getElementById('img-input').addEventListener('change',function(e){loadFile(e.target.files[0]);});

function loadFile(f) {
  if(!f||!f.type.startsWith('image/'))return;
  origFile=f;
  img.onload=function(){
    var c=document.getElementById('canvas-main');
    var maxW=Math.min(700,window.innerWidth-80);
    scale=Math.min(maxW/img.naturalWidth,1);
    c.width=Math.round(img.naturalWidth*scale);
    c.height=Math.round(img.naturalHeight*scale);
    c.getContext('2d').drawImage(img,0,0,c.width,c.height);
    selectAll();
    document.getElementById('file-info').textContent=f.name+' — '+img.naturalWidth+'×'+img.naturalHeight+'px';
    var fmt=document.getElementById('fmt');
    fmt.value=f.type==='image/png'?'image/png':f.type==='image/webp'?'image/webp':'image/jpeg';
    document.getElementById('controls').style.display='';
    setupDrag();
  };
  img.src=URL.createObjectURL(f);
}

function selectAll(){setC(0,0,img.naturalWidth,img.naturalHeight);}

function cropRatio(rw,rh){
  var maxW=img.naturalWidth,maxH=img.naturalHeight;
  var w=maxW,h=Math.round(w*rh/rw);
  if(h>maxH){h=maxH;w=Math.round(h*rw/rh);}
  var x=Math.round((maxW-w)/2),y=Math.round((maxH-h)/2);
  setC(x,y,w,h);
}

function setC(x,y,w,h){
  cropX=Math.max(0,Math.min(x,img.naturalWidth));
  cropY=Math.max(0,Math.min(y,img.naturalHeight));
  cropW=Math.max(1,Math.min(w,img.naturalWidth-cropX));
  cropH=Math.max(1,Math.min(h,img.naturalHeight-cropY));
  document.getElementById('cx').value=Math.round(cropX);
  document.getElementById('cy').value=Math.round(cropY);
  document.getElementById('cw').value=Math.round(cropW);
  document.getElementById('ch').value=Math.round(cropH);
  updateOverlay();
}

function updateFromInputs(){
  cropX=parseInt(document.getElementById('cx').value)||0;
  cropY=parseInt(document.getElementById('cy').value)||0;
  cropW=parseInt(document.getElementById('cw').value)||1;
  cropH=parseInt(document.getElementById('ch').value)||1;
  updateOverlay();
}

function updateOverlay(){
  var ov=document.getElementById('crop-overlay');
  ov.style.display='block';
  ov.style.left=Math.round(cropX*scale)+'px';
  ov.style.top=Math.round(cropY*scale)+'px';
  ov.style.width=Math.round(cropW*scale)+'px';
  ov.style.height=Math.round(cropH*scale)+'px';
}

function setupDrag(){
  var c=document.getElementById('canvas-main');
  c.addEventListener('mousedown',function(e){
    var r=c.getBoundingClientRect();
    dragStart={x:e.clientX-r.left,y:e.clientY-r.top};
    isDragging=true;
  });
  document.addEventListener('mousemove',function(e){
    if(!isDragging)return;
    var r=c.getBoundingClientRect();
    var x=e.clientX-r.left,y=e.clientY-r.top;
    var x0=Math.min(dragStart.x,x),y0=Math.min(dragStart.y,y);
    var w=Math.abs(x-dragStart.x),h=Math.abs(y-dragStart.y);
    setC(x0/scale,y0/scale,w/scale,h/scale);
  });
  document.addEventListener('mouseup',function(){isDragging=false;});
}

function cropAndDownload(){
  var fmt=document.getElementById('fmt').value;
  var ext=fmt.split('/')[1]==='jpeg'?'jpg':fmt.split('/')[1];
  var c=document.createElement('canvas');
  c.width=Math.round(cropW); c.height=Math.round(cropH);
  c.getContext('2d').drawImage(img,cropX,cropY,cropW,cropH,0,0,cropW,cropH);
  c.toBlob(function(b){
    var a=document.createElement('a');
    a.href=URL.createObjectURL(b);
    a.download=(origFile?origFile.name.replace(/\.[^.]+$/,''):'imagine')+'-decupat.'+ext;
    a.click();
    var s=document.getElementById('status');s.textContent='✓ Descărcat!';s.style.color='#16a34a';
  },fmt,0.92);
}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
