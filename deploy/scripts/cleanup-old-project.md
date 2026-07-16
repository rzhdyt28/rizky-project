# Membersihkan sisa percobaan lama (rizky-project versi gabung)

Karena versi lama menimpa skeleton dengan berbagai file, cara paling bersih:

1. JANGAN mengutak-atik folder lama. Buat folder BARU:
   composer create-project laravel/laravel rizky-project-api
2. Salin file dari paket ini mengikuti docs/SETUP.md (bagian "peta salin file").
3. Database: DROP database lama yang berisi tabel campur aduk, buat baru:
   DROP DATABASE IF EXISTS undangan_central; CREATE DATABASE rizky_project;
4. Folder lama boleh diarsip/zip sebagai referensi, lalu dihapus.

Gejala "duplicate" kemarin terjadi karena migration skeleton + migration paket
berjalan dua kali di database yang sama. Dengan database baru + migration
modular (per folder modul), itu tidak terjadi lagi.
