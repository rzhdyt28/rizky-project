import { openSessionContext, safeClose } from "../utils/browserSession.js";
import { humanDelay } from "../utils/delay.js";
import { extractJobCards, withRetry, detectBlockPage } from "../utils/scrapeHelpers.js";
import { logger } from "../utils/logger.js";

const PLATFORM = "indeed";

const SELECTORS = {
  container: ["div.job_seen_beacon", "td.resultContent", "div.jobsearch-SerpJobCard"],
  title: ["h2.jobTitle span", "a.jcs-JobTitle span", "h2.jobTitle a"],
  company: [
    "span.companyName",
    "[data-testid='company-name']",
    "a[data-testid='company-name']",
    "[class*='companyName']",
  ],
  location: ["div.companyLocation", "[data-testid='text-location']"],
  link: ["a.jcs-JobTitle", "h2.jobTitle a", "a"],
};

/**
 * Mengambil daftar lowongan dari Indeed Indonesia.
 * Catatan: sebagian lowongan Indeed adalah agregasi dari situs perusahaan
 * lain — link hasil bisa mengarah keluar dari domain Indeed. Modul executor
 * menangani kasus redirect ini secara terpisah.
 */
export async function fetchIndeedJobs(keyword, location, limit = 15) {
  let browser;
  try {
    const session = await openSessionContext(PLATFORM);
    browser = session.browser;
    const page = await session.context.newPage();

    const url = `https://id.indeed.com/jobs?q=${encodeURIComponent(keyword)}&l=${encodeURIComponent(location)}`;

    logger.info(`[indeed] Membuka pencarian: "${keyword}" di "${location}"`);

    await withRetry(
      () => page.goto(url, { waitUntil: "domcontentloaded", timeout: 30000 }),
      { retries: 2, baseDelayMs: 4000, label: "indeed: buka halaman pencarian" }
    );
    await humanDelay(3000, 6000);

    const block = await detectBlockPage(page);
    if (block.blocked) {
      logger.warn(`[indeed] Terdeteksi block (${block.indicator}) — lewati run ini.`);
      return [];
    }

    const jobs = await extractJobCards(page, SELECTORS);
    const limited = jobs.slice(0, limit);
    logger.success(`[indeed] ${limited.length} lowongan ditemukan (dari ${jobs.length} total di halaman).`);
    return limited;
  } catch (err) {
    logger.error("[indeed] Gagal mengambil lowongan:", err.message);
    return [];
  } finally {
    if (browser) await safeClose(browser);
  }
}
