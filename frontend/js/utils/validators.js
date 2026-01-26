export class FormValidator {
  constructor(formElement) {
    this.form = formElement;
    this.errors = new Map();
  }

  required(field, message = "This field is required") {
    if (!field || !field.value?.trim()) {
      this.addError(field, message);
      return false;
    }
    this.clearError(field);
    return true;
  }

  email(field, message = "Please enter a valid email") {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!field || !emailRegex.test(field.value?.trim())) {
      this.addError(field, message);
      return false;
    }
    this.clearError(field);
    return true;
  }

  minLength(field, length, message = `Minimum ${length} characters`) {
    if (!field || field.value?.trim().length < length) {
      this.addError(field, message);
      return false;
    }
    this.clearError(field);
    return true;
  }

  maxLength(field, length, message = `Maximum ${length} characters`) {
    if (!field || field.value?.trim().length > length) {
      this.addError(field, message);
      return false;
    }
    this.clearError(field);
    return true;
  }

  matches(field, pattern, message = "Invalid format") {
    if (!field || !pattern.test(field.value?.trim())) {
      this.addError(field, message);
      return false;
    }
    this.clearError(field);
    return true;
  }

  custom(field, validatorFn, message = "Validation failed") {
    if (!field || !validatorFn(field.value?.trim())) {
      this.addError(field, message);
      return false;
    }
    this.clearError(field);
    return true;
  }

  addError(field, message) {
    if (!field) return;
    field.classList.add("input-error");
    this.errors.set(field.id, message);
  }

  clearError(field) {
    if (!field) return;
    field.classList.remove("input-error");
    this.errors.delete(field.id);
  }

  clearAll() {
    this.errors.clear();
    if (this.form) {
      this.form.querySelectorAll(".input-error").forEach((input) => {
        input.classList.remove("input-error");
      });
    }
  }

  isValid() {
    return this.errors.size === 0;
  }

  hasErrors() {
    return this.errors.size > 0;
  }

  getErrors() {
    return Object.fromEntries(this.errors);
  }

  getFirstError() {
    if (this.errors.size === 0) return null;
    return this.errors.values().next().value;
  }

  focusFirstError() {
    if (this.errors.size === 0) return;
    const firstErrorId = this.errors.keys().next().value;
    const field = document.getElementById(firstErrorId);
    if (field) field.focus();
  }

  displayErrors() {
    if (!this.form) return;

    this.form.querySelectorAll(".field-error").forEach((el) => {
      el.style.display = "none";
      el.textContent = "";
    });

    this.errors.forEach((message, fieldId) => {
      const field = document.getElementById(fieldId);
      if (!field) return;

      let errorEl = field.parentElement.querySelector(".field-error");
      if (!errorEl) {
        errorEl = document.createElement("div");
        errorEl.className = "field-error";
        field.parentElement.appendChild(errorEl);
      }

      errorEl.textContent = message;
      errorEl.style.display = "block";
    });
  }
}
