<?php
$pageTitle = 'Analizor densitate cuvinte cheie — GhidRomânesc';
$metaDescription = 'Analizezi un text și afli ce cuvinte apar cel mai des. Util pentru optimizare SEO și verificare densitate cuvinte cheie.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/densitate-cuvinte/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">📝 Densitate cuvinte cheie</h1>
  <p class="page-subtitle">Analizezi orice text și afli frecvența fiecărui cuvânt — util pentru SEO și optimizare conținut.</p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">
  <div class="form-group" style="margin-bottom:1rem">
    <label style="font-weight:700;color:var(--blue-dark)">Text de analizat</label>
    <textarea id="input" class="form-control" rows="8" placeholder="Lipește conținutul articolului sau al paginii..." style="margin-top:.4rem"></textarea>
  </div>
  <div style="display:flex;gap:.75rem;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap">
    <label style="font-size:.85rem;display:flex;align-items:center;gap:.3rem"><input type="checkbox" id="rm-stop" checked> Elimină stopwords</label>
    <label style="font-size:.85rem;display:flex;align-items:center;gap:.3rem">Min. caractere: <input type="number" id="min-len" value="3" min="1" max="10" style="width:50px;margin-left:.25rem;border:1px solid var(--border);border-radius:4px;padding:.2rem .4rem"></label>
    <button onclick="analyze()" class="btn btn-primary btn-sm" style="margin-left:auto">Analizează</button>
  </div>
  <div id="results" style="display:none">
    <div style="display:flex;gap:.75rem;margin-bottom:1rem;font-size:.82rem;color:var(--text-muted)">
      <span>Total cuvinte: <strong id="total-words" style="color:var(--blue-dark)">0</strong></span>
      <span>Cuvinte unice: <strong id="unique-words" style="color:var(--blue-dark)">0</strong></span>
    </div>
    <div id="word-list" style="display:flex;flex-direction:column;gap:.3rem"></div>
  </div>
</div>
</div>
<script>
const stop = new Set(['de','la','in','cu','si','sau','pe','dar','ca','sa','ne','se','nu','un','o','al','ai','ale','a','i','ii','lui','ei','lor','din','prin','spre','intre','despre','dupa','este','sunt','fi','fost','are','au','cel','cea','cei','cele','care','ce','cum','cand','unde','cine','care','acest','aceasta','acesti','aceste','acel','aceea','acei','acele','mult','mai','tot','toata','toti','toate','daca','atunci','astfel','insa','deci','apoi','iar','chiar','poate','face','putea','trebui','vrea']);

function analyze(){
  const text=document.getElementById('input').value.toLowerCase();
  const rmStop=document.getElementById('rm-stop').checked;
  const minLen=+document.getElementById('min-len').value||3;
  const words=text.replace(/[^\wăâîșțşţ\s]/gi,' ').split(/\s+/).filter(w=>w.length>=minLen&&(!rmStop||!stop.has(w)));
  const freq={};
  words.forEach(w=>freq[w]=(freq[w]||0)+1);
  const sorted=Object.entries(freq).sort((a,b)=>b[1]-a[1]).slice(0,30);
  const total=words.length, unique=Object.keys(freq).length;
  document.getElementById('total-words').textContent=total;
  document.getElementById('unique-words').textContent=unique;
  const maxCnt=sorted[0]?.[1]||1;
  document.getElementById('word-list').innerHTML=sorted.map(([w,cnt])=>{
    const pct=(cnt/total*100).toFixed(1);
    const barW=Math.round(cnt/maxCnt*100);
    const color=pct>3?'#dc2626':pct>2?'#b45309':'#16a34a';
    return `<div style="display:grid;grid-template-columns:140px 1fr 60px 50px;gap:.5rem;align-items:center">
      <span style="font-size:.85rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${w}</span>
      <div style="background:#e2e8f0;border-radius:4px;height:8px"><div style="background:${color};width:${barW}%;height:8px;border-radius:4px"></div></div>
      <span style="font-size:.78rem;text-align:center;color:var(--text-muted)">${cnt}x</span>
      <span style="font-size:.78rem;text-align:right;color:${color};font-weight:700">${pct}%</span>
    </div>`;
  }).join('');
  document.getElementById('results').style.display='';
}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
