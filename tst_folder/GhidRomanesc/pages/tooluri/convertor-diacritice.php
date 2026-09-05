<?php
$pageTitle       = 'Convertor diacritice românești — corectare ș/ț vs ş/ţ — GhidRomânesc';
$metaDescription = 'Convertești rapid diacriticele românești: corectezi ş/ţ (cu sedilă, greșit) în ș/ț (cu virgulă, corect) și invers. Gratuit, instant, fără upload.';
$canonicalUrl    = SITE_DOMAIN . '/tooluri/convertor-diacritice/';

$toolFaq = [
    ['q' => 'Care sunt diacriticele corecte în română: ș/ț sau ş/ţ?',
     'a' => 'Diacriticele corecte conform standardului Unicode și DOOM3 sunt ș (U+0219, s cu virgulă jos) și ț (U+021B, t cu virgulă jos). Variantele ş (U+015F) și ţ (U+0163), cu sedilă, sunt tehnic incorecte pentru română — deși arată similar pe ecran.'],
    ['q' => 'De ce tastatura Windows produce diacritice greșite?',
     'a' => 'Layout-ul "Romanian (Legacy)" activ pe multe PC-uri cu Windows produce ş și ţ cu sedilă (greșit). Layout-urile "Romanian (Programmers)" sau "Romanian (Standard)" produc ș și ț corecte. Schimbi layout-ul din Setări → Oră și limbă → Limbă → Română → Opțiuni.'],
    ['q' => 'Convertorul modifică și alte caractere din text?',
     'a' => 'Nu. Se modifică strict cele 4 perechi: ş↔ș, Ş↔Ș, ţ↔ț, Ţ↔Ț. Literele ă, â, î, Ă, Â, Î și orice alt caracter rămân neatinse.'],
    ['q' => 'Cum corectez diacriticele în Word sau Google Docs fără acest tool?',
     'a' => 'Folosești Find & Replace (Ctrl+H) și înlocuiești manual perechile ş→ș, Ş→Ș, ţ→ț, Ţ→Ț. Sau copiezi textul aici, convertești, și lipești rezultatul înapoi în document.'],
];

$faqSchema = json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type'          => 'Question',
        'name'           => $f['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ], $toolFaq),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

require __DIR__ . '/../../templates/header.php';
?>
<div class="page-header"><div class="container">
  <nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom:.5rem;color:rgba(255,255,255,.65);font-size:.85rem">
    <a href="/" style="color:inherit">Acasă</a>
    <span class="breadcrumb-sep" aria-hidden="true">›</span>
    <a href="/tooluri/" style="color:inherit">Tooluri</a>
    <span class="breadcrumb-sep" aria-hidden="true">›</span>
    <span aria-current="page">Convertor diacritice</span>
  </nav>
  <h1 class="page-title">🇷🇴 Convertor diacritice românești</h1>
  <p class="page-subtitle">Corectezi ş/ţ (cu sedilă, greșit) ↔ ș/ț (cu virgulă, corect). Instant, fără upload.</p>
</div></div>

<div class="container-sm" style="padding-bottom:4rem">

<div style="background:#fff4e6;border:1.5px solid #fcd34d;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;font-size:.85rem;line-height:1.6">
  <strong>De ce contează?</strong> Diacriticele corecte sunt <strong>ș, ț</strong> (cu virgulă jos — standard Unicode și DOOM3), nu <strong>ş, ţ</strong> (cu sedilă — eroare frecventă pe tastatura Windows cu layout vechi). Diferența este invizibilă la prima vedere, dar importantă pentru corectitudine.
</div>

<div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:1.5rem;margin-bottom:1.5rem">

  <!-- Direcție conversie -->
  <div style="display:flex;gap:.65rem;flex-wrap:wrap;margin-bottom:1.25rem" role="group" aria-label="Direcție conversie">
    <label id="lbl-r1" style="display:flex;align-items:center;gap:.5rem;font-size:.88rem;cursor:pointer;font-weight:600;padding:.5rem .875rem;border:2px solid #457b9d;border-radius:8px;background:#eef4fb;transition:all .15s">
      <input type="radio" name="dir" value="to-correct" id="r1" checked>
      ş ţ → ș ț <span style="font-size:.75rem;color:var(--text-muted);font-weight:400">(corectează)</span>
    </label>
    <label id="lbl-r2" style="display:flex;align-items:center;gap:.5rem;font-size:.88rem;cursor:pointer;font-weight:600;padding:.5rem .875rem;border:2px solid var(--border);border-radius:8px;transition:all .15s">
      <input type="radio" name="dir" value="to-sedilla" id="r2">
      ș ț → ş ţ <span style="font-size:.75rem;color:var(--text-muted);font-weight:400">(inversează)</span>
    </label>
  </div>

  <!-- Grid textareas -->
  <div id="tgrid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
    <div>
      <label style="font-weight:700;color:var(--blue-dark);font-size:.85rem;display:block;margin-bottom:.4rem">Text original</label>
      <textarea id="tinput" class="form-control" rows="10"
        placeholder="Lipește textul cu diacritice greșit scrise...&#10;&#10;ex: Ministerul Sănătăţii şi Familiei"
        style="resize:vertical;font-size:.9rem;line-height:1.65"
        aria-label="Text de convertit"></textarea>
    </div>
    <div>
      <label style="font-weight:700;color:var(--blue-dark);font-size:.85rem;display:block;margin-bottom:.4rem">
        Text corectat
        <span id="cbadge" style="font-size:.75rem;color:#16a34a;font-weight:400;margin-left:.3rem"></span>
      </label>
      <textarea id="toutput" class="form-control" rows="10" readonly
        placeholder="Rezultatul apare automat..."
        style="background:#f8fafc;resize:vertical;font-size:.9rem;line-height:1.65"
        aria-label="Text convertit" aria-live="polite"></textarea>
    </div>
  </div>

  <!-- Butoane acțiune -->
  <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center">
    <button onclick="doCopy()" class="btn btn-primary btn-sm">📋 Copiază rezultatul</button>
    <button onclick="doSwap()" class="btn btn-secondary btn-sm">⇅ Inversează textele</button>
    <button onclick="doClear()" class="btn btn-secondary btn-sm">✕ Șterge tot</button>
    <button onclick="doTest()" class="btn btn-secondary btn-sm" title="Încarcă text demo cu diacritice greșite (ş/ţ cu sedilă)">🧪 Test demo</button>
  </div>
  <div id="fmsg" style="font-size:.82rem;color:#16a34a;margin-top:.5rem;min-height:1.2em" aria-live="polite"></div>
</div>

<!-- Referință Unicode -->
<div style="background:#f8fafc;border-radius:12px;padding:1.1rem 1.4rem;font-size:.83rem;color:var(--text-muted)">
  <strong style="color:var(--blue-dark);display:block;margin-bottom:.5rem">Perechi de caractere convertite:</strong>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.3rem .75rem;font-family:monospace">
    <span>ş (U+015F) ↔ ș (U+0219)</span>
    <span>Ş (U+015E) ↔ Ș (U+0218)</span>
    <span>ţ (U+0163) ↔ ț (U+021B)</span>
    <span>Ţ (U+0162) ↔ Ț (U+021A)</span>
  </div>
</div>
</div>

<!-- FAQ -->
<div class="container-sm" style="padding-bottom:1.5rem">
  <h2 style="font-size:1.1rem;font-weight:800;color:var(--blue-dark);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid var(--border)">
    Întrebări frecvente — diacritice românești
  </h2>
  <div style="display:flex;flex-direction:column;gap:.5rem">
    <?php foreach ($toolFaq as $item): ?>
    <details style="border:1px solid var(--border);border-radius:10px;overflow:hidden">
      <summary style="padding:.875rem 1rem;cursor:pointer;font-weight:600;font-size:.9rem;color:var(--blue-dark);list-style:none;display:flex;justify-content:space-between;align-items:center;user-select:none">
        <?= e($item['q']) ?><span style="font-size:.75rem;color:var(--text-muted);margin-left:.5rem;flex-shrink:0">▸</span>
      </summary>
      <div style="padding:.875rem 1rem;font-size:.875rem;color:var(--text-muted);line-height:1.7;border-top:1px solid var(--border);background:#fafafa">
        <?= e($item['a']) ?>
      </div>
    </details>
    <?php endforeach; ?>
  </div>
</div>

<div class="container-sm" style="padding-bottom:3rem">
  <h2 style="font-size:1rem;font-weight:700;color:var(--blue-dark);margin-bottom:.875rem">Tooluri similare</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem">
    <?php foreach ([
      ['slug'=>'densitate-cuvinte',  'icon'=>'📝','title'=>'Densitate cuvinte cheie'],
      ['slug'=>'calculator-cuvinte', 'icon'=>'⏱️', 'title'=>'Calculator cuvinte & timp'],
      ['slug'=>'generator-slug',     'icon'=>'🔗','title'=>'Generator slug URL'],
      ['slug'=>'analizor-seo',       'icon'=>'📊','title'=>'Analizor titlu & meta'],
    ] as $r): ?>
    <a href="/tooluri/<?= $r['slug'] ?>/" style="display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;border:1.5px solid var(--border);border-radius:10px;text-decoration:none;background:#fff;font-size:.875rem;font-weight:600;color:var(--blue-dark);transition:border-color .15s" onmouseover="this.style.borderColor='#457b9d'" onmouseout="this.style.borderColor='var(--border)'">
      <span><?= $r['icon'] ?></span><?= e($r['title']) ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<script>
// Folosim charCodeAt() — 100% independent de encoding-ul fișierului PHP
// Sedilă (greșit): ş=0x015F Ş=0x015E ţ=0x0163 Ţ=0x0162
// Virgulă (corect): ș=0x0219 Ș=0x0218 ț=0x021B Ț=0x021A

var MAP_TO_CORRECT = {};
MAP_TO_CORRECT[0x015F] = 0x0219; // ş → ș
MAP_TO_CORRECT[0x015E] = 0x0218; // Ş → Ș
MAP_TO_CORRECT[0x0163] = 0x021B; // ţ → ț
MAP_TO_CORRECT[0x0162] = 0x021A; // Ţ → Ț

var MAP_TO_SEDILLA = {};
MAP_TO_SEDILLA[0x0219] = 0x015F; // ș → ş
MAP_TO_SEDILLA[0x0218] = 0x015E; // Ș → Ş
MAP_TO_SEDILLA[0x021B] = 0x0163; // ț → ţ
MAP_TO_SEDILLA[0x021A] = 0x0162; // Ț → Ţ

function doConvert() {
  var text = document.getElementById('tinput').value;
  if (!text) {
    document.getElementById('toutput').value = '';
    document.getElementById('cbadge').textContent = '';
    return;
  }

  var toCorrect = document.getElementById('r1').checked;
  var map = toCorrect ? MAP_TO_CORRECT : MAP_TO_SEDILLA;
  var result = '';
  var count = 0;

  for (var i = 0; i < text.length; i++) {
    var code = text.charCodeAt(i);
    if (map[code] !== undefined) {
      result += String.fromCharCode(map[code]);
      count++;
    } else {
      result += text[i];
    }
  }

  document.getElementById('toutput').value = result;
  if (count > 0) {
    document.getElementById('cbadge').textContent = '— ' + count + ' înlocuiri';
    document.getElementById('cbadge').style.color = '#16a34a';
  } else {
    // Arată câte din fiecare tip de diacritice există în text (diagnosticare)
    var hasSedilla = 0, hasVirgula = 0;
    for (var j = 0; j < text.length; j++) {
      var c = text.charCodeAt(j);
      if (c===0x015F||c===0x015E||c===0x0163||c===0x0162) hasSedilla++;
      if (c===0x0219||c===0x0218||c===0x021B||c===0x021A) hasVirgula++;
    }
    if (hasSedilla === 0 && hasVirgula === 0) {
      document.getElementById('cbadge').textContent = '— textul nu conține ș/ț/ş/ţ';
    } else {
      document.getElementById('cbadge').textContent = '— ' + (hasSedilla ? hasSedilla+' sedilă' : '') + (hasVirgula ? ' '+hasVirgula+' virgulă' : '') + ' — deja corect pentru direcția selectată';
    }
    document.getElementById('cbadge').style.color = 'var(--text-muted)';
  }
}

// Text demo cu diacritice greșite (ş/ţ cu sedilă, U+015F / U+0163)
function doTest() {
  // Construim șirul din coduri Unicode — nu depindem de encoding fișier
  var DEMO = [
    'M','i','n','i','s','t','e','r','u','l',' ',
    'S','ă','n','ă','t','ă',
    String.fromCharCode(0x0163),'i','i',' ', // ţ cu SEDILĂ (greșit)
    String.fromCharCode(0x015F),'i',' ',     // ş cu SEDILĂ (greșit)
    'F','a','m','i','l','i','e','i','\n\n',
    'S','o','c','i','e','t','a','t','e','a',
    ' ','"','R','o','m','â','n','i','a',' ','L','i','b','e','r','ă','"',' ',
    'S','.','R','.','L','.','\n',
    'S','t','r','.',' ',
    String.fromCharCode(0x015E),'t','e','f','a','n',   // Ş cu SEDILĂ (greșit)
    ' ','c','e','l',' ','M','a','r','e',' ','n','r','.',' ','1',
  ].join('');
  document.getElementById('tinput').value = DEMO;
  document.getElementById('r1').checked = true;
  updateRadioStyle();
}

function updateRadioStyle() {
  var r1 = document.getElementById('r1').checked;
  document.getElementById('lbl-r1').style.borderColor   = r1 ? '#457b9d' : 'var(--border)';
  document.getElementById('lbl-r1').style.background    = r1 ? '#eef4fb' : '';
  document.getElementById('lbl-r2').style.borderColor   = r1 ? 'var(--border)' : '#457b9d';
  document.getElementById('lbl-r2').style.background    = r1 ? '' : '#eef4fb';
  doConvert();
}

function doCopy() {
  var txt = document.getElementById('toutput').value;
  if (!txt) { flash('Nimic de copiat.'); return; }
  if (navigator.clipboard) {
    navigator.clipboard.writeText(txt).then(function(){ flash('✓ Text copiat în clipboard!'); });
  } else {
    document.getElementById('toutput').select();
    document.execCommand('copy');
    flash('✓ Text copiat!');
  }
}

function doSwap() {
  var out = document.getElementById('toutput').value;
  if (!out) { flash('Nu există text de inversat.'); return; }
  document.getElementById('tinput').value = out;
  document.getElementById('toutput').value = '';
  document.getElementById('cbadge').textContent = '';
  doConvert();
}

function doClear() {
  document.getElementById('tinput').value = '';
  document.getElementById('toutput').value = '';
  document.getElementById('cbadge').textContent = '';
}

function flash(msg) {
  var el = document.getElementById('fmsg');
  el.textContent = msg;
  setTimeout(function(){ el.textContent = ''; }, 2500);
}

// Mobile: stivuiești textareas
if (window.innerWidth < 640) {
  document.getElementById('tgrid').style.gridTemplateColumns = '1fr';
}

// ── Auto-test la încărcare ── verifică că JS funcționează corect ──────────────
(function selfTest() {
  // Test: ş (0x015F) trebuie convertit la ș (0x0219)
  var testIn = String.fromCharCode(0x015F) + String.fromCharCode(0x0163); // "şţ"
  var expected = String.fromCharCode(0x0219) + String.fromCharCode(0x021B); // "șț"
  var result = '';
  for (var i = 0; i < testIn.length; i++) {
    var code = testIn.charCodeAt(i);
    result += MAP_TO_CORRECT[code] !== undefined ? String.fromCharCode(MAP_TO_CORRECT[code]) : testIn[i];
  }
  if (result !== expected) {
    // Test eșuat — afișează diagnostic
    var msg = document.getElementById('fmsg');
    msg.style.color = '#dc2626';
    msg.textContent = '⚠️ Diagnostic: conversia NU funcționează în acest browser (charCode test failed). Trimite acest mesaj suportului.';
    console.error('[Diacritice] self-test FAIL:', {
      testIn: testIn, expected: expected, got: result,
      codes: [testIn.charCodeAt(0), testIn.charCodeAt(1)]
    });
  } else {
    console.log('[Diacritice] self-test OK: charCodeAt conversia funcționează corect.');
  }
})();

// Event listeners
document.getElementById('tinput').addEventListener('input', doConvert);
document.getElementById('r1').addEventListener('change', updateRadioStyle);
document.getElementById('r2').addEventListener('change', updateRadioStyle);
</script>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
