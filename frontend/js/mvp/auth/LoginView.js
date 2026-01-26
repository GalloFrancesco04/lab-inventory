import { BaseView } from "../core/BaseView.js";

export class LoginView extends BaseView {
  constructor(presenter, mediator) {
    super(mediator);
    this.presenter = presenter;
    this.maintenancePresenter = null; // Will be set after initialization
    this.setupElements();
    this.setupEventListeners();
  }

  setMaintenancePresenter(maintenancePresenter) {
    this.maintenancePresenter = maintenancePresenter;
  }

  setupElements() {
    this.loginView = this.getElement("login-view");
    this.loginForm = this.getElement("login-form");
    this.usernameInput = this.getElement("login-username");
    this.passwordInput = this.getElement("login-password");
    this.togglePasswordBtn = this.getElement("toggle-password");
    this.errorDiv = this.getElement("login-error");

    this.mainNavbar = this.getElement("main-navbar");
    this.mainToolbar = this.getElement("main-toolbar");
    this.mainContent = this.getElement("main-content");
    this.userNameSpan = this.getElement("user-name");
    this.logoutBtn = this.getElement("logout-btn");
  }

  setupEventListeners() {
    this.loginForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      this.handleLogin();
    });

    this.togglePasswordBtn?.addEventListener("click", (e) => {
      e.preventDefault();
      this.togglePasswordVisibility();
    });

    this.logoutBtn?.addEventListener("click", () => {
      this.handleLogout();
    });
  }

  handleLogin() {
    const username = this.usernameInput.value.trim();
    const password = this.passwordInput.value.trim();

    if (!username || !password) {
      this.showError("Please enter username and password");
      return;
    }

    const result = this.presenter.login(username, password);

    if (result.success) {
      this.hideError();
      this.loginForm.reset();

      // Set target route based on role before reload so the hash persists
      const targetHash =
        result.user.role === "technician" ? "#/maintenance" : "#/loans";
      window.location.hash = targetHash;

      // Reload to ensure layout renders correctly with persisted session
      setTimeout(() => {
        window.location.reload();
      }, 200);
    } else {
      this.showError(result.error);
    }
  }

  handleLogout() {
    this.presenter.logout();
    this.showLoginView();

    // Show info toast if available
    if (this.mediator?.toaster) {
      this.mediator.toaster.info("Logged out successfully");
    }
  }

  togglePasswordVisibility() {
    const isPassword = this.passwordInput.type === "password";
    this.passwordInput.type = isPassword ? "text" : "password";

    // Toggle between eye and eye-off icons
    if (isPassword) {
      // Eye with slash (password visible)
      this.togglePasswordBtn.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
          <line x1="1" y1="1" x2="23" y2="23"></line>
        </svg>
      `;
    } else {
      // Eye (password hidden)
      this.togglePasswordBtn.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
          <circle cx="12" cy="12" r="3"></circle>
        </svg>
      `;
    }
  }

  showError(message) {
    this.errorDiv.textContent = message;
    this.errorDiv.style.display = "block";
  }

  hideError() {
    this.errorDiv.style.display = "none";
    this.errorDiv.textContent = "";
  }

  showLoginView() {
    this.loginView.style.display = "flex";
    this.mainNavbar.style.display = "none";
    this.mainToolbar.style.display = "none";
    this.mainContent.style.display = "none";
    this.loginForm.reset();
    this.hideError();

    // Hide all other views
    document.querySelectorAll(".view").forEach((el) => {
      if (el.id !== "login-view") {
        el.classList.add("hidden");
      }
    });
  }

  showMainApp(user) {
    this.loginView.style.display = "none";

    if (this.mainNavbar) {
      this.mainNavbar.style.display = "flex";
    }

    if (this.mainToolbar) {
      this.mainToolbar.style.display = "flex";
    }

    if (this.mainContent) {
      this.mainContent.style.display = "block";
    }

    this.userNameSpan.textContent = user.fullName;

    // Hide tabs based on role
    this.updateNavbarForRole(user.role);
  }

  updateNavbarForRole(role) {
    const navButtons = this.mainNavbar.querySelectorAll("[data-route]");

    if (role === "technician") {
      // Technician: only show maintenance tab
      navButtons.forEach((btn) => {
        if (btn.dataset.route === "#/maintenance") {
          btn.style.display = "block";
        } else {
          btn.style.display = "none";
        }
      });
    } else {
      // Admin: show all tabs
      navButtons.forEach((btn) => {
        btn.style.display = "block";
      });
    }
  }

  checkAuthAndRender() {
    const user = this.presenter.getCurrentUser();

    if (user) {
      this.showMainApp(user);
    } else {
      this.showLoginView();
    }
  }
}

export function initLoginView(presenter, mediator) {
  return new LoginView(presenter, mediator);
}
