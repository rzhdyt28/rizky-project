function timestamp() {
  return new Date().toISOString().replace("T", " ").slice(0, 19);
}

export const logger = {
  info: (...args) => console.log(`[${timestamp()}] [INFO]`, ...args),
  warn: (...args) => console.warn(`[${timestamp()}] [WARN]`, ...args),
  error: (...args) => console.error(`[${timestamp()}] [ERROR]`, ...args),
  success: (...args) => console.log(`[${timestamp()}] [OK]`, ...args),
};
