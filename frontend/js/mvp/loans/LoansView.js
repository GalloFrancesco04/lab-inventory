import { BaseView } from "../core/BaseView.js";
import { ItemPicker } from "../../utils/forms/ItemPicker.js";
import { NewClientForm } from "../../utils/forms/NewClientForm.js";
import { LoanDetailModal } from "./modals/LoanDetailModal.js";
import { CloseLoanForm } from "./modals/CloseLoanForm.js";
import { getLoanStatusBadgeClass } from "../../utils/ui/badges.js";
import {
  VALIDATION_ERRORS,
  SUCCESS_MESSAGES,
  ERROR_MESSAGES,
} from "../../utils/messages.js";

class LoansView extends BaseView {
  constructor(presenter, mediator) {
    super(presenter, mediator);
    this.initializeView({
      viewSelector: "#loans-view",
      searchViewName: "loans",
      renderMethod: "renderLoansTable",
    });
  }

  render() {
    this.renderLoansTable();
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
    this.detailStart = this.getElement("detail-loan-start");
    this.detailEnd = this.getElement("detail-loan-end");
    this.detailReturn = this.getElement("detail-loan-return");
    this.detailId = this.getElement("detail-loan-id");
    this.detailItems = this.getElement("detail-loan-items");
    this.detailNotes = this.getElement("detail-loan-notes");
    this.closeLoanBtn = this.getElement("detail-close-loan");

    // Close loan modal
    this.closeLoanForm = this.getElement("close-loan-form");
    this.closeLoanNotes = this.getElement("close-loan-notes");
    this.closeLoanDamagedMenu = this.getElement("close-loan-damaged-menu");
    this.closeLoanDamagedTrigger = this.getElement(
      "close-loan-damaged-trigger",
    );

    this.loanDetailModal = new LoanDetailModal({
      modalId: "loan-detail-modal",
      openModal: (id) => this.openModal(id),
      fields: {
        title: this.detailTitle,
        status: this.detailStatus,
        client: this.detailClient,
        startDate: this.detailStart,
        endDate: this.detailEnd,
        actualReturnDate: this.detailReturn,
        id: this.detailId,
        items: this.detailItems,
        notes: this.detailNotes,
      },
    });

    this.closeLoanHandler = new CloseLoanForm({
      form: this.closeLoanForm,
      notesInput: this.closeLoanNotes,
      damagedMenu: this.closeLoanDamagedMenu,
      damagedTrigger: this.closeLoanDamagedTrigger,
      toaster: this.mediator.toaster,
      logger: this.mediator.logger,
      onSubmit: ({ damagedItems, notes }) => {
        if (this.currentLoanId) {
          const today = new Date().toISOString().split("T")[0];
          this.presenter.closeLoan(
            this.currentLoanId,
            today,
            damagedItems,
            notes,
          );
          this.renderLoansTable();
          // Refresh available items after closing loan
          this.refreshAvailableItems();
          // refresh modal details
          const updated = this.presenter.getLoanById(this.currentLoanId);
          this.loanDetailModal?.show(updated);
        }
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
            // Set min date to today
            if (this.loanDate) {
              const today = new Date().toISOString().split("T")[0];
              this.loanDate.setAttribute("min", today);
            }
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
    this.newLoanBtn?.addEventListener("click", () => {
      // Refresh available items each time modal opens
      this.refreshAvailableItems();
      this.openModal("new-loan-modal");
    });

    this.newLoanForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      const items = this.itemPicker?.getSelectedItems() || [];
      const client = this.loanClient?.value?.trim();
      const endDate = this.loanDate?.value || "";

      if (!client) {
        this.mediator.toaster.error(ERROR_MESSAGES.SELECT_CLIENT);
        return;
      }

      if (items.length === 0) {
        this.mediator.toaster.error(ERROR_MESSAGES.SELECT_ITEMS);
        return;
      }

      if (items.length > 5) {
        this.mediator.toaster.error(ERROR_MESSAGES.MAX_ITEMS);
        return;
      }

      // Validate return date is not in the past
      if (endDate) {
        const selectedDate = new Date(endDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (selectedDate < today) {
          this.mediator.toaster.error("Return date cannot be in the past");
          return;
        }
      }

      // Generate today's date as startDate
      const today = new Date();
      const startDate = today.toISOString().split("T")[0];

      try {
        const newLoan = this.presenter.addLoan({
          client,
          startDate,
          endDate,
          items,
        });

        if (newLoan) {
          this.mediator.logger.mock("New loan created", newLoan);
          this.mediator.toaster.success(SUCCESS_MESSAGES.LOAN_CREATED);
          this.closeModal("new-loan-modal");
          this.itemPicker?.reset();
          // Refresh available items after loan creation
          this.refreshAvailableItems();
        } else {
          this.mediator.toaster.error(ERROR_MESSAGES.LOAN_FAILED);
        }
      } catch (error) {
        this.handleError(ERROR_MESSAGES.LOAN_FAILED, error);
      }
    });

    this.closeLoanBtn?.addEventListener("click", () => {
      // Populate damaged items list with current loan items
      if (this.currentLoanId) {
        const loan = this.presenter.getLoanById(this.currentLoanId);
        this.closeLoanHandler?.setItems(loan?.items || []);
      }
      this.openModal("close-loan-modal");
    });

    this.newClientBtn?.addEventListener("click", () => {
      this.openModal("new-client-modal");
    });
  }

  refreshAvailableItems() {
    try {
      this.availableItems = this.presenter.getAvailableItems
        ? this.presenter.getAvailableItems()
        : [];
      this.itemPicker?.setItems(this.availableItems);
    } catch (error) {
      this.handleError("Unable to refresh available items", error);
    }
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

    this.currentLoanId = loan.id;
    if (this.closeLoanBtn) {
      this.closeLoanBtn.style.display = loan.status === "closed" ? "none" : "";
    }
    this.loanDetailModal?.show({
      ...loan,
      _isLate: this.isLateReturn(loan),
    });
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

    // Apply search filter
    const searchFiltered = this.applyFilter(
      loans,
      (loan) => [
        loan.id,
        loan.client,
        loan.status,
        loan.startDate || "",
        loan.endDate || "",
        loan.actualReturnDate || "",
        String(loan.items?.length || 0),
      ],
      this.searchQuery,
    );

    // Apply advanced filters
    return this.applyAdvancedFilters(searchFiltered);
  }

  getSortedLoans() {
    const loans = this.getFilteredLoans();
    return this.applySorting(loans, {
      id: (loan) => loan.id,
      client: (loan) => loan.client.toLowerCase(),
      items: (loan) => loan.items?.length || 0,
      startDate: (loan) => loan.startDate || "",
      endDate: (loan) => loan.endDate || "",
      actualReturnDate: (loan) => loan.actualReturnDate || "",
      status: (loan) => loan.status.toLowerCase(),
    });
  }

  isLateReturn(loan) {
    if (!loan?.endDate || !loan?.actualReturnDate) return false;
    const end = this.parseDate(loan.endDate);
    const actual = this.parseDate(loan.actualReturnDate);
    if (!end || !actual) return false;
    return actual > end;
  }

  formatDate(dateStr) {
    const date = this.parseDate(dateStr);
    if (!date || isNaN(date.getTime())) return "-";
    const dd = String(date.getDate()).padStart(2, "0");
    const mm = String(date.getMonth() + 1).padStart(2, "0");
    const yyyy = date.getFullYear();
    return `${dd}/${mm}/${yyyy}`;
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
      const itemsBadge = document.createElement("span");
      itemsBadge.className = "badge badge-neutral";
      itemsBadge.textContent = loan.items?.length ?? 0;
      itemCell.appendChild(itemsBadge);

      const startDateCell = document.createElement("td");
      startDateCell.textContent = this.formatDate(loan.startDate || "-");

      const endDateCell = document.createElement("td");
      endDateCell.textContent = this.formatDate(loan.endDate || "-");

      const returnDateCell = document.createElement("td");
      returnDateCell.textContent = this.formatDate(
        loan.actualReturnDate || "-",
      );
      returnDateCell.style.color = this.isLateReturn(loan)
        ? "var(--danger)"
        : "";

      const statusCell = document.createElement("td");
      const statusBadge = document.createElement("span");
      statusBadge.className = getLoanStatusBadgeClass(loan.status);
      statusBadge.textContent = loan.status;
      statusCell.appendChild(statusBadge);

      row.append(
        idCell,
        clientCell,
        itemCell,
        startDateCell,
        endDateCell,
        returnDateCell,
        statusCell,
      );
      row.addEventListener("click", () => this.showLoanDetails(loan.id));

      this.loansTbody.appendChild(row);
    });
  }
}

export function initLoansView(presenter, mediator) {
  return new LoansView(presenter, mediator);
}
