function setStatusBadge(el, statusText) {
  el.textContent = statusText;
  el.className =
    "badge " + (statusText === "Active" ? "badge-success" : "badge-neutral");
}

function renderItems(container, items) {
  container.innerHTML = "";
  items.forEach((item) => {
    const span = document.createElement("span");
    span.className = "chip";
    span.textContent = item;
    container.appendChild(span);
  });
}

export function initLoansView(presenter) {
  // New Loan modal elements
  const newLoanModal = document.getElementById("new-loan-modal");
  const newLoanBtn = document.getElementById("new-loan-btn");
  const cancelLoanBtn = document.getElementById("cancel-loan-btn");
  const modalCloseBtn = document.querySelector("#new-loan-modal .modal-close");
  const newLoanForm = document.getElementById("new-loan-form");
  const loansTbody = document.getElementById("loans-tbody");

  const loanDetailModal = document.getElementById("loan-detail-modal");
  const loanDetailClose = document.getElementById("loan-detail-close");
  const detailTitle = document.getElementById("detail-loan-title");
  const detailStatus = document.getElementById("detail-loan-status");
  const detailClient = document.getElementById("detail-loan-client");
  const detailReturn = document.getElementById("detail-loan-return");
  const detailId = document.getElementById("detail-loan-id");
  const detailItems = document.getElementById("detail-loan-items");
  const closeLoanBtn = document.getElementById("detail-close-loan");

  const closeLoanModal = document.getElementById("close-loan-modal");
  const closeLoanCloseBtn = document.getElementById("close-loan-notes-close");
  const closeLoanCancelBtn = document.getElementById("close-loan-cancel");
  const closeLoanForm = document.getElementById("close-loan-form");
  const closeLoanNotes = document.getElementById("close-loan-notes");
  const closeLoanDamaged = document.getElementById("close-loan-damaged");

  function openNewLoanModal() {
    newLoanModal.classList.add("open");
  }

  function closeNewLoanModal() {
    newLoanModal.classList.remove("open");
    newLoanForm.reset();
  }

  function openLoanDetailModal() {
    loanDetailModal.classList.add("open");
  }

  function closeLoanDetailModal() {
    loanDetailModal.classList.remove("open");
  }

  function openCloseLoanModal() {
    closeLoanModal?.classList.add("open");
  }

  function closeCloseLoanModal() {
    closeLoanModal?.classList.remove("open");
    closeLoanForm?.reset();
  }

  function showLoanDetails(loanId) {
    const loan = presenter.getLoanById(loanId);
    if (!loan) return;

    detailTitle.textContent = `Loan #${loan.id}`;
    detailId.textContent = loan.id;
    detailClient.textContent = loan.client;
    detailReturn.textContent = loan.returnDate || "-";
    setStatusBadge(detailStatus, loan.status);
    renderItems(detailItems, loan.items);
    openLoanDetailModal();
  }

  function getStatusBadgeClass(status) {
    if (status === "Active") return "badge badge-success";
    if (status === "Closed") return "badge badge-neutral";
    return "badge";
  }

  function renderLoansTable() {
    if (!loansTbody) return;
    loansTbody.innerHTML = "";
    presenter.getLoans().forEach((loan) => {
      const row = document.createElement("tr");
      row.className = "table-row";
      row.dataset.loanId = loan.id;

      const idCell = document.createElement("td");
      idCell.textContent = loan.id;

      const clientCell = document.createElement("td");
      clientCell.textContent = loan.client;

      const itemCell = document.createElement("td");
      itemCell.textContent = loan.items?.join(", ") || "-";

      const statusCell = document.createElement("td");
      const statusBadge = document.createElement("span");
      statusBadge.className = getStatusBadgeClass(loan.status);
      statusBadge.textContent = loan.status;
      statusCell.appendChild(statusBadge);

      row.append(idCell, clientCell, itemCell, statusCell);
      row.addEventListener("click", () => showLoanDetails(loan.id));

      loansTbody.appendChild(row);
    });
  }

  // New Loan modal events
  newLoanBtn?.addEventListener("click", openNewLoanModal);
  cancelLoanBtn?.addEventListener("click", closeNewLoanModal);
  modalCloseBtn?.addEventListener("click", closeNewLoanModal);

  newLoanForm?.addEventListener("submit", (e) => {
    e.preventDefault();
    console.log("New loan created (mock)");
    closeNewLoanModal();
  });

  newLoanModal?.addEventListener("click", (e) => {
    if (e.target === newLoanModal) closeNewLoanModal();
  });

  // Loan detail modal events
  closeLoanBtn?.addEventListener("click", () => {
    openCloseLoanModal();
  });
  loanDetailClose?.addEventListener("click", closeLoanDetailModal);
  loanDetailModal?.addEventListener("click", (e) => {
    if (e.target === loanDetailModal) closeLoanDetailModal();
  });

  // Close Loan notes modal events
  closeLoanCloseBtn?.addEventListener("click", closeCloseLoanModal);
  closeLoanCancelBtn?.addEventListener("click", closeCloseLoanModal);
  closeLoanModal?.addEventListener("click", (e) => {
    if (e.target === closeLoanModal) closeCloseLoanModal();
  });

  closeLoanForm?.addEventListener("submit", (e) => {
    e.preventDefault();
    console.log("Close loan notes (mock)", {
      notes: closeLoanNotes?.value || "",
      damaged: closeLoanDamaged?.checked || false,
    });
    closeCloseLoanModal();
    closeLoanDetailModal();
  });

  // Close modals with Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      if (newLoanModal?.classList.contains("open")) closeNewLoanModal();
      if (loanDetailModal?.classList.contains("open")) closeLoanDetailModal();
      if (closeLoanModal?.classList.contains("open")) closeCloseLoanModal();
    }
  });

  renderLoansTable();
}
