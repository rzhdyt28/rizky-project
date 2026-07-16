import Database from "better-sqlite3";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { logger } from "../utils/logger.js";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const DB_PATH = process.env.DATABASE_PATH || "./data/app.db";

let db = null;

export function getDb() {
  if (db) return db;

  const dir = path.dirname(DB_PATH);
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });

  db = new Database(DB_PATH);
  db.pragma("journal_mode = WAL");

  const schemaPath = path.join(__dirname, "schema.sql");
  const schema = fs.readFileSync(schemaPath, "utf-8");
  db.exec(schema);

  return db;
}

/** Cek apakah URL lowongan sudah pernah diproses, agar tidak duplikat. */
export function findByUrl(applyUrl) {
  return getDb()
    .prepare("SELECT * FROM applications WHERE apply_url = ?")
    .get(applyUrl);
}

export function insertApplication(data) {
  const stmt = getDb().prepare(`
    INSERT INTO applications
      (platform, job_title, company, apply_url, location, match_score, match_reason,
       scam_status, scam_reasons, cover_letter, screenshot_path, status)
    VALUES
      (@platform, @job_title, @company, @apply_url, @location, @match_score, @match_reason,
       @scam_status, @scam_reasons, @cover_letter, @screenshot_path, @status)
  `);

  const info = stmt.run({
    platform: data.platform,
    job_title: data.job_title || null,
    company: data.company || null,
    apply_url: data.apply_url,
    location: data.location || null,
    match_score: data.match_score ?? null,
    match_reason: data.match_reason || null,
    scam_status: data.scam_status || "PASS",
    scam_reasons: data.scam_reasons ? JSON.stringify(data.scam_reasons) : null,
    cover_letter: data.cover_letter || null,
    screenshot_path: data.screenshot_path || null,
    status: data.status || "draft",
  });

  return info.lastInsertRowid;
}

export function updateApplicationStatus(id, status) {
  return getDb()
    .prepare("UPDATE applications SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
    .run(status, id);
}

export function listApplications({ platform, status, scamStatus, limit = 100 } = {}) {
  let query = "SELECT * FROM applications WHERE 1=1";
  const params = [];

  if (platform) {
    query += " AND platform = ?";
    params.push(platform);
  }
  if (status) {
    query += " AND status = ?";
    params.push(status);
  }
  if (scamStatus) {
    query += " AND scam_status = ?";
    params.push(scamStatus);
  }

  query += " ORDER BY created_at DESC LIMIT ?";
  params.push(limit);

  return getDb().prepare(query).all(...params);
}

export function getApplicationById(id) {
  return getDb().prepare("SELECT * FROM applications WHERE id = ?").get(id);
}

export function insertRunLog(data) {
  const stmt = getDb().prepare(`
    INSERT INTO run_logs (platform, jobs_found, jobs_blocked_scam, jobs_matched, jobs_drafted, error)
    VALUES (@platform, @jobs_found, @jobs_blocked_scam, @jobs_matched, @jobs_drafted, @error)
  `);
  stmt.run({
    platform: data.platform,
    jobs_found: data.jobs_found || 0,
    jobs_blocked_scam: data.jobs_blocked_scam || 0,
    jobs_matched: data.jobs_matched || 0,
    jobs_drafted: data.jobs_drafted || 0,
    error: data.error || null,
  });
}

export function getStats() {
  const db = getDb();
  const total = db.prepare("SELECT COUNT(*) as n FROM applications").get().n;
  const byStatus = db.prepare("SELECT status, COUNT(*) as n FROM applications GROUP BY status").all();
  const byScam = db.prepare("SELECT scam_status, COUNT(*) as n FROM applications GROUP BY scam_status").all();
  const byPlatform = db.prepare("SELECT platform, COUNT(*) as n FROM applications GROUP BY platform").all();
  return { total, byStatus, byScam, byPlatform };
}

// Jalankan langsung: `node src/db/index.js --init` untuk inisialisasi database.
if (process.argv[1] === fileURLToPath(import.meta.url) && process.argv.includes("--init")) {
  getDb();
  logger.success(`Database siap di: ${DB_PATH}`);
}
