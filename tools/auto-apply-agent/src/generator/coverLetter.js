import { askText } from "../utils/llmClient.js";

const TONE_BY_PLATFORM = {
  linkedin: "formal dan profesional",
  jobstreet: "formal namun ringkas",
  indeed: "profesional dan lugas",
  glints: "profesional tapi boleh sedikit lebih santai/personal",
};

/**
 * Membuat cover letter singkat yang disesuaikan dengan lowongan,
 * profil kandidat, dan nada khas platform tempat lowongan ditemukan.
 */
export async function generateCoverLetter(job, profile, platform = "linkedin") {
  const tone = TONE_BY_PLATFORM[platform] || "profesional";

  const letter = await askText({
    maxTokens: 500,
    prompt: `Buatkan cover letter singkat (maksimal 4 paragraf pendek) dengan nada ${tone},
dalam Bahasa Indonesia, untuk melamar posisi "${job.title}" di perusahaan "${job.company}".

Gunakan data profil berikut untuk menyoroti pengalaman & skill yang relevan (jangan mengarang
pengalaman yang tidak ada di profil):
${JSON.stringify(profile, null, 2)}

Ringkasan lowongan:
${(job.description || "-").slice(0, 2000)}

Tulis langsung isi suratnya saja, tanpa placeholder seperti [Nama Anda] yang belum terisi —
gunakan data yang tersedia di profil.`,
  });

  return letter;
}
