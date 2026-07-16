import fs from "fs";
import { openSessionContext, safeClose } from "../utils/browserSession.js";
import { humanDelay } from "../utils/delay.js";
import { withRetry, detectBlockPage } from "../utils/scrapeHelpers.js";
import { logger } from "../utils/logger.js";

/**
 * =========================================================================
 * BAB 8 — APPLICATION EXECUTOR (versi diperluas)
 * =========================================================================
 */

// Platform yang TIDAK PERNAH boleh di-auto-submit, terlepas dari nilai MODE.
// LinkedIn punya proteksi anti-bot paling agresif dan risiko ban permanen —
// ini bukan pembatasan sembarangan, ini melindungi akun Anda sendiri.
const NEVER_AUTO_SUBMIT = new Set(["linkedin"]);

const COVER_LETTER_SELECTORS = [
  'textarea[name="cover_letter"]',
  'textarea[aria-label*="message" i]',
  'textarea[aria-label*="cover" i]',
  'textarea[placeholder*="cover letter" i]',
  'textarea[placeholder*="surat lamaran" i]',
  'div[contenteditable="true"][aria-label*="message" i]',
];

/**
 * Menyiapkan lamaran: membuka halaman, mendeteksi apakah link membawa kita
 * keluar dari domain platform asal (umum terjadi di Indeed), mengisi cover
 * letter jika ada kolom yang cocok, dan mengambil screenshot sebagai draft
 * untuk ditinjau. TIDAK PERNAH melakukan klik submit otomatis untuk LinkedIn.
 */
export async function prepareApplication(job, profile, coverLetter, platform, mode = "review") {
  let browser;
  try {
    const session = await openSessionContext(platform);
    browser = session.browser;
    const page = await session.context.newPage();

    await withRetry(
      () => page.goto(job.link, { waitUntil: "domcontentloaded", timeout: 30000 }),
      { retries: 2, baseDelayMs: 3000, label: `${platform}: buka halaman lamaran` }
    );
    await humanDelay(2000, 5000);

    const block = await detectBlockPage(page);
    if (block.blocked) {
      logger.warn(`[${platform}] Terdeteksi block/captcha (${block.indicator}) saat menyiapkan lamaran — dihentikan, perlu ditinjau manual.`);
      return { status: "blocked", indicator: block.indicator };
    }

    // Deteksi redirect keluar domain (paling sering terjadi di Indeed, yang
    // banyak mengagregasi lowongan dari situs karier perusahaan lain).
    const originalHost = new URL(job.link).hostname;
    const currentHost = new URL(page.url()).hostname;
    const redirectedOutside = originalHost !== currentHost;
    if (redirectedOutside) {
      logger.warn(`[${platform}] Halaman redirect ke domain lain (${currentHost}) — form kemungkinan berbeda struktur, perlu isi manual saat review.`);
    }

    // Coba isi kolom cover letter/pesan dengan beberapa kandidat selector.
    let filled = false;
    for (const selector of COVER_LETTER_SELECTORS) {
      try {
        const field = await page.$(selector);
        if (field) {
          await field.fill(coverLetter);
          filled = true;
          logger.info(`[${platform}] Cover letter terisi otomatis (selector: ${selector}).`);
          break;
        }
      } catch {
        // Selector tidak cocok / elemen tidak fillable — lanjut coba kandidat berikutnya.
      }
    }

    if (!filled) {
      logger.warn(`[${platform}] Kolom cover letter tidak ditemukan otomatis — isi manual saat review.`);
    }

    const dir = "data/drafts";
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });

    const safeId = job.link.replace(/[^a-zA-Z0-9]/g, "_").slice(-60);
    const screenshotPath = `${dir}/${platform}_${safeId}.png`;
    await page.screenshot({ path: screenshotPath, fullPage: true });

    const canAutoSubmit = mode === "auto" && !NEVER_AUTO_SUBMIT.has(platform) && !redirectedOutside;
    if (mode === "auto" && NEVER_AUTO_SUBMIT.has(platform)) {
      logger.warn(`[${platform}] Mode auto diminta tapi platform ini masuk daftar NEVER_AUTO_SUBMIT — submit tetap manual demi keamanan akun.`);
    }
    if (canAutoSubmit) {
      logger.warn(`[${platform}] Mode auto aktif untuk platform ini — pastikan ini benar-benar diinginkan dan sudah diuji di mode review sebelumnya.`);
      // Auto-submit sengaja TIDAK diimplementasikan default di starter project ini.
      // Jika Anda ingin mengaktifkannya untuk platform selain LinkedIn, tambahkan
      // page.click(...) di sini dengan selector yang sudah diverifikasi manual.
    }

    await safeClose(browser);
    browser = null;

    return {
      status: "draft_ready",
      screenshotPath,
      coverLetterFilled: filled,
      redirectedOutside,
      autoSubmitted: false,
    };
  } catch (err) {
    logger.error(`[${platform}] Gagal menyiapkan lamaran untuk "${job.title}":`, err.message);
    return { status: "failed", error: err.message };
  } finally {
    if (browser) await safeClose(browser);
  }
}
