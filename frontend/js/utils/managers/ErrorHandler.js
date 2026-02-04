export class ErrorHandler {
  constructor(logger = null, toaster = null) {
    this.logger = logger;
    this.toaster = toaster;
    this.errorHistory = [];
    this.maxHistory = 50;
  }

  handle(message, error, context = {}) {
    const errorEntry = {
      message,
      error: error instanceof Error ? error : new Error(String(error)),
      context: {
        view: context.view || "unknown",
        method: context.method || "unknown",
        timestamp: context.timestamp || new Date(),
        severity: context.severity || "error",
        ...context,
      },
    };

    // Add to history
    this.addToHistory(errorEntry);

    // Log the error
    this.log(errorEntry);

    // Notify user
    this.notify(message, errorEntry.context.severity);
    return errorEntry;
  }

  log(errorEntry) {
    if (!this.logger) {
      console.error("[ErrorHandler]", errorEntry);
      return;
    }

    const { message, error, context } = errorEntry;
    const logMessage = `[${context.view}.${context.method}] ${message}`;

    if (context.severity === "error") {
      this.logger.error(logMessage, error);
    } else if (context.severity === "warning") {
      this.logger.warn?.(logMessage, error) || console.warn(logMessage, error);
    } else {
      this.logger.debug?.(logMessage, error) || console.log(logMessage, error);
    }
  }

  notify(message, severity = "error") {
    if (!this.toaster) return;

    if (severity === "error") {
      this.toaster.error(message);
    } else if (severity === "warning") {
      this.toaster.warning(message);
    } else if (severity === "info") {
      this.toaster.info(message);
    }
  }

  addToHistory(errorEntry) {
    this.errorHistory.push(errorEntry);

    // Keep history size bounded
    if (this.errorHistory.length > this.maxHistory) {
      this.errorHistory.shift();
    }
  }

  getHistory() {
    return [...this.errorHistory];
  }

  clearHistory() {
    this.errorHistory = [];
  }

  getRecentErrors(count = 10) {
    return this.errorHistory.slice(-count);
  }

  exportHistory() {
    return JSON.stringify(
      this.errorHistory.map((entry) => ({
        ...entry,
        error: {
          message: entry.error.message,
          stack: entry.error.stack,
        },
      })),
      null,
      2,
    );
  }
}
