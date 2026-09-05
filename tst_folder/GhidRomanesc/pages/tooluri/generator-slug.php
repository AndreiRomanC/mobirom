<?php
$pageTitle = 'Generator slug URL din text românesc — GhidRomânesc';
$metaDescription = 'Transformă orice text românesc în URL curat pentru SEO. Elimină diacriticele, spațiile și caracterele speciale automat.';
$canonicalUrl = SITE_DOMAIN . '/tooluri/generator-slug/';
require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:.5rem"><a href="/tooluri/" style="color:inherit">← Toate toolurile</a></div>
  <h1 class="page-title">🔗 Generator slug URL</h1>
  <p class="page-subtitle">Transformă titluri românești în URL-uri curate, fără diacritice, gata pentru SEO.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">
<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:2rem;margin-bottom:1.5rem">
  <div class="form-group" style="margin-bottom:1.25rem">
    <label style="font-weight:700;color:var(--blue-dark)">Text de transformat</label>
    <textarea id="input" class="form-control" rows="4" placeholder="ex: Cum să îți faci pașaportul în 2026 — ghid complet" oninput="generate()" style="margin-top:.5rem;font-size:.95rem"></textarea>
  </div>

  <div style="background:#f0f4ff;border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.25rem">
    <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.04em">Slug generat</div>
    <div id="result" style="font-family:'Courier New',monospace;font-size:1rem;color:var(--blue-dark);font-weight:700;word-break:break-all;min-height:1.5rem">—</div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
    <button onclick="copySlug()" class="btn btn-primary">📋 Copiază slug</button>
    <button onclick="copyUrl()" class="btn btn-secondary">🔗 Copiază URL complet</button>
  </div>

  <div id="copy-msg" style="text-align:center;font-size:.85rem;color:#16a34a;margin-top:.6rem;height:1.2em"></div>

  <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border)">
    <div style="font-weight:700;font-size:.85rem;color:var(--blue-dark);margin-bottom:.75rem">Opțiuni</div>
    <div style="display:flex;flex-wrap:wrap;gap:.75rem">
      <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer">
        <input type="checkbox" id="opt-lower" checked onchange="generate()"> Litere mici
      </label>
      <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer">
        <input type="checkbox" id="opt-hyphen" checked onchange="generate()"> Liniuță între cuvinte
      </label>
      <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer">
        <input type="checkbox" id="opt-trim" checked onchange="generate()"> Elimină stopwords (de, la, în, cu, și)
      </label>
    </div>
  </div>
</div>

<div style="background:#f8fafc;border-radius:12px;padding:1.25rem 1.5rem;font-size:.85rem;color:var(--text-muted)">
  <strong style="color:var(--blue-dark)">Exemplu:</strong> „Cum îți faci pașaportul în 2026" → <code>cum-iti-faci-pasaportul-2026</code>
</div>
</div>

<script>
const stopwords = ['de','la','in','cu','si','sau','pe','dar','ca','sa','ne','se','nu','un','o','al','ai','ale','unui','unei','unor','cel','cea','cei','cele','este','sunt','fi','fost','are','au','a','i','ii','lui','ei','lor','din','prin','spre','intre','despre','dupa','inainte','inauntru'];

function slugify(text, lower, hyphen, trim) {
  const map = {'ă':'a','â':'a','î':'i','ș':'s','ț':'t','ş':'s','ţ':'t','Ă':'A','Â':'A','Î':'I','Ș':'S','Ț':'T','é':'e','è':'e','ê':'e','à':'a','á':'a','ü':'u','ö':'o','—':'','–':''};
  text = text.replace(/[ăâîșțşţĂÂÎȘȚéèêàáüö—–]/g, c => map[c] || c);
  text = text.replace(/[^a-zA-Z0-9\s]/g, ' ');
  if (lower) text = text.toLowerCase();
  let words = text.trim().split(/\s+/).filter(w => w.length > 0);
  if (trim) words = words.filter(w => !stopwords.includes(w.toLowerCase()));
  return hyphen ? words.join('-') : words.join('');
}

function generate() {
  const text = document.getElementById('input').value;
  const lower = document.getElementById('opt-lower').checked;
  const hyphen = document.getElementById('opt-hyphen').checked;
  const trim = document.getElementById('opt-trim').checked;
  const slug = slugify(text, lower, hyphen, trim) || '—';
  document.getElementById('result').textContent = slug;
}

function copySlug() {
  const s = document.getElementById('result').textContent;
  if (s === '—') return;
  navigator.clipboard.writeText(s).then(() => flash('✓ Slug copiat!'));
}

function copyUrl() {
  const s = document.getElementById('result').textContent;
  if (s === '—') return;
  navigator.clipboard.writeText('https://exemplu.ro/' + s + '/').then(() => flash('✓ URL copiat!'));
}

function flash(msg) {
  const el = document.getElementById('copy-msg');
  el.textContent = msg;
  setTimeout(() => el.textContent = '', 2000);
}
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
