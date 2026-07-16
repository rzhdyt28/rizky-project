import { openSessionContext, safeClose } from "../utils/browserSession.js";
import { humanDelay } from "../utils/delay.js";
import { extractJobCards, withRetry, detectBlockPage } from "../utils/scrapeHelpers.js";
import { logger } from "../utils/logger.js";

const PLATFORM = "glints";

const SELECTORS = {
  container: ["div[class*='JobCard']", "a[class*='opportunity']", "div[class*='OpportunityCard']", "div[class*='CardContainer']"],
  title: ["[class*='title']", "h2", "h3", "[class*='JobTitle']"],
  company: [
    "[class*='company']",
    "[class*='CompanyName']",
    "[class*='company-name']",
    "a[href*='/companies/']",
    "span[class*='Company']",
  ],
  location: ["[class*='location']", "[class*='Location']"],
  link: ["a"],
};

/**
 * Mengambil daftar lowongan dari Glints Indonesia. Glints relatif lebih
 * longgar dibanding LinkedIn, tapi tetap terapkan volume rendah & delay wajar.
 */
export async function fetchGlintsJobs(keyword, location, limit = 15) {
  let browser;
  try {
    const session = await openSessionContext(PLATFORM);
    browser = session.browser;
    const page = await session.context.newPage();

    const url = `https://glints.com/id/opportunities/jobs/explore?keyword=${encodeURIComponent(
      keyword
    )}&country=ID&locationName=${encodeURIComponent(location)}`;

    logger.info(`[glints] Membuka pencarian: "${keyword}" di "${location}"`);

    await withRetry(
      () => page.goto(url, { waitUntil: "domcontentloaded", timeout: 30000 }),
      { retries: 2, baseDelayMs: 4000, label: "glints: buka halaman pencarian" }
    );
    await humanDelay(3000, 6000);

    const block = await detectBlockPage(page);
    if (block.blocked) {
      logger.warn(`[glints] Terdeteksi block (${block.indicator}) — lewati run ini.`);
      return [];
    }

    const jobs = await extractJobCards(page, SELECTORS);
    const limited = jobs.slice(0, limit);
    logger.success(`[glints] ${limited.length} lowongan ditemukan (dari ${jobs.length} total di halaman).`);
    return limited;
  } catch (err) {
    logger.error("[glints] Gagal mengambil lowongan:", err.message);
    return [];
  } finally {
    if (browser) await safeClose(browser);
  }
}
