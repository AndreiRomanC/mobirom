#!/usr/bin/env bash
# ============================================================================
#  Deploy "stocks" (InvestIQ) to Hostinger via FTP.
#  Uploads the whole project tree EXCEPT api/cache/, the build zip, and junk.
#
#  IMPORTANT (learned the hard way — see memory "deploy-ftp-stocks"):
#   * Host is  andivio.com  — "ftp.andivio.com" does NOT resolve (NXDOMAIN).
#   * Hostinger anti-brute-force bans the OUTBOUND IP after several rapid
#     FTP logins → you then get "530 Login incorrect" even with a good password.
#     => This script opens ONE session. Do NOT loop / retry-hammer it.
#   * Static files (index.html) may be LiteSpeed-cached; api/api.php is not.
#
#  Usage:  bash deploy.sh
# ============================================================================
set -u

# ---- credentials / target (edit these) -------------------------------------
#  Goal: keep the app at  https://andivio.com/stocks/
#  The .stocks account is chrooted straight to public_html/stocks, so REMOTE_DIR="." lands there.
FTP_HOST="andivio.com"                 # NOT ftp.andivio.com (that host is NXDOMAIN — confirmed 2026-08-08)
FTP_USER="u190087235.andrei"           # home = public_html (root), so deploy into the stocks/ subfolder
FTP_PASS='Length1234@'                 # single-quoted so !, @, # etc. stay literal
REMOTE_DIR="stocks"                    # this account lands in public_html → go into stocks/  (URL: https://andivio.com/stocks/)
LOCAL_DIR="$(cd "$(dirname "$0")" && pwd)"

# ---- alternative account (public_html root) — deploy to /stocks from there --
# FTP_USER="u190087235.andrei"
# FTP_PASS='stocks1234!@'
# REMOTE_DIR="stocks"                  # this account lands in public_html, so go into the stocks subfolder

command -v lftp >/dev/null 2>&1 || { echo "❌ lftp not installed (brew install lftp)"; exit 1; }

echo "→ Deploying $LOCAL_DIR  →  ftp://$FTP_USER@$FTP_HOST/$REMOTE_DIR"

lftp -u "$FTP_USER,$FTP_PASS" "$FTP_HOST" <<LFTP
set ftp:ssl-allow no
set ftp:passive-mode true
set net:max-retries 1
set net:timeout 25
set net:reconnect-interval-base 8
set cmd:fail-exit yes
lcd "$LOCAL_DIR"
mirror --reverse --only-newer --verbose \
  --exclude ^api/cache/ \
  --exclude-glob *.zip \
  --exclude-glob *.sh \
  --exclude-glob .DS_Store \
  --exclude-glob .git* \
  ./ "$REMOTE_DIR"
bye
LFTP

rc=$?
if [ $rc -eq 0 ]; then
  echo "✅ Upload OK. Live: https://andivio.com/${REMOTE_DIR#.}${REMOTE_DIR:+/}  (hard-refresh: Cmd+Shift+R)"
else
  echo "❌ Failed (exit $rc). If '530 Login incorrect': wrong user/pass OR the IP is temporarily banned"
  echo "   (wait it out, or upload the zip manually via hPanel File Manager). Do NOT re-run repeatedly."
fi
exit $rc
