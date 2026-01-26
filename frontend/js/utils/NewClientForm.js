import { FormValidator } from "./validators.js";

export class NewClientForm {
  constructor({ form, firstInput, lastInput, emailInput, toaster, onCreate }) {
    this.form = form;
    this.firstInput = firstInput;
    this.lastInput = lastInput;
    this.emailInput = emailInput;
    this.toaster = toaster;
    this.onCreate = onCreate;
    this.validator = new FormValidator(form);

    this.handleSubmit = this.handleSubmit.bind(this);
    this.form?.addEventListener("submit", this.handleSubmit);
  }

  setOnCreate(callback) {
    this.onCreate = callback;
  }

  reset() {
    this.validator.clearAll();
    this.form?.reset();
  }

  validate() {
    this.validator.clearAll();

    const first = this.firstInput?.value?.trim() || "";
    const email = this.emailInput?.value?.trim() || "";

    const requiredOk = this.validator.required(
      this.firstInput,
      "First name is required",
    );
    const emailOk = email
      ? this.validator.email(this.emailInput, "Enter a valid email")
      : true;

    if (!requiredOk || !emailOk) {
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

    const first = this.firstInput?.value?.trim() || "";
    const last = this.lastInput?.value?.trim() || "";
    const email = this.emailInput?.value?.trim() || "";
    const displayName = [first, last].filter(Boolean).join(" ") || first;

    if (!this.onCreate) return;

    const created = this.onCreate({ displayName, email, first, last });
    if (created) {
      this.reset();
    }
  }
}
