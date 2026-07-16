import fs from "fs";
import { askJSON } from "../utils/llmClient.js";
import { logger } from "../utils/logger.js";

/**
 * =========================================================================
 * BAB 5 — MODUL DETEKSI LOWONGAN PALSU / SCAM (versi diperluas)
 * =========================================================================
 * Tiga lapis pengecekan, dari yang paling murah/cepat ke paling mahal:
 *   1. Heuristik kata kunci (instan, gratis, dua tingkat keparahan)
 *   2. Daftar blacklist/whitelist perusahaan yang Anda kelola sendiri
 *   3. Analisis LLM untuk konteks yang lebih halus (typo, nada bombastis, dst)
 *
 * Keputusan akhir mengambil yang PALING KETAT dari ketiga lapis di atas.
 */

// Indikator KUAT — kemunculannya sendirian sudah cukup untuk BLOCK,
// karena secara historis paling sering dipakai pada lowongan palsu.
const RED_FLAG_KEYWORDS_HIGH = [
  "transfer dana",
  "transfer terlebih dahulu",
  "transfer ke rekening",
  "kirim uang",
  "uang jaminan",
  "deposit alat kerja",
  "bayar deposit",
  "wajib membayar",
  "biaya pelatihan wajib",
  "dp seragam",
  "beli seragam terlebih dahulu",
  "kirim ktp dan rekening",
  "foto ktp dan buku tabungan",
];

// Indikator SEDANG — cukup mencurigakan untuk ditandai REVIEW (perlu cek
// manual), tapi tidak selalu berarti scam (mis. banyak lowongan remote
// legit juga hanya berkomunikasi lewat WhatsApp).
const RED_FLAG_KEYWORDS_MEDIUM = [
  "biaya admin",
  "biaya pendaftaran",
  "biaya training",
  "hanya chat wa",
  "hubungi wa saja",
  "wa only",
  "tanpa interview langsung diterima",
  "gaji tanpa pengalaman puluhan juta",
  "kerja santai gaji besar",
  "auto approve",
  "join sekarang gaji cair",
];

function quickHeuristicCheck(text) {
  const lower = (text || "").toLowerCase();
  const highHits = RED_FLAG_KEYWORDS_HIGH.filter((kw) => lower.includes(kw));
  const mediumHits = RED_FLAG_KEYWORDS_MEDIUM.filter((kw) => lower.includes(kw));
  return { highHits, mediumHits, hasHigh: highHits.length > 0, hasMedium: mediumHits.length > 0 };
}

/**
 * Cek nama perusahaan terhadap daftar blacklist/whitelist yang Anda kelola
 * sendiri di data/company_lists.json. Format file (opsional, dibuat otomatis
 * kosong jika belum ada):
 * { "blacklist": ["Nama Perusahaan Palsu"], "whitelist": ["Nama Perusahaan Terpercaya"] }
 */
function checkCompanyLists(companyName) {
  const listPath = "data/company_lists.json";
  if (!fs.existsSync(listPath)) return { onBlacklist: false, onWhitelist: false };

  try {
    const lists = JSON.parse(fs.readFileSync(listPath, "utf-8"));
    const name = (companyName || "").toLowerCase().trim();
    const onBlacklist = (lists.blacklist || []).some((n) => name.includes(n.toLowerCase()));
    const onWhitelist = (lists.whitelist || []).some((n) => name.includes(n.toLowerCase()));
    return { onBlacklist, onWhitelist };
  } catch (err) {
    logger.warn("Gagal membaca data/company_lists.json:", err.message);
    return { onBlacklist: false, onWhitelist: false };
  }
}

/**
 * Analisis satu lowongan untuk indikasi penipuan/lowongan palsu.
 * Mengembalikan status: PASS (aman dilanjutkan), REVIEW (perlu cek manual),
 * BLOCK (indikasi kuat scam, tidak diproses lebih lanjut secara otomatis).
 */
export async function scamCheck(job) {
  const jobText = `${job.title || ""} ${job.company || ""} ${job.description || ""}`;
  const heuristic = quickHeuristicCheck(jobText);
  const companyCheck = checkCompanyLists(job.company);

  // Whitelist eksplisit -> percaya penuh, tidak perlu panggil LLM (hemat biaya & waktu).
  if (companyCheck.onWhitelist) {
    logger.info(`[${job.company}] ada di whitelist Anda — dilewati dari pengecekan scam.`);
    return {
      heuristicFlag: false,
      heuristicHits: [],
      isSuspicious: false,
      riskLevel: "low",
      reasons: ["Perusahaan ada di whitelist pribadi Anda."],
      finalDecision: "PASS",
    };
  }

  // Blacklist eksplisit -> langsung BLOCK tanpa perlu panggil LLM.
  if (companyCheck.onBlacklist) {
    logger.warn(`[SCAM BLOCK] ${job.title} @ ${job.company} — perusahaan ada di blacklist Anda.`);
    return {
      heuristicFlag: true,
      heuristicHits: ["blacklist"],
      isSuspicious: true,
      riskLevel: "high",
      reasons: ["Perusahaan ada di blacklist pribadi Anda."],
      finalDecision: "BLOCK",
    };
  }

  let llmResult;
  const hasDescription = Boolean(job.description && job.description.trim().length >= 20);

  try {
    llmResult = await askJSON({
      maxTokens: 350,
      prompt: `Analisis lowongan kerja berikut untuk indikasi penipuan/lowongan palsu (konteks Indonesia).
Perhatikan pola umum: permintaan transfer uang di awal proses, permintaan data sensitif
(KTP/rekening/PIN) sebelum ada proses interview resmi, gaji tidak wajar dibanding posisi,
identitas perusahaan tidak jelas/tidak bisa diverifikasi, komunikasi hanya lewat WhatsApp
pribadi, bahasa bombastis/typo berlebihan, atau indikasi skema MLM/investasi berkedok lowongan.

PENTING: Jika field "Deskripsi" di bawah bertanda "-" atau sangat pendek, itu KETERBATASAN
TEKNIS proses pengambilan data (bukan berarti lowongan aslinya tidak punya deskripsi). JANGAN
jadikan deskripsi kosong sebagai satu-satunya alasan untuk risk_level "high" — cukup nilai
"medium" jika informasi memang tidak cukup untuk disimpulkan, dan sebutkan di reasons bahwa
data tidak lengkap.

Judul: ${job.title || "-"}
Perusahaan: ${job.company || "-"}
Lokasi: ${job.location || "-"}
Deskripsi: ${(job.description || "-").slice(0, 3000)}

Jawab HANYA JSON murni, tanpa markdown:
{"is_suspicious": boolean, "risk_level": "low|medium|high", "reasons": string[]}`,
    });
  } catch (err) {
    logger.error(`Scam check LLM gagal untuk "${job.title}":`, err.message);
    // Fail-safe: jika LLM gagal, jangan block otomatis, tapi tandai REVIEW
    // supaya tetap ditinjau manual daripada diproses tanpa pengecekan sama sekali.
    llmResult = { is_suspicious: false, risk_level: "medium", reasons: ["LLM check gagal — perlu tinjauan manual"] };
  }

  let llmRiskLevel = llmResult.risk_level || "medium";
  const reasons = Array.isArray(llmResult.reasons) ? [...llmResult.reasons] : [];
  if (reasons.length === 0) reasons.push("Tidak ada detail alasan dari LLM (respons tidak lengkap).");

  // Jaring pengaman kedua di level kode (tidak hanya mengandalkan LLM patuh
  // instruksi di atas): tanpa deskripsi, dan TANPA heuristik kuat, jangan
  // pernah BLOCK otomatis — turunkan ke maksimal "medium" (REVIEW). Data
  // tidak lengkap harus ditinjau manusia, bukan otomatis dianggap scam.
  if (!hasDescription && llmRiskLevel === "high" && !heuristic.hasHigh) {
    logger.warn(`[${job.title}] Deskripsi tidak berhasil diambil — risk_level LLM "high" diturunkan ke "medium" (REVIEW), bukan BLOCK otomatis.`);
    llmRiskLevel = "medium";
    reasons.unshift("Deskripsi lowongan tidak berhasil diambil (kemungkinan selector perlu diperbarui) — ditinjau manual, bukan otomatis diblokir.");
  }

  if (heuristic.hasHigh) {
    reasons.unshift(`Indikator kuat ditemukan: ${heuristic.highHits.join(", ")}`);
  }
  if (heuristic.hasMedium) {
    reasons.unshift(`Indikator sedang ditemukan: ${heuristic.mediumHits.join(", ")}`);
  }

  // Ambil keputusan PALING KETAT dari heuristik & LLM.
  let finalDecision = "PASS";
  if (heuristic.hasHigh || llmRiskLevel === "high") {
    finalDecision = "BLOCK";
  } else if (heuristic.hasMedium || llmRiskLevel === "medium") {
    finalDecision = "REVIEW";
  }

  if (finalDecision !== "PASS") {
    logger.warn(`[SCAM ${finalDecision}] ${job.title} @ ${job.company} — ${reasons.join(" | ")}`);
  }

  return {
    heuristicFlag: heuristic.hasHigh || heuristic.hasMedium,
    heuristicHits: [...heuristic.highHits, ...heuristic.mediumHits],
    isSuspicious: Boolean(llmResult.is_suspicious),
    riskLevel: llmRiskLevel,
    reasons,
    finalDecision, // PASS | REVIEW | BLOCK
  };
}
