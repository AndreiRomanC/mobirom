<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::requireAdmin();

$pageTitle = 'Trenduri și idei';

// Marchează status
if (($_GET['actiune'] ?? '') === 'status' && isset($_GET['id'], $_GET['status'])) {
    Database::update('trend_ideas', ['status' => sanitize($_GET['status'])], 'id=?', [(int)$_GET['id']]);
    redirect('/admin/trenduri/?ok=1');
}
if (($_GET['actiune'] ?? '') === 'sterge' && isset($_GET['id'])) {
    Database::delete('trend_ideas', 'id=?', [(int)$_GET['id']]);
    redirect('/admin/trenduri/');
}

$filterStatus = sanitize($_GET['status'] ?? 'idee');
$ideas = Database::fetchAll(
    "SELECT * FROM trend_ideas WHERE status=? ORDER BY generated_at DESC LIMIT 100",
    [$filterStatus]
);

$topSearches = Database::fetchAll(
    "SELECT sq.query, sq.count, sq.last_searched,
            (SELECT COUNT(*) FROM articles a
             LEFT JOIN categories c ON a.category_id=c.id
             WHERE a.focus_keyword=sq.query OR a.tags LIKE '%'||sq.query||'%') AS has_article
     FROM search_queries sq
     ORDER BY sq.count DESC
     LIMIT 25"
);

$suggestions = Database::fetchAll(
    "SELECT * FROM topic_suggestions WHERE status='new' ORDER BY created_at DESC LIMIT 20"
);

require __DIR__ . '/../templates/admin-layout.php';
?>

<?php if (isset($_GET['ok'])): ?><div class="admin-notice success">✓ Actualizat.</div><?php endif; ?>

<!-- ── Instrucțiuni: Unde cauți trenduri ── -->
<div class="admin-card" style="margin-bottom:1.25rem">
  <div class="admin-card-header">
    <span class="admin-card-title">📖 Cum găsești idei de articole — ghid rapid</span>
    <button onclick="this.closest('.admin-card').querySelector('.howto-body').style.display = this.closest('.admin-card').querySelector('.howto-body').style.display==='none'?'block':'none'" class="btn btn-sm btn-secondary">Arată/Ascunde</button>
  </div>
  <div class="howto-body" style="display:none">
  <div class="admin-card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

    <div>
      <h3 style="font-size:.9rem;font-weight:700;color:#1d3557;margin-bottom:.75rem">🔍 Surse principale (verifică săptămânal)</h3>
      <div style="display:flex;flex-direction:column;gap:.5rem">
        <?php
        $sources = [
          ['Google Trends România', 'Caută termeni de actualitate pentru românii din RO + diaspora. Folosește comparații: "pasaport vs buletin".', 'https://trends.google.com/trends/explore?geo=RO', '#4285f4'],
          ['Google Trends — Căutări zilnice', 'Pagina "Trending Now" pentru România — ce caută azi românii.', 'https://trends.google.com/trends/trendingsearches/daily?geo=RO', '#34a853'],
          ['Answer The Public', 'Pune un cuvânt cheie (ex: "pasaport") și generează zeci de întrebări reale pe care le pun românii.', 'https://answerthepublic.com', '#ea4335'],
          ['AlsoAsked.com', 'Arată structura de întrebări "People Also Ask" pentru orice subiect. Ideal pentru FAQ-uri.', 'https://alsoasked.com', '#1d3557'],
          ['gov.ro — Noutăți', 'Schimbări de proceduri, legi noi, termene — sursă directă pentru actualizări.', 'https://www.gov.ro/ro/media/comunicate-de-presa', '#002147'],
          ['mae.ro — Comunicate', 'Schimbări consulare, proceduri diaspora, avertizări de călătorie.', 'https://www.mae.ro/node/1455', '#00356b'],
          ['anaf.ro — Noutăți', 'Modificări fiscale, termene ANAF, formulare noi.', 'https://www.anaf.ro/anaf/internet/RO/noutati', '#c0392b'],
          ['YouTube Trends RO', 'Ce subiecte devin virale în România — bun pentru tutorial-uri digitale.', 'https://www.youtube.com/feed/trending?gl=RO&hl=ro', '#ff0000'],
        ];
        foreach ($sources as [$name, $desc, $url, $color]):
        ?>
        <a href="<?= $url ?>" target="_blank" rel="noopener"
           style="display:flex;gap:.75rem;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:.75rem;text-decoration:none;align-items:flex-start;transition:box-shadow .15s"
           onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
          <div style="width:10px;height:10px;border-radius:50%;background:<?= $color ?>;flex-shrink:0;margin-top:4px"></div>
          <div>
            <div style="font-weight:700;font-size:.875rem;color:#1d3557"><?= e($name) ?> ↗</div>
            <div style="font-size:.78rem;color:#6b7280;margin-top:.15rem"><?= e($desc) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <h3 style="font-size:.9rem;font-weight:700;color:#1d3557;margin-bottom:.75rem">💡 Flux de lucru recomandat (15 min/săptămână)</h3>
      <ol style="font-size:.875rem;color:#374151;padding-left:1.25rem;line-height:2">
        <li>Verifică <strong>căutările interne</strong> din tabelul de mai jos — ce caută vizitatorii tăi fără să găsească</li>
        <li>Deschide <strong>Google Trends România</strong> → copiază 5-10 termeni în creștere</li>
        <li>Verifică <strong>gov.ro / mae.ro / anaf.ro</strong> — sunt schimbări de proceduri?</li>
        <li>Citește <strong>sugestiile utilizatorilor</strong> din tabelul de mai jos</li>
        <li>Pune totul în câmpul „Trenduri" din <a href="/admin/ai-studio/" style="color:#2b6cb0;font-weight:600">AI Studio → Idei din trenduri</a></li>
        <li>AI generează 20 de idei prioritizate — alege ce vrei să scrii</li>
      </ol>

      <h3 style="font-size:.9rem;font-weight:700;color:#1d3557;margin:.75rem 0 .5rem">📋 Ce să copiezi în AI Studio</h3>
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:.75rem;font-size:.8rem;color:#475569;font-family:monospace;line-height:1.8">
        - [termen din Google Trends]<br>
        - [întrebare din sugestii utilizatori]<br>
        - [schimbare de pe gov.ro/mae.ro]<br>
        - [căutare internă fără articol]<br>
        - [subiect popular pe YouTube RO]
      </div>

      <div style="margin-top:1rem;padding:.75rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px">
        <p style="font-size:.825rem;color:#1e40af;font-weight:600;margin-bottom:.35rem">🔑 Surse opționale (pentru întrebări reale)</p>
        <p style="font-size:.78rem;color:#1e40af">Grupuri Facebook cu români în diaspora, comentarii YouTube, Reddit r/Romania — folosește-le <em>doar pentru a identifica întrebări reale</em>, nu ca sursă de adevăr. Nu cita niciodată social media ca sursă în articol.</p>
      </div>
    </div>

  </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem">

  <!-- Căutări interne fără articol -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">🔍 Căutări interne — fără articol</span>
      <span style="font-size:.78rem;color:#6b7280">Ce caută vizitatorii tăi și nu găsesc</span>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Termen căutat</th><th>Căutări</th><th>Articol?</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($topSearches as $sq): ?>
          <tr>
            <td style="font-weight:500"><?= e($sq['query']) ?></td>
            <td style="font-weight:700;color:<?= $sq['count'] >= 5 ? '#dc2626' : '#374151' ?>"><?= $sq['count'] ?></td>
            <td><?= $sq['has_article'] > 0
              ? '<span style="color:#16a34a;font-weight:600">✓ Da</span>'
              : '<span style="color:#dc2626;font-weight:600">✗ Lipsă</span>' ?></td>
            <td>
              <a href="/admin/ai-studio/?subiect=<?= urlencode($sq['query']) ?>"
                 class="btn btn-sm btn-primary" title="Generează articol cu AI">AI →</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($topSearches)): ?>
          <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:1.5rem">
            Nicio căutare internă încă. Utilizatorii vor apărea aici când vor folosi bara de căutare.
          </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Sugestii utilizatori -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">💬 Sugestii de la utilizatori</span>
      <a href="/admin/sugestii/" class="btn btn-sm btn-secondary">Toate →</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Subiect sugerat</th><th>Data</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($suggestions as $sug): ?>
          <tr>
            <td style="font-weight:500"><?= e(truncate($sug['subject'], 55)) ?></td>
            <td style="font-size:.78rem;color:#9ca3af"><?= formatDate($sug['created_at']) ?></td>
            <td>
              <a href="/admin/ai-studio/?subiect=<?= urlencode($sug['subject']) ?>"
                 class="btn btn-sm btn-primary">AI →</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($suggestions)): ?>
          <tr><td colspan="3" style="text-align:center;color:#9ca3af;padding:1.5rem">
            Nicio sugestie nouă. Utilizatorii pot sugera subiecte de pe pagina
            <a href="/sugereaza-subiect/" target="_blank">/sugereaza-subiect/</a>
          </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Shortcut direct spre AI Studio -->
<div class="admin-card" style="margin-bottom:1.25rem;background:linear-gradient(135deg,#1d3557,#2a4a7f);color:#fff">
  <div class="admin-card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
    <div>
      <div style="font-weight:700;font-size:1.05rem;margin-bottom:.25rem">Ai trendurile? Generează idei acum →</div>
      <div style="font-size:.85rem;color:rgba(255,255,255,.75)">Copiază termenii din sursele de mai sus și lasă AI-ul să genereze 20 de idei prioritizate.</div>
    </div>
    <a href="/admin/ai-studio/#tab-idei" class="btn btn-lg" style="background:#fff;color:#1d3557;flex-shrink:0">
      💡 AI Studio — Idei din trenduri
    </a>
  </div>
</div>

<!-- Idei generate anterior -->
<?php if (!empty($ideas) || $filterStatus !== 'idee'): ?>
<div class="admin-card">
  <div class="admin-card-header">
    <span class="admin-card-title">💡 Idei generate anterior</span>
    <div style="display:flex;gap:.35rem">
      <?php foreach (['idee'=>'Idei','in_lucru'=>'În lucru','publicat'=>'Publicate','respins'=>'Respinse'] as $s => $l): ?>
      <a href="?status=<?=$s?>" class="filter-tab<?= $filterStatus===$s?' active':'' ?>"><?=$l?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="admin-card-body">
    <?php if (empty($ideas)): ?>
    <p style="color:#9ca3af;text-align:center;padding:1.5rem">
      Nicio idee generată în această categorie. Mergi la
      <a href="/admin/ai-studio/" style="color:#2b6cb0">AI Studio → Idei din trenduri</a>
      pentru a genera idei noi.
    </p>
    <?php endif; ?>
    <?php foreach ($ideas as $i => $idea): ?>
    <div class="trend-idea-card">
      <div class="trend-idea-number"><?= $i+1 ?></div>
      <div class="trend-idea-body">
        <div class="trend-idea-title"><?= e($idea['title']) ?></div>
        <div class="trend-idea-meta">
          <?php if ($idea['category']): ?>
          <span style="background:#eff6ff;color:#1d4ed8;border-radius:4px;padding:.1rem .4rem;font-size:.72rem"><?= e($idea['category']) ?></span>
          <?php endif; ?>
          <?php if ($idea['keyword']): ?><span>🔑 <?= e($idea['keyword']) ?></span><?php endif; ?>
          <?= riskBadge($idea['risk_level']) ?>
          <?php if ($idea['seo_difficulty']): ?>
          <span style="font-size:.75rem;color:#6b7280">SEO: <?= e($idea['seo_difficulty']) ?></span>
          <?php endif; ?>
          <?php if ($idea['recommendation']): ?>
          <span style="font-size:.75rem;color:<?= $idea['recommendation']==='auto_publish'?'#16a34a':($idea['recommendation']==='blocked'?'#dc2626':'#b45309') ?>">
            <?= ['auto_publish'=>'✓ Auto-publish','pending'=>'⏳ Verificare','blocked'=>'🔴 Blocat'][$idea['recommendation']] ?? '' ?>
          </span>
          <?php endif; ?>
        </div>
        <?php if ($idea['why_search']): ?>
        <div style="font-size:.82rem;color:#4b5563;margin-top:.35rem"><?= e(truncate($idea['why_search'], 130)) ?></div>
        <?php endif; ?>
      </div>
      <div class="trend-idea-actions">
        <a href="/admin/ai-studio/?subiect=<?= urlencode($idea['title']) ?>" class="btn btn-sm btn-primary" title="Generează articol">AI →</a>
        <a href="/admin/trenduri/?actiune=status&id=<?=$idea['id']?>&status=in_lucru" class="btn btn-sm btn-secondary" title="Marchează în lucru">✎</a>
        <a href="/admin/trenduri/?actiune=status&id=<?=$idea['id']?>&status=respins" class="btn btn-sm btn-danger" title="Respinge">✕</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
