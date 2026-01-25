import { BasePresenter } from "../core/BasePresenter.js";

export class AuthPresenter extends BasePresenter {
  constructor() {
    super();

    // Mock users
    this.users = [
      {
        id: 1,
        username: "admin",
        password: "admin123",
        role: "admin",
        fullName: "Administrator",
      },
      {
        id: 2,
        username: "mario",
        password: "tech123",
        role: "technician",
        fullName: "Mario Rossi",
        techName: "M. Rossi",
      },
      {
        id: 3,
        username: "luigi",
        password: "tech123",
        role: "technician",
        fullName: "Luigi Verdi",
        techName: "R. Verdi", // R. Verdi in maintenance data
      },
      {
        id: 4,
        username: "anna",
        password: "tech123",
        role: "technician",
        fullName: "Anna Bianchi",
        techName: "A. Bianchi",
      },
    ];

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

  login(username, password) {
    const user = this.users.find(
      (u) => u.username === username && u.password === password,
    );

    if (user) {
      // Don't store password in currentUser
      this.currentUser = {
        id: user.id,
        username: user.username,
        role: user.role,
        fullName: user.fullName,
        techName: user.techName, // For filtering maintenance by technician
      };
      this.saveCurrentUser();
      this.notifyListeners();
      return { success: true, user: this.currentUser };
    }

    return { success: false, error: "Invalid username or password" };
  }

  logout() {
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
