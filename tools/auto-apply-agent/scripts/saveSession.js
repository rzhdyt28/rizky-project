import fs from "fs";
import readline from "readline";
import { chromium } from "playwright";
import { logger } from "../src/utils/logger.js";

const URLS = {
  linkedin: "https://www.linkedin.com/login",
  jobstreet: "https://id.jobstreet.com/id/oauth/login",
  indeed: "https://secure.indeed.com/auth",
  glints: "https://glints.com/id/login",
};

/**
 * Beberapa situs (terutama yang di belakang Cloudflare/PerimeterX, seperti
 * Indeed) mendeteksi bahwa browser dikendalikan lewat automation protocol
 * (Chrome DevTools Protocol) — BUKAN mendeteksi bahwa Anda robot saat solve
 * captcha-nya. Akibatnya, captcha bisa tampak "tidak merespons" walau sudah
 * diselesaikan manual, karena sesi tetap ditandai mencurigakan di level
 * browser, bukan di level captcha itu sendiri.
 *
 * Dua langkah di bawah MENGURANGI (bukan menghilangkan total) sinyal itu,
 * untuk proses login yang tetap 100% manual/dikerjakan tangan Anda sendiri:
 *   1. Pakai Chrome asli yang sudah terinstal di laptop (channel: "chrome"),
 *      bukan build Chromium bawaan Playwright yang lebih mudah dikenali.
 *   2. Matikan flag "--enable-automation" yang secara default disisipkan
 *      Playwright (ini yang memicu banner "Chrome is being controlled by
 *      automated test software" di beberapa versi Chrome).
 */
async function launchForManualLogin() {
  try {
    const browser = await chromium.launch({
      headless: false,
      channel: "chrome",
      ignoreDefaultArgs: ["--enable-automation"],
      args: ["--disable-blink-features=AutomationControlled"],
    });
    logger.info("Menggunakan Chrome asli yang terinstal di laptop Anda.");
    return browser;
  } catch (err) {
    logger.warn(
      "Chrome asli tidak ditemukan di laptop ini, memakai Chromium bawaan Playwright sebagai fallback. " +
      "Jika situs terus menahan Anda di captcha, lihat opsi 'Bab 4a — Import Cookies' di README."
    );
    return chromium.launch({
      headless: false,
      ignoreDefaultArgs: ["--enable-automation"],
      args: ["--disable-blink-features=AutomationControlled"],
    });
  }
}

async function main() {
  const platform = process.argv[2];

  if (!platform || !URLS[platform]) {
    logger.error(`Platform tidak valid. Gunakan salah satu: ${Object.keys(URLS).join(", ")}`);
    logger.info("Contoh: npm run save-session -- linkedin");
    process.exit(1);
  }

  const dir = "data/sessions";
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });

  logger.info(`Membuka browser untuk login manual ke ${platform}...`);
  const browser = await launchForManualLogin();
  const context = await browser.newContext();
  const page = await context.newPage();
  await page.goto(URLS[platform]);

  logger.info("=".repeat(60));
  logger.info(`Silakan login secara MANUAL di jendela browser yang terbuka.`);
  logger.info(`Setelah selesai login dan halaman utama sudah tampil,`);
  logger.info(`kembali ke terminal ini dan tekan ENTER untuk menyimpan session.`);
  logger.info(`Kalau Anda terjebak di captcha yang tidak merespons setelah`);
  logger.info(`diselesaikan berkali-kali, tekan Ctrl+C dan lihat opsi`);
  logger.info(`"Import Cookies" di README (Bab 4a) — cara yang lebih pasti berhasil.`);
  logger.info("=".repeat(60));

  const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
  await new Promise((resolve) => rl.question("Tekan ENTER setelah login selesai... ", resolve));
  rl.close();

  const sessionPath = `${dir}/${platform}.json`;
  await context.storageState({ path: sessionPath });
  await browser.close();

  logger.success(`Session ${platform} tersimpan di: ${sessionPath}`);
  logger.info("Catatan: session bisa kedaluwarsa setelah beberapa minggu — ulangi proses ini jika collector mulai gagal login.");
}

main().catch((err) => {
  logger.error("Gagal menyimpan session:", err.message);
  process.exit(1);
});
