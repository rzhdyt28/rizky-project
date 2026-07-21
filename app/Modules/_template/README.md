# _template — Cara Menambah Modul/Project Baru

Folder ini adalah cetakan untuk project baru apa pun yang ingin kamu tambahkan
ke platform (misal: toko online, blog, sistem kasir, apa saja).

Sistemnya otomatis: `ModuleServiceProvider` membaca semua folder di
`app/Modules/*` — begitu folder modulmu ada, routes dan migrations-nya
langsung aktif TANPA daftar manual di file mana pun.

---

## PENTING — sebelum mulai: ini modul baru, atau project baru?

Ada 2 jenis penambahan yang BEDA TOTAL, dan salah pilih artinya kamu bikin
data nyasar ke database yang salah:

1. **Modul baru DI DALAM produk yang sama** (mis. nambah fitur baru buat
   pelanggan Undangan yang sudah ada) → ikuti langkah standar di bawah,
   pakai `BelongsToTenant` seperti biasa, connection ikut default produk itu
   (mis. `undangan`).
2. **Project baru yang independen** (produk terpisah dengan bisnis/pelanggan
   sendiri, mis. "Skripsi", "Bisnis") → **JANGAN** pakai `BelongsToTenant`/
   `tenants` punya Undangan. Project itu butuh database sendiri + auth/billing
   sendiri. Lihat bagian **["Project baru yang independen"](#project-baru-yang-independen-database-sendiri)**
   di bawah — jangan lanjut ke langkah standar dulu.

**Kenapa dipisah begini** (riwayat keputusan, supaya tidak ditanyakan ulang):
repo ini awalnya 1 database untuk semua modul (termasuk Portfolio — situs
personal, bukan SaaS — yang numpang pakai `tenant_id`/`tenants` milik
Undangan cuma karena copy-paste template, BUKAN kebutuhan asli, dan ini
sempat menyebabkan bug nyata). Project-project berikutnya (Undangan, Skripsi,
Bisnis, dst) sudah disepakati sebagai **produk-produk independen, bukan
fitur dari 1 platform** — masing-masing database sendiri supaya tidak
"berat"/"ruwet" dalam 1 database besar, dan tiap produk bisa berdiri sendiri
(dijual/dipisah/di-deploy terpisah kapan saja tanpa membongkar produk lain).

---

## Langkah membuat modul baru DI PRODUK YANG SAMA (contoh: modul "Toko" di dalam Undangan)

### 1. Copy folder ini

```bash
cp -r app/Modules/_template app/Modules/Toko
```

Aturan nama: PascalCase (`Toko`, `BlogPribadi`, `SistemKasir`).
URL API-nya otomatis jadi kebab-case: `/api/toko`, `/api/blog-pribadi`.

### 2. Isi tiap folder — apa yang ditaruh di mana

```
app/Modules/Toko/
├── Models/                  ← Eloquent model milik modul ini saja
│   └── Product.php            (pakai trait BelongsToTenant kalau datanya per-pelanggan)
├── Http/Controllers/        ← Controller API modul ini
│   └── ProductController.php
├── Policies/                ← (opsional) aturan siapa boleh apa
│   └── ProductPolicy.php
├── database/migrations/     ← tabel-tabel milik modul ini
│   └── 2026_XX_XX_create_products_table.php
├── routes.php               ← daftar endpoint modul (WAJIB ada)
└── README.md                ← catat keputusan desainmu di sini
```

### 3. Minimal yang harus dibuat

**a. Migration** — `database/migrations/2026_XX_XX_create_products_table.php`.
Kalau data milik per-pelanggan (multi-tenant), tambahkan kolom:
```php
$table->string('tenant_id');
$table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
```

**b. Model** — `Models/Product.php`:
```php
<?php

namespace App\Modules\Toko\Models;

use App\Core\Concerns\BelongsToTenant;   // <- otomatis scoping per tenant
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use BelongsToTenant;   // hapus baris ini kalau data global (bukan per pelanggan)

    protected $guarded = [];
}
```

**c. Controller** — `Http/Controllers/ProductController.php`:
```php
<?php

namespace App\Modules\Toko\Http\Controllers;

use App\Modules\Toko\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    public function index()          { return Product::latest()->paginate(20); }
    public function store(Request $r){ return Product::create($r->validate(['name' => 'required'])); }
}
```

**d. Routes** — `routes.php` (prefix `/api/toko` sudah otomatis):
```php
<?php

use App\Modules\Toko\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products',  [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
});
```

### 4. Jalankan migrate

```bash
php artisan migrate
```
Migration modul otomatis terbaca (tidak perlu pindah file ke database/migrations pusat).

### 5. (Opsional) Tambahan yang sering diperlukan

| Kebutuhan                         | Yang dibuat                                              |
|-----------------------------------|----------------------------------------------------------|
| Batasi akses per role             | middleware `role:super-admin` di routes.php               |
| Batasi kuota per paket            | panggil `App\Core\Services\PlanLimitService`             |
| Kelola datanya dari admin panel   | buat Filament Resource di `app/Filament/Resources/`      |
| Log perubahan data                | trait `Spatie\Activitylog\Traits\LogsActivity` di model  |
| Aturan kepemilikan (policy)       | buat di `Policies/`, daftarkan di `AuthServiceProvider`  |
| Halaman frontend                  | buat folder modul kembar di repo `rizky-project-web`: `src/modules/toko/` |

### 6. Frontend-nya (repo rizky-project-web)

Di repo Vue, copy `src/modules/_template` → `src/modules/toko`, buat halaman,
daftarkan route-nya di `src/router/index.js`, lalu fetch ke `/api/toko/...`
lewat `src/shared/api/client.js`. Panduan detail ada di README repo web.

---

## Project baru yang independen (database sendiri)

Contoh nyata yang sudah jalan di repo ini: `db_undangan` (Core + modul
Invitation) dan `db_portfolio` (modul Portfolio) — lihat `config/database.php`
untuk definisinya. Ikuti pola yang sama untuk project baru (mis. "Skripsi"):

### 1. Tambah connection baru di `config/database.php`

Copy blok `undangan`/`portfolio` yang sudah ada, ganti nama & env var:
```php
'skripsi' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_SKRIPSI_DATABASE', 'db_skripsi'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '', 'prefix_indexes' => true, 'strict' => true, 'engine' => null,
],
```
Tambah `DB_SKRIPSI_DATABASE=db_skripsi` di `.env`, lalu buat database-nya:
`mysql -u root -e "CREATE DATABASE db_skripsi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"`

### 2. Copy `app/Core` jadi auth+billing milik project ini sendiri

```bash
cp -r app/Core app/Modules/Skripsi/Core
```
Ganti namespace `App\Core` → `App\Modules\Skripsi\Core` di semua file hasil
copy, dan tambah `protected $connection = 'skripsi';` di tiap model
(`User`, `Tenant`, `Plan`, `Subscription`, `Payment`, `Coupon`) — **JANGAN**
share `app/Core` yang lama, supaya login & billing 2 project benar-benar
terpisah (tidak ada 1 akun dipakai lintas produk).

### 3. Semua model & migration project ini set `$connection`

```php
class Product extends Model
{
    protected $connection = 'skripsi';   // <- WAJIB, bukan BelongsToTenant
    protected $guarded = [];
}
```
Migration juga set `protected $connection = 'skripsi';` di class Migration-nya
(bukan lewat flag `--database` tiap kali) — supaya `php artisan migrate` polos
otomatis menulis ke database yang benar. Contoh nyata:
`app/Modules/Portfolio/database/migrations/2026_01_01_000005_create_portfolio_tables.php`.

### 4. Routes project ini

`app/Modules/Skripsi/routes.php` + `app/Modules/Skripsi/Core/routes.php`
(auth/checkout versi Skripsi sendiri) — prefix `/api/skripsi` otomatis via
`ModuleServiceProvider`, tidak perlu daftar manual.

### 5. Filament Resource project ini — dikelompokkan per folder

Resource admin (`app/Filament/Resources/`) dikelompokkan per project dalam
subfolder, BUKAN rata semua di 1 folder — supaya gampang dibaca begitu
project bertambah. Contoh nyata yang sudah ada: `Resources/Undangan/*` dan
`Resources/Portfolio/*`.
```
app/Filament/Resources/
├── Undangan/PlanResource.php, InvitationResource.php, ...
├── Portfolio/PortfolioProfileResource.php, ...
└── Skripsi/ProductResource.php, ...        <- project baru taruh di sini
```
Namespace class-nya ikut folder (`App\Filament\Resources\Skripsi\ProductResource`,
Pages-nya `App\Filament\Resources\Skripsi\ProductResource\Pages\...`).
`AdminPanelProvider.php` **tidak perlu diubah** — `discoverResources()` Filament
memindai subfolder secara rekursif dan menghitung namespace otomatis dari
lokasi file, jadi taruh di folder yang benar = otomatis kedeteksi.

### 6. Midtrans (kalau project ini butuh pembayaran)

**Default: pakai 1 akun Midtrans yang sama untuk SEMUA project** (kunci
`MIDTRANS_SERVER_KEY`/`MIDTRANS_CLIENT_KEY` di `.env`, dipakai bersama lewat
`App\Core\Http\Controllers\CheckoutController` versi masing-masing project).
Alasan: settlement-nya tetap ke 1 orang/rekening yang sama, jadi daftar akun
merchant baru tiap project cuma menambah friksi tanpa manfaat.

Supaya transaksi antar project tidak tabrakan/ketuker, **`order_id` yang
dikirim ke Midtrans WAJIB diberi prefix nama project** (mis. `undangan-{id}`,
`skripsi-{id}`). Webhook (`/api/payments/midtrans/webhook`, didaftarkan di
`Core/routes.php` versi tiap project) baca prefix itu untuk tahu project mana
yang harus di-update. Midtrans cuma bisa 1 URL webhook per akun — kalau lebih
dari 1 project sudah pakai Midtrans, webhook itu jadi 1 "pintu masuk" yang
meneruskan ke `Core` project yang benar berdasarkan prefix, BUKAN tiap
project punya webhook URL sendiri yang didaftarkan terpisah di Midtrans.

**Upgrade ke akun terpisah nanti** (kalau salah satu project jadi badan usaha
sendiri): tambah `MIDTRANS_SERVER_KEY_<PROJECT>`/`MIDTRANS_CLIENT_KEY_<PROJECT>`
khusus project itu di `.env`, pakai di `CheckoutController` versi project itu
saja — project lain tidak perlu dibongkar sama sekali.

### 7. Batasan penting

- **Tidak ada JOIN/FK lintas database** antar project — kalau butuh data dari
  project lain, query manual 2 tahap by ID (ambil ID dari 1 connection, cari
  ke connection lain), BUKAN relasi Eloquent `belongsTo`/`hasMany` biasa.
- Filament admin panel tetap 1 aplikasi (1 login Anda sebagai owner) —
  Resource tiap project cukup query ke connection masing-masing, tidak perlu
  aplikasi Filament terpisah per project.

---

## Checklist sebelum dianggap selesai

- [ ] `php artisan migrate` sukses tanpa error
- [ ] `php artisan route:list | grep toko` menampilkan endpoint-mu
- [ ] Endpoint dites via Postman/curl (401 kalau belum login = normal untuk route auth)
- [ ] Modul di produk yang sama → pakai `BelongsToTenant` bila data per pelanggan.
      Project independen baru → **jangan** pakai `BelongsToTenant`, pakai `$connection` sendiri (lihat bagian di atas)
- [ ] Tulis keputusan penting di README modul
