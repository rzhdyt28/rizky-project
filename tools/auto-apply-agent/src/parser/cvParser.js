import fs from "fs";
import path from "path";
import { askJSON } from "../utils/llmClient.js";
import { logger } from "../utils/logger.js";

async function extractText(filePath) {
  const ext = path.extname(filePath).toLowerCase();

  if (ext === ".pdf") {
    const pdfParse = (await import("pdf-parse")).default;
    const buffer = fs.readFileSync(filePath);
    const { text } = await pdfParse(buffer);
    return text;
  }

  if (ext === ".docx") {
    const mammoth = (await import("mammoth")).default;
    const { value } = await mammoth.extractRawText({ path: filePath });
    return value;
  }

  throw new Error(`Format file tidak didukung: ${ext}. Gunakan .pdf atau .docx`);
}

/**
 * Membaca CV (PDF/Docx) dan mengubahnya menjadi JSON terstruktur
 * (kontak, ringkasan, skill, pengalaman, pendidikan) menggunakan LLM.
 */
export async function parseCV(filePath) {
  if (!fs.existsSync(filePath)) {
    throw new Error(`File CV tidak ditemukan: ${filePath}`);
  }

  logger.info(`Membaca CV: ${filePath}`);
  const text = await extractText(filePath);

  if (!text || text.trim().length < 20) {
    throw new Error("Teks CV kosong atau terlalu pendek — cek apakah file hasil scan gambar (perlu OCR).");
  }

  const profile = await askJSON({
    maxTokens: 1200,
    prompt: `Ekstrak informasi berikut dari CV ini dan jawab HANYA dalam format JSON murni, tanpa markdown/penjelasan tambahan:
{
  "name": string,
  "email": string,
  "phone": string,
  "summary": string,
  "skills": string[],
  "experience": [{ "title": string, "company": string, "duration": string, "highlights": string[] }],
  "education": [{ "degree": string, "institution": string, "year": string }],
  "years_of_experience": number
}

Isi CV:
${text}`,
  });

  logger.success(`CV berhasil di-parse untuk: ${profile.name || "(nama tidak terdeteksi)"}`);
  return profile;
}
