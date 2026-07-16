#!/usr/bin/env bash
#
# =============================================================================
# BAB 11 — SCRIPT OTOMATIS PERSIAPAN VPS
# =============================================================================
# Menjalankan semua langkah persiapan server yang sebelumnya manual:
# swap, Node.js, PM2, dan dependency sistem untuk Playwright/Chromium.
#
# Cara pakai (di VPS, sebagai user dengan akses sudo):
#   chmod +x deploy/setup-vps.sh
#   ./deploy/setup-vps.sh
#
set -euo pipefail

SWAP_SIZE_GB="${SWAP_SIZE_GB:-2}"
NODE_MAJOR="${NODE_MAJOR:-20}"

log()  { echo -e "\033[1;34m[setup-vps]\033[0m $1"; }
warn() { echo -e "\033[1;33m[setup-vps][WARN]\033[0m $1"; }
err()  { echo -e "\033[1;31m[setup-vps][ERROR]\033[0m $1" >&2; }

if [[ $EUID -eq 0 ]]; then
  warn "Sedang berjalan sebagai root. Disarankan pakai user non-root dengan sudo."
fi

# ---------------------------------------------------------------------------
log "1/6 — Update paket sistem"
sudo apt update -y && sudo apt upgrade -y

# ---------------------------------------------------------------------------
log "2/6 — Menambahkan swap ${SWAP_SIZE_GB}GB (jika belum ada)"
if swapon --show | grep -q "/swapfile"; then
  log "Swap sudah aktif, dilewati."
else
  sudo fallocate -l "${SWAP_SIZE_GB}G" /swapfile
  sudo chmod 600 /swapfile
  sudo mkswap /swapfile
  sudo swapon /swapfile
  if ! grep -q "/swapfile" /etc/fstab; then
    echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
  fi
  log "Swap aktif:"
  free -h
fi

# ---------------------------------------------------------------------------
log "3/6 — Install Node.js ${NODE_MAJOR}.x (jika belum ada versi yang sesuai)"
if command -v node >/dev/null 2>&1 && [[ "$(node -v | grep -oE '^v[0-9]+' | tr -d v)" -ge "$NODE_MAJOR" ]]; then
  log "Node.js sudah terpasang: $(node -v), dilewati."
else
  curl -fsSL "https://deb.nodesource.com/setup_${NODE_MAJOR}.x" | sudo -E bash -
  sudo apt install -y nodejs
fi
log "Versi Node.js: $(node -v)"
log "Versi npm: $(npm -v)"

# ---------------------------------------------------------------------------
log "4/6 — Install dependency sistem untuk build native module & Chromium"
sudo apt install -y git curl build-essential

# ---------------------------------------------------------------------------
log "5/6 — Install PM2 (process manager)"
if command -v pm2 >/dev/null 2>&1; then
  log "PM2 sudah terpasang, dilewati."
else
  sudo npm install -g pm2
fi

# ---------------------------------------------------------------------------
log "6/6 — Nonaktifkan service yang tidak perlu untuk hemat RAM (opsional)"
for svc in snapd; do
  if systemctl is-active --quiet "$svc" 2>/dev/null; then
    warn "Menonaktifkan service '$svc' untuk hemat RAM..."
    sudo systemctl stop "$svc" || true
    sudo systemctl disable "$svc" || true
  fi
done

log "Selesai. Cek RAM & swap final:"
free -h

log ""
log "Langkah selanjutnya:"
log "  1. git clone <repo-anda> && cd auto-apply-agent"
log "  2. npm install --production && npx playwright install --with-deps chromium"
log "  3. cp .env.example .env && nano .env"
log "  4. npm run init-db"
log "  5. Upload data/sessions dari laptop: scp -r data/sessions user@ip-vps:~/auto-apply-agent/data/"
log "  6. ./deploy/setup-nginx-ssl.sh namadomainanda.com"
log "  7. pm2 start ecosystem.config.js && pm2 save && pm2 startup"
