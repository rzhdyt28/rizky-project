<?php

namespace App\Modules\Skripsi\Saw\Support;

/**
 * Teks baku penjelasan metode SAW, dipakai buat auto-isi deskripsi studi
 * kasus baru. Mahasiswa bisa mengedit teks ini sesuai kebutuhan skripsinya.
 * Algoritma lain (AHP/TOPSIS/dst) bikin class serupa dengan pola yang sama.
 */
class SawExplanations
{
    public static function default(): string
    {
        return <<<'TEXT'
Metode SAW (Simple Additive Weighting) adalah salah satu metode Sistem
Pendukung Keputusan (SPK) yang bekerja dengan mencari penjumlahan terbobot
dari rating kinerja setiap alternatif pada semua kriteria.

Langkah singkatnya:
1. Setiap kriteria dinormalisasi — kriteria "benefit" (semakin besar semakin
   baik) dibagi nilai maksimum, kriteria "cost" (semakin kecil semakin baik)
   memakai nilai minimum dibagi nilai tersebut.
2. Hasil normalisasi dikalikan bobot masing-masing kriteria.
3. Nilai-nilai tersebut dijumlahkan per alternatif menjadi skor akhir.
4. Alternatif dengan skor tertinggi adalah rekomendasi terbaik.

Anda bisa mengedit teks ini sesuai konteks studi kasus skripsi Anda.
TEXT;
    }
}
