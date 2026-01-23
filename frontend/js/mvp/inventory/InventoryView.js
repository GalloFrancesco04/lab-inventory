function statusBadgeClass(status) {
  if (status === "Available") return "badge badge-success";
  if (status === "Loaned") return "badge badge-warning";
  return "badge";
}

export function initInventoryView(presenter) {
  const rows = document.querySelectorAll("#inventory-view [data-item-id]");

  const itemModal = document.getElementById("item-detail-modal");
  const itemModalClose = document.getElementById("item-detail-close");
  const itemTitle = document.getElementById("item-modal-title");
  const itemName = document.getElementById("item-name");
  const itemType = document.getElementById("item-type");
  const itemStatus = document.getElementById("item-status");
  const itemLocation = document.getElementById("item-location");
  const maintTbody = document.getElementById("item-maintenance-tbody");
  const sendToMaintBtn = document.getElementById("send-to-maintenance-btn");

  function typeBadgeClass(type) {
    if (type === "Repair") return "badge badge-danger";
    if (type === "Replacement") return "badge badge-warning";
    if (type === "Preventive") return "badge badge-success";
    return "badge";
  }

  function renderMaintenance(list) {
    maintTbody.innerHTML = "";
    if (!list || list.length === 0) {
      const div = document.createElement("div");
      div.style.color = "var(--text-muted)";
      div.style.padding = "var(--space-3)";
      div.textContent = "No maintenance yet";
      maintTbody.appendChild(div);
      return;
    }
    list.forEach((m) => {
      const div = document.createElement("div");
      div.className = "maintenance-entry";
      div.innerHTML = `
        <div class="maint-row">
          <div class="maint-label">Date</div>
          <div class="maint-value">${m.date}</div>
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
      maintTbody.appendChild(div);
    });
  }

  function openItemModal(itemId) {
    const item = presenter.getItemById(itemId);
    if (!item) return;
    itemTitle.textContent = `Item #${item.id}`;
    itemName.textContent = item.name;
    itemType.textContent = item.type;
    itemLocation.textContent = item.location;
    itemStatus.className = statusBadgeClass(item.status);
    itemStatus.textContent = item.status;
    renderMaintenance(item.maintenance);
    itemModal.classList.add("open");
  }

  rows.forEach((row) => {
    row.addEventListener("click", () => {
      openItemModal(row.dataset.itemId);
    });
  });

  function closeItemModal() {
    itemModal.classList.remove("open");
  }

  itemModalClose?.addEventListener("click", closeItemModal);
  itemModal?.addEventListener("click", (e) => {
    if (e.target === itemModal) closeItemModal();
  });

  sendToMaintBtn?.addEventListener("click", () => {
    console.log("Send to maintenance (mock)");
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && itemModal?.classList.contains("open"))
      closeItemModal();
  });
}
