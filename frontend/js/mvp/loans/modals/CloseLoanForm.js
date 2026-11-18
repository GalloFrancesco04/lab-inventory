export class CloseLoanForm {
  constructor({
    form,
    notesInput,
    damagedMenu,
    damagedTrigger,
    toaster,
    logger,
    onSubmit,
  }) {
    this.form = form;
    this.notesInput = notesInput;
    this.damagedMenu = damagedMenu;
    this.damagedTrigger = damagedTrigger;
    this.damagedContainer = this.damagedMenu?.closest(".multi-select") || null;
    this.toaster = toaster;
    this.logger = logger;
    this.onSubmit = onSubmit;

    this.handleSubmit = this.handleSubmit.bind(this);
    this.toggleMenu = this.toggleMenu.bind(this);
    this.handleOutsideClick = this.handleOutsideClick.bind(this);
    this.updateLabel = this.updateLabel.bind(this);
    this.form?.addEventListener("submit", this.handleSubmit);
    this.damagedTrigger?.addEventListener("click", this.toggleMenu);
    document.addEventListener("click", this.handleOutsideClick);
  }

  destroy() {
    this.form?.removeEventListener("submit", this.handleSubmit);
    this.damagedTrigger?.removeEventListener("click", this.toggleMenu);
    document.removeEventListener("click", this.handleOutsideClick);
  }

  reset() {
    this.form?.reset();
    if (this.damagedMenu) this.damagedMenu.innerHTML = "";
    this.setMenuOpen(false);
    this.updateLabel();
  }

  setItems(items = []) {
    if (!this.damagedMenu) return;
    this.damagedMenu.innerHTML = "";
    if (!items.length) {
      const empty = document.createElement("div");
      empty.className = "multi-select-empty";
      empty.textContent = "No items to mark";
      this.damagedMenu.appendChild(empty);
      this.updateLabel();
      return;
    }

    items.forEach((item) => {
      const row = document.createElement("label");
      row.className = "multi-select-option";

      const input = document.createElement("input");
      input.type = "checkbox";
      input.name = "damaged-item";
      input.value = item.id || "";
      input.dataset.name = item.name || "";
      input.addEventListener("change", this.updateLabel);

      const text = document.createElement("span");
      const name = item.name || "Item";
      const meta = item.type ? ` (${item.type})` : "";
      text.textContent = `${name}${meta}`;

      row.appendChild(input);
      row.appendChild(text);
      this.damagedMenu.appendChild(row);
    });

    this.updateLabel();
  }

  setMenuOpen(isOpen) {
    if (!this.damagedContainer) return;
    this.damagedContainer.classList.toggle("open", Boolean(isOpen));
  }

  toggleMenu(e) {
    e?.stopPropagation();
    if (!this.damagedContainer) return;
    const isOpen = this.damagedContainer.classList.contains("open");
    this.setMenuOpen(!isOpen);
  }

  handleOutsideClick(e) {
    if (!this.damagedContainer) return;
    if (
      this.damagedContainer.contains(e.target) ||
      e.target === this.damagedTrigger
    ) {
      return;
    }
    this.setMenuOpen(false);
  }

  updateLabel() {
    if (!this.damagedTrigger || !this.damagedMenu) return;
    const selected = Array.from(
      this.damagedMenu.querySelectorAll('input[name="damaged-item"]:checked'),
    );
    if (selected.length === 0) {
      this.damagedTrigger.textContent = "Select damaged items (optional)";
    } else if (selected.length === 1) {
      const opt = selected[0];
      const name =
        opt.dataset.name || opt.closest("label")?.innerText || "Item";
      this.damagedTrigger.textContent = `1 item: ${name}`;
    } else {
      this.damagedTrigger.textContent = `${selected.length} items selected`;
    }
  }

  handleSubmit(e) {
    e.preventDefault();
    const damagedItems = this.damagedMenu
      ? Array.from(
          this.damagedMenu.querySelectorAll(
            'input[name="damaged-item"]:checked',
          ),
        ).map((input) => ({
          id: input.value,
          name: input.dataset.name || "",
        }))
      : [];
    const payload = {
      notes: this.notesInput?.value || "",
      damagedItems,
    };
    this.setMenuOpen(false);
    this.logger?.mock("Close loan", payload);
    if (this.onSubmit) this.onSubmit(payload);
  }
}
