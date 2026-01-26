export class CloseLoanForm {
  constructor({ form, notesInput, damagedInput, toaster, logger, onSubmit }) {
    this.form = form;
    this.notesInput = notesInput;
    this.damagedInput = damagedInput;
    this.toaster = toaster;
    this.logger = logger;
    this.onSubmit = onSubmit;

    this.handleSubmit = this.handleSubmit.bind(this);
    this.form?.addEventListener("submit", this.handleSubmit);
  }

  reset() {
    this.form?.reset();
  }

  handleSubmit(e) {
    e.preventDefault();
    const payload = {
      notes: this.notesInput?.value || "",
      damaged: this.damagedInput?.checked || false,
    };
    this.logger?.mock("Close loan", payload);
    this.toaster?.info("Loan closed successfully");
    if (this.onSubmit) this.onSubmit(payload);
  }
}
