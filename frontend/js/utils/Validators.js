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

export class ValidationError extends Error {
  constructor(message, field = null) {
    super(message);
    this.name = "ValidationError";
    this.field = field;
  }
}

export const LoanValidator = {
  validateCreate(loan) {
    if (!loan) throw new ValidationError("Loan data is required");
    if (!loan.client || typeof loan.client !== "string")
      throw new ValidationError("Valid client name is required", "client");
    if (!loan.startDate)
      throw new ValidationError("Start date is required", "startDate");
    if (!loan.endDate)
      throw new ValidationError("End date is required", "endDate");

    const startDate = new Date(loan.startDate);
    const endDate = new Date(loan.endDate);

    if (isNaN(startDate.getTime()))
      throw new ValidationError("Invalid start date format", "startDate");
    if (isNaN(endDate.getTime()))
      throw new ValidationError("Invalid end date format", "endDate");
    if (endDate <= startDate)
      throw new ValidationError("End date must be after start date", "endDate");

    if (!Array.isArray(loan.items) || loan.items.length === 0)
      throw new ValidationError("At least one item is required", "items");

    loan.items.forEach((item, index) => {
      if (!item.id || typeof item.id !== "number")
        throw new ValidationError(`Item ${index + 1} has invalid ID`, "items");
    });

    return true;
  },

  validateClose(loanId, actualReturnDate) {
    if (!loanId || typeof loanId !== "number")
      throw new ValidationError("Valid loan ID is required", "loanId");
    if (!actualReturnDate)
      throw new ValidationError("Return date is required", "actualReturnDate");

    const returnDate = new Date(actualReturnDate);
    if (isNaN(returnDate.getTime()))
      throw new ValidationError(
        "Invalid return date format",
        "actualReturnDate",
      );

    return true;
  },
};

export const InventoryValidator = {
  validateCreate(item) {
    if (!item) throw new ValidationError("Item data is required");
    if (!item.location || typeof item.location !== "string")
      throw new ValidationError("Valid location is required", "location");
    if (item.location.trim().length === 0)
      throw new ValidationError("Location cannot be empty", "location");
    return true;
  },

  validateStatusTransition(currentStatus, newStatus) {
    const validTransitions = {
      available: ["loaned", "maintenance", "outofservice"],
      loaned: ["available", "damaged"],
      maintenance: ["available", "damaged", "outofservice"],
      damaged: ["maintenance", "outofservice"],
      outofservice: ["available"],
    };

    if (!validTransitions[currentStatus])
      throw new ValidationError(
        `Unknown status: ${currentStatus}`,
        "currentStatus",
      );
    if (!validTransitions[currentStatus].includes(newStatus))
      throw new ValidationError(
        `Cannot transition from ${currentStatus} to ${newStatus}`,
        "newStatus",
      );
    return true;
  },
};

export const MaintenanceValidator = {
  validateCreate(maintenance) {
    if (!maintenance) throw new ValidationError("Maintenance data is required");
    if (!maintenance.itemId || typeof maintenance.itemId !== "number")
      throw new ValidationError("Valid item ID is required", "itemId");
    if (!maintenance.description || typeof maintenance.description !== "string")
      throw new ValidationError("Description is required", "description");
    if (maintenance.description.trim().length === 0)
      throw new ValidationError("Description cannot be empty", "description");
    return true;
  },

  validateClose(maintenanceId, notes) {
    if (!maintenanceId || typeof maintenanceId !== "number")
      throw new ValidationError(
        "Valid maintenance ID is required",
        "maintenanceId",
      );
    if (!notes || typeof notes !== "string")
      throw new ValidationError("Closing notes are required", "notes");
    if (notes.trim().length === 0)
      throw new ValidationError("Closing notes cannot be empty", "notes");
    return true;
  },
};

export const ClientValidator = {
  validateCreate(client) {
    if (!client) throw new ValidationError("Client data is required");
    if (!client.name || typeof client.name !== "string")
      throw new ValidationError("Valid client name is required", "name");
    if (client.name.trim().length === 0)
      throw new ValidationError("Name cannot be empty", "name");
    if (!client.email || typeof client.email !== "string")
      throw new ValidationError("Valid email is required", "email");

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(client.email))
      throw new ValidationError("Invalid email format", "email");
    return true;
  },
};
