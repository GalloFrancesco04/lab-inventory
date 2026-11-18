import { FormValidator } from "../../utils/Validators.js";

export class NewItemForm {
  constructor({
    form,
    codeInput,
    nameInput,
    descriptionInput,
    brandInput,
    modelInput,
    quantityInput,
    locationInput,
    toaster,
    onCreate,
  }) {
    this.form = form;
    this.codeInput = codeInput;
    this.nameInput = nameInput;
    this.descriptionInput = descriptionInput;
    this.brandInput = brandInput;
    this.modelInput = modelInput;
    this.quantityInput = quantityInput;
    this.locationInput = locationInput;
    this.toaster = toaster;
    this.onCreate = onCreate;
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

    const codeOk = this.validator.required(this.codeInput, "Code is required");
    const nameOk = this.validator.required(
      this.nameInput,
      "Type name is required",
    );

    const quantityOk = this.validator.required(
      this.quantityInput,
      "Quantity is required",
    );

    let quantityValueOk = true;
    if (quantityOk && this.quantityInput && this.quantityInput.value !== "") {
      const quantityVal = Number(this.quantityInput.value);
      if (Number.isNaN(quantityVal) || quantityVal < 0) {
        quantityValueOk = false;
        this.validator.addError(
          this.quantityInput,
          "Quantity must be a non-negative number",
        );
      }
    }

    if (!codeOk || !nameOk || !quantityOk || !quantityValueOk) {
      this.validator.displayErrors();
      if (this.toaster) this.toaster.error("Fix the highlighted errors");
      this.validator.focusFirstError();
      return false;
    }

    return true;
  }

  async handleSubmit(e) {
    e.preventDefault();
    if (!this.validate()) return;

    const typeData = {
      name: this.nameInput?.value?.trim() || "",
      code: this.codeInput?.value?.trim().toUpperCase() || "",
      description: this.descriptionInput?.value?.trim() || "",
      brand: this.brandInput?.value?.trim() || "",
      model: this.modelInput?.value?.trim() || "",
      quantity: Number(this.quantityInput?.value || 0),
      location: this.locationInput?.value?.trim() || "",
    };

    if (!this.onCreate) return;

    const result = await this.onCreate(typeData);
    if (result) {
      this.reset();
    }
  }
}
