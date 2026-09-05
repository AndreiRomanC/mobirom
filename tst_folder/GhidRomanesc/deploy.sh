#!/bin/bash
# GhidRomânesc — Script deploy automat via FTP
# Utilizare: ./deploy.sh
# Sau cu credențiale custom: FTP_PASS=parola ./deploy.sh

set -e

# ─── Configurare FTP ──────────────────────────────────────────────────────────
FTP_HOST="${FTP_HOST:-82.29.157.230}"
FTP_PORT="${FTP_PORT:-21}"
FTP_USER="${FTP_USER:-u190087235.ghidromanesc.ro}"
FTP_PASS="${FTP_PASS:-}"   # Setează parola corectă sau exporta FTP_PASS înainte
FTP_REMOTE="${FTP_REMOTE:-.}"   # FTP login intră direct în public_html
LOCAL_DIR="$(cd "$(dirname "$0")" && pwd)"

# ─── Validare ────────────────────────────────────────────────────────────────
if [ -z "$FTP_PASS" ]; then
    echo "⚠️  Parolă FTP lipsă. Setează FTP_PASS=parola_ta"
    echo "   Exemplu: FTP_PASS='parola' ./deploy.sh"
    exit 1
fi

if ! command -v lftp &> /dev/null; then
    echo "❌ lftp nu este instalat. Rulează: brew install lftp"
    exit 1
fi

# ─── Fișiere de exclus ───────────────────────────────────────────────────────
EXCLUDE_ARGS="--exclude \.git --exclude \.gitignore --exclude node_modules --exclude deploy\.sh --exclude test\.php --exclude migrate\.php --exclude .*\.log$ --exclude \.DS_Store --exclude DEPLOY\.md --exclude README\.md --exclude AGENTS\.md --exclude CLAUDE\.md --exclude \.installed --exclude \.env\.deploy --exclude ^sitemap\.xml$"

# data/ și install* NICIODATĂ nu se trimit pe server
EXCLUDE_DATA="--exclude ^data/ --exclude ^install\.php --exclude ^install/"

# ─── Deploy ──────────────────────────────────────────────────────────────────
echo ""
echo "🚀 GhidRomânesc — Deploy FTP"
echo "   Host:   $FTP_HOST:$FTP_PORT"
echo "   User:   $FTP_USER"
echo "   Remote: $FTP_REMOTE"
echo "   Local:  $LOCAL_DIR"
echo ""
echo "⏳ Se uploadează fișierele... (prima dată poate dura 2-5 minute)"
echo ""

lftp -c "
set ssl:verify-certificate no
set ftp:ssl-force true
set ftp:ssl-protect-data true
set net:timeout 30
set net:max-retries 3
set ftp:passive-mode on
open ftp://$FTP_HOST:$FTP_PORT
user '$FTP_USER' '$FTP_PASS'

# Mirror: sincronizează LOCAL → REMOTE (upload doar fișierele modificate)
# ATENȚIE: --delete e INTENȚIONAT omis — data/ de pe server nu trebuie ștersă niciodată de deploy
mirror \
  --reverse \
  --verbose=1 \
  --parallel=3 \
  $EXCLUDE_ARGS \
  $EXCLUDE_DATA \
  '$LOCAL_DIR/' \
  '$FTP_REMOTE/'

quit
"

echo ""
echo "✅ Deploy finalizat!"
echo "   Site: https://ghidromanesc.ro"
