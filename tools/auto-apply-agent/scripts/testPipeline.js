import "dotenv/config";
import fs from "fs";
import { scamCheck } from "../src/filter/scamFilter.js";
import { scoreJob } from "../src/matcher/matchJob.js";
import { generateCoverLetter } from "../src/generator/coverLetter.js";
import { logger } from "../src/utils/logger.js";

/**
 * Menguji modul filter/matcher/generator TANPA perlu login/scraping browser.
 * Berguna untuk memverifikasi API key provider LLM yang aktif (lihat LLM_PROVIDER
 * di .env) & logika sebelum menyambungkan ke collector platform sungguhan.
 * Jalankan: npm run test-pipeline
 */

const DUMMY_JOB_NORMAL = {
  title: "Frontend Developer",
  company: "PT Teknologi Maju Bersama",
  location: "Jakarta Selatan",
  description:
    "Kami mencari Frontend Developer dengan pengalaman React minimal 1 tahun. " +
    "Kirim CV melalui portal resmi kami. Proses rekrutmen meliputi tes teknis dan " +
    "wawancara HR & user. Gaji kompetitif sesuai pengalaman.",
  link: "https://example.com/jobs/frontend-developer-123",
};

const DUMMY_JOB_SUSPICIOUS = {
  title: "Admin Input Data - Kerja Santai Gaji Besar",
  company: "CV Sukses Bersama Grup",
  location: "Remote",
  description:
    "Dibutuhkan admin input data, tanpa pengalaman langsung diterima! Gaji 15 juta/bulan. " +
    "Hubungi WA saja untuk info lebih lanjut. Sebelum mulai kerja, kandidat wajib transfer " +
    "biaya training dan deposit alat kerja sebesar Rp 500.000.",
  link: "https://example.com/jobs/admin-suspicious-456",
};

async function main() {
  if (!fs.existsSync("data/profile.json")) {
    logger.error("data/profile.json belum ada. Salin dari data/profile.example.json dulu.");
    process.exit(1);
  }
  const profile = JSON.parse(fs.readFileSync("data/profile.json", "utf-8"));

  logger.info("=== Test 1: Scam Filter — lowongan normal (diharapkan PASS) ===");
  const scam1 = await scamCheck(DUMMY_JOB_NORMAL);
  console.log(scam1);

  logger.info("\n=== Test 2: Scam Filter — lowongan mencurigakan (diharapkan BLOCK/REVIEW) ===");
  const scam2 = await scamCheck(DUMMY_JOB_SUSPICIOUS);
  console.log(scam2);

  logger.info("\n=== Test 3: Matching Engine ===");
  const match = await scoreJob(DUMMY_JOB_NORMAL, profile);
  console.log(match);

  logger.info("\n=== Test 4: Cover Letter Generator ===");
  const letter = await generateCoverLetter(DUMMY_JOB_NORMAL, profile, "linkedin");
  console.log(letter);

  logger.success("\nSemua test selesai. Jika hasil di atas masuk akal, integrasi LLM sudah berfungsi dengan benar.");
}

main().catch((err) => {
  logger.error("Test pipeline gagal:", err.message);
  process.exit(1);
});
