<?php
$pageTitle = 'Rotește pagini PDF gratuit online — GhidRomânesc';
$metaDescription = 'Rotește una sau toate paginile dintr-un PDF cu 90°, 180° sau 270°. Gratuit, direct în browser, fără upload.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/roteste-pdf/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">🔄 Rotește pagini PDF</h1>
  <p class="page-subtitle">Corectezi orientarea paginilor dintr-un PDF. 100% local, fără upload.</p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem">
  <div id="drop-zone" style="border:2.5px dashed #cbd5e1;border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;margin-bottom:1.5rem" onclick="document.getElementById('pdf-input').click()">
    <div style="font-size:2.5rem;margin-bottom:.5rem">📄</div>
    <div style="font-weight:700;color:var(--blue-dark)">Selectează PDF-ul</div>
    <input type="file" id="pdf-input" accept=".pdf,application/pdf" style="display:none">
  </div>

  <div id="form-section" style="display:none">
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem">
      📄 <strong id="file-name"></strong> — <span id="page-count"></span> pagini
    </div>

    <div class="form-group" style="margin-bottom:1rem">
      <label style="font-weight:700;color:var(--blue-dark)">Rotire</label>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-top:.5rem">
        <?php foreach(['90'=>'↻ 90°','180'=>'↕ 180°','270'=>'↺ 270°'] as $deg=>$label): ?>
        <label style="display:flex;align-items:center;justify-content:center;gap:.4rem;border:2px solid var(--border);border-radius:8px;padding:.6rem;cursor:pointer;font-weight:600;font-size:.9rem;transition:all .15s" onmouseover="this.style.borderColor='#457b9d'" onmouseout="this.style.borderColor=document.getElementById('deg<?=$deg?>').checked?'#457b9d':'var(--border)'">
          <input type="radio" name="degrees" id="deg<?=$deg?>" value="<?=$deg?>" style="display:none" onchange="document.querySelectorAll('[name=degrees]').forEach(r=>r.parentElement.style.borderColor=r.checked?'#457b9d':'var(--border)')"> <?=$label?>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-group" style="margin-bottom:1.25rem">
      <label style="font-weight:700;color:var(--blue-dark)">Pagini afectate</label>
      <div style="display:flex;gap:.75rem;margin-top:.5rem;flex-wrap:wrap">
        <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer"><input type="radio" name="pages" value="all" checked> Toate paginile</label>
        <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer"><input type="radio" name="pages" value="custom"> Pagini specifice:
          <input type="text" id="custom-pages" class="form-control" placeholder="ex: 1,3,5-8" style="width:140px;margin-left:.25rem;display:none">
        </label>
      </div>
    </div>

    <button onclick="rotatePDF()" class="btn btn-primary" style="width:100%">🔄 Rotește și descarcă</button>
    <div id="status" style="margin-top:1rem;font-size:.875rem;text-align:center"></div>
  </div>
</div>
</div>

<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script>
let selectedFile=null, totalPages=0;

document.querySelectorAll('[name=pages]').forEach(r=>r.addEventListener('change',()=>{
  document.getElementById('custom-pages').style.display=r.value==='custom'&&r.checked?'':'none';
}));

document.getElementById('pdf-input').addEventListener('change', async e=>{
  selectedFile=e.target.files[0];
  if(!selectedFile)return;
  const doc=await PDFLib.PDFDocument.load(await selectedFile.arrayBuffer());
  totalPages=doc.getPageCount();
  document.getElementById('file-name').textContent=selectedFile.name;
  document.getElementById('page-count').textContent=totalPages;
  document.getElementById('form-section').style.display='';
});

async function rotatePDF(){
  const degEl=document.querySelector('[name=degrees]:checked');
  if(!degEl)return setStatus('⚠️ Selectează unghiul de rotire.','orange');
  const deg=+degEl.value;
  setStatus('⏳ Se procesează...','#457b9d');
  try{
    const {PDFDocument,degrees}=PDFLib;
    const doc=await PDFDocument.load(await selectedFile.arrayBuffer());
    const pagesMode=document.querySelector('[name=pages]:checked').value;
    let indices=[];
    if(pagesMode==='all'){indices=[...Array(totalPages).keys()];}
    else{const txt=document.getElementById('custom-pages').value;txt.split(',').forEach(p=>{p=p.trim();const m=p.match(/^(\d+)-(\d+)$/);if(m)for(let i=+m[1];i<=+m[2];i++)indices.push(i-1);else if(/^\d+$/.test(p))indices.push(+p-1);});}
    indices.forEach(i=>{if(i>=0&&i<totalPages)doc.getPage(i).setRotation(degrees(deg));});
    const bytes=await doc.save();
    download(bytes,selectedFile.name.replace('.pdf',`-rotit${deg}.pdf`));
    setStatus('✓ PDF rotit descărcat!','#16a34a');
  }catch(e){setStatus('✕ '+e.message,'#dc2626');}
}

function download(bytes,name){const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([bytes],{type:'application/pdf'}));a.download=name;a.click();}
function setStatus(m,c){const s=document.getElementById('status');s.textContent=m;s.style.color=c;}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
