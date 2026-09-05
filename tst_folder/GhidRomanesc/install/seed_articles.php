<?php
function seedArticles(PDO $pdo): void {
    // Obține ID-urile categoriilor
    $cats = [];
    $stmt = $pdo->query('SELECT id, slug FROM categories');
    foreach ($stmt->fetchAll() as $c) $cats[$c['slug']] = $c['id'];

    // Obține ID-ul autorului (admin)
    $authorId = $pdo->query('SELECT id FROM users LIMIT 1')->fetchColumn();

    $articles = [
        [
            'category_id'      => $cats['acte-institutii'] ?? 1,
            'author_id'        => $authorId,
            'title'            => 'Cum faci programare online pentru pașaport în 2024',
            'slug'             => 'cum-faci-programare-pasaport-online',
            'excerpt'          => 'Ghid pas cu pas pentru programarea online la pașaport prin platforma de programări a Poliției Române. Durată, acte necesare și costuri explicate simplu.',
            'content'          => '<div class="article-summary"><h3>Pe scurt</h3><ul><li>Programarea se face online pe <strong>pasapoarte.politiaromana.ro</strong></li><li>Ai nevoie de buletin valabil și de formularul de cerere completat</li><li>Durata standard este de 5-10 zile lucrătoare</li><li>Costul este 258 lei pentru pașaport simplu electronic</li><li>Programarea se face cu câteva săptămâni înainte — locurile se ocupă rapid</li></ul></div>

<h2>Cine are nevoie de acest ghid</h2>
<p>Dacă vrei să faci sau să reînnoiești pașaportul și nu știi de unde să începi, acest ghid îți explică pas cu pas cum funcționează sistemul de programări online.</p>

<h2>Ce trebuie să știi înainte</h2>
<ul>
<li>Programările se fac pe site-ul oficial al Poliției Române</li>
<li>Locurile disponibile se ocupă rapid, în special în perioade aglomerate (vară, sărbători)</li>
<li>Trebuie să ai buletinul valabil la momentul programării</li>
</ul>

<h2>Pașii de urmat</h2>
<ol>
<li><strong>Intră pe pasapoarte.politiaromana.ro</strong> — acesta este site-ul oficial</li>
<li><strong>Alege județul și serviciul</strong> de pașapoarte din zona ta</li>
<li><strong>Selectează data și ora</strong> disponibile — alege cât mai din timp</li>
<li><strong>Completează datele personale</strong> și salvează confirmarea</li>
<li><strong>Completează cererea de pașaport</strong> — o poți descărca de pe site sau o primești la ghișeu</li>
<li><strong>Prezintă-te la programare</strong> cu toate actele necesare</li>
</ol>

<h2>Acte necesare</h2>
<ul>
<li>Buletin de identitate valabil</li>
<li>Pașaportul vechi (dacă ai)</li>
<li>Cerere completată (se poate descărca de pe site)</li>
<li>Dovada plății taxei</li>
</ul>

<h2>Cât durează</h2>
<p>Standard: <strong>5-10 zile lucrătoare</strong> după depunerea dosarului. Există și regim de urgență (contra cost suplimentar).</p>

<h2>Cât costă</h2>
<ul>
<li>Pașaport simplu electronic (valabil 5 ani, pentru copii sub 18 ani): 217 lei</li>
<li>Pașaport simplu electronic (valabil 10 ani, adulți): 258 lei</li>
<li>Regim de urgență: tarif suplimentar — verifică pe site-ul oficial</li>
</ul>

<h2>Greșeli frecvente</h2>
<ul>
<li>❌ A veni fără toate actele necesare</li>
<li>❌ A uita să plătești taxa înainte sau a nu aduce dovada plății</li>
<li>❌ A nu verifica dacă buletinul este valabil</li>
<li>❌ A face programare cu prea puțin timp înainte de călătorie</li>
</ul>

<div class="disclaimer-box">⚠️ <strong>Important:</strong> Tarifele și procedurile se pot schimba. Verifică întotdeauna pe <a href="https://www.politiaromana.ro" target="_blank" rel="nofollow">politiaromana.ro</a> înainte de a merge la programare.</div>

<h2>Surse oficiale</h2>
<ul>
<li><a href="https://www.politiaromana.ro" target="_blank" rel="nofollow">politiaromana.ro</a> — Poliția Română</li>
<li><a href="https://pasapoarte.politiaromana.ro" target="_blank" rel="nofollow">pasapoarte.politiaromana.ro</a> — Programări online</li>
</ul>',
            'article_type'     => 'ghid_complet',
            'meta_title'       => 'Cum faci programare pașaport online în România | GhidRomânesc',
            'meta_description' => 'Ghid pas cu pas pentru programare online la pașaport. Acte necesare, costuri și pași explicați simplu.',
            'focus_keyword'    => 'programare pasaport online',
            'tags'             => 'pasaport,programare,politia romana,acte',
            'status'           => 'published',
            'risk_level'       => 'galben',
            'published_at'     => date('Y-m-d H:i:s', strtotime('-2 days')),
            'review_date'      => date('Y-m-d', strtotime('+6 months')),
            'needs_disclaimer' => 1,
            'views'            => 1247,
        ],
        [
            'category_id'      => $cats['digital-ai'] ?? 2,
            'author_id'        => $authorId,
            'title'            => 'Cum transformi o poză în PDF pe telefon sau calculator',
            'slug'             => 'cum-transformi-poza-in-pdf',
            'excerpt'          => 'Metodă simplă și gratuită pentru a converti orice imagine (JPG, PNG) în fișier PDF, de pe telefon sau calculator. Fără aplicații plătite.',
            'content'          => '<div class="article-summary"><h3>Pe scurt</h3><ul><li>Pe telefon (Android/iPhone): folosești opțiunea de printare → salvează ca PDF</li><li>Pe calculator (Windows): deschizi imaginea → Print → Salvează ca PDF</li><li>Online: gratuit pe <strong>ilovepdf.com</strong> sau <strong>smallpdf.com</strong></li><li>Nu ai nevoie de nicio aplicație plătită</li></ul></div>

<h2>Metoda 1: Pe telefon Android</h2>
<ol>
<li>Deschide poza în Galerie</li>
<li>Apasă cele 3 puncte → <strong>Printare</strong> (sau Share → Print)</li>
<li>La imprimantă, alege <strong>Salvare ca PDF</strong></li>
<li>Alege locul unde vrei să salvezi și apasă <strong>Salvare</strong></li>
</ol>

<h2>Metoda 2: Pe iPhone/iPad</h2>
<ol>
<li>Deschide poza în Photos</li>
<li>Apasă butonul de Share (pătrățelul cu săgeată)</li>
<li>Derulează în jos și apasă <strong>Print</strong></li>
<li>Pincheaza (mărește) previzualizarea — se deschide ca PDF</li>
<li>Apasă Share → Salvează în Files</li>
</ol>

<h2>Metoda 3: Pe calculator Windows</h2>
<ol>
<li>Deschide imaginea (dublu-click)</li>
<li>Apasă <strong>Ctrl+P</strong> (Print)</li>
<li>La imprimantă, selectează <strong>Microsoft Print to PDF</strong></li>
<li>Apasă Print și alege unde să salvezi</li>
</ol>

<h2>Metoda 4: Online — fără instalare</h2>
<p>Intră pe <strong>ilovepdf.com → JPG to PDF</strong> și încarcă imaginea. Durează 10 secunde.</p>

<h2>Sfat util</h2>
<p>Dacă vrei să combini mai multe poze într-un singur PDF, folosește ilovepdf.com sau smallpdf.com — sunt gratuite pentru uz basic.</p>',
            'article_type'     => 'tutorial',
            'meta_title'       => 'Cum transformi o poză în PDF gratuit | GhidRomânesc',
            'meta_description' => 'Metodă simplă și gratuită: transformă orice imagine în PDF de pe telefon sau calculator, fără aplicații plătite.',
            'focus_keyword'    => 'transforma poza in pdf',
            'tags'             => 'pdf,poza,conversie,gratuit,telefon,calculator',
            'status'           => 'published',
            'risk_level'       => 'verde',
            'published_at'     => date('Y-m-d H:i:s', strtotime('-1 day')),
            'review_date'      => date('Y-m-d', strtotime('+12 months')),
            'needs_disclaimer' => 0,
            'views'            => 892,
        ],
        [
            'category_id'      => $cats['diaspora'] ?? 3,
            'author_id'        => $authorId,
            'title'            => 'Cum faci programare la Consulatul Român din Germania',
            'slug'             => 'programare-consulat-roman-germania',
            'excerpt'          => 'Ghid complet pentru programarea online la consulatele române din Germania: Berlin, München, Frankfurt, Stuttgart. Servicii disponibile, documente necesare și timp de așteptare.',
            'content'          => '<div class="article-summary"><h3>Pe scurt</h3><ul><li>Programările se fac exclusiv online, prin <strong>programari.mae.ro</strong></li><li>Există consulate în Berlin, München, Frankfurt, Stuttgart și Bonn</li><li>Locurile sunt limitate și se ocupă rapid — programează-te cu 2-4 săptămâni înainte</li><li>Serviciile consulare includ: pașaport, buletin, naștere, căsătorie, procuri notariale</li></ul></div>

<h2>Consulate române în Germania</h2>
<ul>
<li><strong>Ambasada României la Berlin</strong> — acoperă nordul Germaniei</li>
<li><strong>Consulatul General München</strong> — Bavaria, Baden-Württemberg (parțial)</li>
<li><strong>Consulatul General Frankfurt</strong> — Hessa, Renania-Palatinat, Saar</li>
<li><strong>Consulatul General Stuttgart</strong> — Baden-Württemberg</li>
<li><strong>Consulatul General Bonn</strong> — Renania de Nord-Westfalia</li>
</ul>

<h2>Pașii pentru programare online</h2>
<ol>
<li>Intră pe <strong>programari.mae.ro</strong></li>
<li>Alege <strong>Consulatul</strong> din zona ta</li>
<li>Selectează <strong>serviciul dorit</strong> (pașaport, buletin, etc.)</li>
<li>Completează datele personale</li>
<li>Alege data și ora disponibile</li>
<li>Confirmă prin email</li>
</ol>

<div class="disclaimer-box">⚠️ <strong>Important:</strong> Programele consulatelor, serviciile disponibile și documentele necesare se pot schimba. Verifică întotdeauna pe site-ul oficial al consulatului înainte de a te deplasa.</div>

<h2>Surse oficiale</h2>
<ul>
<li><a href="https://www.mae.ro" target="_blank" rel="nofollow">mae.ro</a> — Ministerul Afacerilor Externe</li>
<li><a href="https://programari.mae.ro" target="_blank" rel="nofollow">programari.mae.ro</a> — Programări online MAE</li>
</ul>',
            'article_type'     => 'ghid_complet',
            'meta_title'       => 'Programare Consulat Român Germania | GhidRomânesc',
            'meta_description' => 'Cum faci programare online la consulatele române din Germania. Berlin, München, Frankfurt, Stuttgart — ghid pas cu pas.',
            'focus_keyword'    => 'programare consulat roman germania',
            'tags'             => 'consulat,germania,diaspora,programare,mae',
            'status'           => 'published',
            'risk_level'       => 'galben',
            'published_at'     => date('Y-m-d H:i:s', strtotime('-3 days')),
            'review_date'      => date('Y-m-d', strtotime('+3 months')),
            'needs_disclaimer' => 1,
            'views'            => 2341,
        ],
        [
            'category_id'      => $cats['digital-ai'] ?? 2,
            'author_id'        => $authorId,
            'title'            => 'Cum folosești ChatGPT în română — ghid pentru începători',
            'slug'             => 'cum-folosesti-chatgpt-in-romana',
            'excerpt'          => 'Totul despre ChatGPT: ce este, cum îți faci cont gratuit, cum scrii cereri în română și cum îl folosești pentru treaba zilnică, emailuri, traduceri și rezumate.',
            'content'          => '<div class="article-summary"><h3>Pe scurt</h3><ul><li>ChatGPT este un asistent AI disponibil gratuit pe <strong>chat.openai.com</strong></li><li>Înțelege și răspunde perfect în limba română</li><li>Îl poți folosi pentru emailuri, rezumate, traduceri, idei, explicații</li><li>Nu are nevoie de instalare — funcționează direct în browser</li><li>Versiunea gratuită este suficientă pentru uz zilnic</li></ul></div>

<h2>Ce este ChatGPT</h2>
<p>ChatGPT este un asistent artificial creat de compania OpenAI. Funcționează ca o conversație: îi pui o întrebare sau îi dai o sarcină, el răspunde. Înțelege și scrie perfect în română.</p>

<h2>Cum îți faci cont (gratuit)</h2>
<ol>
<li>Intră pe <strong>chat.openai.com</strong></li>
<li>Click pe <strong>Sign Up</strong></li>
<li>Înregistrează-te cu email sau cu contul Google/Microsoft</li>
<li>Confirmă emailul și ești gata</li>
</ol>

<h2>Ce poți face cu ChatGPT în română</h2>
<ul>
<li>✅ Scrie emailuri profesionale</li>
<li>✅ Rezumă texte lungi</li>
<li>✅ Traduce documente</li>
<li>✅ Explică termeni juridici sau financiari pe înțelesul tău</li>
<li>✅ Generează idei pentru CV sau scrisoare de intenție</li>
<li>✅ Ajutor cu gramatică și ortografie</li>
</ul>

<h2>Exemple de cereri în română</h2>
<p>„Scrie-mi un email profesional prin care cer o zi liberă."</p>
<p>„Explică-mi ce înseamnă TVA pe înțelesul unui începător."</p>
<p>„Rezumă textul următor în 5 puncte: [text]"</p>

<h2>Limitări importante</h2>
<ul>
<li>❌ Nu are acces la internet în versiunea gratuită — informațiile pot fi vechi</li>
<li>❌ Poate face greșeli — verifică informațiile importante din surse oficiale</li>
<li>❌ Nu da informații personale (CNP, parole, date bancare)</li>
</ul>',
            'article_type'     => 'tutorial',
            'meta_title'       => 'Cum folosești ChatGPT în română — Ghid pentru începători',
            'meta_description' => 'Ghid simplu pentru ChatGPT: cum faci cont gratuit, cum scrii în română și ce poți face cu el în viața de zi cu zi.',
            'focus_keyword'    => 'chatgpt in romana',
            'tags'             => 'chatgpt,ai,inteligenta artificiala,tutorial,incepatatori',
            'status'           => 'published',
            'risk_level'       => 'verde',
            'published_at'     => date('Y-m-d H:i:s'),
            'review_date'      => date('Y-m-d', strtotime('+6 months')),
            'needs_disclaimer' => 0,
            'views'            => 3102,
        ],
        [
            'category_id'      => $cats['modele-checklist'] ?? 6,
            'author_id'        => $authorId,
            'title'            => 'Model email oficial în română — 10 modele pentru orice situație',
            'slug'             => 'model-email-oficial-romana',
            'excerpt'          => '10 modele de emailuri oficiale în română, gata de folosit: email cerere, email reclamație, email de mulțumire, email pentru angajator și altele.',
            'content'          => '<div class="article-summary"><h3>Pe scurt</h3><ul><li>Un email oficial trebuie să fie scurt, clar și politicos</li><li>Structura: Subiect clar → Salut formal → Motiv → Cerere/informație → Mulțumire → Semnătură</li><li>Evită prescurtările și limbajul informal</li></ul></div>

<h2>Structura unui email oficial</h2>
<p><strong>Subiect:</strong> Scurt și clar (ex: „Cerere zi liberă — [Numele tău]")<br>
<strong>Salut:</strong> „Stimate/Stimată [Titlu Nume]," sau „Bună ziua,"<br>
<strong>Conținut:</strong> Max 2-3 paragrafe, la obiect<br>
<strong>Încheiere:</strong> „Cu stimă," / „Cu respect,"<br>
<strong>Semnătură:</strong> Nume, funcție, date de contact</p>

<h2>Model 1 — Cerere zi liberă</h2>
<div class="email-template">
<p>Subiect: Cerere zi liberă — [Numele tău], [Data]</p>
<p>Stimate/Stimată [Numele managerului],</p>
<p>Vă contactez pentru a solicita o zi liberă în data de [data], din motive personale.</p>
<p>Voi asigura predarea sarcinilor aflate în curs înainte de această dată.</p>
<p>Vă mulțumesc pentru înțelegere.</p>
<p>Cu stimă,<br>[Numele tău]<br>[Funcția]</p>
</div>

<h2>Model 2 — Email de reclamație</h2>
<div class="email-template">
<p>Subiect: Reclamație — [Subiect concret]</p>
<p>Stimate/Stimată [Destinatar],</p>
<p>Vă contactez în legătură cu [descriere problemă, data când s-a întâmplat].</p>
<p>Solicit [soluția dorită — rambursare, remediere, clarificare] în termenul legal.</p>
<p>Aștept răspunsul dumneavoastră în maximum [X] zile lucrătoare.</p>
<p>Cu stimă,<br>[Numele tău]<br>[Date contact]</p>
</div>

<h2>Model 3 — Email pentru o instituție publică</h2>
<div class="email-template">
<p>Subiect: Solicitare informații — [Subiect]</p>
<p>Bună ziua,</p>
<p>Subsemnatul/Subsemnata [Numele tău complet], CNP [CNP], cu domiciliul în [adresa], solicit informații privind [subiectul concret].</p>
<p>Vă rog să îmi comunicați [ce anume dorești să afli sau să primești].</p>
<p>Vă mulțumesc.</p>
<p>Cu stimă,<br>[Numele tău]<br>[Telefon/Email]</p>
</div>',
            'article_type'     => 'model_email',
            'meta_title'       => 'Model Email Oficial în Română — 10 Modele Gata de Folosit',
            'meta_description' => '10 modele de emailuri oficiale în română, gata de copiat și adaptat. Email cerere, reclamație, instituții publice și altele.',
            'focus_keyword'    => 'model email oficial romana',
            'tags'             => 'email,model,cerere,oficial,profesional',
            'status'           => 'published',
            'risk_level'       => 'verde',
            'published_at'     => date('Y-m-d H:i:s', strtotime('-5 days')),
            'review_date'      => date('Y-m-d', strtotime('+12 months')),
            'needs_disclaimer' => 0,
            'views'            => 1876,
        ],
        [
            'category_id'      => $cats['bani-taxe'] ?? 4,
            'author_id'        => $authorId,
            'title'            => 'Cum creezi cont în SPV ANAF și ce poți face cu el',
            'slug'             => 'cum-creezi-cont-spv-anaf',
            'excerpt'          => 'Ghid pas cu pas pentru crearea contului în Spațiul Privat Virtual (SPV) al ANAF. Ce documente îți trebuie, cum te înregistrezi și ce servicii fiscale poți accesa online.',
            'content'          => '<div class="article-summary"><h3>Pe scurt</h3><ul><li>SPV ANAF este portalul fiscal oficial unde îți gestionezi situația fiscală online</li><li>Poți verifica declarații, impozite, plăți și comunica cu ANAF fără a merge la ghișeu</li><li>Înregistrarea se face pe <strong>anaf.ro → SPV</strong></li><li>Ai nevoie de buletin și CNP</li></ul></div>

<h2>Ce este SPV ANAF</h2>
<p>Spațiul Privat Virtual (SPV) este portalul online al ANAF unde persoanele fizice și juridice pot gestiona situația fiscală: vizualiza declarații, verifica plăți, primi documente oficiale și comunica cu ANAF.</p>

<h2>Ce poți face în SPV</h2>
<ul>
<li>✅ Verifica situația fiscală (datorii, plăți)</li>
<li>✅ Depune declarații online (D212, D700 etc.)</li>
<li>✅ Primi documente oficiale de la ANAF</li>
<li>✅ Verifica istoricul declarațiilor</li>
<li>✅ Comunica electronic cu ANAF</li>
</ul>

<h2>Cum creezi contul</h2>
<ol>
<li>Intră pe <strong>anaf.ro</strong> → Servicii Online → Spațiul Privat Virtual</li>
<li>Click <strong>Înregistrare</strong></li>
<li>Completează CNP-ul și datele personale</li>
<li>Selectează metoda de verificare (cu semnătură electronică sau la ghișeu ANAF)</li>
<li>Confirmă identitatea și activează contul</li>
</ol>

<div class="disclaimer-box">⚠️ <strong>Important:</strong> Procedura de înregistrare în SPV se poate modifica. Verifică întotdeauna instrucțiunile actualizate pe <a href="https://www.anaf.ro" target="_blank" rel="nofollow">anaf.ro</a>. Acest articol are scop informativ și nu reprezintă consultanță fiscală.</div>

<h2>Surse oficiale</h2>
<ul>
<li><a href="https://www.anaf.ro" target="_blank" rel="nofollow">anaf.ro</a> — ANAF oficial</li>
</ul>',
            'article_type'     => 'ghid_complet',
            'meta_title'       => 'Cum creezi cont SPV ANAF | Ghid Pas cu Pas | GhidRomânesc',
            'meta_description' => 'Ghid pas cu pas pentru contul SPV ANAF. Ce este, ce poți face online și cum te înregistrezi fără a merge la ghișeu.',
            'focus_keyword'    => 'cont spv anaf',
            'tags'             => 'anaf,spv,fiscal,declaratii,online',
            'status'           => 'published',
            'risk_level'       => 'rosu',
            'published_at'     => date('Y-m-d H:i:s', strtotime('-7 days')),
            'review_date'      => date('Y-m-d', strtotime('+2 months')),
            'needs_disclaimer' => 1,
            'views'            => 4521,
        ],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO articles
        (category_id, author_id, title, slug, excerpt, content, article_type,
         meta_title, meta_description, focus_keyword, tags, status, risk_level,
         published_at, review_date, needs_disclaimer, views, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now','localtime'), datetime('now','localtime'))
    ");

    foreach ($articles as $a) {
        $stmt->execute([
            $a['category_id'], $a['author_id'], $a['title'], $a['slug'],
            $a['excerpt'], $a['content'], $a['article_type'],
            $a['meta_title'], $a['meta_description'], $a['focus_keyword'],
            $a['tags'], $a['status'], $a['risk_level'],
            $a['published_at'], $a['review_date'], $a['needs_disclaimer'], $a['views'],
        ]);
    }
}
