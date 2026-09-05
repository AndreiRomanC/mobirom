<?php
$pageTitle = 'Câte pagini are PDF-ul? Verifică online gratuit — GhidRomânesc';
$metaDescription = 'Verifică instantaneu câte pagini are un fișier PDF, fără să îl deschizi. Gratuit, direct în browser.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/numar-pagini/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">🔢 Câte pagini are PDF-ul?</h1>
  <p class="page-subtitle">Verifici instantaneu numărul de pagini fără să deschizi fișierul.</p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem">
  <div id="drop-zone" style="border:2.5px dashed #cbd5e1;border-radius:12px;padding:3rem;text-align:center;cursor:pointer" onclick="document.getElementById('pdf-input').click()">
    <div style="font-size:3rem;margin-bottom:.75rem">📄</div>
    <div style="font-weight:700;color:var(--blue-dark);font-size:1.1rem">Selectează sau trage PDF-ul</div>
    <div style="font-size:.85rem;color:var(--text-muted);margin-top:.4rem">Poți selecta mai multe fișiere simultan</div>
    <input type="file" id="pdf-input" accept=".pdf,application/pdf" multiple style="display:none">
  </div>
  <div id="results" style="margin-top:1.5rem"></div>
</div>
</div>
<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script>
document.getElementById('pdf-input').addEventListener('change', async e => {
  const results = document.getElementById('results');
  results.innerHTML = '<p style="text-align:center;color:#457b9d">⏳ Se procesează...</p>';
  const rows = await Promise.all(Array.from(e.target.files).map(async f => {
    try {
      const doc = await PDFLib.PDFDocument.load(await f.arrayBuffer());
      return `<tr><td style="font-size:.85rem">📄 ${f.name}</td><td style="font-weight:800;font-size:1.1rem;text-align:center;color:var(--blue-dark)">${doc.getPageCount()}</td><td style="font-size:.82rem;color:var(--text-muted);text-align:center">${(f.size/1024).toFixed(0)} KB</td></tr>`;
    } catch { return `<tr><td>📄 ${f.name}</td><td colspan="2" style="color:#dc2626">Fișier invalid</td></tr>`; }
  }));
  results.innerHTML = `<table style="width:100%;border-collapse:collapse"><thead><tr style="border-bottom:2px solid var(--border)"><th style="text-align:left;padding:.5rem 0;font-size:.8rem">Fișier</th><th style="padding:.5rem;font-size:.8rem">Pagini</th><th style="padding:.5rem;font-size:.8rem">Dimensiune</th></tr></thead><tbody>${rows.join('')}</tbody></table>`;
});
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
