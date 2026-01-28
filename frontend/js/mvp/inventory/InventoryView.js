import { BaseView } from "../core/BaseView.js";
import { FormValidator } from "../../utils/validators.js";
import { ItemDetailModal } from "./modals/ItemDetailModal.js";
import { ObjectListModal } from "./modals/ObjectListModal.js";
import { SendToMaintenanceForm } from "./modals/SendToMaintenanceForm.js";
import { NewItemForm } from "./NewItemForm.js";
import {
  getItemStatusBadgeClass,
  getMaintenanceTypeBadgeClass,
} from "../../utils/ui/badges.js";
import {
  VALIDATION_ERRORS,
  SUCCESS_MESSAGES,
  ERROR_MESSAGES,
} from "../../utils/messages.js";

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
    this.newItemName = this.getElement("new-item-name");
    this.newItemDescription = this.getElement("new-item-description");
    this.newItemBrand = this.getElement("new-item-brand");
    this.newItemModel = this.getElement("new-item-model");
    this.newItemQuantity = this.getElement("new-item-quantity");
    this.newItemLocation = this.getElement("new-item-location");
    this.newItemNote = this.getElement("new-item-description");
    this.newItemBtn = this.getElement("new-item-btn");
    this.newTypeBtn = this.getElement("new-type-btn");

    // New Instance Modal
    this.newInstanceForm = this.getElement("new-instance-form");
    this.newInstanceLocation = this.getElement("new-instance-location");

    // New Type Modal
    this.newTypeForm = this.getElement("new-type-form");
    this.newTypeName = this.getElement("new-type-name");

    // Detail Modal
    this.itemTitle = this.getElement("item-modal-title");
    this.itemName = this.getElement("item-name");
    this.itemType = this.getElement("item-type");
    this.itemStatus = this.getElement("item-status");
    this.itemId = this.getElement("item-id");
    this.itemLocation = this.getElement("item-location");
    this.itemBrand = this.getElement("item-brand");
    this.itemModel = this.getElement("item-model");
    this.itemNote = this.getElement("item-note");
    this.maintTbody = this.getElement("item-maintenance-tbody");

    // Edit Item Modal
    this.editItemForm = this.getElement("edit-item-form");
    this.editItemName = this.getElement("edit-item-name");
    this.editItemBrand = this.getElement("edit-item-brand");
    this.editItemModel = this.getElement("edit-item-model");
    this.editItemLocation = this.getElement("edit-item-location");
    this.editItemStatus = this.getElement("edit-item-status");
    this.editItemNote = this.getElement("edit-item-note");
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
      nameInput: this.newItemName,
      descriptionInput: this.newItemNote,
      brandInput: this.newItemBrand,
      modelInput: this.newItemModel,
      quantityInput: this.newItemQuantity,
      locationInput: this.newItemLocation,
      toaster: this.mediator.toaster,
      onCreate: (typeData) => {
        try {
          const newType = this.presenter.addObjectType(typeData);
          if (newType) {
            this.mediator.toaster.success(
              `Type "${newType.name}" added successfully`,
            );
            this.closeModal("new-item-modal");
            return true;
          }
          this.mediator.toaster.error("Unable to add type");
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
        note: this.itemNote,
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
      onSubmit: ({ description, technician }) => {
        try {
          const result = this.presenter.createMaintenance(
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
            this.populateObjectTypes();
            this.newItemFormHandler?.reset();
          },
          onClose: () => this.newItemFormHandler?.reset(),
        },
        closeButtons: ["new-item-close", "new-item-cancel"],
      },
      "new-type-modal": {
        options: {
          onOpen: () => {
            this.newTypeForm?.reset();
          },
          onClose: () => this.newTypeForm?.reset(),
        },
        closeButtons: ["new-type-close", "new-type-cancel"],
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

  populateObjectTypes() {
    let types = [];
    try {
      types = this.presenter.getItemTypes();
    } catch (error) {
      this.handleError("Unable to load item types", error);
      return;
    }
    this.newItemType.innerHTML = '<option value="">Select a type...</option>';
    types.forEach((type) => {
      const option = document.createElement("option");
      option.value = type.name;
      option.textContent = type.name;
      this.newItemType.appendChild(option);
    });
  }

  setupEventListeners() {
    // New Item Button
    this.newItemBtn?.addEventListener("click", () => {
      this.openModal("new-item-modal");
    });

    // New Type Button
    this.newTypeBtn?.addEventListener("click", () => {
      this.openModal("new-type-modal");
    });

    // New Type Form
    this.newTypeForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      const typeName = this.newTypeName?.value?.trim();

      if (!typeName) {
        this.mediator.toaster.error("Type name is required");
        return;
      }

      try {
        const code = typeName.substring(0, 4).toUpperCase().padEnd(4, "X");
        const typeData = {
          name: typeName,
          code: code,
          description: "",
          brand: "Various",
          model: "Various",
          quantity: 0,
        };
        const newType = this.presenter.addObjectType(typeData);
        if (newType) {
          this.mediator.toaster.success(
            `Type "${newType.name}" created successfully`,
          );
          this.closeModal("new-type-modal");
          this.populateObjectTypes();
          this.newItemType.value = newType.name;
        } else {
          this.mediator.toaster.error("Type already exists or invalid name");
        }
      } catch (error) {
        this.handleError("Unable to create type", error);
      }
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

    this.sendToMaintForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      this.handleSendToMaintenance();
    });

    // New Instance Form
    this.newInstanceForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      const location = this.newInstanceLocation?.value?.trim();

      if (!location) {
        this.mediator.toaster.error("Location is required");
        return;
      }

      try {
        const result = this.presenter.addObjectInstance(
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

    this.editItemForm?.addEventListener("submit", (e) => {
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

      // Business rules: only allow status change between available and outofservice
      // Damaged items can be sent to maintenance or changed to outofservice only
      if (currentItem && currentItem.status !== newStatus) {
        const allowedTransitions = {
          available: ["outofservice"],
          outofservice: ["available"],
          damaged: ["outofservice"], // Damaged items can only go to outofservice
        };

        const allowedNewStatuses = allowedTransitions[currentItem.status] || [];

        if (!allowedNewStatuses.includes(newStatus)) {
          if (currentItem.status === "damaged") {
            this.mediator.toaster.error(
              "Damaged items can only be marked as Out of Service or sent to maintenance.",
            );
          } else {
            this.mediator.toaster.error(
              "Status can only be changed between Available and Out of Service. Other status changes require specific workflows (loan, maintenance).",
            );
          }
          return;
        }
      }

      const payload = {
        name: this.editItemName?.value?.trim() || "",
        brand: this.editItemBrand?.value?.trim() || "",
        model: this.editItemModel?.value?.trim() || "",
        location: this.editItemLocation?.value?.trim() || "",
        status: newStatus,
        note: this.editItemNote?.value?.trim() || "",
      };

      const nameOk = this.editItemValidator.required(
        this.editItemName,
        VALIDATION_ERRORS.REQUIRED,
      );
      const locationOk = this.editItemValidator.required(
        this.editItemLocation,
        VALIDATION_ERRORS.REQUIRED,
      );
      const statusOk = this.editItemValidator.required(
        this.editItemStatus,
        VALIDATION_ERRORS.REQUIRED,
      );

      if (!nameOk || !locationOk || !statusOk) {
        this.editItemValidator.displayErrors();
        this.mediator.toaster.error(VALIDATION_ERRORS.FIX_ERRORS);
        this.editItemValidator.focusFirstError();
        return;
      }

      try {
        this.presenter.updateItem(this.currentItemId, payload);
        this.mediator.toaster.success(SUCCESS_MESSAGES.ITEM_UPDATED);
        this.closeModal("edit-item-modal");
        this.openItemModal(this.currentItemId);
      } catch (error) {
        this.handleError(ERROR_MESSAGES.FAILED_UPDATE_ITEM, error);
      }
    });
  }

  getFilteredItems() {
    let items = [];
    try {
      items = this.presenter.getItems();
    } catch (error) {
      this.handleError("Unable to load items", error);
      return [];
    }

    // Apply search filter
    const searchFiltered = this.applyFilter(
      items,
      (item) => [
        item.id,
        item.name,
        item.type,
        item.brand,
        item.model,
        item.status,
        item.location,
      ],
      this.searchQuery,
    );

    // Apply advanced filters
    return this.applyAdvancedFilters(searchFiltered);
  }

  getSortedItems() {
    const items = this.getFilteredItems();
    return this.applySorting(items, {
      id: (item) => item.id,
      name: (item) => item.name.toLowerCase(),
      type: (item) => item.type.toLowerCase(),
      brand: (item) => (item.brand || "").toLowerCase(),
      model: (item) => (item.model || "").toLowerCase(),
      status: (item) => item.status.toLowerCase(),
      location: (item) => item.location.toLowerCase(),
    });
  }

  render() {
    const tbody = document.querySelector("#inventory-view tbody");
    if (!tbody) return;
    tbody.innerHTML = "";
    let types = [];
    try {
      types = this.presenter.getItemTypes();
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
          <div class="maint-value">${m.startDate || "-"}</div>
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
    if (this.editItemNote) this.editItemNote.value = item.note || "";
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
