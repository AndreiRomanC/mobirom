<?php
$pageTitle = 'Adaugă marcaj de apă (watermark) PDF gratuit — GhidRomânesc';
$metaDescription = 'Adaugi text ca marcaj de apă pe toate paginile unui PDF. Gratuit, direct în browser, fără upload pe server.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/marcaj-pdf/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">💧 Marcaj de apă PDF</h1>
  <p class="page-subtitle">Adaugi text diagonal pe toate paginile unui PDF. 100% local, fără upload.</p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div id="drop-zone" style="border:2.5px dashed #cbd5e1;border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;margin-bottom:1.5rem" onclick="document.getElementById('pdf-input').click()">
    <div style="font-size:2.5rem;margin-bottom:.5rem">📄</div>
    <div style="font-weight:700;color:var(--blue-dark)">Selectează PDF-ul</div>
    <input type="file" id="pdf-input" accept=".pdf,application/pdf" style="display:none">
  </div>

  <div id="form-section" style="display:none">
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem">
      📄 <strong id="file-name"></strong>
    </div>
    <div class="form-group" style="margin-bottom:1rem">
      <label style="font-weight:700;color:var(--blue-dark)">Text marcaj de apă</label>
      <input type="text" id="wm-text" class="form-control" value="CONFIDENȚIAL" style="margin-top:.4rem;font-size:1rem">
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-bottom:1.25rem">
      <div>
        <label style="font-size:.8rem;font-weight:600;color:var(--blue-dark)">Culoare</label>
        <select id="wm-color" class="form-control" style="margin-top:.3rem">
          <option value="gray">Gri</option>
          <option value="red">Roșu</option>
          <option value="blue">Albastru</option>
          <option value="lightgray">Gri deschis</option>
        </select>
      </div>
      <div>
        <label style="font-size:.8rem;font-weight:600;color:var(--blue-dark)">Transparență</label>
        <select id="wm-opacity" class="form-control" style="margin-top:.3rem">
          <option value="0.1">10%</option>
          <option value="0.2" selected>20%</option>
          <option value="0.3">30%</option>
          <option value="0.5">50%</option>
        </select>
      </div>
      <div>
        <label style="font-size:.8rem;font-weight:600;color:var(--blue-dark)">Mărime font</label>
        <select id="wm-size" class="form-control" style="margin-top:.3rem">
          <option value="40">Mic</option>
          <option value="60" selected>Mediu</option>
          <option value="80">Mare</option>
          <option value="100">Foarte mare</option>
        </select>
      </div>
    </div>
    <button onclick="addWatermark()" class="btn btn-primary" style="width:100%">💧 Adaugă marcaj și descarcă</button>
    <div id="status" style="margin-top:1rem;font-size:.875rem;text-align:center"></div>
  </div>
</div>
</div>

<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script>
let selectedFile = null;
document.getElementById('pdf-input').addEventListener('change', e => {
  selectedFile = e.target.files[0];
  if (!selectedFile) return;
  document.getElementById('file-name').textContent = selectedFile.name;
  document.getElementById('form-section').style.display = '';
});

async function addWatermark() {
  if (!selectedFile) return;
  const text = document.getElementById('wm-text').value || 'WATERMARK';
  const colorKey = document.getElementById('wm-color').value;
  const opacity = parseFloat(document.getElementById('wm-opacity').value);
  const fontSize = parseInt(document.getElementById('wm-size').value);
  setStatus('⏳ Se procesează...', '#457b9d');
  try {
    const { PDFDocument, rgb, degrees } = PDFLib;
    const colors = { gray:[0.5,0.5,0.5], red:[0.8,0.1,0.1], blue:[0.1,0.1,0.8], lightgray:[0.8,0.8,0.8] };
    const [r,g,b] = colors[colorKey] || colors.gray;
    const doc = await PDFDocument.load(await selectedFile.arrayBuffer());
    const pages = doc.getPages();
    const font = await doc.embedFont(PDFLib.StandardFonts.HelveticaBold);
    pages.forEach(page => {
      const { width, height } = page.getSize();
      const textWidth = font.widthOfTextAtSize(text, fontSize);
      page.drawText(text, {
        x: (width - textWidth) / 2,
        y: height / 2 - fontSize / 2,
        size: fontSize,
        font,
        color: rgb(r, g, b),
        opacity,
        rotate: degrees(45),
      });
    });
    const bytes = await doc.save();
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([bytes], {type:'application/pdf'}));
    a.download = selectedFile.name.replace('.pdf', '-marcaj.pdf');
    a.click();
    setStatus('✓ PDF cu marcaj descărcat!', '#16a34a');
  } catch(e) { setStatus('✕ Eroare: ' + e.message, '#dc2626'); }
}
function setStatus(m,c){const s=document.getElementById('status');s.textContent=m;s.style.color=c;}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
