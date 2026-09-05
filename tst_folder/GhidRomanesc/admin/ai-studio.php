<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Article.php';
require_once __DIR__ . '/../src/AI.php';
Auth::requireAdmin();

$pageTitle  = 'AI Studio';
$categories = Database::fetchAll('SELECT * FROM categories ORDER BY name');
$prefillSubject = sanitize($_GET['subiect'] ?? '');

// Prompturi editabile — cu fallback la cele implicite
$promptArticol = Database::fetchColumn('SELECT value FROM settings WHERE key_name="prompt_articol"')
                 ?: AI::getDefaultArticlePrompt();
$promptIdei    = Database::fetchColumn('SELECT value FROM settings WHERE key_name="prompt_idei"')
                 ?: AI::getDefaultIdeasPrompt();

require __DIR__ . '/../templates/admin-layout.php';
?>

<p style="color:var(--text-muted);margin-bottom:1.5rem">Generează articole, checklist-uri, idei și conținut SEO cu ajutorul AI-ului. Articolele generate trebuie verificate înainte de publicare.</p>

<!-- Tabs -->
<div class="ai-tabs">
  <button class="ai-tab active" data-tab="articol">✏️ Creează articol</button>
  <button class="ai-tab" data-tab="idei">💡 Idei din trenduri</button>
  <button class="ai-tab" data-tab="rescriere">🔄 Rescrie simplu</button>
  <button class="ai-tab" data-tab="prompturi">⚙️ Prompturi editabile</button>
</div>

<!-- ── Tab: Creează articol ── -->
<div id="tab-articol" class="ai-tab-content active">
  <div style="display:grid;grid-template-columns:380px 1fr;gap:1.5rem;align-items:start">

    <div>
      <div class="admin-card">
        <div class="admin-card-header"><span class="admin-card-title">Parametri articol</span></div>
        <div class="admin-card-body">
          <form id="ai-article-form">
            <div class="form-group">
              <label>Subiect articol *</label>
              <input type="text" name="subject" class="form-control" value="<?= e($prefillSubject) ?>" placeholder="ex: Cum reînnoiești buletinul de identitate" required>
            </div>

            <div class="form-group">
              <label>Categorie</label>
              <select name="category" class="form-control">
                <option value="">— Selectează —</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['name']) ?>"><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label>Tip articol</label>
              <select name="type" class="form-control">
                <option value="ghid complet">Ghid complet</option>
                <option value="ghid rapid">Ghid rapid</option>
                <option value="checklist">Checklist</option>
                <option value="FAQ">FAQ</option>
                <option value="model de email">Model de email</option>
                <option value="tutorial digital">Tutorial digital</option>
                <option value="actualizare importantă">Actualizare importantă</option>
              </select>
            </div>

            <div class="form-group">
              <label>Public țintă</label>
              <select name="audience" class="form-control">
                <option value="toți românii">Toți românii</option>
                <option value="românii din România">Români din România</option>
                <option value="românii din diaspora">Diaspora</option>
                <option value="începători în digital">Începători digital</option>
                <option value="angajați">Angajați</option>
                <option value="studenți">Studenți</option>
                <option value="PFA și SRL">PFA / SRL</option>
                <option value="familie">Familie</option>
              </select>
            </div>

            <div class="form-group">
              <label>Nivel de risc</label>
              <select name="risk" class="form-control">
                <option value="verde">Verde (publicare automată OK)</option>
                <option value="galben" selected>Galben (necesită verificare)</option>
                <option value="rosu">Roșu (blocat, verificare specială)</option>
              </select>
            </div>

            <div class="form-group">
              <label>Cuvânt-cheie principal</label>
              <input type="text" name="keyword" class="form-control" placeholder="ex: reînnoire buletin identitate">
            </div>

            <div class="form-group">
              <label>Surse oficiale de verificat</label>
              <input type="text" name="sources" class="form-control" placeholder="ex: mae.ro, politiaromana.ro">
            </div>

            <div class="form-group">
              <label>Ton</label>
              <select name="tone" class="form-control">
                <option value="simplu și prietenos">Simplu și prietenos</option>
                <option value="explicativ">Explicativ</option>
                <option value="serios">Serios</option>
              </select>
            </div>

            <div class="form-group">
              <label>Lungime</label>
              <select name="length" class="form-control">
                <option value="complet">Complet (toate secțiunile)</option>
                <option value="mediu">Mediu</option>
                <option value="scurt">Scurt (ghid rapid)</option>
              </select>
            </div>

            <button type="button" id="btn-generate-article" class="btn btn-primary btn-block" style="margin-top:.5rem">
              🤖 Generează cu AI
            </button>
          </form>
        </div>
      </div>
    </div>

    <div>
      <div class="admin-card">
        <div class="admin-card-header">
          <span class="admin-card-title">Articol generat</span>
          <div style="display:flex;gap:.5rem">
            <button type="button" id="btn-save-draft" class="btn btn-sm btn-secondary" disabled onclick="saveDraftFromAI()">💾 Salvează draft</button>
          </div>
        </div>
        <div class="admin-card-body">
          <div id="ai-output-article" class="ai-output">
            <p style="color:var(--text-muted);text-align:center;padding:2rem">Completează parametrii și apasă „Generează cu AI".<br><small>Poate dura 30-90 secunde.</small></p>
          </div>
        </div>
      </div>

      <!-- Câmpuri SEO populate automat -->
      <div class="admin-card" style="margin-top:1rem">
        <div class="admin-card-header"><span class="admin-card-title">SEO extras automat</span></div>
        <div class="admin-card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
          <div><label style="font-size:.8rem">Meta title</label><input type="text" id="meta_title" name="meta_title" class="form-control"></div>
          <div><label style="font-size:.8rem">Slug URL</label><input type="text" id="slug" name="slug" class="form-control"></div>
          <div style="grid-column:1/-1"><label style="font-size:.8rem">Meta description</label><textarea id="meta_description" name="meta_description" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="admin-card-body" style="padding-top:0">
          <a href="/admin/articole/?actiune=nou" class="btn btn-secondary btn-sm">→ Creează articol manual cu aceste date</a>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ── Tab: Idei din trenduri ── -->
<div id="tab-idei" class="ai-tab-content">
  <div style="display:grid;grid-template-columns:380px 1fr;gap:1.5rem;align-items:start">

    <div class="admin-card">
      <div class="admin-card-header"><span class="admin-card-title">Introdu trenduri și subiecte</span></div>
      <div class="admin-card-body">
        <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem">Introdu orice fel de date: trenduri Google, căutări interne, întrebări de pe Facebook/Reddit, subiecte de actualitate. AI-ul va genera 20 de idei de articole.</p>
        <textarea id="trends-input" class="form-control" rows="12" placeholder="ex:
- pașaport expirat în Italia
- cum îmi recuperez buletin pierdut
- ANAF online 2024 schimbări
- ChatGPT în română
- cum îmi fac PFA
- programare consulat Germania întârziere
- salariu minim 2024
..."></textarea>
        <button type="button" id="btn-generate-ideas" class="btn btn-primary btn-block" style="margin-top:.75rem">
          💡 Generează 20 idei
        </button>

        <!-- Căutări interne — sugestii rapide -->
        <?php
        $searches = Database::fetchAll('SELECT query, count FROM search_queries ORDER BY count DESC LIMIT 15');
        if (!empty($searches)):
        ?>
        <div style="margin-top:1.25rem">
          <p style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted);margin-bottom:.5rem">Căutări interne recente</p>
          <div style="display:flex;flex-wrap:wrap;gap:.35rem">
            <?php foreach ($searches as $s): ?>
            <span onclick="document.getElementById('trends-input').value += '\n- '+this.textContent.trim()"
                  style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:4px;padding:.25rem .5rem;font-size:.78rem;cursor:pointer"
                  title="Click pentru a adăuga în lista de trenduri">
              <?= e($s['query']) ?> (<?= $s['count'] ?>)
            </span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header"><span class="admin-card-title">Idei generate de AI</span></div>
      <div class="admin-card-body">
        <div id="ai-output-ideas" class="ai-output" style="min-height:400px">
          <p style="color:var(--text-muted);text-align:center;padding:2rem">Introdu trenduri și apasă „Generează idei".</p>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ── Tab: Rescrie simplu ── -->
<div id="tab-rescriere" class="ai-tab-content">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
    <div class="admin-card">
      <div class="admin-card-header"><span class="admin-card-title">Text original</span></div>
      <div class="admin-card-body">
        <textarea id="rewrite-input" class="form-control" rows="15" placeholder="Lipește textul complex, juridic sau tehnic pe care vrei să-l rescrii pe înțelesul oricui..."></textarea>
        <button type="button" id="btn-rewrite" class="btn btn-primary" style="margin-top:.75rem" onclick="doRewrite()">🔄 Rescrie simplu</button>
      </div>
    </div>
    <div class="admin-card">
      <div class="admin-card-header"><span class="admin-card-title">Versiune simplificată</span></div>
      <div class="admin-card-body">
        <div id="rewrite-output" class="ai-output" style="min-height:300px">
          <p style="color:var(--text-muted);text-align:center;padding:2rem">Rezultatul va apărea aici.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Tab: Prompturi editabile ── -->
<div id="tab-prompturi" class="ai-tab-content">
  <p style="color:var(--text-muted);margin-bottom:1.5rem">Editează prompturile folosite de AI pentru generarea articolelor și ideilor. Lasă gol pentru a folosi prompturile implicite.</p>

  <form method="POST" action="/api/save-settings.php">
    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">

    <div class="admin-card" style="margin-bottom:1.25rem">
      <div class="admin-card-header">
        <span class="admin-card-title">Prompt articol</span>
        <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('prompt-articol').value=''">Resetează la implicit</button>
      </div>
      <div class="admin-card-body">
        <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem">Variabile disponibile: [SUBIECT] [CATEGORIE] [TIP_ARTICOL] [PUBLIC] [RISC] [KEYWORD] [SURSE] [TON] [LUNGIME]</p>
        <textarea id="prompt-articol" name="prompt_articol" class="form-control prompt-editor" rows="18"><?= htmlspecialchars($promptArticol) ?></textarea>
      </div>
    </div>

    <div class="admin-card" style="margin-bottom:1.25rem">
      <div class="admin-card-header">
        <span class="admin-card-title">Prompt idei din trenduri</span>
        <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('prompt-idei').value=''">Resetează la implicit</button>
      </div>
      <div class="admin-card-body">
        <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem">Variabile disponibile: [TRENDURI]</p>
        <textarea id="prompt-idei" name="prompt_idei" class="form-control prompt-editor" rows="14"><?= htmlspecialchars($promptIdei) ?></textarea>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">💾 Salvează prompturile</button>
  </form>
</div>

<script>
async function doRewrite() {
  const input  = document.getElementById('rewrite-input').value.trim();
  const output = document.getElementById('rewrite-output');
  const btn    = document.getElementById('btn-rewrite');
  if (!input) return;
  btn.disabled = true; btn.textContent = 'Se rescrie...';
  output.innerHTML = '<div class="spinner"></div>';
  try {
    const res  = await fetch('/api/ai-generate.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-Token': window.csrfToken||''},
      body: JSON.stringify({action:'rewrite', text: input}),
    });
    const json = await res.json();
    if (json.success) output.innerHTML = '<pre style="white-space:pre-wrap;font-size:.875rem;line-height:1.7">'+escHtml(json.content)+'</pre>';
    else output.innerHTML = '<p style="color:#dc2626">Eroare: '+escHtml(json.error||'')+'</p>';
  } catch(e) { output.innerHTML = '<p style="color:#dc2626">Eroare: '+e.message+'</p>'; }
  btn.disabled=false; btn.textContent='🔄 Rescrie simplu';
}

function saveDraftFromAI() {
  const subject = document.querySelector('#ai-article-form [name="subject"]')?.value || '';
  const cat     = document.querySelector('#ai-article-form [name="category"]')?.value || '';
  const content = window.aiGeneratedContent || '';
  const meta_title = document.getElementById('meta_title')?.value || '';
  const slug       = document.getElementById('slug')?.value || '';
  const meta_desc  = document.getElementById('meta_description')?.value || '';

  const params = new URLSearchParams({
    actiune: 'nou',
    prefill_title: subject,
    prefill_content: content.substring(0, 200),
  });
  window.location.href = '/admin/articole/?' + params;
}

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
