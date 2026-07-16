import { openSessionContext, safeClose } from "../utils/browserSession.js";
import { humanDelay } from "../utils/delay.js";
import { extractJobCards, withRetry, detectBlockPage } from "../utils/scrapeHelpers.js";
import { logger } from "../utils/logger.js";

const PLATFORM = "jobstreet";

const SELECTORS = {
  container: [
    "article[data-testid='job-card']",
    "article.job-card",
    "div[data-automation='job-item']",
  ],
  title: ["a[data-testid='job-title']", "h1", "h3", "a[data-automation='jobTitle']"],
  company: [
    "[data-testid='company-name']",
    ".company-name",
    "a[data-automation='jobCompany']",
    "span[data-automation='jobCompany']",
    "[class*='CompanyName']",
    "a[href*='/companies/']",
  ],
  location: ["[data-testid='job-location']", ".job-location", "a[data-automation='jobLocation']"],
  link: ["a[data-testid='job-title']", "a[data-automation='jobTitle']", "a"],
};

/**
 * Mengambil daftar lowongan dari Jobstreet Indonesia (id.jobstreet.com).
 * Jobstreet kadang menampilkan captcha pada beberapa alur — jika terdeteksi,
 * fungsi berhenti untuk sesi ini dan tidak mencoba membypassnya.
 */
export async function fetchJobstreetJobs(keyword, location, limit = 15) {
  let browser;
  try {
    const session = await openSessionContext(PLATFORM);
    browser = session.browser;
    const page = await session.context.newPage();

    const url = `https://id.jobstreet.com/id/${encodeURIComponent(keyword)}-jobs/in-${encodeURIComponent(
      location
    )}`;

    logger.info(`[jobstreet] Membuka pencarian: "${keyword}" di "${location}"`);

    await withRetry(
      () => page.goto(url, { waitUntil: "domcontentloaded", timeout: 30000 }),
      { retries: 2, baseDelayMs: 4000, label: "jobstreet: buka halaman pencarian" }
    );
    await humanDelay(3000, 6000);

    const block = await detectBlockPage(page);
    if (block.blocked) {
      logger.warn(`[jobstreet] Captcha/block terdeteksi (${block.indicator}) — lewati run ini, lanjutkan manual dari browser Anda.`);
      return [];
    }

    const jobs = await extractJobCards(page, SELECTORS);
    const limited = jobs.slice(0, limit);
    logger.success(`[jobstreet] ${limited.length} lowongan ditemukan (dari ${jobs.length} total di halaman).`);
    return limited;
  } catch (err) {
    logger.error("[jobstreet] Gagal mengambil lowongan:", err.message);
    return [];
  } finally {
    if (browser) await safeClose(browser);
  }
}
