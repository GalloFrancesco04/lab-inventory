import { BaseView } from "../core/BaseView.js";
import { formatDate } from "../../utils/Transformers.js";
import { FormValidator } from "../../utils/Validators.js";
import { ItemDetailModal } from "./modals/ItemDetailModal.js";
import { ObjectListModal } from "./modals/ObjectListModal.js";
import { SendToMaintenanceForm } from "./modals/SendToMaintenanceForm.js";
import { NewItemForm } from "./NewItemForm.js";
import {
  getItemStatusBadgeClass,
  getMaintenanceTypeBadgeClass,
} from "../../utils/ui/Badges.js";
import {
  VALIDATION_ERRORS,
  SUCCESS_MESSAGES,
  ERROR_MESSAGES,
} from "../../utils/Messages.js";

class InventoryView extends BaseView {
  constructor(presenter, mediator) {
    super(presenter, mediator);
    this.initializeView({
      viewSelector: "#inventory-view",
      searchViewName: "inventory",
      renderMethod: "render",
    });
  }

  setupElements() {
    // New Item Modal
    this.newItemForm = this.getElement("new-item-form");
    this.newItemCode = this.getElement("new-item-code");
    this.newItemName = this.getElement("new-item-name");
    this.newItemDescription = this.getElement("new-item-description");
    this.newItemBrand = this.getElement("new-item-brand");
    this.newItemModel = this.getElement("new-item-model");
    this.newItemQuantity = this.getElement("new-item-quantity");
    this.newItemLocation = this.getElement("new-item-location");
    this.newItemNote = this.getElement("new-item-description");
    this.newItemBtn = this.getElement("new-item-btn");

    // New Instance Modal
    this.newInstanceForm = this.getElement("new-instance-form");
    this.newInstanceLocation = this.getElement("new-instance-location");

    // New Type Modal

    // Detail Modal
    this.itemTitle = this.getElement("item-modal-title");
    this.itemName = this.getElement("item-name");
    this.itemType = this.getElement("item-type");
    this.itemStatus = this.getElement("item-status");
    this.itemId = this.getElement("item-id");
    this.itemLocation = this.getElement("item-location");
    this.itemBrand = this.getElement("item-brand");
    this.itemModel = this.getElement("item-model");
    this.maintTbody = this.getElement("item-maintenance-tbody");

    // Edit Item Modal
    this.editItemForm = this.getElement("edit-item-form");
    this.editItemName = this.getElement("edit-item-name");
    this.editItemBrand = this.getElement("edit-item-brand");
    this.editItemModel = this.getElement("edit-item-model");
    this.editItemLocation = this.getElement("edit-item-location");
    this.editItemStatus = this.getElement("edit-item-status");
    this.editItemBtn = this.getElement("edit-item-btn");

    // Buttons
    this.sendToMaintBtn = this.getElement("send-to-maintenance-btn");

    // Send to Maintenance Modal
    this.sendToMaintForm = this.getElement("send-to-maintenance-form");
    this.maintenanceDescInput = this.getElement("maintenance-desc");
    this.maintenanceTechnicianSelect = this.getElement(
      "maintenance-technician",
    );
    this.sendToMaintCancelBtn = this.getElement("send-to-maintenance-cancel");

    this.currentItemId = null;
    this.currentTypeCode = null;
    this.pendingInstanceTypeCode = null;
    this.pendingInstanceTypeName = null;

    // Object list modal (to show instances of a type)
    this.objectListModal = new ObjectListModal({
      modalId: "object-list-modal",
      openModal: (id) => this.openModal(id),
      presenter: this.presenter,
      mediator: this.mediator,
      onObjectClick: (objectId) => this.openItemModal(objectId),
      onNewInstance: (typeCode, typeName) =>
        this.openNewInstanceForm(typeCode, typeName),
    });

    this.newItemFormHandler = new NewItemForm({
      form: this.newItemForm,
      codeInput: this.newItemCode,
      nameInput: this.newItemName,
      descriptionInput: this.newItemNote,
      brandInput: this.newItemBrand,
      modelInput: this.newItemModel,
      quantityInput: this.newItemQuantity,
      locationInput: this.newItemLocation,
      toaster: this.mediator.toaster,
      onCreate: async (typeData) => {
        try {
          const newType = await this.presenter.addObjectType(typeData);
          if (newType) {
            this.mediator.toaster.success(
              `Type "${newType.name}" added successfully`,
            );
            this.closeModal("new-item-modal");
            return true;
          }
          this.mediator.toaster.error("A type with this code already exists");
          return false;
        } catch (error) {
          this.handleError("Unable to add type", error);
          return false;
        }
      },
    });

    this.itemDetailModal = new ItemDetailModal({
      modalId: "item-detail-modal",
      openModal: (id) => this.openModal(id),
      fields: {
        title: this.itemTitle,
        name: this.itemName,
        type: this.itemType,
        status: this.itemStatus,
        id: this.itemId,
        location: this.itemLocation,
        brand: this.itemBrand,
        model: this.itemModel,
      },
      renderMaintenance: (list) => this.renderMaintenance(list),
      sendToMaintenanceBtn: this.sendToMaintBtn,
      editItemBtn: this.editItemBtn,
      toaster: this.mediator.toaster,
      mediator: this.mediator,
    });

    this.maintenanceForm = new SendToMaintenanceForm({
      form: this.sendToMaintForm,
      descInput: this.maintenanceDescInput,
      technicianSelect: this.maintenanceTechnicianSelect,
      toaster: this.mediator.toaster,
      onSubmit: async ({ description, technician }) => {
        try {
          const result = await this.presenter.createMaintenance(
            this.currentItemId,
            description,
            technician,
          );

          if (result) {
            this.mediator.toaster.success(
              "Item sent to maintenance successfully",
            );
            this.closeModal("send-to-maintenance-modal");
            this.closeModal("item-detail-modal");
            return true;
          }
          this.mediator.toaster.error("Unable to create maintenance record");
          return false;
        } catch (error) {
          this.handleError("Unable to create maintenance record", error);
          return false;
        }
      },
    });

    this.editItemValidator = new FormValidator(this.editItemForm);
  }

  setupModals() {
    this.registerModals({
      "new-item-modal": {
        options: {
          onOpen: () => {
            this.newItemFormHandler?.reset();
          },
          onClose: () => this.newItemFormHandler?.reset(),
        },
        closeButtons: ["new-item-close", "new-item-cancel"],
      },
      "item-detail-modal": {
        closeButtons: ["item-detail-close"],
      },
      "object-list-modal": {
        closeButtons: ["object-list-close"],
      },
      "new-instance-modal": {
        options: {
          onOpen: () => {
            this.newInstanceForm?.reset();
          },
          onClose: () => {
            this.newInstanceForm?.reset();
            this.pendingInstanceTypeCode = null;
            this.pendingInstanceTypeName = null;
          },
        },
        closeButtons: ["new-instance-close", "new-instance-cancel"],
      },
      "edit-item-modal": {
        options: {
          onOpen: () => {
            this.editItemForm?.reset();
            this.editItemValidator?.clearAll();
            this.populateEditItemForm();
          },
          onClose: () => this.editItemValidator?.clearAll(),
        },
        closeButtons: ["edit-item-close", "edit-item-cancel"],
      },
      "send-to-maintenance-modal": {
        options: {
          onOpen: () => {
            this.maintenanceForm?.reset();
          },
          onClose: () => this.maintenanceForm?.reset(),
        },
        closeButtons: ["send-to-maintenance-close"],
      },
    });
  }

  setupEventListeners() {
    // New Item Button
    this.newItemBtn?.addEventListener("click", () => {
      this.openModal("new-item-modal");
    });

    // Event delegation for table rows
    const tbody = this.getElement("inventory-tbody")?.parentElement;
    tbody?.addEventListener("click", (e) => {
      const row = e.target.closest("[data-item-id]");
      if (row) {
        this.openItemModal(row.dataset.itemId);
      }
    });

    this.sendToMaintBtn?.addEventListener("click", () => {
      this.openModal("send-to-maintenance-modal");
    });

    this.editItemBtn?.addEventListener("click", () => {
      if (!this.currentItemId) return;
      this.openModal("edit-item-modal");
    });

    this.sendToMaintCancelBtn?.addEventListener("click", () => {
      this.closeModal("send-to-maintenance-modal");
    });

    // New Instance Form
    this.newInstanceForm?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const location = this.newInstanceLocation?.value?.trim();

      if (!location) {
        this.mediator.toaster.error("Location is required");
        return;
      }

      try {
        const result = await this.presenter.addObjectInstance(
          this.pendingInstanceTypeCode,
          location,
        );

        if (result) {
          this.mediator.toaster.success(
            `New instance of "${this.pendingInstanceTypeName}" created successfully`,
          );
          this.closeModal("new-instance-modal");
          this.objectListModal?.refreshCurrentView();
        } else {
          this.mediator.toaster.error("Unable to create new instance");
        }
      } catch (error) {
        this.handleError("Unable to create new instance", error);
      }
    });

    this.editItemForm?.addEventListener("submit", async (e) => {
      e.preventDefault();
      this.editItemValidator?.clearAll();

      const newStatus = this.editItemStatus?.value || "";

      // Get current item to check original status
      let currentItem = null;
      try {
        currentItem = this.presenter.getItemById(this.currentItemId);
      } catch (error) {
        this.handleError("Unable to load item", error);
        return;
      }

      // Business rules (see backend item states): only damaged items can be
      // retired as Out of Service; other changes follow loans and maintenance
      if (currentItem && currentItem.status !== newStatus) {
        const allowedTransitions = {
          damaged: ["outofservice"],
        };

        const allowedNewStatuses = allowedTransitions[currentItem.status] || [];

        if (!allowedNewStatuses.includes(newStatus)) {
          this.mediator.toaster.error(
            "Only damaged items can be marked as Out of Service. Other status changes happen through loans and maintenance.",
          );
          return;
        }
      }

      // Name, brand and model belong to the item type and are read-only here
      const payload = {
        location: this.editItemLocation?.value?.trim() || "",
        status: newStatus,
      };

      const locationOk = this.editItemValidator.required(
        this.editItemLocation,
        VALIDATION_ERRORS.REQUIRED,
      );
      const statusOk = this.editItemValidator.required(
        this.editItemStatus,
        VALIDATION_ERRORS.REQUIRED,
      );

      if (!locationOk || !statusOk) {
        this.editItemValidator.displayErrors();
        this.mediator.toaster.error(VALIDATION_ERRORS.FIX_ERRORS);
        this.editItemValidator.focusFirstError();
        return;
      }

      try {
        await this.presenter.updateItem(this.currentItemId, payload);
        this.mediator.toaster.success(SUCCESS_MESSAGES.ITEM_UPDATED);
        this.closeModal("edit-item-modal");
        this.openItemModal(this.currentItemId);
      } catch (error) {
        this.handleError(ERROR_MESSAGES.FAILED_UPDATE_ITEM, error);
      }
    });
  }

  // The table lists item types; status and location filters keep the types
  // that have at least one unit matching them
  getVisibleTypes() {
    const { status, type, location } = this.activeFilters || {};
    let types = this.presenter.getItemTypes();

    if (status || location) {
      const matchingCodes = new Set(
        this.presenter
          .getItems()
          .filter(
            (item) =>
              (!status || item.status === status) &&
              (!location ||
                item.location?.toLowerCase().includes(location.toLowerCase())),
          )
          .map((item) => item.typeCode),
      );
      types = types.filter((t) => matchingCodes.has(t.code));
    }
    if (type) {
      types = types.filter((t) => t.code === type);
    }

    types = this.applyFilter(
      types,
      (t) => [t.code, t.name, t.description, t.brand, t.model],
      this.searchQuery,
    );

    return this.applySorting(types, {
      code: (t) => t.code.toLowerCase(),
      name: (t) => t.name.toLowerCase(),
      description: (t) => (t.description || "").toLowerCase(),
      brand: (t) => (t.brand || "").toLowerCase(),
      model: (t) => (t.model || "").toLowerCase(),
      quantity: (t) => t.quantity,
    });
  }

  render() {
    const tbody = document.querySelector("#inventory-view tbody");
    if (!tbody) return;
    tbody.innerHTML = "";
    let types = [];
    try {
      types = this.getVisibleTypes();
    } catch (error) {
      this.handleError("Unable to load item types", error);
      return;
    }
    types.forEach((type) => {
      const tr = document.createElement("tr");
      tr.className = "table-row";
      tr.setAttribute("data-type-code", type.code);

      const codeCell = document.createElement("td");
      codeCell.textContent = type.code;

      const nameCell = document.createElement("td");
      nameCell.textContent = type.name;

      const descCell = document.createElement("td");
      descCell.textContent = type.description || "-";

      const brandCell = document.createElement("td");
      brandCell.textContent = type.brand || "-";

      const modelCell = document.createElement("td");
      modelCell.textContent = type.model || "-";

      const quantityCell = document.createElement("td");
      quantityCell.textContent = type.quantity || 0;

      tr.append(
        codeCell,
        nameCell,
        descCell,
        brandCell,
        modelCell,
        quantityCell,
      );
      tr.addEventListener("click", () =>
        this.openObjectListModal(type.code, type.name),
      );
      tbody.appendChild(tr);
    });
  }

  openObjectListModal(typeCode, typeName) {
    this.currentTypeCode = typeCode;
    this.objectListModal?.show(typeCode, typeName);
  }

  renderMaintenance(list) {
    if (!this.maintTbody) return;
    this.maintTbody.innerHTML = "";
    if (!list || list.length === 0) {
      const div = document.createElement("div");
      div.style.color = "var(--text-muted)";
      div.style.padding = "var(--space-3)";
      div.textContent = "No maintenance yet";
      this.maintTbody.appendChild(div);
      return;
    }
    list.forEach((m) => {
      const div = document.createElement("div");
      div.className = "maintenance-entry";
      div.innerHTML = `
        <div class="maint-row">
          <div class="maint-label">Date</div>
          <div class="maint-value">${formatDate(m.startDate)}</div>
        </div>
        <div class="maint-row">
          <div class="maint-label">Technician</div>
          <div class="maint-value">${m.tech || "-"}</div>
        </div>
        <div class="maint-row">
          <div class="maint-label">Description</div>
          <div class="maint-value">${m.desc || "-"}</div>
        </div>
      `;
      this.maintTbody.appendChild(div);
    });
  }

  populateEditItemForm() {
    if (!this.currentItemId) return;

    let item = null;
    try {
      item = this.presenter.getItemById(this.currentItemId);
    } catch (error) {
      this.handleError("Unable to load item data", error);
      return;
    }

    if (!item) {
      this.mediator.toaster.error(ERROR_MESSAGES.ITEM_NOT_FOUND);
      return;
    }

    if (this.editItemName) this.editItemName.value = item.name || "";
    if (this.editItemBrand) this.editItemBrand.value = item.brand || "";
    if (this.editItemModel) this.editItemModel.value = item.model || "";
    if (this.editItemLocation)
      this.editItemLocation.value = item.location || "";
    if (this.editItemStatus) this.editItemStatus.value = item.status || "";
  }

  openItemModal(itemId) {
    let item = null;
    try {
      item = this.presenter.getItemById(itemId);
    } catch (error) {
      this.handleError("Unable to load item details", error);
      return;
    }
    if (!item) {
      this.mediator.toaster.error(ERROR_MESSAGES.ITEM_NOT_FOUND);
      return;
    }

    this.currentItemId = itemId;
    this.populateTechnicians();
    this.itemDetailModal?.show(item);
  }

  openNewInstanceForm(typeCode, typeName) {
    // Store the type information for use when submitting the form
    this.pendingInstanceTypeCode = typeCode;
    this.pendingInstanceTypeName = typeName;

    // Open the new instance modal
    this.openModal("new-instance-modal");
  }

  populateTechnicians() {
    let technicians = [];
    try {
      technicians = this.presenter.getTechnicians();
    } catch (error) {
      this.handleError("Unable to load technicians", error);
      return;
    }
    this.maintenanceTechnicianSelect.innerHTML =
      '<option value="">Select a technician...</option>';
    technicians.forEach((tech) => {
      const option = document.createElement("option");
      option.value = tech.id;
      option.textContent = tech.email;
      this.maintenanceTechnicianSelect.appendChild(option);
    });
  }

  destroy() {
    super.destroy();
    this.newItemFormHandler?.destroy();
    this.itemDetailModal?.destroy();
    this.maintenanceForm?.destroy();
  }
}

export function initInventoryView(presenter, mediator) {
  return new InventoryView(presenter, mediator);
}
