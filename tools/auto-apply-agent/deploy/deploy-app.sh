#!/usr/bin/env bash
#
# =============================================================================
# BAB 11 — SCRIPT DEPLOY / REDEPLOY APLIKASI
# =============================================================================
# Dipakai untuk deploy pertama kali MAUPUN update kode di kemudian hari.
# Aman dijalankan berulang kali (idempotent).
#
# Cara pakai (di VPS, di dalam folder project):
#   chmod +x deploy/deploy-app.sh
#   ./deploy/deploy-app.sh
#
set -euo pipefail

log()  { echo -e "\033[1;34m[deploy]\033[0m $1"; }
err()  { echo -e "\033[1;31m[deploy][ERROR]\033[0m $1" >&2; }

if [[ ! -f "package.json" ]]; then
  err "Jalankan script ini dari root folder project (tempat package.json berada)."
  exit 1
fi

if [[ ! -f ".env" ]]; then
  err ".env belum ada. Jalankan: cp .env.example .env lalu isi konfigurasinya."
  exit 1
fi

# ---------------------------------------------------------------------------
if [[ -d ".git" ]]; then
  log "1/5 — Menarik update kode terbaru dari git"
  git pull
else
  log "1/5 — Bukan git repo, lewati git pull (asumsi kode sudah di-upload manual/scp)"
fi

# ---------------------------------------------------------------------------
log "2/5 — Install dependencies"
npm install --production

# ---------------------------------------------------------------------------
log "3/5 — Install/verifikasi Chromium untuk Playwright"
npx playwright install --with-deps chromium

# ---------------------------------------------------------------------------
log "4/5 — Inisialisasi/migrasi database (aman dijalankan berulang)"
npm run init-db

# ---------------------------------------------------------------------------
log "5/5 — (Re)start proses via PM2"
if pm2 list | grep -q "auto-apply"; then
  pm2 restart ecosystem.config.js
else
  pm2 start ecosystem.config.js
fi
pm2 save

log "Deploy selesai. Cek status:"
pm2 status
log ""
log "Cek log dengan: pm2 logs auto-apply-scheduler"
log "Cek RAM dengan: free -h && pm2 monit"
