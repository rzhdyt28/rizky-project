import "dotenv/config";
import cron from "node-cron";
import fs from "fs";
import { fetchLinkedInJobs } from "./collector/linkedin.js";
import { fetchJobstreetJobs } from "./collector/jobstreet.js";
import { fetchIndeedJobs } from "./collector/indeed.js";
import { fetchGlintsJobs } from "./collector/glints.js";
import { scamCheck } from "./filter/scamFilter.js";
import { scoreJob } from "./matcher/matchJob.js";
import { generateCoverLetter } from "./generator/coverLetter.js";
import { prepareApplication } from "./executor/applyForm.js";
import { fetchJobDescription } from "./utils/fetchJobDetail.js";
import { findByUrl, insertApplication, insertRunLog } from "./db/index.js";
import { logger } from "./utils/logger.js";

const MODE = process.env.MODE || "review";
const MAX_JOBS = Number(process.env.MAX_JOBS_PER_PLATFORM_PER_RUN || 15);
const MATCH_THRESHOLD = 50;

const PLATFORM_COLLECTORS = {
  linkedin: fetchLinkedInJobs,
  jobstreet: fetchJobstreetJobs,
  indeed: fetchIndeedJobs,
  glints: fetchGlintsJobs,
};

function loadProfile() {
  const profilePath = "data/profile.json";
  if (!fs.existsSync(profilePath)) {
    throw new Error(
      "data/profile.json belum ada. Salin data/profile.example.json menjadi data/profile.json lalu isi data Anda."
    );
  }
  return JSON.parse(fs.readFileSync(profilePath, "utf-8"));
}

/**
 * Menjalankan pipeline penuh untuk satu platform:
 * collect -> scam filter -> match -> generate cover letter -> prepare draft -> simpan ke DB.
 */
async function runPlatform(platform, profile) {
  const collector = PLATFORM_COLLECTORS[platform];
  if (!collector) throw new Error(`Platform tidak dikenal: ${platform}`);

  const stats = { platform, jobs_found: 0, jobs_blocked_scam: 0, jobs_matched: 0, jobs_drafted: 0, error: null };

  try {
    const searchConfig = profile.search?.[platform] || {};

    // Mendukung banyak kata kunci per platform (mis. "System Administrator",
    // "IT Support", "IT Helpdesk", dst) — dijalankan berurutan (bukan
    // paralel, tetap hemat RAM), hasilnya digabung & di-dedup berdasarkan
    // link sebelum masuk ke pipeline scam filter/matching.
    // Tetap kompatibel ke belakang: field lama "keyword" (string tunggal)
    // masih didukung untuk profile.json versi sebelumnya.
    let keywords = searchConfig.keywords || profile.search?.default_keywords || [];
    if (typeof keywords === "string") keywords = [keywords];
    if (keywords.length === 0) {
      const singleKeyword = searchConfig.keyword || profile.search?.default_keyword || "";
      if (singleKeyword) keywords = [singleKeyword];
    }

    const location = searchConfig.location || profile.preferences?.location || "";

    if (keywords.length === 0) {
      logger.warn(`[${platform}] Tidak ada kata kunci pencarian di profile.json — dilewati.`);
      return stats;
    }

    // Bagi kuota MAX_JOBS secara merata ke tiap kata kunci, minimal 3 per kata kunci.
    const perKeywordLimit = Math.max(3, Math.ceil(MAX_JOBS / keywords.length));

    const seenLinks = new Set();
    const jobs = [];
    for (const keyword of keywords) {
      const result = await collector(keyword, location, perKeywordLimit);
      for (const job of result) {
        if (job.link && !seenLinks.has(job.link)) {
          seenLinks.add(job.link);
          jobs.push(job);
        }
      }
      if (jobs.length >= MAX_JOBS) break;
    }
    const limitedJobs = jobs.slice(0, MAX_JOBS);
    stats.jobs_found = limitedJobs.length;

    if (limitedJobs.length > 0) {
      const missingCompany = limitedJobs.filter((j) => !j.company).length;
      if (missingCompany / limitedJobs.length >= 0.7) {
        logger.warn(
          `[${platform}] ${missingCompany}/${limitedJobs.length} lowongan tidak punya data perusahaan — ` +
          `kemungkinan besar selector "company" di src/collector/${platform}.js sudah tidak cocok ` +
          `dengan struktur halaman saat ini. Cek lewat DevTools dan perbarui SELECTORS di file tersebut.`
        );
      }
    }

    for (const job of limitedJobs) {
      // Lewati jika URL ini sudah pernah diproses sebelumnya.
      if (findByUrl(job.link)) continue;

      // Ambil deskripsi lengkap dari halaman detail — kartu di list search
      // tidak memuat deskripsi, dan tanpa ini scam filter/matcher hanya
      // punya title+company sehingga mudah salah menilai (lihat README
      // bagian "Troubleshooting: semua lowongan ter-BLOCK").
      job.description = await fetchJobDescription(platform, job.link);

      const scam = await scamCheck(job);

      if (scam.finalDecision === "BLOCK") {
        stats.jobs_blocked_scam += 1;
        insertApplication({
          platform,
          job_title: job.title,
          company: job.company,
          apply_url: job.link,
          location: job.location,
          scam_status: "BLOCK",
          scam_reasons: scam.reasons,
          status: "skipped",
        });
        continue;
      }

      const { score, reason } = await scoreJob(job, profile);

      if (score < MATCH_THRESHOLD) {
        // Simpan juga yang tidak lolos skor, untuk transparansi di dashboard.
        insertApplication({
          platform,
          job_title: job.title,
          company: job.company,
          apply_url: job.link,
          location: job.location,
          match_score: score,
          match_reason: reason,
          scam_status: scam.finalDecision,
          scam_reasons: scam.reasons,
          status: "skipped",
        });
        continue;
      }

      stats.jobs_matched += 1;
      const coverLetter = await generateCoverLetter(job, profile, platform);
      const draft = await prepareApplication(job, profile, coverLetter, platform, MODE);

      if (draft.status === "draft_ready") stats.jobs_drafted += 1;

      insertApplication({
        platform,
        job_title: job.title,
        company: job.company,
        apply_url: job.link,
        location: job.location,
        match_score: score,
        match_reason: reason,
        scam_status: scam.finalDecision,
        scam_reasons: scam.reasons,
        cover_letter: coverLetter,
        screenshot_path: draft.screenshotPath || null,
        status: draft.status === "draft_ready" ? "draft" : "failed",
      });
    }
  } catch (err) {
    logger.error(`[${platform}] Pipeline gagal:`, err.message);
    stats.error = err.message;
  }

  insertRunLog(stats);
  logger.success(
    `[${platform}] Selesai — ditemukan: ${stats.jobs_found}, diblokir(scam): ${stats.jobs_blocked_scam}, cocok: ${stats.jobs_matched}, draft siap: ${stats.jobs_drafted}`
  );
  return stats;
}

/** Jalankan semua platform secara BERURUTAN (bukan paralel) untuk hemat RAM di VPS 1GB. */
async function runAllPlatforms() {
  const profile = loadProfile();
  const platforms = Object.keys(PLATFORM_COLLECTORS);

  logger.info(`=== Mulai run pipeline (mode: ${MODE}) ===`);
  for (const platform of platforms) {
    await runPlatform(platform, profile);
  }
  logger.info("=== Run pipeline selesai ===");
}

const isMain = process.argv[1] && process.argv[1].endsWith("scheduler.js");

if (isMain) {
  if (process.argv.includes("--once")) {
    // Mode testing: jalankan sekali langsung, tanpa cron.
    runAllPlatforms().catch((err) => {
      logger.error("Run manual gagal:", err.message);
      process.exit(1);
    });
  } else {
    // Jadwal default: tiap hari jam 08:00.
    logger.info("Scheduler aktif. Jadwal: setiap hari jam 08:00.");
    cron.schedule("0 8 * * *", () => {
      runAllPlatforms().catch((err) => logger.error("Run terjadwal gagal:", err.message));
    });
  }
}

export { runAllPlatforms, runPlatform };
