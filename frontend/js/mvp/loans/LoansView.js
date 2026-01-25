import { BaseView } from "../core/BaseView.js";

class LoansView extends BaseView {
  constructor(presenter, mediator) {
    super(presenter, mediator);
    this.searchQuery = "";
    this.sortColumn = null;
    this.sortDirection = null; // 'asc' | 'desc' | null
    this.setupElements();
    this.setupModals();
    this.setupEventListeners();
    this.setupSorting();
    // Register search callback
    this.mediator.searchManager.registerView("loans", (query) =>
      this.handleSearch(query),
    );
    // Auto re-render on presenter data changes
    this.setupAutoRender(() => this.renderLoansTable());
    this.renderLoansTable();
  }

  setupElements() {
    // Table
    this.loansTbody = this.getElement("loans-tbody");
    this.newLoanBtn = this.getElement("new-loan-btn");

    // New loan modal
    this.newLoanForm = this.getElement("new-loan-form");
    this.loanClient = this.getElement("loan-client");
    this.loanItemsSelect = this.getElement("loan-items-select");
    this.loanItemsPanel = this.getElement("loan-items-panel");
    this.loanItemsSearch = this.getElement("loan-items-search");
    this.loanItemsSelected = this.getElement("loan-items-selected");
    this.loanDate = this.getElement("loan-date");

    this.availableItems = this.presenter.getAvailableItems
      ? this.presenter.getAvailableItems()
      : [];
    this.selectedItemIds = new Set();
    this.itemLabelsMap = new Map(); // Store label refs for filtering

    // Populate the panel with checkboxes
    if (this.loanItemsPanel) {
      this.availableItems.forEach((item) => {
        const label = document.createElement("label");
        label.style.display = "flex";
        label.style.alignItems = "center";
        label.style.gap = "12px";
        label.style.padding = "10px 12px";
        label.style.borderBottom = "1px solid #f0f0f0";
        label.style.cursor = "pointer";
        label.style.userSelect = "none";
        label.style.width = "100%";
        label.style.boxSizing = "border-box";
        label.dataset.itemId = item.id;
        label.dataset.itemSearch =
          `${item.name} ${item.type} ${item.id}`.toLowerCase();

        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.value = item.id;
        checkbox.dataset.itemId = item.id;
        checkbox.dataset.itemName = item.name;
        checkbox.dataset.itemType = item.type;
        checkbox.style.cursor = "pointer";
        checkbox.style.flexShrink = "0";

        const text = document.createElement("span");
        text.textContent = `${item.name} (${item.type}) - ID: ${item.id}`;
        text.style.flex = "1";

        label.append(checkbox, text);
        this.loanItemsPanel.appendChild(label);
        this.itemLabelsMap.set(item.id, label);
      });
    }

    // Loan detail modal
    this.detailTitle = this.getElement("detail-loan-title");
    this.detailStatus = this.getElement("detail-loan-status");
    this.detailClient = this.getElement("detail-loan-client");
    this.detailReturn = this.getElement("detail-loan-return");
    this.detailId = this.getElement("detail-loan-id");
    this.detailItems = this.getElement("detail-loan-items");
    this.closeLoanBtn = this.getElement("detail-close-loan");

    // Close loan modal
    this.closeLoanForm = this.getElement("close-loan-form");
    this.closeLoanNotes = this.getElement("close-loan-notes");
    this.closeLoanDamaged = this.getElement("close-loan-damaged");
  }

  setupModals() {
    this.registerModal("new-loan-modal", {
      onOpen: () => {
        this.newLoanForm?.reset();
        this.resetItemPicker();
      },
      onClose: () => {
        this.newLoanForm?.reset();
        this.resetItemPicker();
      },
    });

    this.registerModal("loan-detail-modal");
    this.registerModal("close-loan-modal", {
      onClose: () => {
        this.closeLoanForm?.reset();
      },
    });

    this.setupModalCloseButtons("new-loan-modal", [
      "cancel-loan-btn",
      "new-loan-close",
    ]);
    this.setupModalCloseButtons("loan-detail-modal", ["loan-detail-close"]);
    this.setupModalCloseButtons("close-loan-modal", [
      "close-loan-notes-close",
      "close-loan-cancel",
    ]);
  }

  setupEventListeners() {
    this.newLoanBtn?.addEventListener("click", () =>
      this.openModal("new-loan-modal"),
    );

    // Toggle dropdown panel
    this.loanItemsSelect?.addEventListener("click", (e) => {
      e.stopPropagation();
      if (!this.loanItemsPanel) return;
      const isOpen = this.loanItemsPanel.style.display === "flex";
      this.loanItemsPanel.style.display = isOpen ? "none" : "flex";
      if (!isOpen) {
        // Focus search input when opening
        this.loanItemsSearch?.focus();
      }
    });

    // Search/filter items
    this.loanItemsSearch?.addEventListener("input", (e) => {
      const query = e.target.value.toLowerCase();
      this.itemLabelsMap.forEach((label) => {
        const matches = label.dataset.itemSearch.includes(query);
        label.style.display = matches ? "flex" : "none";
      });
    });

    // Close panel when clicking outside
    document.addEventListener("click", (e) => {
      if (
        !this.loanItemsPanel ||
        this.loanItemsPanel.style.display !== "flex"
      ) {
        return;
      }
      const clickedInsidePanel = this.loanItemsPanel.contains(e.target);
      const clickedButton = this.loanItemsSelect?.contains(e.target);
      if (!clickedInsidePanel && !clickedButton) {
        this.loanItemsPanel.style.display = "none";
      }
    });

    // Handle checkbox changes in panel
    this.loanItemsPanel?.addEventListener("change", (e) => {
      if (e.target && e.target.type === "checkbox") {
        const selected = this.getSelectedItemsFromDOM();
        if (selected.length > 5) {
          e.target.checked = false;
          this.mediator.toaster.warning("Max 5 items per loan");
          return;
        }
        this.selectedItemIds = new Set(selected.map((i) => i.id));
        this.renderSelectedChips(this.getSelectedItems());
      }
    });

    this.newLoanForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      const items = this.getSelectedItems();
      const client = this.loanClient?.value?.trim();
      const returnDate = this.loanDate?.value || "";

      if (!client) {
        this.mediator.toaster.error("Select a client");
        return;
      }

      if (items.length === 0) {
        this.mediator.toaster.error("Select at least one item (max 5)");
        return;
      }

      if (items.length > 5) {
        this.mediator.toaster.error("You can select at most 5 items");
        return;
      }

      const newLoan = this.presenter.addLoan({
        client,
        returnDate,
        items,
      });

      if (newLoan) {
        this.mediator.logger.mock("New loan created", newLoan);
        this.mediator.toaster.success("New loan created successfully");
        this.closeModal("new-loan-modal");
        this.resetItemPicker();
      } else {
        this.mediator.toaster.error("Unable to create loan");
      }
    });

    this.closeLoanBtn?.addEventListener("click", () => {
      this.openModal("close-loan-modal");
    });

    this.closeLoanForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      this.mediator.logger.mock("Close loan", {
        notes: this.closeLoanNotes?.value || "",
        damaged: this.closeLoanDamaged?.checked || false,
      });
      this.mediator.toaster.info("Loan closed successfully");
      this.closeModal("close-loan-modal");
      this.closeModal("loan-detail-modal");
    });
  }

  showLoanDetails(loanId) {
    const loan = this.presenter.getLoanById(loanId);
    if (!loan) return;

    this.detailTitle.textContent = `Loan #${loan.id}`;
    this.detailId.textContent = loan.id;
    this.detailClient.textContent = loan.client;
    this.detailReturn.textContent = loan.returnDate || "-";
    this.setStatusBadge(this.detailStatus, loan.status);
    this.renderItems(this.detailItems, loan.items);
    this.openModal("loan-detail-modal");
  }

  setStatusBadge(el, statusText) {
    el.textContent = statusText;
    el.className =
      "badge " + (statusText === "active" ? "badge-success" : "badge-neutral");
  }

  renderItems(container, items) {
    container.innerHTML = "";
    items.forEach((item) => {
      const span = document.createElement("span");
      span.className = "chip";
      span.textContent = item.name || item;
      container.appendChild(span);
    });
  }

  getSelectedItems() {
    const selectedIds = Array.from(this.selectedItemIds);
    return this.availableItems.filter((item) => selectedIds.includes(item.id));
  }

  getSelectedItemsFromDOM() {
    if (!this.loanItemsPanel) return [];
    const checked = this.loanItemsPanel.querySelectorAll(
      'input[type="checkbox"]:checked',
    );
    return Array.from(checked).map((input) => ({
      id: input.value,
      name: input.dataset.itemName,
      type: input.dataset.itemType,
    }));
  }

  renderSelectedChips(selected) {
    if (!this.loanItemsSelected) return;
    this.loanItemsSelected.innerHTML = "";
    selected.forEach((item) => {
      const chip = document.createElement("span");
      chip.className = "chip";
      chip.textContent = item.name;
      const closeBtn = document.createElement("button");
      closeBtn.type = "button";
      closeBtn.textContent = "×";
      closeBtn.addEventListener("click", () => {
        this.uncheckItem(item.id);
      });
      chip.appendChild(closeBtn);
      this.loanItemsSelected.appendChild(chip);
    });
  }

  resetItemPicker() {
    this.selectedItemIds = new Set();
    if (this.loanItemsPanel) {
      const checkboxes = this.loanItemsPanel.querySelectorAll(
        'input[type="checkbox"]',
      );
      checkboxes.forEach((cb) => (cb.checked = false));
    }
    if (this.loanItemsSearch) {
      this.loanItemsSearch.value = "";
    }
    // Show all items again
    this.itemLabelsMap.forEach((label) => {
      label.style.display = "flex";
    });
    this.renderSelectedChips([]);
    if (this.loanItemsPanel) {
      this.loanItemsPanel.style.display = "none";
    }
  }

  uncheckItem(id) {
    if (this.loanItemsPanel) {
      const checkbox = this.loanItemsPanel.querySelector(
        `input[type="checkbox"][value="${id}"]`,
      );
      if (checkbox) checkbox.checked = false;
    }
    this.selectedItemIds.delete(id);
    this.renderSelectedChips(this.getSelectedItems());
  }

  getSelectedItems() {
    const selectedIds = Array.from(this.selectedItemIds);
    return this.availableItems.filter((item) => selectedIds.includes(item.id));
  }

  renderSelectedChips(selected) {
    if (!this.loanItemsSelected) return;
    this.loanItemsSelected.innerHTML = "";
    selected.forEach((item) => {
      const chip = document.createElement("span");
      chip.className = "chip";
      chip.textContent = item.name;
      const closeBtn = document.createElement("button");
      closeBtn.type = "button";
      closeBtn.textContent = "×";
      closeBtn.addEventListener("click", () => {
        this.selectedItemIds.delete(item.id);
        if (this.loanItemsSelect) {
          Array.from(this.loanItemsSelect.options).forEach((opt) => {
            if (opt.value === item.id) opt.selected = false;
          });
        }
        this.renderSelectedChips(this.getSelectedItems());
      });
      chip.appendChild(closeBtn);
      this.loanItemsSelected.appendChild(chip);
    });
  }

  getStatusBadgeClass(status) {
    if (status === "active") return "badge badge-success";
    if (status === "closed") return "badge badge-neutral";
    return "badge";
  }

  handleSearch(query) {
    this.searchQuery = query.toLowerCase();
    this.renderLoansTable();
  }

  getFilteredLoans() {
    const loans = this.presenter.getLoans();
    if (!this.searchQuery) return loans;

    return loans.filter((loan) => {
      const searchableText = [
        loan.id,
        loan.client,
        loan.status,
        loan.returnDate || "",
        ...(loan.items?.map((item) => item.name) || []),
      ]
        .join(" ")
        .toLowerCase();

      return searchableText.includes(this.searchQuery);
    });
  }

  getSortedLoans() {
    let loans = this.getFilteredLoans();

    if (!this.sortColumn) return loans;

    return [...loans].sort((a, b) => {
      let aVal, bVal;

      switch (this.sortColumn) {
        case "id":
          aVal = a.id;
          bVal = b.id;
          break;
        case "client":
          aVal = a.client.toLowerCase();
          bVal = b.client.toLowerCase();
          break;
        case "items":
          aVal =
            a.items
              ?.map((i) => i.name)
              .join(", ")
              .toLowerCase() || "";
          bVal =
            b.items
              ?.map((i) => i.name)
              .join(", ")
              .toLowerCase() || "";
          break;
        case "returnDate":
          aVal = a.returnDate || "";
          bVal = b.returnDate || "";
          break;
        case "status":
          aVal = a.status.toLowerCase();
          bVal = b.status.toLowerCase();
          break;
        default:
          return 0;
      }

      if (aVal < bVal) return this.sortDirection === "asc" ? -1 : 1;
      if (aVal > bVal) return this.sortDirection === "asc" ? 1 : -1;
      return 0;
    });
  }

  setupSorting() {
    const headers = document.querySelectorAll("#loans-view th[data-sortable]");
    headers.forEach((header) => {
      header.addEventListener("click", () => {
        const sortKey = header.dataset.sortKey;
        this.handleSort(sortKey, header);
      });
    });
  }

  handleSort(column, headerElement) {
    // Toggle sort direction
    if (this.sortColumn === column) {
      if (this.sortDirection === "asc") {
        this.sortDirection = "desc";
      } else if (this.sortDirection === "desc") {
        this.sortColumn = null;
        this.sortDirection = null;
      } else {
        this.sortDirection = "asc";
      }
    } else {
      this.sortColumn = column;
      this.sortDirection = "asc";
    }

    // Update header classes
    const allHeaders = document.querySelectorAll(
      "#loans-view th[data-sortable]",
    );
    allHeaders.forEach((h) => {
      h.classList.remove("sorted-asc", "sorted-desc");
    });

    if (this.sortDirection) {
      headerElement.classList.add(`sorted-${this.sortDirection}`);
    }

    this.renderLoansTable();
  }

  renderLoansTable() {
    if (!this.loansTbody) return;
    this.loansTbody.innerHTML = "";
    this.getSortedLoans().forEach((loan) => {
      const row = document.createElement("tr");
      row.className = "table-row";
      row.dataset.loanId = loan.id;

      const idCell = document.createElement("td");
      idCell.textContent = loan.id;

      const clientCell = document.createElement("td");
      clientCell.textContent = loan.client;

      const itemCell = document.createElement("td");
      itemCell.textContent =
        loan.items?.map((item) => item.name).join(", ") || "-";

      const returnDateCell = document.createElement("td");
      returnDateCell.textContent = loan.returnDate || "-";

      const statusCell = document.createElement("td");
      const statusBadge = document.createElement("span");
      statusBadge.className = this.getStatusBadgeClass(loan.status);
      statusBadge.textContent = loan.status;
      statusCell.appendChild(statusBadge);

      row.append(idCell, clientCell, itemCell, returnDateCell, statusCell);
      row.addEventListener("click", () => this.showLoanDetails(loan.id));

      this.loansTbody.appendChild(row);
    });
  }
}

export function initLoansView(presenter, mediator) {
  return new LoansView(presenter, mediator);
}
