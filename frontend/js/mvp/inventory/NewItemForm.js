import { FormValidator } from "../../utils/validators.js";

export class NewItemForm {
  constructor({
    form,
    typeSelect,
    nameInput,
    brandInput,
    modelInput,
    noteInput,
    locationInput,
    toaster,
    onCreate,
  }) {
    this.form = form;
    this.typeSelect = typeSelect;
    this.nameInput = nameInput;
    this.brandInput = brandInput;
    this.modelInput = modelInput;
    this.noteInput = noteInput;
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

    let priceOk = true;
    if (this.priceInput && this.priceInput.value) {
      const priceVal = Number(this.priceInput.value);
      if (Number.isNaN(priceVal) || priceVal < 0) {
        priceOk = false;
        this.validator.addError(
          this.priceInput,
          "Price must be a positive number",
        );
      }
    }

    if (!nameOk || !priceOk) {
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

    const itemData = {
      name: this.nameInput?.value?.trim() || "",
    };

    if (!this.onCreate) return;

    const result = this.onCreate(itemData);
    if (result) {
      this.reset();
    }
  }
}
