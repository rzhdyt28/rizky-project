#!/usr/bin/env bash
# deploy/scripts/deploy.sh — dijalankan di VPS untuk rilis versi baru.
# Pakai atomic-symlink supaya rollback mudah.
set -euo pipefail

APP_DIR=/var/www/rizky-project-api
RELEASES=$APP_DIR/releases
SHARED=$APP_DIR/shared
CURRENT=$APP_DIR/current
REPO=${REPO:-git@github.com:rzhdyt28/rizky-project-api.git}
BRANCH=${BRANCH:-main}
KEEP=5     # simpan 5 rilis terakhir untuk rollback cepat

mkdir -p "$RELEASES" "$SHARED/storage" "$SHARED/env"
[[ -f "$SHARED/env/.env" ]] || { echo "Isi $SHARED/env/.env dulu"; exit 1; }

TS=$(date +%Y%m%d%H%M%S)
NEW=$RELEASES/$TS
git clone --depth 1 -b "$BRANCH" "$REPO" "$NEW"
cd "$NEW"

ln -sfn "$SHARED/env/.env" .env
rm -rf storage && ln -sfn "$SHARED/storage" storage

composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build

php artisan migrate --force
php artisan storage:link || true
php artisan config:cache route:cache view:cache event:cache
php artisan optimize

ln -sfn "$NEW" "$CURRENT"

# reload PHP-FPM & restart worker + Horizon (kalau terpasang)
sudo systemctl reload php8.3-fpm
sudo systemctl restart laravel-queue.service || true
sudo systemctl restart horizon.service       || true

# rotasi rilis lama
ls -1dt "$RELEASES"/*/ | tail -n +$((KEEP+1)) | xargs -r rm -rf

echo "✓ Deploy $TS selesai"
