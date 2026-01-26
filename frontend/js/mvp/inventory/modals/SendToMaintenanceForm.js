import { FormValidator } from "../../../utils/validators.js";

export class SendToMaintenanceForm {
  constructor({ form, descInput, technicianSelect, toaster, onSubmit }) {
    this.form = form;
    this.descInput = descInput;
    this.technicianSelect = technicianSelect;
    this.toaster = toaster;
    this.onSubmit = onSubmit;
    this.validator = new FormValidator(form);

    this.handleSubmit = this.handleSubmit.bind(this);
    this.form?.addEventListener("submit", this.handleSubmit);
  }

  destroy() {
    this.form?.removeEventListener("submit", this.handleSubmit);
  }

  reset() {
    this.validator.clearAll();
    this.form?.reset();
  }

  validate() {
    this.validator.clearAll();

    const descOk = this.validator.required(
      this.descInput,
      "Problem description is required",
    );
    const techOk = this.validator.required(
      this.technicianSelect,
      "Please select a technician",
    );

    if (!descOk || !techOk) {
      this.validator.displayErrors();
      if (this.toaster) this.toaster.error("Fix the highlighted errors");
      this.validator.focusFirstError();
      return false;
    }

    return true;
  }

  handleSubmit(e) {
    e.preventDefault();
    if (!this.validate()) return;

    const description = this.descInput?.value?.trim() || "";
    const technician = this.technicianSelect?.value || "";

    if (!this.onSubmit) return;

    const result = this.onSubmit({ description, technician });
    if (result) {
      this.reset();
    }
  }
}
