import { humanDelay } from "./delay.js";
import { logger } from "./logger.js";

/**
 * =========================================================================
 * BAB 4 — HELPER SCRAPING ROBUST
 * =========================================================================
 * Modul ini adalah inti "ketahanan" (resilience) dari semua collector.
 * Alih-alih mengandalkan SATU selector CSS yang mudah patah saat platform
 * mengubah struktur halaman, setiap fungsi di sini mencoba BEBERAPA
 * kandidat selector secara berurutan sampai salah satu berhasil.
 *
 * Ini tidak menjamin selector selalu 100% akurat (struktur halaman bisa
 * berubah kapan saja), tapi jauh lebih tahan banting dibanding satu
 * selector statis, dan memberi log yang jelas saat SEMUA kandidat gagal
 * sehingga Anda tahu persis kapan perlu memperbarui selector.
 */

/**
 * Coba beberapa selector CSS secara berurutan, kembalikan elemen/hasil
 * pertama yang ditemukan. Melempar error deskriptif jika semua gagal.
 */
export async function trySelectors(page, selectors, { timeout = 8000 } = {}) {
  const errors = [];
  for (const selector of selectors) {
    try {
      await page.waitForSelector(selector, { timeout: timeout / selectors.length || 2000 });
      return selector;
    } catch (err) {
      errors.push(`"${selector}": ${err.message.split("\n")[0]}`);
    }
  }
  throw new Error(
    `Tidak ada selector yang cocok dari ${selectors.length} kandidat. Detail:\n  - ${errors.join("\n  - ")}\n` +
    `Kemungkinan besar struktur halaman berubah — buka halaman ini manual, cek lewat DevTools, ` +
    `dan perbarui daftar selector di file collector terkait.`
  );
}

/**
 * Ekstrak data dari kartu lowongan dengan mencoba beberapa kandidat
 * selector CSS untuk tiap field (container, title, company, location, link).
 * Mengembalikan array job yang sudah dibersihkan (field kosong -> null,
 * duplikat berdasarkan link -> dihapus).
 */
export async function extractJobCards(page, fieldSelectors) {
  const { container, title, company, location, link } = fieldSelectors;

  let containerSelector;
  try {
    containerSelector = await trySelectors(page, container);
  } catch (err) {
    logger.error(err.message);
    return [];
  }

  const jobs = await page.$$eval(
    containerSelector,
    (cards, sels) => {
      function firstMatch(el, selectorList) {
        for (const s of selectorList) {
          const found = el.querySelector(s);
          if (found) return found;
        }
        return null;
      }

      return cards.map((card) => {
        const titleEl = firstMatch(card, sels.title);
        const companyEl = firstMatch(card, sels.company);
        const locationEl = firstMatch(card, sels.location);
        const linkEl = firstMatch(card, sels.link) || card.querySelector("a");

        return {
          title: titleEl?.innerText?.trim() || null,
          company: companyEl?.innerText?.trim() || null,
          location: locationEl?.innerText?.trim() || null,
          link: linkEl?.href || null,
        };
      });
    },
    { title, company, location, link }
  );

  // Bersihkan: buang yang tidak punya title/link, hapus duplikat berdasarkan link.
  const seen = new Set();
  const cleaned = [];
  for (const job of jobs) {
    if (!job.title || !job.link) continue;
    if (seen.has(job.link)) continue;
    seen.add(job.link);
    cleaned.push(job);
  }
  return cleaned;
}

/**
 * Retry dengan exponential backoff untuk operasi yang bisa gagal sementara
 * (mis. timeout jaringan, elemen belum ter-render). TIDAK dipakai untuk
 * mengulangi aksi yang berisiko terdeteksi bot berulang kali dalam waktu
 * singkat — beri jeda manusiawi antar percobaan.
 */
export async function withRetry(fn, { retries = 3, baseDelayMs = 3000, label = "operasi" } = {}) {
  let lastErr;
  for (let attempt = 1; attempt <= retries; attempt++) {
    try {
      return await fn(attempt);
    } catch (err) {
      lastErr = err;
      logger.warn(`[retry] ${label} gagal (percobaan ${attempt}/${retries}): ${err.message}`);
      if (attempt < retries) {
        const backoff = baseDelayMs * attempt;
        await humanDelay(backoff, backoff + 2000);
      }
    }
  }
  throw new Error(`${label} tetap gagal setelah ${retries} percobaan. Error terakhir: ${lastErr.message}`);
}

/**
 * Deteksi tanda-tanda umum bahwa halaman memblokir/menantang kita sebagai
 * bot (captcha, halaman "verifikasi diperlukan", rate-limit page).
 */
export async function detectBlockPage(page) {
  const indicators = [
    "iframe[src*='captcha']",
    "div#captcha",
    "div[class*='captcha']",
    "text=/verify you.{0,20}human/i",
    "text=/unusual traffic/i",
    "text=/akses dibatasi/i",
  ];

  for (const sel of indicators) {
    try {
      const el = await page.$(sel);
      if (el) return { blocked: true, indicator: sel };
    } catch {
      // selector jenis "text=" hanya valid di beberapa versi Playwright locator API;
      // abaikan error selector tak dikenal, lanjut cek indikator berikutnya.
    }
  }
  return { blocked: false };
}
