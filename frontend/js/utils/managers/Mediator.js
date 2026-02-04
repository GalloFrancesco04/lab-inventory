import { Toaster } from "../ui/Toaster.js";
import { SearchManager } from "./SearchManager.js";
import { ModalManager } from "./ModalManager.js";
import { Logger } from "../Logger.js";
import { ErrorHandler } from "./ErrorHandler.js";

export class Mediator {
  constructor() {
    this.toaster = new Toaster();
    this.searchManager = new SearchManager();
    this.modalManager = new ModalManager();
    this.logger = new Logger();
    this.errorHandler = new ErrorHandler(this.logger, this.toaster);

    // Event Bus for cross-module communication
    this.eventListeners = new Map();
  }

  // Emit an event that other modules can listen to
  emit(event, data) {
    this.logger.debug(`Event emitted: ${event}`, data);

    if (!this.eventListeners.has(event)) return;

    const listeners = this.eventListeners.get(event);
    listeners.forEach((callback) => {
      try {
        callback(data);
      } catch (error) {
        this.logger.error(`Error in event listener for ${event}`, error);
      }
    });
  }

  // Register a listener for an event
  on(event, callback) {
    if (!this.eventListeners.has(event)) {
      this.eventListeners.set(event, []);
    }

    this.eventListeners.get(event).push(callback);

    // Return unsubscribe function
    return () => {
      const listeners = this.eventListeners.get(event);
      const index = listeners.indexOf(callback);
      if (index > -1) {
        listeners.splice(index, 1);
      }
    };
  }

  // Register a one-time listener for an event
  once(event, callback) {
    const unsubscribe = this.on(event, (data) => {
      callback(data);
      unsubscribe();
    });
  }

  //Remove listener for an event
  off(event, callback) {
    if (!this.eventListeners.has(event)) return;

    const listeners = this.eventListeners.get(event);
    const index = listeners.indexOf(callback);
    if (index > -1) {
      listeners.splice(index, 1);
    }
  }

  handleError(message, error, context = {}) {
    return this.errorHandler.handle(message, error, context);
  }

  notify(sender, event, data) {
    this.logger.debug(`Notify: ${event}`, { sender, data });
    this.emit(event, data);
  }
}
