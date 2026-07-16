import "dotenv/config";
import fs from "fs";
import { logger } from "../src/utils/logger.js";
import { getActiveProviderInfo } from "../src/utils/llmClient.js";

let ok = true;

function check(label, condition, hint) {
  if (condition) {
    logger.success(`${label}`);
  } else {
    logger.error(`${label} — ${hint}`);
    ok = false;
  }
}

logger.info("Memeriksa kesiapan environment...\n");

const providerInfo = getActiveProviderInfo();
logger.info(`Provider LLM aktif: ${providerInfo.provider} (model: ${providerInfo.model || "-"})`);

check(
  `${providerInfo.apiKeyEnv || "API key"} terisi`,
  providerInfo.apiKeySet,
  `Salin .env.example menjadi .env dan isi ${providerInfo.apiKeyEnv || "API key"} untuk provider "${providerInfo.provider}".`
);

check(
  "data/profile.json ada",
  fs.existsSync("data/profile.json"),
  "Salin data/profile.example.json menjadi data/profile.json lalu isi data Anda."
);

const platforms = ["linkedin", "jobstreet", "indeed", "glints"];
for (const p of platforms) {
  check(
    `Session login "${p}" ada`,
    fs.existsSync(`data/sessions/${p}.json`),
    `Jalankan: npm run save-session -- ${p}`
  );
}

check("Database ada", fs.existsSync(process.env.DATABASE_PATH || "./data/app.db"), "Jalankan: npm run init-db");

console.log("");
if (ok) {
  logger.success("Semua pemeriksaan lolos. Siap menjalankan: npm start -- --once");
} else {
  logger.warn("Beberapa hal belum siap — lengkapi item di atas sebelum menjalankan pipeline.");
}
