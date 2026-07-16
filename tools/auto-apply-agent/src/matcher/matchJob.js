import { askJSON } from "../utils/llmClient.js";

/**
 * Menilai kecocokan sebuah lowongan terhadap profil kandidat.
 * Mengembalikan skor 0-100 beserta alasan singkat.
 */
export async function scoreJob(job, profile) {
  const result = await askJSON({
    maxTokens: 350,
    prompt: `Beri skor kecocokan 0-100 antara profil kandidat dan lowongan berikut, beserta alasan singkat (maks 2 kalimat).
Pertimbangkan: kecocokan skill, level pengalaman, lokasi/remote, dan preferensi kandidat.

Profil kandidat:
${JSON.stringify(profile, null, 2)}

Lowongan:
Judul: ${job.title || "-"}
Perusahaan: ${job.company || "-"}
Lokasi: ${job.location || "-"}
Deskripsi: ${(job.description || "-").slice(0, 3000)}

Jawab HANYA JSON murni:
{"score": number, "reason": string}`,
  });

  return {
    score: Math.max(0, Math.min(100, Number(result.score) || 0)),
    reason: result.reason || "",
  };
}
