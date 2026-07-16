#!/usr/bin/env bash
#
# =============================================================================
# BAB 11 — SCRIPT OTOMATIS SETUP NGINX + SSL
# =============================================================================
# Membuat konfigurasi reverse proxy Nginx untuk dashboard, lalu memasang
# SSL gratis via Let's Encrypt.
#
# Cara pakai (di VPS, setelah A record domain sudah diarahkan ke IP VPS):
#   chmod +x deploy/setup-nginx-ssl.sh
#   ./deploy/setup-nginx-ssl.sh namadomainanda.com
#
set -euo pipefail

DOMAIN="${1:-}"
DASHBOARD_PORT="${DASHBOARD_PORT:-3000}"

log()  { echo -e "\033[1;34m[setup-nginx-ssl]\033[0m $1"; }
err()  { echo -e "\033[1;31m[setup-nginx-ssl][ERROR]\033[0m $1" >&2; }

if [[ -z "$DOMAIN" ]]; then
  err "Domain belum diisi."
  echo "Cara pakai: ./deploy/setup-nginx-ssl.sh namadomainanda.com"
  exit 1
fi

log "Domain target: $DOMAIN (dan www.$DOMAIN)"
log "Dashboard diasumsikan berjalan di port: $DASHBOARD_PORT"

# ---------------------------------------------------------------------------
log "1/4 — Install Nginx (jika belum ada)"
if ! command -v nginx >/dev/null 2>&1; then
  sudo apt update -y
  sudo apt install -y nginx
fi
sudo systemctl enable nginx
sudo systemctl start nginx

# ---------------------------------------------------------------------------
log "2/4 — Menulis konfigurasi reverse proxy"
CONFIG_PATH="/etc/nginx/sites-available/auto-apply"

sudo tee "$CONFIG_PATH" > /dev/null << EOF
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};

    location / {
        proxy_pass http://127.0.0.1:${DASHBOARD_PORT};
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF

sudo ln -sf "$CONFIG_PATH" /etc/nginx/sites-enabled/auto-apply

log "Menguji konfigurasi Nginx..."
sudo nginx -t
sudo systemctl reload nginx
log "Nginx reverse proxy aktif untuk $DOMAIN -> 127.0.0.1:${DASHBOARD_PORT}"

# ---------------------------------------------------------------------------
log "3/4 — Install Certbot"
if ! command -v certbot >/dev/null 2>&1; then
  sudo apt install -y certbot python3-certbot-nginx
fi

# ---------------------------------------------------------------------------
log "4/4 — Memasang SSL Let's Encrypt"
log "Pastikan A record ${DOMAIN} dan www.${DOMAIN} sudah mengarah ke IP VPS ini sebelum lanjut."
read -rp "Lanjutkan pemasangan SSL sekarang? (y/n) " confirm
if [[ "$confirm" == "y" || "$confirm" == "Y" ]]; then
  sudo certbot --nginx -d "$DOMAIN" -d "www.$DOMAIN"
  log "Menguji auto-renewal SSL..."
  sudo certbot renew --dry-run
  log "SSL aktif. Dashboard bisa diakses di: https://$DOMAIN"
else
  log "Pemasangan SSL dilewati. Jalankan manual nanti dengan:"
  log "  sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN"
fi
