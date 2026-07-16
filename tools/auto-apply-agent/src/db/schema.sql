CREATE TABLE IF NOT EXISTS applications (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  platform TEXT NOT NULL,              -- linkedin, jobstreet, indeed, glints
  job_title TEXT,
  company TEXT,
  apply_url TEXT,
  location TEXT,
  match_score INTEGER,
  match_reason TEXT,
  scam_status TEXT DEFAULT 'PASS',     -- PASS, REVIEW, BLOCK
  scam_reasons TEXT,                   -- JSON array as text
  cover_letter TEXT,
  screenshot_path TEXT,
  status TEXT DEFAULT 'draft',         -- draft, applied, viewed, interview, rejected, skipped
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_applications_platform ON applications(platform);
CREATE INDEX IF NOT EXISTS idx_applications_status ON applications(status);
CREATE INDEX IF NOT EXISTS idx_applications_scam_status ON applications(scam_status);

CREATE TABLE IF NOT EXISTS run_logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  platform TEXT,
  jobs_found INTEGER DEFAULT 0,
  jobs_blocked_scam INTEGER DEFAULT 0,
  jobs_matched INTEGER DEFAULT 0,
  jobs_drafted INTEGER DEFAULT 0,
  error TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
