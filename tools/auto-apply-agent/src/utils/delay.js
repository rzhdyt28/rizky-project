/**
 * Delay acak antar-aksi agar pola aktivitas menyerupai manusia,
 * bukan pola robotik yang mudah dideteksi sistem anti-bot.
 */
export function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

export async function humanDelay(minMs = 4000, maxMs = 12000) {
  const ms = Math.floor(Math.random() * (maxMs - minMs + 1)) + minMs;
  await sleep(ms);
  return ms;
}
