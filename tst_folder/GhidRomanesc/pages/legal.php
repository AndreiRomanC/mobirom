<?php
// Pagini legale multiple (politic-editoriala, surse, disclaimer, confidentialitate, cookies)
// Variabila $legalPage vine de la router

$pages = [
'politica-editoriala' => [
  'title'    => 'Politică editorială',
  'subtitle' => 'Cum creăm, verificăm și actualizăm conținutul',
  'content'  => '
<h2>Principii editoriale</h2>
<p>GhidRomânesc publică exclusiv conținut practic, util și verificat. Nu publicăm știri politice, conținut speculativ, clickbait sau informații neconfirmate din surse oficiale.</p>
<h2>Procesul de creare</h2>
<ol>
<li><strong>Identificarea nevoii:</strong> Subiectele sunt alese pe baza căutărilor utilizatorilor, a trendurilor și a sugestiilor primite.</li>
<li><strong>Cercetare:</strong> Fiecare articol se bazează pe surse oficiale (gov.ro, mae.ro, anaf.ro, etc.) și/sau experți verificați.</li>
<li><strong>Redactare:</strong> Conținutul este scris în limbaj simplu, structurat și ușor de parcurs.</li>
<li><strong>Verificare:</strong> Articolele sunt verificate înainte de publicare. Cele cu conținut sensibil (taxe, acte, legislație) trec printr-o verificare specială.</li>
<li><strong>Publicare:</strong> Articolele cu risc scăzut (tutoriale, modele de emailuri, explicații generale) pot fi publicate automat. Cele cu conținut sensibil necesită aprobare manuală.</li>
</ol>
<h2>Sistemul de risc editorial</h2>
<ul>
<li><strong>Verde:</strong> Articole cu risc scăzut (tutoriale digitale, modele emailuri, explicații generale). Pot fi publicate automat.</li>
<li><strong>Galben:</strong> Articole cu proceduri administrative (acte, instituții, consulate). Necesită verificare editorială.</li>
<li><strong>Roșu:</strong> Articole cu conținut fiscal, juridic, medical sau financiar. Necesită aprobare specială și disclaimer.</li>
</ul>
<h2>Actualizarea conținutului</h2>
<p>Fiecare articol are o dată de reverificare recomandată. Articolele cu risc galben sunt reverificabile la 3-6 luni, cele roșii la 30-90 zile.</p>
<h2>Corectarea erorilor</h2>
<p>Dacă identifici o informație incorectă sau depășită, te rugăm să <a href="/raporteaza-eroare/">raportezi eroarea</a>. Vom verifica și corecta în cel mai scurt timp posibil.</p>
',
],

'surse-verificare' => [
  'title'    => 'Surse și verificare',
  'subtitle' => 'Cum selectăm și verificăm sursele',
  'content'  => '
<h2>Sursele noastre</h2>
<p>Toate informațiile publicate pe GhidRomânesc se bazează pe surse oficiale sau verificabile. Fiecare articol include sursele utilizate.</p>
<h2>Ierarhia surselor</h2>
<ul>
<li><strong>Oficial:</strong> Site-uri și documente ale autorităților publice (gov.ro, mae.ro, anaf.ro, cnpp.ro, etc.)</li>
<li><strong>Expert:</strong> Informații furnizate de profesioniști verificați (avocați, contabili, specialiști)</li>
<li><strong>Presă:</strong> Informații din mass-media, folosite doar pentru context, nu ca sursă primară</li>
<li><strong>Comunitate:</strong> Experiențe și întrebări ale utilizatorilor — folosite pentru identificarea subiectelor, nu ca sursă de adevăr</li>
</ul>
<h2>Surse principale utilizate</h2>
<ul>
<li>gov.ro — Guvernul României</li>
<li>mae.ro — Ministerul Afacerilor Externe</li>
<li>anaf.ro — Agenția Națională de Administrare Fiscală</li>
<li>politiaromana.ro — Poliția Română (pașapoarte, permise)</li>
<li>cnpp.ro — Casa Națională de Pensii Publice</li>
<li>ghiseul.ro — Plata taxelor locale online</li>
<li>e-guvernare.ro — Servicii digitale guvernamentale</li>
<li>Site-urile consulatelor și ambasadelor</li>
</ul>
<h2>Limitările noastre</h2>
<p>Nu putem garanta că toate informațiile sunt actualizate în timp real. Regulile, taxele și procedurile se pot schimba fără preaviz. Verifică întotdeauna sursa oficială înainte de a lua o decizie.</p>
',
],

'limitarea-responsabilitatii' => [
  'title'    => 'Limitarea responsabilității',
  'subtitle' => 'Termeni importanți de înțeles înainte de a folosi site-ul',
  'content'  => '
<h2>Scop informativ</h2>
<p>GhidRomânesc.ro este o platformă informativă și explicativă. Conținutul publicat are exclusiv scop educațional și informativ.</p>
<h2>Nu este consultanță</h2>
<p>Informațiile de pe GhidRomânesc <strong>nu constituie și nu înlocuiesc:</strong></p>
<ul>
<li>Consultanță juridică personalizată</li>
<li>Consultanță fiscală sau contabilă personalizată</li>
<li>Consultanță financiară sau de investiții</li>
<li>Consultanță medicală sau psihologică</li>
</ul>
<p>Pentru decizii importante — juridice, fiscale, financiare sau medicale — consultați întotdeauna un specialist autorizat.</p>
<h2>Nu suntem instituție publică</h2>
<p>GhidRomânesc nu este o instituție publică și nu reprezintă Guvernul României, ANAF, MAE, Ministerul de Interne, primăriile, consulatele sau orice altă autoritate publică.</p>
<h2>Responsabilitatea utilizatorului</h2>
<p>Utilizatorul este responsabil pentru deciziile luate pe baza informațiilor de pe acest site. GhidRomânesc nu poate fi responsabilizat pentru consecințele deciziilor bazate exclusiv pe conținutul publicat.</p>
<h2>Modificări legislative</h2>
<p>Procedurile, taxele și regulile se pot modifica. Deși ne străduim să menținem conținutul actualizat, nu putem garanta acuratețea în timp real. Verifică întotdeauna sursa oficială.</p>
',
],

'confidentialitate' => [
  'title'    => 'Politică de confidențialitate',
  'subtitle' => 'Cum colectăm și utilizăm datele tale personale',
  'content'  => '
<h2>Date colectate</h2>
<p>GhidRomânesc colectează date minime, necesare funcționării site-ului:</p>
<ul>
<li><strong>Newsletter:</strong> Adresa de email — dacă te abonezi voluntar</li>
<li><strong>Formulare:</strong> Datele furnizate în formularele de contact, raportare erori sau sugestii</li>
<li><strong>Log-uri tehnice:</strong> Adresa IP, browserul și paginile vizitate — pentru securitate și statistici anonime</li>
<li><strong>Căutări interne:</strong> Termenii de căutare — în mod anonim, pentru a îmbunătăți conținutul</li>
</ul>
<h2>Cum utilizăm datele</h2>
<ul>
<li>Trimiterea newsletter-ului (dacă ești abonat)</li>
<li>Răspunsuri la mesajele trimise prin formulare</li>
<li>Îmbunătățirea conținutului și experienței utilizatorilor</li>
<li>Securitatea site-ului</li>
</ul>
<h2>Nu vindem datele tale</h2>
<p>Nu vindem, nu închiriem și nu transferăm datele tale personale către terți, cu excepția cazurilor prevăzute de lege.</p>
<h2>Drepturile tale GDPR</h2>
<p>Ai dreptul de acces, rectificare, ștergere și portabilitate a datelor tale. Poți solicita oricând dezabonarea de la newsletter sau ștergerea datelor prin <a href="/contact/">formularul de contact</a>.</p>
<h2>Cookie-uri</h2>
<p>Folosim cookie-uri tehnice și de analiză. Detalii în <a href="/cookies/">Politica de cookies</a>.</p>
',
],

'cookies' => [
  'title'    => 'Politică de cookies',
  'subtitle' => 'Ce cookie-uri folosim și de ce',
  'content'  => '
<h2>Ce sunt cookie-urile</h2>
<p>Cookie-urile sunt fișiere mici stocate în browserul tău atunci când vizitezi un site. Ele permit site-ului să rețină informații despre vizita ta.</p>
<h2>Cookie-uri folosite de GhidRomânesc</h2>
<h3>Cookie-uri necesare (tehnice)</h3>
<ul>
<li><strong>Sesiune:</strong> Menține sesiunea activă (expiră la închiderea browserului)</li>
<li><strong>Preferințe:</strong> Reține setările tale (ex: dacă ai acceptat cookie-urile)</li>
</ul>
<h3>Cookie-uri de analiză</h3>
<p>Folosim Google Analytics sau o alternativă respectuoasă a intimității (ex: Matomo) pentru a înțelege cum este utilizat site-ul. Datele sunt anonimizate.</p>
<h2>Gestionarea cookie-urilor</h2>
<p>Poți dezactiva cookie-urile din setările browserului tău. Dacă dezactivezi cookie-urile necesare, unele funcționalități ale site-ului pot fi afectate.</p>
<h2>Acordul tău</h2>
<p>Prin continuarea utilizării site-ului, ești de acord cu utilizarea cookie-urilor descrise mai sus. Poți retrage acordul oricând din setările browserului.</p>
',
],
];

$legalSlug = $legalPage ?? 'politica-editoriala';
$currentPage = $pages[$legalSlug] ?? null;

if (!$currentPage) {
    http_response_code(404);
    require __DIR__ . '/../templates/header.php';
    echo '<div class="container" style="padding:4rem 0;text-align:center"><h2>Pagina nu a fost găsită</h2></div>';
    require __DIR__ . '/../templates/footer.php';
    exit;
}

$pageTitle = $currentPage['title'];
$metaDescription = $currentPage['subtitle'];
require __DIR__ . '/../templates/header.php';
?>
<div class="page-header"><div class="container">
  <nav class="breadcrumb" style="margin-bottom:.75rem"><a href="/">Acasă</a> <span class="breadcrumb-sep">›</span> <span><?= e($currentPage['title']) ?></span></nav>
  <h1 class="page-title"><?= e($currentPage['title']) ?></h1>
  <p class="page-subtitle"><?= e($currentPage['subtitle']) ?></p>
</div></div>
<div class="container-sm" style="padding-bottom:4rem">
  <div class="page-content"><?= $currentPage['content'] ?></div>
  <div style="margin-top:2.5rem;padding-top:1.5rem;border-top:1px solid var(--border);font-size:.8rem;color:var(--text-muted)">Ultima actualizare: <?= date('d.m.Y') ?></div>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>
