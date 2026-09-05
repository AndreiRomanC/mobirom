<?php
$pageTitle = 'Împarte PDF — extrage pagini gratuit online — GhidRomânesc';
$metaDescription = 'Extrage pagini sau intervale de pagini dintr-un PDF, gratuit și direct în browser. Niciun document nu ajunge pe server.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/imparte-pdf/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">✂️ Împarte PDF</h1>
  <p class="page-subtitle">Extrage pagini sau intervale dintr-un PDF. Procesare locală — documentul rămâne la tine.</p>
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
      📄 <strong id="file-name"></strong> — <span id="page-count"></span> pagini
    </div>

    <div class="form-group" style="margin-bottom:1rem">
      <label style="font-weight:600;color:var(--blue-dark)">Pagini de extras</label>
      <p style="font-size:.8rem;color:var(--text-muted);margin:.25rem 0 .5rem">Exemplu: <code>1,3,5-8,10</code> sau <code>2-5</code> sau <code>1</code></p>
      <input type="text" id="pages-input" class="form-control" placeholder="ex: 1-3, 5, 7-9">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
      <button onclick="extractPages()" class="btn btn-primary">✂️ Extrage și descarcă</button>
      <button onclick="extractAll()" class="btn btn-secondary">📑 O pagină per fișier</button>
    </div>
    <div id="status" style="font-size:.875rem;text-align:center"></div>
  </div>
</div>
</div>

<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script>
let selectedFile = null, totalPages = 0;

document.getElementById('pdf-input').addEventListener('change', async e => {
  selectedFile = e.target.files[0];
  if (!selectedFile) return;
  const buf = await selectedFile.arrayBuffer();
  const doc = await PDFLib.PDFDocument.load(buf);
  totalPages = doc.getPageCount();
  document.getElementById('file-name').textContent = selectedFile.name;
  document.getElementById('page-count').textContent = totalPages;
  document.getElementById('form-section').style.display = '';
});

function parsePages(str) {
  const pages = new Set();
  str.split(',').forEach(part => {
    part = part.trim();
    const m = part.match(/^(\d+)-(\d+)$/);
    if (m) { for(let i=+m[1];i<=+m[2];i++) pages.add(i); }
    else if (/^\d+$/.test(part)) pages.add(+part);
  });
  return [...pages].filter(p => p>=1 && p<=totalPages).map(p=>p-1).sort((a,b)=>a-b);
}

async function extractPages() {
  const input = document.getElementById('pages-input').value.trim();
  if (!input) return setStatus('⚠️ Introdu paginile dorite.','orange');
  const indices = parsePages(input);
  if (!indices.length) return setStatus('⚠️ Nicio pagină validă găsită.','orange');
  setStatus('⏳ Se procesează...','#457b9d');
  try {
    const { PDFDocument } = PDFLib;
    const src = await PDFDocument.load(await selectedFile.arrayBuffer());
    const out = await PDFDocument.create();
    const copied = await out.copyPages(src, indices);
    copied.forEach(p => out.addPage(p));
    const bytes = await out.save();
    const name = selectedFile.name.replace('.pdf', `-pag${input.replace(/\s/g,'')}.pdf`);
    download(bytes, name);
    setStatus(`✓ Extras ${indices.length} pagini!`, '#16a34a');
  } catch(e) { setStatus('✕ ' + e.message, '#dc2626'); }
}

async function extractAll() {
  setStatus('⏳ Se generează fișierele...','#457b9d');
  try {
    const { PDFDocument } = PDFLib;
    const src = await PDFDocument.load(await selectedFile.arrayBuffer());
    for (let i=0; i<totalPages; i++) {
      const out = await PDFDocument.create();
      const [p] = await out.copyPages(src, [i]);
      out.addPage(p);
      download(await out.save(), selectedFile.name.replace('.pdf',`-pag${i+1}.pdf`));
      await new Promise(r=>setTimeout(r,300));
    }
    setStatus(`✓ ${totalPages} fișiere descărcate!`, '#16a34a');
  } catch(e) { setStatus('✕ ' + e.message, '#dc2626'); }
}

function download(bytes, name) {
  const a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([bytes],{type:'application/pdf'})); a.download = name; a.click();
}
function setStatus(m,c){const s=document.getElementById('status');s.textContent=m;s.style.color=c;}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
