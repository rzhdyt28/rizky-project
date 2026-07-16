import { logger } from "./logger.js";

/**
 * =========================================================================
 * LLM CLIENT — bisa ganti provider lewat .env, tanpa ubah kode lain
 * =========================================================================
 * Semua modul lain (scamFilter, matchJob, coverLetter, cvParser) HANYA
 * memanggil askJSON() / askText() dari file ini — mereka tidak tahu dan
 * tidak peduli provider mana yang sebenarnya dipakai di belakang layar.
 * Itu artinya: ganti provider = ganti isi file ini + .env saja.
 *
 * Provider yang didukung (atur lewat LLM_PROVIDER di .env):
 *   - "groq"        (default, DIREKOMENDASIKAN — gratis permanen, cepat, kuota besar)
 *   - "gemini"       (gratis permanen, konteks besar)
 *   - "openrouter"   (gratis untuk model tertentu, banyak pilihan model)
 *   - "anthropic"    (berbayar, kualitas tertinggi — dipakai versi sebelumnya)
 *
 * Tidak menambah dependency npm baru — memakai fetch bawaan Node.js 18+.
 */

const PROVIDER = (process.env.LLM_PROVIDER || "groq").toLowerCase();

const PROVIDER_CONFIG = {
  groq: {
    format: "openai",
    url: "https://api.groq.com/openai/v1/chat/completions",
    apiKeyEnv: "GROQ_API_KEY",
    defaultModel: "llama-3.3-70b-versatile",
    signupHint: "Daftar gratis tanpa kartu kredit di https://console.groq.com/keys",
  },
  openrouter: {
    format: "openai",
    url: "https://openrouter.ai/api/v1/chat/completions",
    apiKeyEnv: "OPENROUTER_API_KEY",
    defaultModel: "deepseek/deepseek-r1:free",
    signupHint: "Daftar gratis di https://openrouter.ai/keys",
  },
  gemini: {
    format: "gemini",
    apiKeyEnv: "GEMINI_API_KEY",
    defaultModel: "gemini-2.5-flash",
    signupHint: "Daftar gratis tanpa kartu kredit di https://aistudio.google.com/apikey",
  },
  anthropic: {
    format: "anthropic",
    url: "https://api.anthropic.com/v1/messages",
    apiKeyEnv: "ANTHROPIC_API_KEY",
    defaultModel: "claude-sonnet-4-6",
    signupHint: "Berbayar — https://console.anthropic.com",
  },
};

function getConfig() {
  const config = PROVIDER_CONFIG[PROVIDER];
  if (!config) {
    throw new Error(
      `LLM_PROVIDER="${PROVIDER}" tidak dikenal. Pilihan: ${Object.keys(PROVIDER_CONFIG).join(", ")}`
    );
  }

  const apiKey = process.env[config.apiKeyEnv];
  if (!apiKey || apiKey.startsWith("your_")) {
    throw new Error(
      `${config.apiKeyEnv} belum diisi di .env. ${config.signupHint}`
    );
  }

  const model = process.env.LLM_MODEL || config.defaultModel;
  return { ...config, apiKey, model };
}

async function callOpenAICompatible({ url, apiKey, model, prompt, maxTokens }) {
  const res = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${apiKey}`,
    },
    body: JSON.stringify({
      model,
      max_tokens: maxTokens,
      messages: [{ role: "user", content: prompt }],
    }),
  });

  if (!res.ok) {
    const body = await res.text().catch(() => "");
    throw new Error(`Request ke ${url} gagal (HTTP ${res.status}): ${body.slice(0, 300)}`);
  }

  const data = await res.json();
  const text = data?.choices?.[0]?.message?.content;
  if (typeof text !== "string") {
    throw new Error(`Respons tidak sesuai format yang diharapkan: ${JSON.stringify(data).slice(0, 300)}`);
  }
  return text;
}

async function callGemini({ apiKey, model, prompt, maxTokens }) {
  const url = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;

  const res = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      contents: [{ parts: [{ text: prompt }] }],
      generationConfig: { maxOutputTokens: maxTokens },
    }),
  });

  if (!res.ok) {
    const body = await res.text().catch(() => "");
    throw new Error(`Request ke Gemini gagal (HTTP ${res.status}): ${body.slice(0, 300)}`);
  }

  const data = await res.json();
  const text = data?.candidates?.[0]?.content?.parts?.[0]?.text;
  if (typeof text !== "string") {
    throw new Error(`Respons Gemini tidak sesuai format yang diharapkan: ${JSON.stringify(data).slice(0, 300)}`);
  }
  return text;
}

async function callAnthropic({ url, apiKey, model, prompt, maxTokens }) {
  const res = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "x-api-key": apiKey,
      "anthropic-version": "2023-06-01",
    },
    body: JSON.stringify({
      model,
      max_tokens: maxTokens,
      messages: [{ role: "user", content: prompt }],
    }),
  });

  if (!res.ok) {
    const body = await res.text().catch(() => "");
    throw new Error(`Request ke Anthropic gagal (HTTP ${res.status}): ${body.slice(0, 300)}`);
  }

  const data = await res.json();
  const text = data?.content
    ?.filter((b) => b.type === "text")
    .map((b) => b.text)
    .join("\n");
  if (typeof text !== "string" || text.length === 0) {
    throw new Error(`Respons Anthropic tidak sesuai format yang diharapkan: ${JSON.stringify(data).slice(0, 300)}`);
  }
  return text;
}

/** Kirim prompt ke provider yang sedang aktif, kembalikan teks mentah. */
async function sendPrompt(prompt, maxTokens) {
  const config = getConfig();

  if (config.format === "openai") {
    return callOpenAICompatible({ url: config.url, apiKey: config.apiKey, model: config.model, prompt, maxTokens });
  }
  if (config.format === "gemini") {
    return callGemini({ apiKey: config.apiKey, model: config.model, prompt, maxTokens });
  }
  if (config.format === "anthropic") {
    return callAnthropic({ url: config.url, apiKey: config.apiKey, model: config.model, prompt, maxTokens });
  }
  throw new Error(`Format provider "${config.format}" belum diimplementasikan.`);
}

/** Kirim prompt yang mengharapkan balasan JSON murni. */
export async function askJSON({ maxTokens = 500, prompt }) {
  const rawText = await sendPrompt(prompt, maxTokens);
  const cleaned = rawText.replace(/^```json\s*|^```\s*|```$/gm, "").trim();

  try {
    return JSON.parse(cleaned);
  } catch (err) {
    logger.error("Gagal parse JSON dari respons LLM:", cleaned);
    throw new Error(`Respons LLM bukan JSON valid: ${err.message}`);
  }
}

/** Kirim prompt yang mengharapkan balasan teks biasa (mis. cover letter). */
export async function askText({ maxTokens = 600, prompt }) {
  const text = await sendPrompt(prompt, maxTokens);
  return text.trim();
}

/** Berguna untuk scripts/checkEnv.js — info provider aktif tanpa membocorkan key. */
export function getActiveProviderInfo() {
  const config = PROVIDER_CONFIG[PROVIDER];
  if (!config) return { provider: PROVIDER, valid: false };
  const apiKey = process.env[config.apiKeyEnv];
  return {
    provider: PROVIDER,
    model: process.env.LLM_MODEL || config.defaultModel,
    apiKeyEnv: config.apiKeyEnv,
    apiKeySet: Boolean(apiKey && !apiKey.startsWith("your_")),
  };
}
