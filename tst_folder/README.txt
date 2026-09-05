ANDIVIO WORKSPACE LITE

Parolă implicită:
Andrei123@

Ce este:
O aplicație rapidă și simplă pentru upload/download privat de fișiere, cu interfață modernă minimalistă.

Stil vizual:
Swiss / Apple minimal:
- foarte curat
- sistem font, fără fonturi externe
- fără imagini externe
- zone cu înălțime rezervată ca să evite CLS / layout shift
- rapid și ușor de încărcat

Funcții:
- login cu parolă
- sesiuni cu cookie-uri HttpOnly, SameSite și Secure pe HTTPS
- drag & drop upload
- upload multiplu
- limită aplicație 10 GB / fișier
- foldere automate pe zi: uploads/YYYY-MM-DD/
- grupare și sortare pe zile, descrescător
- căutare rapidă
- download prin aplicație, după login
- Download all într-o arhivă ZIP generată server-side
- ștergere fișiere din interfață
- ștergere în masă cu confirmare și CSRF
- blocare acces direct la uploads/
- blocare acces direct la data/
- noindex / nofollow / noarchive
- headere de securitate și Content Security Policy

Instalare:
1. Uploadează folderul pe server.
2. Asigură-te că PHP poate scrie în:
   uploads/
   data/
3. Schimbă parola în index.php:
   const APP_PASSWORD = 'Andrei123@';
4. Deschide index.php în browser.

Recomandări pentru folosire într-o corporație:
- Folosește un domeniu clar al companiei și HTTPS cu certificat valid; nu folosi IP-ul serverului sau un domeniu temporar.
- Cere echipei IT să permită domeniul prin allowlist dacă politica blochează servicii noi. Nu există o metodă legitimă de a ocoli filtrele corporative.
- Schimbă parola implicită înainte de publicare și păstrează aplicația în spatele HTTPS.
- Configurează `post_max_size` și `upload_max_filesize` suficient de mari pentru fișierele permise; altfel serverul poate respinge uploadul înainte ca PHP să îl proceseze.
- Activează scanarea antivirus/DLP pe server dacă aplicația primește arhive sau executabile.
- Pentru documente sensibile sau fluxuri auditate, SharePoint, OneDrive for Business ori un portal aprobat de companie sunt de preferat unei aplicații PHP independente.

Pentru 10 GB:
Aplicația permite 10 GB, dar serverul trebuie să accepte.
Sunt incluse .htaccess și .user.ini.

Apache/PHP:
upload_max_filesize=10240M
post_max_size=10240M
max_execution_time=7200
max_input_time=7200

Nginx:
client_max_body_size 10G;

Structură:
andivio-workspace-lite/
  index.php
  assets/app.css
  assets/app.js
  uploads/.htaccess
  data/files.json
  data/.htaccess
  .htaccess
  .user.ini
  robots.txt
  README.txt
