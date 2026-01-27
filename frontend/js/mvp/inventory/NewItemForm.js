import { FormValidator } from "../../utils/validators.js";

export class NewItemForm {
  constructor({
    form,
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

    if (!nameOk || !quantityOk || !quantityValueOk) {
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

    const typeName = this.nameInput?.value?.trim() || "";
    const code = typeName.substring(0, 4).toUpperCase().padEnd(4, "X");

    const typeData = {
      name: typeName,
      code: code,
      description: this.descriptionInput?.value?.trim() || "",
      brand: this.brandInput?.value?.trim() || "",
      model: this.modelInput?.value?.trim() || "",
      quantity: Number(this.quantityInput?.value || 0),
      location: this.locationInput?.value?.trim() || "",
    };

    if (!this.onCreate) return;

    const result = this.onCreate(typeData);
    if (result) {
      this.reset();
    }
  }
}
