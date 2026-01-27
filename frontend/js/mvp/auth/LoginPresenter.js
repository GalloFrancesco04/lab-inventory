import { BasePresenter } from "../core/BasePresenter.js";

export class AuthPresenter extends BasePresenter {
  constructor() {
    super();

    // Mock users
    this.users = [
      {
        id: 1,
        email: "admin@library.com",
        password: "admin123",
        role: "admin",
        fullName: "Administrator",
      },
      {
        id: 2,
        email: "mario.rossi@library.com",
        password: "tech123",
        role: "technician",
        fullName: "Mario Rossi",
        techName: "Mario Rossi",
      },
      {
        id: 3,
        email: "sara.bianchi@library.com",
        password: "tech123",
        role: "technician",
        fullName: "Sara Bianchi",
        techName: "Sara Bianchi",
      },
      {
        id: 4,
        email: "roberto.verdi@library.com",
        password: "tech123",
        role: "technician",
        fullName: "Roberto Verdi",
        techName: "Roberto Verdi",
      },
      {
        id: 5,
        email: "anna.neri@library.com",
        password: "tech123",
        role: "technician",
        fullName: "Anna Neri",
        techName: "Anna Neri",
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

  login(email, password) {
    const user = this.users.find(
      (u) => u.email === email && u.password === password,
    );

    if (user) {
      // Don't store password in currentUser
      this.currentUser = {
        id: user.id,
        email: user.email,
        role: user.role,
        fullName: user.fullName,
        techName: user.techName, // For filtering maintenance by technician
      };
      this.saveCurrentUser();
      this.notifyListeners();
      return { success: true, user: this.currentUser };
    }

    return { success: false, error: "Invalid email or password" };
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
