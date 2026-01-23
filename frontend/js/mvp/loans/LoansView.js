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

  const loanDetailModal = document.getElementById("loan-detail-modal");
  const loanDetailClose = document.getElementById("loan-detail-close");
  const detailTitle = document.getElementById("detail-loan-title");
  const detailStatus = document.getElementById("detail-loan-status");
  const detailClient = document.getElementById("detail-loan-client");
  const detailReturn = document.getElementById("detail-loan-return");
  const detailId = document.getElementById("detail-loan-id");
  const detailItems = document.getElementById("detail-loan-items");
  const closeLoanBtn = document.getElementById("detail-close-loan");

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

  // Wire table buttons
  document.querySelectorAll("[data-loan-id]").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const loanId = e.currentTarget.dataset.loanId;
      showLoanDetails(loanId);
    });
  });

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
  closeLoanBtn?.addEventListener("click", () =>
    console.log("Close loan (mock)"),
  );
  loanDetailClose?.addEventListener("click", closeLoanDetailModal);
  loanDetailModal?.addEventListener("click", (e) => {
    if (e.target === loanDetailModal) closeLoanDetailModal();
  });

  // Close modals with Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      if (newLoanModal?.classList.contains("open")) closeNewLoanModal();
      if (loanDetailModal?.classList.contains("open")) closeLoanDetailModal();
    }
  });
}
