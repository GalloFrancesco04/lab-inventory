export const logger = {
  debug(...args) {
    if (import.meta.env?.DEV) {
      console.log("[DEBUG]", ...args);
    }
  },

  info(...args) {
    console.info("[INFO]", ...args);
  },

  warn(...args) {
    console.warn("[WARN]", ...args);
  },

  error(...args) {
    console.error("[ERROR]", ...args);
  },

  // For temporary/mock actions during development
  mock(action, data) {
    console.log(`[MOCK] ${action}`, data || "");
  },
};
