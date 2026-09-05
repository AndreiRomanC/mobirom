<?php
$pageTitle = 'Unește PDF-uri gratuit online — GhidRomânesc';
$metaDescription = 'Combină mai multe fișiere PDF într-unul singur, gratuit, direct în browser. Niciun fișier nu ajunge pe server.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/uneste-pdf/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">🔗 Unește PDF-uri</h1>
  <p class="page-subtitle">Combină oricâte PDF-uri într-un singur fișier. Procesare 100% locală — documentele nu ajung niciodată pe server.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">

<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">

  <div id="drop-zone" style="border:2.5px dashed #cbd5e1;border-radius:12px;padding:2.5rem;text-align:center;cursor:pointer;transition:border-color .2s;margin-bottom:1.5rem" onclick="document.getElementById('pdf-input').click()">
    <div style="font-size:2.5rem;margin-bottom:.5rem">📁</div>
    <div style="font-weight:700;color:var(--blue-dark);margin-bottom:.3rem">Trage PDF-urile aici sau dă click</div>
    <div style="font-size:.82rem;color:var(--text-muted)">Poți selecta mai multe fișiere simultan</div>
    <input type="file" id="pdf-input" accept=".pdf,application/pdf" multiple style="display:none">
  </div>

  <div id="file-list" style="display:none;margin-bottom:1.5rem">
    <div style="font-weight:600;font-size:.9rem;margin-bottom:.75rem;color:var(--blue-dark)">PDF-uri selectate (trage pentru a reordona):</div>
    <ul id="sortable" style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.4rem"></ul>
  </div>

  <button id="btn-merge" class="btn btn-primary" style="width:100%;display:none" onclick="mergePDFs()">🔗 Unește și descarcă PDF</button>
  <div id="status" style="margin-top:1rem;font-size:.875rem;text-align:center"></div>
</div>

<div style="background:#f8fafc;border-radius:12px;padding:1.25rem 1.5rem;font-size:.85rem;color:var(--text-muted)">
  <strong style="color:var(--blue-dark)">Cum funcționează:</strong> Selectezi PDF-urile, le reordonezi prin drag & drop, dai click pe „Unește". Totul se întâmplă în browserul tău — fără internet necesar după încărcare, fără upload, fără conturi.
</div>

</div>

<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script>
let files = [];

document.getElementById('pdf-input').addEventListener('change', e => addFiles(Array.from(e.target.files)));

const dz = document.getElementById('drop-zone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor='#457b9d'; });
dz.addEventListener('dragleave', () => dz.style.borderColor='#cbd5e1');
dz.addEventListener('drop', e => { e.preventDefault(); dz.style.borderColor='#cbd5e1'; addFiles(Array.from(e.dataTransfer.files).filter(f=>f.type==='application/pdf')); });

function addFiles(newFiles) {
  files.push(...newFiles);
  renderList();
}

function renderList() {
  const ul = document.getElementById('sortable');
  ul.innerHTML = '';
  files.forEach((f, i) => {
    const li = document.createElement('li');
    li.style = 'display:flex;align-items:center;gap:.6rem;background:#f8fafc;border:1px solid var(--border);border-radius:8px;padding:.5rem .75rem;cursor:grab';
    li.innerHTML = `<span style="color:#94a3b8;cursor:grab">⠿</span><span style="flex:1;font-size:.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">📄 ${f.name}</span><span style="font-size:.75rem;color:var(--text-muted)">${(f.size/1024).toFixed(0)}KB</span><button onclick="removeFile(${i})" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:1rem;padding:0">✕</button>`;
    ul.appendChild(li);
  });
  const hasFiles = files.length > 0;
  document.getElementById('file-list').style.display = hasFiles ? '' : 'none';
  document.getElementById('btn-merge').style.display = hasFiles ? '' : 'none';
}

function removeFile(i) { files.splice(i, 1); renderList(); }

async function mergePDFs() {
  if (files.length < 2) { setStatus('⚠️ Adaugă cel puțin 2 fișiere PDF.', 'orange'); return; }
  setStatus('⏳ Se procesează...', '#457b9d');
  try {
    const { PDFDocument } = PDFLib;
    const merged = await PDFDocument.create();
    for (const f of files) {
      const buf = await f.arrayBuffer();
      const doc = await PDFDocument.load(buf);
      const pages = await merged.copyPages(doc, doc.getPageIndices());
      pages.forEach(p => merged.addPage(p));
    }
    const bytes = await merged.save();
    download(bytes, 'ghidromanesc-unit.pdf');
    setStatus('✓ PDF unit descărcat cu succes!', '#16a34a');
  } catch(e) {
    setStatus('✕ Eroare: ' + e.message, '#dc2626');
  }
}

function download(bytes, name) {
  const blob = new Blob([bytes], {type:'application/pdf'});
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = name;
  a.click();
}

function setStatus(msg, color) {
  const s = document.getElementById('status');
  s.textContent = msg;
  s.style.color = color;
}
</script>

<?php require __DIR__ . '/../../templates/footer.php'; ?>
