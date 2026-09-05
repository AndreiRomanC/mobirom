# GhidRomânesc — Ghid de instalare și deployment

## Cerințe server

- **PHP** 7.4+ sau 8.x (recomandat 8.1+)
- **MySQL** 5.7+ sau MariaDB 10.3+
- **mod_rewrite** activat (Apache) sau echivalent Nginx
- **cURL** activat în PHP (pentru AI Studio)
- **mail()** configurat (opțional, pentru newsletter)

---

## Pași de instalare

### 1. Urcă fișierele pe server

Urcă **toate fișierele** în `public_html/` (sau directorul rădăcină al domeniului) via:
- cPanel File Manager → Upload
- FTP (FileZilla etc.)
- Git (dacă serverul suportă)

**Nu urca** directorul `.git/` și `node_modules/` (nu există, dar dacă apar).

### 2. Creează baza de date MySQL

În cPanel → **MySQL Databases**:
1. Creează o bază de date nouă (ex: `ghidro_db`)
2. Creează un utilizator MySQL (ex: `ghidro_user`)
3. Atribuie utilizatorul la baza de date (toate privilegiile)

### 3. Rulează instalarea

Deschide în browser:
```
https://ghidromanesc.ro/install.php
```

Completează:
- Host MySQL: de obicei `localhost`
- Numele bazei de date, utilizatorul și parola MySQL
- Email și parolă pentru contul de administrator
- Domeniu (ex: `https://ghidromanesc.ro`)
- Cheia API Anthropic (optional — o poți adăuga mai târziu)

Apasă **Instalează**. Scriptul:
- Creează toate tabelele
- Inserează categoriile
- Creează contul de admin
- Adaugă 6 articole demo
- Scrie configurația în `config.php`
- Creează fișierul `.installed`

### 4. Șterge install.php

**IMPORTANT pentru securitate!** După instalare:
```
Șterge fișierul install.php de pe server!
```

### 5. Configurează AI Studio (opțional)

1. Mergi la [console.anthropic.com](https://console.anthropic.com)
2. Creează un cont și generează o cheie API
3. În admin → **Setări** → completează câmpul "Cheie API Anthropic"

---

## Structura fișierelor

```
/
├── index.php              — Router principal (toate URL-urile)
├── config.php             — Configurație bază de date, API, site
├── install.php            — Script instalare (șterge după instalare!)
├── .htaccess              — URL rewriting, securitate, cache
├── robots.txt             — Instrucțiuni crawlere
├── sitemap.php            — Sitemap XML dinamic
│
├── src/                   — Clase PHP
│   ├── Database.php       — Conexiune MySQL + query helpers
│   ├── Auth.php           — Autentificare sesiuni
│   ├── Article.php        — Model articole
│   ├── AI.php             — Integrare Claude AI
│   └── helpers.php        — Funcții utilitare
│
├── pages/                 — Paginile publice
│   ├── home.php           — Homepage
│   ├── article.php        — Articol individual
│   ├── category.php       — Pagină categorie
│   ├── search.php         — Căutare
│   ├── about.php          — Despre
│   ├── legal.php          — Pagini legale (5 pagini)
│   ├── contact.php        — Contact
│   ├── report.php         — Raportează eroare
│   └── suggest.php        — Sugerează subiect
│
├── admin/                 — Panou de administrare
│   ├── index.php          — Dashboard
│   ├── login.php          — Autentificare
│   ├── articole.php       — Gestionare articole
│   ├── ai-studio.php      — AI Studio
│   ├── calendar.php       — Calendar editorial
│   ├── trenduri.php       — Trenduri și idei
│   ├── raportari.php      — Raportări erori
│   ├── sugestii.php       — Sugestii utilizatori
│   ├── newsletter.php     — Abonați newsletter
│   └── setari.php         — Setări și prompturi AI
│
├── api/                   — Endpoint-uri JSON
│   ├── ai-generate.php    — Generare AI (articole, idei, SEO, risc)
│   ├── newsletter.php     — Abonare newsletter
│   └── save-settings.php  — Salvare setări
│
├── templates/             — Layout-uri HTML
│   ├── header.php         — Header public (cu SEO, meta, schema.org)
│   ├── footer.php         — Footer public (newsletter inclus)
│   ├── admin-layout.php   — Sidebar admin
│   └── admin-footer.php   — Footer admin
│
├── assets/
│   ├── css/style.css      — CSS public complet
│   ├── css/admin.css      — CSS admin
│   ├── js/main.js         — JavaScript public
│   ├── js/admin.js        — JavaScript admin
│   └── img/               — Imagini (favicon etc.)
│
└── install/
    ├── schema.sql          — Schema MySQL completă
    └── seed_articles.php   — Date demo
```

---

## Accesul la admin

```
https://ghidromanesc.ro/admin/
```

Login cu emailul și parola setate la instalare.

---

## Configurare Nginx (alternativă la Apache/.htaccess)

Dacă folosești Nginx în loc de Apache:

```nginx
server {
    listen 80;
    server_name ghidromanesc.ro www.ghidromanesc.ro;
    root /var/www/public_html;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ /admin/([a-z-]+)/?$ {
        try_files $uri /admin/$1.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(css|js|svg|png|jpg|webp)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location ~ /\. { deny all; }
    location ~ /install/ { deny all; }
}
```

---

## Funcționalități incluse

### Site public
- ✅ Homepage cu Hero, Categorii, Articole populare, Diaspora, Digital&AI, Newsletter, Trust bar
- ✅ Pagini categorii cu filtrare pe tip articol și paginare
- ✅ Pagini articole cu breadcrumb, disclaimer automat, surse, acțiuni (share WhatsApp, print, salvare)
- ✅ Căutare full-text
- ✅ Pagini legale: Despre, Politică editorială, Surse, Disclaimer, Confidențialitate, Cookies, Contact
- ✅ Formular raportare erori
- ✅ Formular sugestii subiecte
- ✅ Newsletter cu confirmare email
- ✅ Sitemap XML dinamic
- ✅ Schema.org (Organization, Article, BreadcrumbList, WebSite + SearchAction)
- ✅ Open Graph și Twitter Cards
- ✅ Mobile-first design
- ✅ Print styling

### Admin
- ✅ Dashboard cu statistici, alerte, articole de verificat, top căutări
- ✅ CRUD articole cu sistem de risc verde/galben/roșu
- ✅ Publicare automată doar pentru articolele verzi
- ✅ Verificare automată înainte de publicare
- ✅ Calendar editorial Kanban (Idee → Draft AI → Verificare → Aprobat → Programat → Publicat)
- ✅ AI Studio: generare articole, idei din trenduri, rescriere simplă, verificare risc, SEO automat
- ✅ Prompturi AI editabile din interfață
- ✅ Sistem de reverificare: verde 6-12 luni, galben 3-6 luni, roșu 1-3 luni
- ✅ Gestionare raportări erori
- ✅ Gestionare sugestii utilizatori
- ✅ Statistici newsletter
- ✅ Trenduri și oportunități de articole din căutări interne

---

## Securitate

- Sesiuni PHP cu cookie httponly și strict mode
- Prepared statements pentru toate query-urile (protecție SQL injection)
- Escapare HTML la afișare (protecție XSS)
- CSRF token pe formulare admin
- Directorul `/admin/` nu este indexat
- Fișierele sensibile sunt blocate prin `.htaccess`
- Parole hash cu bcrypt (cost 12)
