#!/usr/bin/env bash
# deploy/scripts/add-custom-domain.sh
# Pola C — dijalankan tiap kali pelanggan Platinum menambah domainnya sendiri.
# Prasyarat: DNS pelanggan (A/CNAME) sudah mengarah ke IP VPS ini.
#
# Pemakaian:
#   sudo ./add-custom-domain.sh reza-dan-mega.com <tenant_id>
set -euo pipefail

DOMAIN="${1:?domain wajib, contoh: reza-dan-mega.com}"
TENANT="${2:?tenant_id wajib}"

# 1) Daftarkan ke tabel domains (stancl/tenancy)
cd /var/www/rizky-project-api/current
php artisan tinker --execute="
  \App\Models\Domain::updateOrCreate(
    ['domain' => '$DOMAIN'],
    ['tenant_id' => '$TENANT', 'is_custom' => true]
  );
"

# 2) Terbitkan sertifikat SSL untuk domain ini (HTTP-01 via webroot)
sudo certbot certonly --webroot -w /var/www/certbot \
    -d "$DOMAIN" --non-interactive --agree-tos -m admin@undanganku.com

# 3) Tambahkan blok server Nginx khusus untuk sertifikat domain ini
CONF="/etc/nginx/sites-available/custom-$DOMAIN.conf"
sudo tee "$CONF" >/dev/null <<NGINX
server {
    listen 443 ssl;
    http2 on;
    server_name $DOMAIN;
    ssl_certificate     /etc/letsencrypt/live/$DOMAIN/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/$DOMAIN/privkey.pem;
    include /etc/nginx/snippets/undangan-saas-body.conf;
}
NGINX
sudo ln -sf "$CONF" /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

echo "✓ Custom domain $DOMAIN aktif untuk tenant $TENANT"
