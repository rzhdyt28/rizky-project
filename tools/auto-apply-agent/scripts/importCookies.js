import fs from "fs";
import path from "path";
import { logger } from "../src/utils/logger.js";

/**
 * =========================================================================
 * IMPORT COOKIES — jalur alternatif kalau login manual via Playwright
 * terus-menerus terjebak captcha (umum terjadi di situs yang diproteksi
 * Cloudflare/PerimeterX seperti Indeed).
 * =========================================================================
 *
 * Cara pakai:
 *   1. Login ke platform target dengan browser HARIAN Anda yang biasa
 *      (Chrome/Firefox/Edge normal — BUKAN yang dibuka script ini/Playwright).
 *   2. Pasang ekstensi export cookie, misalnya "Cookie-Editor"
 *      (tersedia di Chrome Web Store & Firefox Add-ons).
 *   3. Di halaman platform yang sudah login, buka ekstensi tsb, pilih
 *      "Export" -> "JSON", simpan sebagai file, mis. data/cookies-raw/indeed.json
 *   4. Jalankan: node scripts/importCookies.js indeed data/cookies-raw/indeed.json
 *   5. Script ini akan membuat data/sessions/indeed.json dalam format yang
 *      dipahami Playwright (storageState).
 *
 * Kenapa ini lebih pasti berhasil dibanding login lewat Playwright:
 * login-nya terjadi 100% di browser normal Anda yang tidak punya sinyal
 * automation sama sekali — situs tidak tahu Playwright pernah terlibat,
 * baru menyentuh Playwright setelah sesi valid dipindahkan.
 */

const PLATFORM_DOMAINS = {
  linkedin: "linkedin.com",
  jobstreet: "jobstreet.com",
  indeed: "indeed.com",
  glints: "glints.com",
};

function normalizeCookie(raw, expectedDomain) {
  // Ekstensi export cookie punya nama field yang sedikit berbeda-beda
  // (Cookie-Editor, EditThisCookie, dll) — normalisasi ke format Playwright.
  const name = raw.name;
  const value = raw.value;
  const domain = raw.domain || expectedDomain;
  const cookiePath = raw.path || "/";

  let expires = -1;
  if (typeof raw.expirationDate === "number") {
    expires = raw.expirationDate;
  } else if (typeof raw.expires === "number") {
    expires = raw.expires;
  } else if (raw.session === false && raw.expiry) {
    expires = raw.expiry;
  }

  let sameSite = "Lax";
  const rawSameSite = (raw.sameSite || "").toLowerCase();
  if (rawSameSite === "no_restriction" || rawSameSite === "none") sameSite = "None";
  else if (rawSameSite === "strict") sameSite = "Strict";
  else if (rawSameSite === "lax" || rawSameSite === "unspecified" || !rawSameSite) sameSite = "Lax";

  return {
    name,
    value,
    domain: domain.startsWith(".") ? domain : `.${domain}`,
    path: cookiePath,
    expires,
    httpOnly: Boolean(raw.httpOnly),
    secure: Boolean(raw.secure),
    sameSite,
  };
}

function main() {
  const platform = process.argv[2];
  const inputPath = process.argv[3];

  if (!platform || !PLATFORM_DOMAINS[platform] || !inputPath) {
    logger.error("Penggunaan: node scripts/importCookies.js <platform> <path-ke-file-json-hasil-export>");
    logger.info(`Platform valid: ${Object.keys(PLATFORM_DOMAINS).join(", ")}`);
    process.exit(1);
  }

  if (!fs.existsSync(inputPath)) {
    logger.error(`File tidak ditemukan: ${inputPath}`);
    process.exit(1);
  }

  let rawCookies;
  try {
    const parsed = JSON.parse(fs.readFileSync(inputPath, "utf-8"));
    // Beberapa ekstensi membungkus dalam { cookies: [...] }, sebagian langsung array.
    rawCookies = Array.isArray(parsed) ? parsed : parsed.cookies;
    if (!Array.isArray(rawCookies)) {
      throw new Error("Format tidak dikenali — diharapkan array cookie atau { cookies: [...] }.");
    }
  } catch (err) {
    logger.error(`Gagal membaca/parse ${inputPath}:`, err.message);
    process.exit(1);
  }

  const expectedDomain = PLATFORM_DOMAINS[platform];
  const relevant = rawCookies.filter((c) => (c.domain || "").includes(expectedDomain));

  if (relevant.length === 0) {
    logger.error(
      `Tidak ada cookie untuk domain "${expectedDomain}" di file ini. ` +
      `Pastikan Anda export cookie SAAT berada di halaman ${expectedDomain} yang sudah login.`
    );
    process.exit(1);
  }

  const cookies = relevant.map((c) => normalizeCookie(c, expectedDomain));

  const storageState = { cookies, origins: [] };

  const outDir = "data/sessions";
  if (!fs.existsSync(outDir)) fs.mkdirSync(outDir, { recursive: true });
  const outPath = path.join(outDir, `${platform}.json`);

  fs.writeFileSync(outPath, JSON.stringify(storageState, null, 2));

  logger.success(`${cookies.length} cookie berhasil diimport ke ${outPath}`);
  logger.info("Uji session ini dengan: npm start -- --once (atau jalankan collector platform terkait secara manual).");
  logger.info("Jika masih diminta login ulang, kemungkinan cookie sudah kedaluwarsa atau export dilakukan sebelum benar-benar login.");
}

main();
