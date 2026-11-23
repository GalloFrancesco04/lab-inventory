import { BasePresenter } from "../core/BasePresenter.js";
import { request } from "../../api/ApiClient.js";
import { emailToDisplayName } from "../../utils/Transformers.js";

export class AuthPresenter extends BasePresenter {
  constructor() {
    super();

    // Load current user from localStorage if exists
    this.loadCurrentUser();
  }

  loadCurrentUser() {
    const storedUser = localStorage.getItem("currentUser");
    this.currentUser = storedUser ? JSON.parse(storedUser) : null;
  }

  saveCurrentUser() {
    if (this.currentUser) {
      localStorage.setItem("currentUser", JSON.stringify(this.currentUser));
    } else {
      localStorage.removeItem("currentUser");
    }
  }

  async login(email, password) {
    try {
      const user = await request("POST", "/login", { email, password });
      this.currentUser = {
        id: user.id,
        email: user.email,
        role: user.role === 1 ? "admin" : "technician",
        fullName: emailToDisplayName(user.email),
      };
      this.saveCurrentUser();
      this.notifyListeners();
      return { success: true, user: this.currentUser };
    } catch (error) {
      return { success: false, error: "Invalid email or password" };
    }
  }

  async logout() {
    await request("POST", "/logout").catch(() => {});
    this.currentUser = null;
    this.saveCurrentUser();
    this.notifyListeners();
  }

  getCurrentUser() {
    return this.currentUser;
  }

  isLoggedIn() {
    return this.currentUser !== null;
  }

  isAdmin() {
    return this.currentUser?.role === "admin";
  }

  isTechnician() {
    return this.currentUser?.role === "technician";
  }

  getTechnicianName() {
    return this.currentUser?.fullName || "";
  }
}

export function initAuthPresenter() {
  return new AuthPresenter();
}
