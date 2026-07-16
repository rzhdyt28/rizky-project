import { openSessionContext, safeClose } from "../utils/browserSession.js";
import { humanDelay } from "../utils/delay.js";
import { extractJobCards, withRetry, detectBlockPage } from "../utils/scrapeHelpers.js";
import { logger } from "../utils/logger.js";

const PLATFORM = "linkedin";

// Beberapa kandidat selector per field. LinkedIn cukup sering mengubah nama
// class-nya (mis. menambah hash acak), jadi daftar ini mencakup pola yang
// umum dipakai LinkedIn dalam beberapa tahun terakhir. Jika semua kandidat
// gagal, lihat pesan error di log — itu tandanya perlu ditambah selector baru
// hasil inspeksi manual lewat DevTools.
const SELECTORS = {
  container: [
    "div.job-card-container",
    "li.jobs-search-results__list-item",
    "div[data-job-id]",
    "div.base-card",
  ],
  title: [
    ".job-card-list__title",
    ".base-search-card__title",
    "a[class*='job-card-list__title']",
    "[class*='job-card-list__title']",
  ],
  company: [
    ".job-card-container__company-name",
    ".base-search-card__subtitle",
    "a[class*='company-name']",
    "[class*='company-name']",
    "h4[class*='subtitle']",
    "a[data-tracking-control-name*='company']",
  ],
  location: [
    ".job-card-container__metadata-item",
    ".job-search-card__location",
    "li[class*='metadata-item']",
    "[class*='metadata-item']",
  ],
  link: ["a.job-card-list__title", "a.base-card__full-link", "a"],
};

/**
 * Mengambil daftar lowongan dari LinkedIn Jobs Search.
 *
 * LinkedIn adalah platform dengan proteksi anti-bot paling ketat di antara
 * ke-4 platform — fungsi ini SENGAJA tidak melakukan retry agresif saat
 * terdeteksi block (captcha/verifikasi), karena mengulang percobaan pada
 * kondisi seperti itu justru meningkatkan risiko akun dibatasi. Begitu
 * block terdeteksi, fungsi berhenti dan mengembalikan array kosong.
 */
export async function fetchLinkedInJobs(keyword, location, limit = 15) {
  let browser;
  try {
    const session = await openSessionContext(PLATFORM);
    browser = session.browser;
    const page = await session.context.newPage();

    const url = `https://www.linkedin.com/jobs/search/?keywords=${encodeURIComponent(
      keyword
    )}&location=${encodeURIComponent(location)}`;

    logger.info(`[linkedin] Membuka pencarian: "${keyword}" di "${location}"`);

    await withRetry(
      () => page.goto(url, { waitUntil: "domcontentloaded", timeout: 30000 }),
      { retries: 2, baseDelayMs: 4000, label: "linkedin: buka halaman pencarian" }
    );
    await humanDelay(3000, 6000);

    const block = await detectBlockPage(page);
    if (block.blocked) {
      logger.warn(`[linkedin] Terdeteksi block/captcha (${block.indicator}) — hentikan run ini, lanjutkan manual dari browser Anda.`);
      return [];
    }

    const jobs = await extractJobCards(page, SELECTORS);
    const limited = jobs.slice(0, limit);
    logger.success(`[linkedin] ${limited.length} lowongan ditemukan (dari ${jobs.length} total di halaman).`);
    return limited;
  } catch (err) {
    logger.error("[linkedin] Gagal mengambil lowongan:", err.message);
    return [];
  } finally {
    if (browser) await safeClose(browser);
  }
}
