import { Toaster } from "./toaster.js";
import { SearchManager } from "./SearchManager.js";
import { ModalManager } from "./ModalManager.js";
import { Logger } from "./logger.js";

export class Mediator {
  constructor() {
    this.toaster = new Toaster();
    this.searchManager = new SearchManager();
    this.modalManager = new ModalManager();
    this.logger = new Logger();
  }

  // Notify method for component communication
  notify(sender, event, data) {
    this.logger.debug(`Event: ${event}`, { sender, data });
  }
}
