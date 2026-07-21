# Deploy Portofolio ke VPS — Step by Step

Target: `rizkyhidayat.web.id` menampilkan portofolio (Vue SPA + API Laravel)
di satu VPS. Skenario: VPS baru diinstal ulang bersih (**Ubuntu 24.04 LTS**
— LTS terbaru saat ini, didukung sampai 2029/2034 dengan ESM, dan memang
versi yang sudah terpasang di VPS ini sebelumnya, jadi tinggal reinstall ke
versi yang sama), 1 vCPU, ~1GB RAM. Semua perintah `sudo ...` dijalankan
sebagai user biasa ber-sudo (bukan root langsung), kecuali disebutkan lain.

> **Kenapa install ulang dulu?** VPS yang dipakai sebelumnya (202.155.19.165)
> ditemukan sudah disusupi worm cryptomining aktif (proses `crond`/`httpd`
> palsu, ratusan koneksi keluar port 22 ke IP acak, persistence lewat
> crontab `@reboot`). Jangan deploy apa pun di atas mesin yang statusnya
> begitu — install ulang dari panel provider dulu, baru lanjut dari sini.

---

## 0. Reinstall VPS + akses awal

1. Di panel provider VPS: **Reinstall OS** → pilih **Ubuntu 24.04 LTS**. Ini
   akan memberi IP yang sama dengan root password baru (dikirim provider,
   biasanya lewat email/panel).
2. Login pertama kali untuk memverifikasi akses:
   ```bash
   ssh root@<IP_VPS>
   ```
3. **Ganti password root** (jangan pakai bawaan provider lebih lama dari
   perlu):
   ```bash
   passwd
   ```

---

## 1. Hardening dasar (sebelum install apa pun)

Ini langkah yang tadi terlewat di VPS lama — kemungkinan besar itu sebabnya
bisa disusupi (password lemah/default + akses SSH password terbuka ke
publik).

```bash
# 1. Update sistem
apt update && apt upgrade -y

# 2. Buat user baru ber-sudo (jangan pakai root untuk kerja sehari-hari)
adduser deploy
usermod -aG sudo deploy

# 3. Salin SSH key kamu ke user baru (dari komputer lokal, BUKAN di VPS)
#    Jalankan ini di komputer lokal (PowerShell/Git Bash):
#      ssh-copy-id deploy@<IP_VPS>
#    Kalau ssh-copy-id tidak ada (Windows), copy manual:
#      type C:\Users\rizky\.ssh\id_ed25519.pub | ssh root@<IP_VPS> `
#        "mkdir -p /home/deploy/.ssh && cat >> /home/deploy/.ssh/authorized_keys"
#    lalu di VPS: chown -R deploy:deploy /home/deploy/.ssh && chmod 700 /home/deploy/.ssh && chmod 600 /home/deploy/.ssh/authorized_keys

# 4. Matikan login root & password auth via SSH — WAJIB
nano /etc/ssh/sshd_config
#   PermitRootLogin no
#   PasswordAuthentication no
systemctl restart ssh

# 5. Firewall — hanya buka yang perlu
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable

# 6. fail2ban — blokir brute-force otomatis
apt install -y fail2ban
systemctl enable --now fail2ban
```

Dari titik ini, **login pakai `ssh deploy@<IP_VPS>`** (bukan root lagi), dan
`sudo` untuk perintah admin.

---

## 2. DNS

Di panel domain `rizkyhidayat.web.id` (registrar/DNS manager kamu), tambah:

| Type | Name | Value          |
|------|------|----------------|
| A    | @    | `<IP_VPS>`     |
| A    | www  | `<IP_VPS>`     |

Cek sudah propagasi sebelum lanjut ke SSL (bisa 5 menit–beberapa jam):
```bash
# dari komputer lokal
nslookup rizkyhidayat.web.id
```

---

## 3. Install stack di VPS

```bash
sudo apt update

# Swap 2GB — WAJIB di VPS 1GB RAM, kalau tidak build/composer bisa OOM-kill
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab

# Nginx, MySQL, Redis
sudo apt install -y nginx mysql-server redis-server

# PHP 8.3 + ekstensi yang dipakai Laravel/Filament
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl \
  php8.3-redis unzip git

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node 20 (untuk build Vue — bisa juga build di lokal lalu upload dist/,
# tapi build langsung di VPS lebih simpel untuk auto-deploy nanti)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Certbot untuk SSL
sudo apt install -y certbot python3-certbot-nginx
```

Amankan MySQL dan buat database:
```bash
sudo mysql_secure_installation

sudo mysql -e "
CREATE DATABASE rizky_project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rizky_project'@'localhost' IDENTIFIED BY 'GANTI_DENGAN_PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON rizky_project.* TO 'rizky_project'@'localhost';
FLUSH PRIVILEGES;
"
```

---

## 4. Deploy backend (Laravel API)

```bash
sudo mkdir -p /var/www/rizky-project-api
sudo chown deploy:deploy /var/www/rizky-project-api
git clone https://github.com/rzhdyt28/rizky-project.git /var/www/rizky-project-api
cd /var/www/rizky-project-api

composer install --no-dev --optimize-autoloader

cp .env.example .env   # kalau belum ada .env.example, buat manual pakai isi di bawah
```

Isi `.env` produksi (edit sesuai):
```env
APP_NAME="Rizky Hidayat"
APP_ENV=production
APP_KEY=                     # diisi otomatis oleh artisan key:generate di bawah
APP_DEBUG=false
APP_URL=https://rizkyhidayat.web.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rizky_project
DB_USERNAME=rizky_project
DB_PASSWORD=GANTI_DENGAN_PASSWORD_KUAT

SESSION_DRIVER=cookie
SESSION_DOMAIN=.rizkyhidayat.web.id
SESSION_SECURE_COOKIE=true

SANCTUM_STATEFUL_DOMAINS=rizkyhidayat.web.id,www.rizkyhidayat.web.id
FRONTEND_URL=https://rizkyhidayat.web.id

QUEUE_CONNECTION=database
CACHE_STORE=database
```

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class="Database\Seeders\PortfolioSeeder" --force
php artisan storage:link

# Permission (WAJIB, kalau tidak Laravel error 500 nulis log/cache)
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Cache konfigurasi untuk produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Salin foto profil, CV, dan foto dokumentasi kerja (yang sebelumnya sudah
disiapkan di lokal) ke server — dari **komputer lokal**:
```bash
scp -r "c:/laragon/www/rizky-project/storage/app/public/portfolio" deploy@<IP_VPS>:/var/www/rizky-project-api/storage/app/public/
```

---

## 5. Build & deploy frontend (Vue)

```bash
sudo mkdir -p /var/www/rizky-project-web
sudo chown deploy:deploy /var/www/rizky-project-web
git clone https://github.com/rzhdyt28/rizky-project-web.git /tmp/rizky-project-web-src
cd /tmp/rizky-project-web-src
```

Buat `.env.production`:
```env
VITE_API_URL=
VITE_CENTRAL_HOSTS=rizkyhidayat.web.id,www.rizkyhidayat.web.id
```

```bash
npm ci
npm run build

# Isi dist/ jadi yang disajikan Nginx
rsync -a --delete dist/ /var/www/rizky-project-web/
```

---

## 6. Konfigurasi Nginx

`sudo nano /etc/nginx/sites-available/rizkyhidayat.web.id`:
```nginx
server {
    listen 80;
    server_name rizkyhidayat.web.id www.rizkyhidayat.web.id;

    root /var/www/rizky-project-web;
    index index.html;

    # SPA Vue — semua route non-file jatuh ke index.html
    location / {
        try_files $uri $uri/ /index.html;
    }

    # API Laravel
    location ~ ^/(api|sanctum) {
        root /var/www/rizky-project-api/public;
        try_files $uri /index.php?$query_string;

        location ~ \.php$ {
            fastcgi_pass unix:/run/php/php8.3-fpm.sock;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    }

    # File publik (foto profil, CV, dokumentasi kerja)
    location /storage {
        alias /var/www/rizky-project-api/storage/app/public;
    }

    client_max_body_size 20M;
}
```

```bash
sudo ln -s /etc/nginx/sites-available/rizkyhidayat.web.id /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

---

## 7. SSL (Let's Encrypt)

```bash
sudo certbot --nginx -d rizkyhidayat.web.id -d www.rizkyhidayat.web.id
```
Certbot otomatis mengubah blok Nginx di atas jadi `listen 443 ssl` + redirect
80→443, dan memasang cron/systemd timer untuk perpanjangan otomatis.

---

## 8. Verifikasi

```bash
curl -I https://rizkyhidayat.web.id                    # 200, Vue landing
curl -I https://rizkyhidayat.web.id/api/portfolio       # 200 JSON
curl -I https://rizkyhidayat.web.id/storage/portfolio/profile.png  # 200 image
```

Buka `https://rizkyhidayat.web.id/portfolio` di browser — harus tampil sama
seperti hasil clone di `localhost:5173/portfolio`.

---

## 9. (Opsional, untuk nanti) Auto-deploy via GitHub Actions

Setelah semua di atas jalan manual dan kamu paham alurnya, langkah
selanjutnya yang bisa dibuatkan: workflow `.github/workflows/deploy.yml` di
kedua repo yang otomatis `git pull` + `composer install` / `npm run build`
+ `rsync` ke VPS setiap push ke `main`, pakai SSH deploy key khusus CI
(bukan key pribadimu). Bilang saja kalau sudah siap ke tahap ini.
