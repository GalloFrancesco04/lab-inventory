import { BaseView } from "../core/BaseView.js";
import { ItemPicker } from "../../utils/ItemPicker.js";
import { NewClientForm } from "../../utils/NewClientForm.js";
import { LoanDetailModal } from "./LoanDetailModal.js";
import { CloseLoanForm } from "./CloseLoanForm.js";
import { getLoanStatusBadgeClass } from "../../utils/badges.js";

class LoansView extends BaseView {
  constructor(presenter, mediator) {
    super(presenter, mediator);
    this.initializeView({
      viewSelector: "#loans-view",
      searchViewName: "loans",
      renderMethod: "renderLoansTable",
    });
  }

  setupElements() {
    // Table
    this.loansTbody = this.getElement("loans-tbody");
    this.newLoanBtn = this.getElement("new-loan-btn");

    // New loan modal
    this.newLoanForm = this.getElement("new-loan-form");
    this.loanClient = this.getElement("loan-client");
    this.newClientBtn = this.getElement("loan-new-client-btn");
    this.loanItemsSelect = this.getElement("loan-items-select");
    this.loanItemsPanel = this.getElement("loan-items-panel");
    this.loanItemsSearch = this.getElement("loan-items-search");
    this.loanItemsSelected = this.getElement("loan-items-selected");
    this.loanDate = this.getElement("loan-date");

    // New client modal
    this.newClientForm = this.getElement("new-client-form");
    this.clientFirstInput = this.getElement("new-client-first");
    this.clientLastInput = this.getElement("new-client-last");
    this.clientEmailInput = this.getElement("new-client-email");

    this.availableItems = this.presenter.getAvailableItems
      ? this.presenter.getAvailableItems()
      : [];

    this.itemPicker = new ItemPicker({
      panel: this.loanItemsPanel,
      searchInput: this.loanItemsSearch,
      selectedContainer: this.loanItemsSelected,
      toggleButton: this.loanItemsSelect,
      toaster: this.mediator.toaster,
      maxItems: 5,
    });
    this.itemPicker.setItems(this.availableItems);

    this.clientForm = new NewClientForm({
      form: this.newClientForm,
      firstInput: this.clientFirstInput,
      lastInput: this.clientLastInput,
      emailInput: this.clientEmailInput,
      toaster: this.mediator.toaster,
      onCreate: ({ displayName, email }) => {
        try {
          const result = this.presenter.addClient(displayName, email);
          if (result) {
            this.mediator.toaster.success(
              `Client "${displayName}" added successfully`,
            );
            this.closeModal("new-client-modal");
            this.populateClients();
            this.loanClient.value = displayName;
            return true;
          }
          this.mediator.toaster.error("Unable to add client");
          return false;
        } catch (error) {
          this.handleError("Unable to add client", error);
          return false;
        }
      },
    });

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

    this.loanDetailModal = new LoanDetailModal({
      modalId: "loan-detail-modal",
      openModal: (id) => this.openModal(id),
      fields: {
        title: this.detailTitle,
        status: this.detailStatus,
        client: this.detailClient,
        returnDate: this.detailReturn,
        id: this.detailId,
        items: this.detailItems,
      },
    });

    this.closeLoanHandler = new CloseLoanForm({
      form: this.closeLoanForm,
      notesInput: this.closeLoanNotes,
      damagedInput: this.closeLoanDamaged,
      toaster: this.mediator.toaster,
      logger: this.mediator.logger,
      onSubmit: () => {
        this.closeModal("close-loan-modal");
        this.closeModal("loan-detail-modal");
      },
    });
  }

  setupModals() {
    this.registerModals({
      "new-loan-modal": {
        options: {
          onOpen: () => {
            this.newLoanForm?.reset();
            this.populateClients();
            this.itemPicker?.reset();
          },
          onClose: () => {
            this.newLoanForm?.reset();
            this.itemPicker?.reset();
          },
        },
        closeButtons: ["cancel-loan-btn", "new-loan-close"],
      },
      "loan-detail-modal": {
        closeButtons: ["loan-detail-close"],
      },
      "close-loan-modal": {
        options: {
          onClose: () => {
            this.closeLoanHandler?.reset();
          },
        },
        closeButtons: ["close-loan-notes-close", "close-loan-cancel"],
      },
      "new-client-modal": {
        options: {
          onOpen: () => {
            this.clientForm?.reset();
          },
          onClose: () => {
            this.clientForm?.reset();
          },
        },
        closeButtons: ["new-client-close", "new-client-cancel"],
        checkIfRegistered: true,
      },
    });
  }

  setupEventListeners() {
    this.newLoanBtn?.addEventListener("click", () =>
      this.openModal("new-loan-modal"),
    );

    this.newLoanForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      const items = this.itemPicker?.getSelectedItems() || [];
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

      try {
        const newLoan = this.presenter.addLoan({
          client,
          returnDate,
          items,
        });

        if (newLoan) {
          this.mediator.logger.mock("New loan created", newLoan);
          this.mediator.toaster.success("New loan created successfully");
          this.closeModal("new-loan-modal");
          this.itemPicker?.reset();
        } else {
          this.mediator.toaster.error("Unable to create loan");
        }
      } catch (error) {
        this.handleError("Unable to create loan", error);
      }
    });

    this.closeLoanBtn?.addEventListener("click", () => {
      this.openModal("close-loan-modal");
    });

    this.newClientBtn?.addEventListener("click", () => {
      this.openModal("new-client-modal");
    });
  }

  populateClients() {
    let clients = [];
    try {
      clients = this.presenter.getClients();
    } catch (error) {
      this.handleError("Unable to load clients", error);
    }
    this.loanClient.innerHTML = '<option value="">Select a client</option>';
    clients.forEach((client) => {
      const option = document.createElement("option");
      option.value = client;
      option.textContent = client;
      this.loanClient.appendChild(option);
    });
  }

  showLoanDetails(loanId) {
    let loan = null;
    try {
      loan = this.presenter.getLoanById(loanId);
    } catch (error) {
      this.handleError("Unable to load loan details", error);
      return;
    }
    if (!loan) {
      this.mediator.toaster.error("Loan not found");
      return;
    }

    this.loanDetailModal?.show(loan);
  }

  getSelectedItems() {
    return this.itemPicker?.getSelectedItems() || [];
  }

  destroy() {
    super.destroy();
    this.itemPicker?.destroy();
    this.clientForm?.destroy();
    this.loanDetailModal?.destroy();
    this.closeLoanHandler?.destroy();
  }

  getFilteredLoans() {
    let loans = [];
    try {
      loans = this.presenter.getLoans();
    } catch (error) {
      this.handleError("Unable to load loans", error);
      return [];
    }
    return this.applyFilter(
      loans,
      (loan) => [
        loan.id,
        loan.client,
        loan.status,
        loan.returnDate || "",
        ...(loan.items?.map((item) => item.name) || []),
      ],
      this.searchQuery,
    );
  }

  getSortedLoans() {
    const loans = this.getFilteredLoans();
    return this.applySorting(loans, {
      id: (loan) => loan.id,
      client: (loan) => loan.client.toLowerCase(),
      items: (loan) =>
        loan.items
          ?.map((i) => i.name)
          .join(", ")
          .toLowerCase() || "",
      returnDate: (loan) => loan.returnDate || "",
      status: (loan) => loan.status.toLowerCase(),
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
      statusBadge.className = getLoanStatusBadgeClass(loan.status);
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
