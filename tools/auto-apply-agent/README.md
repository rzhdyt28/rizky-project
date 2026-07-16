# Auto Apply Agent — LinkedIn, Jobstreet, Indeed, Glints

AI Agent semi-otomatis untuk pencarian & pelamaran kerja di 4 platform,
dengan modul deteksi lowongan palsu/scam. Dibangun per-bab dari nol sampai
deployment — tiap bab di bawah ini sesuai dengan modul kode yang sudah jadi.

> Pasangan dokumen: `AI_Agent_Auto_Apply_Kerja.docx` (konsep, arsitektur,
> strategi risiko per platform). File ini fokus ke **cara pakai kodenya**.

---

## Bab 0 — Memilih Provider LLM (Gratis vs Berbayar)

Project ini mendukung 4 provider LLM, diatur lewat `LLM_PROVIDER` di `.env`.
Semua modul lain (scam filter, matcher, cover letter generator, CV parser)
tidak perlu diubah sama sekali — mereka hanya memanggil `askJSON()`/`askText()`
dari `src/utils/llmClient.js`, yang menangani perbedaan format tiap provider
di baliknya.

| Provider | Biaya | Kuota gratis | Kartu kredit? | Rekomendasi |
|---|---|---|---|---|
| **Groq** | Gratis permanen | ~14.400 request/hari | Tidak perlu | **Direkomendasikan** — cepat, kuota jauh melebihi kebutuhan project ini |
| **Google Gemini** | Gratis permanen | ~1.500 request/hari, konteks 1M token | Tidak perlu | Alternatif kuat, terutama jika CV/deskripsi lowongan sangat panjang |
| **OpenRouter** | Gratis untuk model tertentu | Bervariasi per model (mis. DeepSeek R1) | Tidak perlu | Alternatif jika ingin coba banyak model open-source |
| **Anthropic (Claude)** | Berbayar | Tidak ada tier gratis permanen | Wajib | Kualitas tertinggi, dipakai jika sudah siap upgrade dari gratisan |

### Cara ganti provider

```bash
# .env
LLM_PROVIDER=groq
GROQ_API_KEY=isi_key_anda
```

Ganti `LLM_PROVIDER` ke `gemini`, `openrouter`, atau `anthropic`, lalu isi
API key yang sesuai (`GEMINI_API_KEY`, `OPENROUTER_API_KEY`, atau
`ANTHROPIC_API_KEY`). Tidak ada file lain yang perlu diubah.

### Cara dapat API key gratis

- **Groq**: [console.groq.com/keys](https://console.groq.com/keys) — daftar dengan email, langsung dapat key.
- **Gemini**: [aistudio.google.com/apikey](https://aistudio.google.com/apikey) — daftar dengan akun Google.
- **OpenRouter**: [openrouter.ai/keys](https://openrouter.ai/keys) — daftar dengan email, pilih model yang bertanda `:free`.

Setelah key terisi, jalankan `npm run check` untuk verifikasi provider aktif
sudah terbaca dengan benar, atau `npm run test-pipeline` untuk uji fungsional
langsung (scam filter, matching, cover letter) tanpa perlu scraping.

---

## Daftar Bab

| Bab | Isi | Lokasi Kode |
|---|---|---|
| 1 | Setup & Konfigurasi | `.env.example`, `data/profile.example.json` |
| 2 | Database | `src/db/` |
| 3 | CV Parser | `src/parser/cvParser.js` |
| 4 | Job Collector (robust: retry, multi-selector, anti-block) | `src/collector/`, `src/utils/scrapeHelpers.js` |
| 5 | Scam/Fake Job Filter (heuristik 2-tingkat + blacklist/whitelist + LLM) | `src/filter/scamFilter.js` |
| 6 | Matching Engine | `src/matcher/matchJob.js` |
| 7 | Cover Letter Generator | `src/generator/coverLetter.js` |
| 8 | Application Executor (robust: retry, deteksi redirect) | `src/executor/applyForm.js` |
| 9 | Dashboard Web | `src/dashboard/` |
| 10 | Scheduler (orkestrasi pipeline) | `src/scheduler.js` |
| 11 | Deployment Otomatis (script siap-jalan, bukan sekadar daftar perintah) | `deploy/` |

---

## Bab 1 — Setup & Konfigurasi

### 1.1 Prasyarat
- Node.js 20 LTS (`node -v`)
- Akun aktif di platform yang ingin dipakai
- API Key salah satu provider LLM — lihat **Bab 0** untuk pilihan gratis
- CV format PDF/DOCX

### 1.2 Instalasi

```bash
cd auto-apply-agent
npm install
npx playwright install --with-deps chromium
```

### 1.3 Environment variables

```bash
cp .env.example .env
nano .env
```

Variabel penting selain API key:
- `MAX_JOBS_PER_PLATFORM_PER_RUN` — batas lowongan diproses per platform per run (default 15, jaga tetap rendah).
- `MIN_DELAY_MS` / `MAX_DELAY_MS` — rentang jeda acak antar aksi (anti-deteksi bot).
- `MODE=review` — default aman, jangan ubah ke `auto` sebelum benar-benar yakin.

### 1.4 Profil kandidat

```bash
cp data/profile.example.json data/profile.json
nano data/profile.json
```

**Pencarian dengan banyak kata kunci sekaligus:** `search.<platform>.keywords`
menerima array, bukan cuma satu kata kunci. Cocok kalau Anda melamar untuk
beberapa peran serumpun sekaligus, misal:
```json
"linkedin": {
  "keywords": ["System Administrator", "IT Support", "IT Helpdesk", "Desktop Support"],
  "location": "Jabodetabek, Indonesia"
}
```
Tiap kata kunci dicari **berurutan** (bukan paralel, tetap hemat RAM), hasilnya
digabung dan di-dedup berdasarkan link sebelum masuk ke scam filter & matching
engine. Kuota `MAX_JOBS_PER_PLATFORM_PER_RUN` dibagi rata ke tiap kata kunci
(minimal 3 lowongan per kata kunci).

### 1.5 (Opsional) Daftar blacklist/whitelist perusahaan

```bash
cp data/company_lists.example.json data/company_lists.json
nano data/company_lists.json
```

Perusahaan di `whitelist` akan otomatis `PASS` tanpa panggil LLM (hemat biaya).
Perusahaan di `blacklist` akan otomatis `BLOCK`. Isi berdasarkan riset/pengalaman Anda sendiri.

---

## Bab 2 — Database

```bash
npm run init-db
```

Membuat `data/app.db` (SQLite) dengan 2 tabel: `applications` (tiap lowongan
yang diproses beserta status scam & status lamaran) dan `run_logs` (riwayat
tiap kali pipeline dijalankan, untuk audit).

Fungsi yang tersedia di `src/db/index.js`: `insertApplication`, `findByUrl`
(cegah duplikat), `updateApplicationStatus`, `listApplications` (dengan
filter), `getApplicationById`, `insertRunLog`, `getStats`.

---

## Bab 3 — CV Parser

```bash
node -e "import('./src/parser/cvParser.js').then(m => m.parseCV('./data/cv.pdf').then(console.log))"
```

Mendukung PDF (`pdf-parse`) dan DOCX (`mammoth`). Mengembalikan JSON
terstruktur: kontak, ringkasan, skill, pengalaman, pendidikan.

---

## Bab 4 — Job Collector (Robust)

Ini bagian yang paling banyak diperkuat. Alih-alih satu selector CSS statis
yang mudah patah, tiap collector (`src/collector/linkedin.js`,
`jobstreet.js`, `indeed.js`, `glints.js`) memakai `src/utils/scrapeHelpers.js`
yang menyediakan:

- **`trySelectors`** — mencoba beberapa kandidat selector berurutan, baru gagal total jika SEMUA kandidat tidak ketemu.
- **`extractJobCards`** — ekstraksi field per kartu lowongan dengan fallback selector per field, plus dedup otomatis berdasarkan link.
- **`withRetry`** — retry dengan backoff untuk kegagalan sementara (timeout jaringan dsb).
- **`detectBlockPage`** — deteksi captcha/halaman verifikasi; begitu terdeteksi, proses berhenti untuk sesi itu (tidak mencoba membypass).

### Login manual & simpan session (wajib, sekali per platform)

```bash
npm run save-session -- linkedin
npm run save-session -- jobstreet
npm run save-session -- indeed
npm run save-session -- glints
```

Browser terbuka -> login manual (termasuk OTP/2FA) -> tekan ENTER di terminal
setelah halaman utama tampil -> session tersimpan di `data/sessions/<platform>.json`.

**Catatan jujur:** selector CSS di tiap collector adalah kandidat terbaik
berdasarkan pola umum platform tersebut, bukan hasil inspeksi langsung ke
situs live (lingkungan development saya tidak berjaringan internet). Jika
`trySelectors` gagal total, pesan error di log akan memberi tahu persis
selector mana yang perlu Anda perbarui lewat DevTools browser.

### Bab 4a — Kalau login manual terjebak captcha terus-menerus (Import Cookies)

Beberapa situs (terutama Indeed, yang diproteksi Cloudflare-style) kadang
mendeteksi bahwa browser dikendalikan lewat automation protocol — **bukan**
mendeteksi captcha-nya salah dijawab. Akibatnya, captcha bisa tampak "tidak
merespons" meski sudah Anda selesaikan berkali-kali secara manual, karena
yang dicurigai adalah browser-nya, bukan jawaban captcha-nya.

`npm run save-session` sudah otomatis mencoba mengurangi sinyal ini (pakai
Chrome asli di laptop Anda kalau tersedia, mematikan flag automation
bawaan). Tapi kalau tetap terjebak, ada jalur yang lebih pasti berhasil:

1. Login ke platform target di browser **harian Anda yang biasa** (Chrome/Firefox/Edge normal — bukan yang dibuka script ini).
2. Pasang ekstensi export cookie, misalnya **Cookie-Editor** (tersedia di Chrome Web Store & Firefox Add-ons).
3. Di halaman platform yang sudah login, buka ekstensi tsb → **Export → JSON** → simpan filenya, mis. `data/cookies-raw/indeed.json`.
4. Jalankan:
   ```bash
   npm run import-cookies -- indeed data/cookies-raw/indeed.json
   ```
5. `data/sessions/indeed.json` otomatis dibuat dalam format yang dipahami Playwright.

Cara ini lebih pasti berhasil karena login sungguhan terjadi di browser
normal Anda yang sama sekali tidak punya sinyal automation — Playwright baru
"menyentuh" sesi tersebut setelah login sudah valid, bukan saat proses login
berlangsung.

**Catatan tambahan soal Indeed khususnya:** kalau session Indeed sebelumnya
gagal tervalidasi (macet di captcha), collector akan mengambil data dari
halaman yang sebenarnya belum login (tampilan terbatas untuk pengunjung
anonim) — ini bisa jadi penyebab tersembunyi kenapa data yang terambil
kosong/aneh, terpisah dari isu selector CSS. Pastikan `data/sessions/indeed.json`
benar-benar hasil dari sesi yang sudah login sebelum menjalankan pipeline.

---

## Bab 5 — Scam/Fake Job Filter

Tiga lapis, dari termurah ke termahal:

1. **Heuristik kata kunci 2-tingkat** — `RED_FLAG_KEYWORDS_HIGH` (langsung `BLOCK`, mis. "transfer dana", "wajib membayar", "kirim ktp dan rekening") dan `RED_FLAG_KEYWORDS_MEDIUM` (`REVIEW`, mis. "hanya chat wa", "kerja santai gaji besar").
2. **Blacklist/whitelist perusahaan** — `data/company_lists.json`, dicek sebelum panggil LLM sama sekali.
3. **Analisis LLM** — menangkap pola yang tidak tertangkap kata kunci (nada bombastis, konteks tidak wajar, dsb).

Keputusan akhir mengambil yang **paling ketat** dari ketiga lapis.

Uji cepat tanpa perlu scraping:

```bash
npm run test-pipeline
```

---

## Bab 6 — Matching Engine

`src/matcher/matchJob.js` — skor 0-100 kecocokan profil vs lowongan
(skill, pengalaman, lokasi, preferensi), dengan fallback aman jika LLM
mengembalikan field tidak lengkap.

---

## Bab 7 — Cover Letter Generator

`src/generator/coverLetter.js` — nada disesuaikan per platform (LinkedIn
lebih formal, Glints boleh sedikit lebih santai), berdasar data profil asli
(tidak mengarang pengalaman).

---

## Bab 8 — Application Executor (Robust)

`src/executor/applyForm.js` diperkuat dengan:

- **Retry** saat membuka halaman lamaran.
- **Deteksi block/captcha** sebelum mengisi apa pun.
- **Deteksi redirect keluar domain** (umum di Indeed) — ditandai di hasil, karena form di domain lain kemungkinan besar strukturnya berbeda.
- **Multi-selector fallback** untuk kolom cover letter (6 kandidat selector).
- **`NEVER_AUTO_SUBMIT`** — LinkedIn tidak akan pernah di-auto-submit apa pun nilai `MODE`, ini bukan pembatasan sembarangan tapi proteksi akun Anda dari deteksi anti-bot LinkedIn yang paling agresif di antara ke-4 platform.

---

## Bab 9 — Dashboard Web

```bash
npm run dashboard
```

Buka `http://localhost:3000`. Fitur: filter per platform/status/status-scam,
lihat alasan penandaan scam, baca draft cover letter, lihat screenshot draft
form, ubah status lamaran setelah submit manual.

### Quick Apply Assist

Untuk lowongan berstatus `draft`, ada tombol **🚀 Apply** (di daftar) atau
**🚀 Siapkan & Buka Lowongan** (di halaman Detail) yang otomatis:
1. Meng-copy cover letter hasil AI ke clipboard Anda.
2. Membuka halaman lamaran tersebut di tab baru.

Anda tinggal `Ctrl+V` cover letter ke kolom yang sesuai dan **submit manual**
di situs aslinya — ini menghilangkan langkah cari-link & copy manual, tapi
**tidak pernah** mengklik submit untuk Anda. Setelah submit selesai, update
status lamaran itu jadi `applied` lewat dropdown di halaman Detail (status
tidak berubah otomatis — sistem tidak tahu Anda benar-benar sudah submit
sampai Anda konfirmasi sendiri).

---

## Bab 10 — Scheduler (Orkestrasi)

```bash
npm run check          # cek kesiapan sistem dulu
npm start -- --once    # jalankan sekali manual (testing)
npm start               # jalankan terjadwal (cron, default tiap hari 08:00)
```

Platform diproses **berurutan** (bukan paralel) untuk hemat RAM di VPS 1GB.
Setiap lowongan yang sudah pernah diproses (berdasarkan URL) otomatis dilewati.

**Ambang batas skor kecocokan** (`MATCH_THRESHOLD` di `src/scheduler.js`,
saat ini **50**) menentukan lowongan mana yang dilanjutkan ke pembuatan cover
letter & draft, dan mana yang ditandai `skipped`. Skor di bawah ambang batas
tetap tercatat di database (bisa dilihat di dashboard) supaya Anda bisa
transparan melihat kenapa suatu lowongan dilewati — bukan dihapus diam-diam.

---

## Bab 11 — Deployment Otomatis

Berbeda dari sekadar daftar perintah manual, folder `deploy/` berisi script
yang benar-benar bisa dieksekusi:

```bash
chmod +x deploy/*.sh

# Di VPS, sekali di awal:
./deploy/setup-vps.sh              # swap, Node.js, PM2, dependency sistem
./deploy/setup-nginx-ssl.sh namadomainanda.com   # Nginx reverse proxy + SSL otomatis

# Deploy pertama & setiap update kode berikutnya:
./deploy/deploy-app.sh             # git pull, npm install, init-db, pm2 restart

# Backup database (jadwalkan via cron):
./deploy/backup-db.sh              # backup + hapus backup >14 hari otomatis
```

Contoh cron untuk backup harian:
```
50 23 * * * /path/ke/auto-apply-agent/deploy/backup-db.sh >> /path/ke/auto-apply-agent/data/backup/backup.log 2>&1
```

Upload session login dari laptop ke VPS (**jangan login dari VPS langsung**,
IP datacenter lebih mudah dicurigai sistem anti-bot):
```bash
scp -r data/sessions user@ip-vps:~/auto-apply-agent/data/
```

---

## Struktur Project Lengkap

```
auto-apply-agent/
├── src/
│   ├── collector/           # Bab 4 - linkedin.js, jobstreet.js, indeed.js, glints.js
│   ├── filter/               # Bab 5 - scamFilter.js
│   ├── parser/                # Bab 3 - cvParser.js
│   ├── matcher/                # Bab 6 - matchJob.js
│   ├── generator/               # Bab 7 - coverLetter.js
│   ├── executor/                # Bab 8 - applyForm.js
│   ├── db/                       # Bab 2 - schema.sql, index.js
│   ├── dashboard/                # Bab 9 - index.js + views/
│   ├── utils/                     # logger, delay, llmClient (multi-provider), browserSession, scrapeHelpers
│   └── scheduler.js              # Bab 10
├── deploy/                        # Bab 11 - setup-vps.sh, setup-nginx-ssl.sh, deploy-app.sh, backup-db.sh
├── scripts/
│   ├── saveSession.js
│   ├── checkEnv.js
│   └── testPipeline.js
├── data/
│   ├── profile.example.json
│   ├── company_lists.example.json
│   ├── sessions/ - drafts/ - backup/
├── .env.example
├── package.json
└── ecosystem.config.js
```

---

## Troubleshooting

| Gejala | Penyebab | Solusi |
|---|---|---|
| `<PROVIDER>_API_KEY belum diisi` atau `terisi: false` | `.env` belum dikonfigurasi untuk provider yang dipilih | `cp .env.example .env`, set `LLM_PROVIDER`, lalu isi API key yang sesuai (lihat Bab 0) |
| **Hampir semua lowongan berstatus `BLOCK`, kolom Perusahaan `-`** | Selector `company` di collector platform terkait tidak cocok dengan struktur halaman saat ini, sehingga data yang dikirim ke scam filter kosong | Cek log — sekarang scheduler otomatis memperingatkan jika ≥70% lowongan tanpa data perusahaan. Perbaiki selector via DevTools. Deskripsi lowongan juga sudah diambil otomatis dari halaman detail (`src/utils/fetchJobDetail.js`) sebelum scam check, dan sejak versi ini data tidak lengkap tidak lagi otomatis dianggap `BLOCK` — turun ke `REVIEW` kecuali ada kata kunci scam kuat |
| `Session untuk "xxx" belum ada` | Belum login manual | `npm run save-session -- xxx` |
| `Tidak ada selector yang cocok dari N kandidat` | Struktur halaman berubah | Cek DevTools, tambahkan selector baru di file collector terkait |
| `Terdeteksi block/captcha` | Sistem anti-bot platform aktif | Berhenti otomatis (sudah by design) — lanjutkan manual, jangan retry paksa |
| **Captcha tidak merespons saat login manual (`npm run save-session`)**, terutama di Indeed | Browser terdeteksi sebagai automation, bukan captcha-nya yang salah | Pakai jalur **Import Cookies** (Bab 4a) — login di browser harian normal, export cookie, import ke project |
| RAM VPS penuh | 1GB tanpa swap | `./deploy/setup-vps.sh` sudah otomatis menambah swap 2GB |
| Backup tidak jalan terjadwal | Cron belum di-set | Lihat contoh cron di Bab 11 |

---

## Batasan yang Perlu Anda Ketahui (jujur, bukan disclaimer kosong)

1. **Selector CSS adalah kandidat terbaik, bukan hasil inspeksi live.** Sudah dibuat robust (multi-fallback + retry), tapi tetap perlu penyesuaian berkala mengikuti perubahan platform.
2. **Auto-submit tidak diaktifkan untuk platform manapun secara default**, dan untuk LinkedIn memang sengaja dikunci permanen — ini melindungi akun Anda, bukan pembatasan fitur semata.
3. **Filter scam membantu, bukan menjamin 100% akurat.** Selalu tinjau status `REVIEW` secara manual.
