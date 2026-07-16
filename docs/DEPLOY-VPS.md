# Deploy Semua di Satu VPS (API + Filament + Vue static)

VPS 1 GB Ubuntu 22.04. Vue disajikan sebagai file static oleh Nginx (ringan,
tanpa Node runtime). Auto Apply Agent TIDAK di VPS ini (jalankan di lokal,
sync SQLite-nya — lihat README).

## Ringkasan langkah (detail teknis identik pola sebelumnya)

1. **DNS**: A record `@`, `www`, `api`, `admin`, dan `*` (wildcard) -> IP VPS.
2. **Server**: install nginx, mysql, redis, php8.3-fpm (+ekstensi), composer,
   certbot (+plugin dns-cloudflare), node 20 (untuk build lokal CI saja,
   sebenarnya opsional di VPS), swap 2GB WAJIB, ufw, fail2ban.
   PHP-FPM: pm=ondemand, max_children=8.
3. **Folder**:
   - `/var/www/rizky-project-api/{releases,shared,current}` (atomic deploy)
   - `/var/www/rizky-project-web/` (isi dist/ dari CI web)
   - `/var/www/certbot/` (ACME webroot)
4. **SSL**: wildcard `rizky.com` + `*.rizky.com` via certbot DNS-01 (Cloudflare).
   Simpan params ke `/etc/nginx/snippets/rizky-ssl.conf`
   (contoh: deploy/nginx/snippets-rizky-ssl.conf).
5. **Nginx**: pasang `deploy/nginx/rizky-project.conf` -> sites-enabled,
   hapus default, `nginx -t && systemctl reload nginx`.
6. **Env produksi API** (`shared/env/.env`): APP_ENV=production, DB baru
   `rizky_project`, FRONTEND_URL=https://rizky.com,
   SANCTUM_STATEFUL_DOMAINS=rizky.com,*.rizky.com, SESSION_DOMAIN=.rizky.com,
   Midtrans production keys.
7. **Deploy pertama API**: manual `bash deploy/scripts/deploy.sh` (repo private:
   pasang deploy key). Selanjutnya otomatis via GitHub Actions.
8. **Deploy web**: push repo web -> Actions build -> rsync dist/ ke
   `/var/www/rizky-project-web/` otomatis.
9. **Services**: `deploy/systemd/*` -> queue worker + scheduler; Horizon
   opsional (`composer require laravel/horizon`) hanya di VPS ini.
10. **Custom domain pelanggan (Pola C)**:
    `sudo deploy/scripts/add-custom-domain.sh domain.com <tenant_id>` —
    domain masuk tabel `domains`, SSL diterbitkan, Nginx blok baru dibuat.
    Vue-nya otomatis tampil karena server block default menyajikan dist/.

## Cek akhir
```bash
curl -I https://rizky.com                   # 200 (Vue landing)
curl -I https://api.rizky.com/api/portfolio # 200 JSON
curl -I https://admin.rizky.com/admin       # 302 login Filament
curl -I https://apa-saja.rizky.com          # 200 (Vue tenant router)
free -h && htop                             # pantau RAM
```
