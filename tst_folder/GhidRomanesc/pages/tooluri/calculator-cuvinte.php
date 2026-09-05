<?php
$pageTitle = 'Calculator cuvinte și timp de citire — GhidRomânesc';
$metaDescription = 'Numără cuvintele, caracterele și estimează timpul de citire al oricărui text. Gratuit și instantaneu.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/calculator-cuvinte/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">⏱️ Calculator cuvinte & timp citire</h1>
  <p class="page-subtitle">Lipești textul, primești instant număr de cuvinte, caractere și timpul estimat de citire.</p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">
  <textarea id="input" class="form-control" rows="10" placeholder="Lipește sau scrie textul tău aici..." oninput="analyze()" style="margin-bottom:1.25rem;font-size:.9rem"></textarea>

  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1.25rem">
    <?php foreach([['id'=>'cnt-words','label'=>'Cuvinte'],['id'=>'cnt-chars','label'=>'Caractere'],['id'=>'cnt-sentences','label'=>'Propoziții'],['id'=>'cnt-para','label'=>'Paragrafe']] as $c): ?>
    <div style="text-align:center;background:#f8fafc;border-radius:10px;padding:.75rem .5rem">
      <div id="<?=$c['id']?>" style="font-size:1.6rem;font-weight:800;color:var(--blue-dark)">0</div>
      <div style="font-size:.75rem;color:var(--text-muted);margin-top:.2rem"><?=$c['label']?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
    <div style="background:#e0f2fe;border-radius:10px;padding:.75rem 1rem;text-align:center">
      <div style="font-size:.75rem;color:#0369a1;font-weight:700;margin-bottom:.2rem">Timp citire mediu</div>
      <div id="read-avg" style="font-size:1.25rem;font-weight:800;color:#0369a1">0 sec.</div>
      <div style="font-size:.72rem;color:#0369a1;opacity:.7">~200 cuv/min</div>
    </div>
    <div style="background:#f0fdf4;border-radius:10px;padding:.75rem 1rem;text-align:center">
      <div style="font-size:.75rem;color:#15803d;font-weight:700;margin-bottom:.2rem">Timp citire rapid</div>
      <div id="read-fast" style="font-size:1.25rem;font-weight:800;color:#15803d">0 sec.</div>
      <div style="font-size:.72rem;color:#15803d;opacity:.7">~300 cuv/min</div>
    </div>
  </div>
</div>
</div>
<script>
function formatTime(sec) {
  if (sec < 60) return Math.max(1, Math.round(sec)) + ' sec.';
  const m = Math.floor(sec/60), s = Math.round(sec%60);
  return m + ' min.' + (s>0?' '+s+' sec.':'');
}
function analyze() {
  const text = document.getElementById('input').value;
  const words = text.trim() ? text.trim().split(/\s+/).length : 0;
  const chars = text.length;
  const sentences = (text.match(/[.!?]+/g)||[]).length;
  const para = text.trim() ? text.trim().split(/\n\s*\n/).length : 0;
  document.getElementById('cnt-words').textContent = words.toLocaleString('ro');
  document.getElementById('cnt-chars').textContent = chars.toLocaleString('ro');
  document.getElementById('cnt-sentences').textContent = sentences.toLocaleString('ro');
  document.getElementById('cnt-para').textContent = para.toLocaleString('ro');
  document.getElementById('read-avg').textContent = formatTime(words/200*60);
  document.getElementById('read-fast').textContent = formatTime(words/300*60);
}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
