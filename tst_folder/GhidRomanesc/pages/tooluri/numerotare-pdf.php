<?php
$pageTitle = 'Adaugă numere de pagină PDF gratuit online — GhidRomânesc';
$metaDescription = 'Adaugi automat numere de pagină la orice PDF. Alegi poziția, formatul și de la ce număr începi. Gratuit, local în browser.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/numerotare-pdf/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">🔢 Numerotare pagini PDF</h1>
  <p class="page-subtitle">Adaugi numere de pagină la orice PDF. Alegi poziția, formatul și numărul de start.</p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem">

  <div id="drop-zone" style="border:2.5px dashed #cbd5e1;border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;margin-bottom:1.5rem" onclick="document.getElementById('pdf-input').click()">
    <div style="font-size:2.5rem;margin-bottom:.5rem">📄</div>
    <div style="font-weight:700;color:var(--blue-dark)">Selectează PDF-ul</div>
    <div style="font-size:.82rem;color:var(--text-muted);margin-top:.25rem">Numere de pagină se adaugă automat pe fiecare pagină</div>
    <input type="file" id="pdf-input" accept=".pdf,application/pdf" style="display:none">
  </div>

  <div id="form-section" style="display:none">
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem">
      📄 <strong id="file-name"></strong> — <span id="page-count"></span> pagini
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
      <div>
        <label style="font-size:.8rem;font-weight:600;color:var(--blue-dark)">Poziție</label>
        <select id="p-pos" class="form-control" style="margin-top:.3rem">
          <option value="bottom-center">Jos — centru</option>
          <option value="bottom-right">Jos — dreapta</option>
          <option value="bottom-left">Jos — stânga</option>
          <option value="top-center">Sus — centru</option>
          <option value="top-right">Sus — dreapta</option>
          <option value="top-left">Sus — stânga</option>
        </select>
      </div>
      <div>
        <label style="font-size:.8rem;font-weight:600;color:var(--blue-dark)">Format</label>
        <select id="p-format" class="form-control" style="margin-top:.3rem">
          <option value="N">1, 2, 3...</option>
          <option value="N/T">1/10, 2/10...</option>
          <option value="Pagina N">Pagina 1, Pagina 2...</option>
          <option value="- N -">- 1 -, - 2 -...</option>
        </select>
      </div>
      <div>
        <label style="font-size:.8rem;font-weight:600;color:var(--blue-dark)">Număr start</label>
        <input type="number" id="p-start" class="form-control" value="1" min="0" style="margin-top:.3rem">
      </div>
      <div>
        <label style="font-size:.8rem;font-weight:600;color:var(--blue-dark)">Mărime font</label>
        <select id="p-size" class="form-control" style="margin-top:.3rem">
          <option value="8">Mic (8pt)</option>
          <option value="10" selected>Normal (10pt)</option>
          <option value="12">Mare (12pt)</option>
        </select>
      </div>
    </div>

    <button onclick="addPageNumbers()" class="btn btn-primary" style="width:100%">🔢 Adaugă numerotare și descarcă</button>
    <div id="status" style="margin-top:1rem;font-size:.875rem;text-align:center"></div>
  </div>
</div>
</div>

<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script>
let selectedFile=null, totalPages=0;
document.getElementById('pdf-input').addEventListener('change', async e=>{
  selectedFile=e.target.files[0]; if(!selectedFile)return;
  const doc=await PDFLib.PDFDocument.load(await selectedFile.arrayBuffer());
  totalPages=doc.getPageCount();
  document.getElementById('file-name').textContent=selectedFile.name;
  document.getElementById('page-count').textContent=totalPages;
  document.getElementById('form-section').style.display='';
});

async function addPageNumbers(){
  if(!selectedFile)return;
  const pos=document.getElementById('p-pos').value;
  const fmt=document.getElementById('p-format').value;
  const start=parseInt(document.getElementById('p-start').value)||1;
  const size=parseInt(document.getElementById('p-size').value)||10;
  setStatus('⏳ Se procesează...','#457b9d');
  try{
    const {PDFDocument,rgb,StandardFonts}=PDFLib;
    const doc=await PDFDocument.load(await selectedFile.arrayBuffer());
    const font=await doc.embedFont(StandardFonts.Helvetica);
    const pages=doc.getPages();
    pages.forEach((page,i)=>{
      const {width,height}=page.getSize();
      const n=start+i, t=totalPages;
      const label=fmt.replace('N',n).replace('T',t+(start-1));
      const tw=font.widthOfTextAtSize(label,size);
      let x,y; const margin=20;
      if(pos.includes('bottom')) y=margin; else y=height-margin-size;
      if(pos.includes('center')) x=(width-tw)/2;
      else if(pos.includes('right')) x=width-tw-margin;
      else x=margin;
      page.drawText(label,{x,y,size,font,color:rgb(0.3,0.3,0.3)});
    });
    const bytes=await doc.save();
    const a=document.createElement('a');
    a.href=URL.createObjectURL(new Blob([bytes],{type:'application/pdf'}));
    a.download=selectedFile.name.replace('.pdf','-numerotat.pdf');
    a.click();
    setStatus('✓ PDF numerotat descărcat!','#16a34a');
  }catch(e){setStatus('✕ Eroare: '+e.message,'#dc2626');}
}
function setStatus(m,c){const s=document.getElementById('status');s.textContent=m;s.style.color=c;}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
