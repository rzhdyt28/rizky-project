import fs from "fs";
import { chromium } from "playwright";
import { logger } from "./logger.js";

/**
 * Membuka browser headless menggunakan session (cookies) yang sudah
 * disimpan lewat `npm run save-session -- <platform>`. Ini menghindari
 * login otomatis dengan password di dalam script, yang jauh lebih mudah
 * dideteksi oleh sistem anti-bot dibanding sesi yang login manual.
 */
export async function openSessionContext(platform) {
  const sessionPath = `data/sessions/${platform}.json`;

  if (!fs.existsSync(sessionPath)) {
    throw new Error(
      `Session untuk "${platform}" belum ada. Jalankan dulu: npm run save-session -- ${platform}`
    );
  }

  const browser = await chromium.launch({
    headless: true,
    args: ["--disable-gpu", "--no-sandbox", "--disable-dev-shm-usage"],
  });

  const context = await browser.newContext({
    storageState: sessionPath,
    userAgent:
      "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36",
    viewport: { width: 1366, height: 768 },
  });

  return { browser, context };
}

export async function safeClose(browser) {
  try {
    await browser.close();
  } catch (err) {
    logger.warn("Gagal menutup browser dengan bersih:", err.message);
  }
}
