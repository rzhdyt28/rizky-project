# _template — Cara Menambah Modul/Project Baru

Folder ini adalah cetakan untuk project baru apa pun yang ingin kamu tambahkan
ke platform (misal: toko online, blog, sistem kasir, apa saja).

Sistemnya otomatis: `ModuleServiceProvider` membaca semua folder di
`app/Modules/*` — begitu folder modulmu ada, routes dan migrations-nya
langsung aktif TANPA daftar manual di file mana pun.

---

## Langkah membuat modul baru (contoh: modul "Toko")

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

## Checklist sebelum dianggap selesai

- [ ] `php artisan migrate` sukses tanpa error
- [ ] `php artisan route:list | grep toko` menampilkan endpoint-mu
- [ ] Endpoint dites via Postman/curl (401 kalau belum login = normal untuk route auth)
- [ ] Model pakai `BelongsToTenant` bila data per pelanggan
- [ ] Tulis keputusan penting di README modul
