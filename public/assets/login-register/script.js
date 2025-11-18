const loginForm = document.getElementById("login-form");
const registerForm = document.getElementById("register-form");
const emailInput = document.getElementById("email-input");
const passwordInput = document.getElementById("password-input");
const nameInput = document.getElementById("name-input");

const api = {
  async authenticateUser(email, password, rememberMe = false) {
    const response = await fetch("api/auth/login", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:
        `email=${encodeURIComponent(email)}` +
        `&password=${encodeURIComponent(password)}` +
        (rememberMe ? `&remember_me=1` : ""),
    });
    if (!response.ok) throw new Error("Failed to authenticate user");
    return await response.json();
  },
  async registerUser(email, name, password) {
    const response = await fetch("api/auth/register", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `email=${encodeURIComponent(email)}&name=${encodeURIComponent(
        name
      )}&password=${encodeURIComponent(password)}`,
    });
    if (!response.ok) throw new Error("Failed to register user");
    return await response.json();
  },
};

// Toast
function errorToast(message) {
  const toast = document.createElement("div");
  toast.textContent = message;
  toast.className = "toast error";
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

// Login
if (loginForm) {
  loginForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const email = emailInput.value;
    const password = passwordInput.value;
    const rememberMe =
      document.getElementById("remember-me-input")?.checked === true;
    // Check so that the inputs are not empty
    if (empty(email) || empty(password)) {
      errorToast("Email and password are required");
      return;
    }
    api
      .authenticateUser(email, password, rememberMe)
      .then((data) => {
        console.log("Authenticated:", data);
        // Redirect or show success message
        data.success
          ? (window.location.href = "tasks")
          : errorToast(data.message);
      })
      .catch((error) => {
        console.error("Authentication failed:", error);

        // Show error message
        errorToast(
          "Authentication failed. Please check your email and password."
        );
      });
  });
}

// Register
if (registerForm) {
  registerForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const email = emailInput.value;
    const name = nameInput.value;
    const password = passwordInput.value;
    //Check so that inputs are not empty
    if (empty(email) || empty(name) || empty(password)) {
      errorToast("Email, name, and password are required");
      return;
    }
    api
      .registerUser(email, name, password)
      .then((data) => {
        console.log("Registered:", data);
        data.success
          ? (window.location.href = "login")
          : errorToast(data.message);
      })
      .catch((error) => {
        console.error("Registration failed:", error);

        // Show error message
        errorToast("Registration failed. Please try again.");
      });
  });
}

//Check if a string is emty.
function empty(str) {
  return !str.trim().length;
}
