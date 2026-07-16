import { openSessionContext, safeClose } from "./browserSession.js";
import { humanDelay } from "./delay.js";
import { trySelectors, withRetry, detectBlockPage } from "./scrapeHelpers.js";
import { logger } from "./logger.js";

/**
 * =========================================================================
 * PELENGKAP BAB 4 — AMBIL DESKRIPSI LOWONGAN (halaman detail)
 * =========================================================================
 * Kartu lowongan di halaman hasil pencarian (yang diambil collector) TIDAK
 * memuat deskripsi lengkap — hanya title/company/location/link. Deskripsi
 * penuh cuma ada di halaman detail tiap lowongan, jadi perlu dibuka satu
 * per satu. Fungsi ini dipanggil SETELAH collector, SEBELUM scam filter,
 * supaya scam filter & matching engine punya konteks yang cukup.
 *
 * Beberapa kandidat selector generik dicoba berurutan (banyak platform job
 * portal memakai pola nama class/atribut yang mirip untuk area deskripsi).
 * Jika semua gagal, fallback ke innerText dari <main> atau <body> lalu
 * dipotong — lebih baik ada teks kasar daripada string kosong yang bikin
 * LLM salah menyimpulkan "lowongan tidak jelas identitasnya".
 */
const DESCRIPTION_SELECTORS = [
  "[class*='JobDescription']",
  "[class*='job-description']",
  "[data-testid*='description']",
  "[class*='description']",
  "article",
  "main",
];

export async function fetchJobDescription(platform, jobUrl) {
  let browser;
  try {
    const session = await openSessionContext(platform);
    browser = session.browser;
    const page = await session.context.newPage();

    await withRetry(
      () => page.goto(jobUrl, { waitUntil: "domcontentloaded", timeout: 30000 }),
      { retries: 2, baseDelayMs: 3000, label: `${platform}: buka halaman detail lowongan` }
    );
    await humanDelay(1500, 3500);

    const block = await detectBlockPage(page);
    if (block.blocked) {
      logger.warn(`[${platform}] Block/captcha terdeteksi saat ambil deskripsi — dilewati.`);
      return null;
    }

    let selector;
    try {
      selector = await trySelectors(page, DESCRIPTION_SELECTORS, { timeout: 6000 });
    } catch {
      logger.warn(`[${platform}] Tidak ada elemen deskripsi yang cocok, fallback ke <body>.`);
      selector = "body";
    }

    const text = await page.$eval(selector, (el) => el.innerText || "");
    const cleaned = text.replace(/\s+/g, " ").trim().slice(0, 4000);

    if (!cleaned || cleaned.length < 20) {
      logger.warn(`[${platform}] Deskripsi hasil ekstraksi terlalu pendek/kosong (${cleaned.length} karakter) — kemungkinan selector perlu diperbarui.`);
      return null;
    }

    return cleaned;
  } catch (err) {
    logger.error(`[${platform}] Gagal ambil deskripsi lowongan (${jobUrl}):`, err.message);
    return null;
  } finally {
    if (browser) await safeClose(browser);
  }
}
