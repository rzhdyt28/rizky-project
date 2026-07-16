# Menghubungkan Vue Terpisah ke API (CORS + Sanctum)

## Saat development (paling sering dipakai)
TIDAK PERLU setting apa-apa. `vite.config.js` di repo web sudah mem-proxy
`/api` dan `/sanctum` ke `http://127.0.0.1:8000`. Cukup:
1. Repo API : `php artisan serve`
2. Repo web : `npm run dev` -> buka http://localhost:5173

## Saat produksi (domain berbeda: rizky.com -> api.rizky.com)
3 hal di sisi API:

1. `config/cors.php` — ganti dengan isi `config/cors_snippet.php`
   (allowed_origins = FRONTEND_URL, supports_credentials = true).
2. `.env` produksi:
   ```env
   FRONTEND_URL=https://rizky.com
   SANCTUM_STATEFUL_DOMAINS=rizky.com,www.rizky.com,*.rizky.com
   SESSION_DOMAIN=.rizky.com      # titik di depan = berlaku utk semua subdomain
   SESSION_SECURE_COOKIE=true
   ```
3. `bootstrap/app.php` -> `$middleware->statefulApi();` (lihat BOOTSTRAP-REGISTRATION.md)

Di sisi web (`.github/workflows/deploy.yml`):
```
VITE_API_URL=https://api.rizky.com
VITE_CENTRAL_HOSTS=rizky.com,www.rizky.com
```

## Alur auth dari Vue
```js
await csrf();                                  // GET /sanctum/csrf-cookie
await api.post('/api/auth/login', {...});      // session cookie tersimpan
await api.get('/api/auth/me');                 // request selanjutnya otomatis terautentikasi
```
Sudah dibungkus rapi di `src/shared/stores/auth.js` — tinggal pakai.

## Catatan custom domain pelanggan (Pola C)
Sanctum cookie TIDAK berlaku lintas domain yang benar-benar beda
(custom-domain.com tidak bisa share session .rizky.com). Ini TIDAK masalah:
halaman undangan publik memang tanpa login; form RSVP/guestbook adalah endpoint
publik ber-throttle. Login/dashboard selalu lewat rizky.com.
