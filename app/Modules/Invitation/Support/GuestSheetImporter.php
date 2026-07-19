<?php

namespace App\Modules\Invitation\Support;

use RuntimeException;
use ZipArchive;

/**
 * IMPORT TAMU MASSAL dari file spreadsheet — TANPA package tambahan.
 * - .csv : parser native (fgetcsv), auto-deteksi pemisah koma / titik-koma
 *          (Excel Indonesia menyimpan CSV dengan ';').
 * - .xlsx: dibaca langsung sebagai zip XML (ZipArchive + SimpleXML bawaan
 *          PHP) — cukup untuk 3 kolom sederhana, tak perlu PhpSpreadsheet.
 *
 * Format kolom (baris header opsional, terdeteksi otomatis):
 *   A: Nama (wajib) | B: No. WhatsApp (opsional) | C: Catatan (opsional)
 *
 * Mengembalikan array [['name' => ..., 'phone' => ..., 'note' => ...], ...]
 */
class GuestSheetImporter
{
    /** Batas aman satu kali import — mencegah request timeout/memori. */
    public const MAX_ROWS = 2000;

    public static function parse(string $absolutePath): array
    {
        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        $rows = match ($ext) {
            'csv', 'txt' => self::parseCsv($absolutePath),
            'xlsx'       => self::parseXlsx($absolutePath),
            default      => throw new RuntimeException("Format .$ext tidak didukung. Gunakan .csv atau .xlsx."),
        };

        return self::normalize($rows);
    }

    // ---------------------------------------------------------------- CSV

    private static function parseCsv(string $path): array
    {
        $fh = fopen($path, 'r');
        if (! $fh) {
            throw new RuntimeException('File tidak bisa dibaca.');
        }

        // Deteksi pemisah dari baris pertama: ';' menang bila lebih banyak.
        $firstLine = fgets($fh) ?: '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($fh);

        $rows = [];
        while (($cols = fgetcsv($fh, 2048, $delimiter)) !== false) {
            $rows[] = $cols;
            if (count($rows) > self::MAX_ROWS + 1) {
                break; // +1 memberi ruang untuk baris header
            }
        }
        fclose($fh);

        return $rows;
    }

    // --------------------------------------------------------------- XLSX

    private static function parseXlsx(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File .xlsx tidak valid (bukan arsip zip).');
        }

        // Shared strings: teks sel biasanya disimpan terpusat di sini.
        $shared = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $sst = simplexml_load_string($xml);
            if ($sst !== false) {
                foreach ($sst->si as $si) {
                    // <t> langsung ATAU rich text (kumpulan <r><t>)
                    $shared[] = isset($si->t)
                        ? (string) $si->t
                        : implode('', array_map(fn ($r) => (string) $r->t, iterator_to_array($si->r ?? [], false)));
                }
            }
        }

        // Sheet pertama (urutan file standar: sheet1.xml).
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false) {
            throw new RuntimeException('Sheet pertama tidak ditemukan di file .xlsx.');
        }
        $sheet = simplexml_load_string($sheetXml);
        if ($sheet === false) {
            throw new RuntimeException('Isi sheet .xlsx tidak bisa dibaca.');
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cols = ['', '', ''];
            foreach ($row->c as $cell) {
                $ref    = (string) $cell['r'];                    // mis. "B12"
                $letter = preg_replace('/\d+/', '', $ref);        // "B"
                $idx    = ord(strtoupper($letter[0] ?? 'A')) - 65; // A=0, B=1, C=2
                if ($idx < 0 || $idx > 2) {
                    continue; // hanya kolom A-C yang dipakai
                }
                $val = (string) ($cell->v ?? '');
                if ((string) $cell['t'] === 's') {                // shared string
                    $val = $shared[(int) $val] ?? '';
                } elseif ((string) $cell['t'] === 'inlineStr') {
                    $val = (string) ($cell->is->t ?? '');
                }
                $cols[$idx] = $val;
            }
            $rows[] = $cols;
            if (count($rows) > self::MAX_ROWS + 1) {
                break;
            }
        }

        return $rows;
    }

    // ---------------------------------------------------------- Normalisasi

    private static function normalize(array $rows): array
    {
        // Buang baris header bila kolom A-nya terbaca sebagai judul kolom.
        if ($rows && preg_match('/^\s*(nama|name)\s*$/i', (string) ($rows[0][0] ?? ''))) {
            array_shift($rows);
        }

        $out = [];
        foreach ($rows as $cols) {
            $name = trim((string) ($cols[0] ?? ''));
            if ($name === '') {
                continue; // baris kosong dilewati diam-diam
            }
            $out[] = [
                'name'  => mb_substr($name, 0, 120),
                'phone' => mb_substr(trim((string) ($cols[1] ?? '')), 0, 30) ?: null,
                'note'  => mb_substr(trim((string) ($cols[2] ?? '')), 0, 160) ?: null,
            ];
            if (count($out) >= self::MAX_ROWS) {
                break;
            }
        }

        if (! $out) {
            throw new RuntimeException('Tidak ada baris tamu yang valid — pastikan kolom A berisi nama.');
        }

        return $out;
    }
}
