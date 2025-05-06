// Toggle password visibility
function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const icon = input.nextElementSibling.querySelector('img');

  const isPassword = input.type === 'password';
  input.type = isPassword ? 'text' : 'password';
  icon.src = isPassword
    ? 'https://api.iconify.design/lucide:eye.svg'
    : 'https://api.iconify.design/lucide:eye-off.svg';
  icon.alt = isPassword ? 'Hide password' : 'Show password';
}

// Handle signup
function handleSignup() {
  const username = document.getElementById("username").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const role = document.getElementById("role").value;
  const terms = document.getElementById("terms").checked;

  if (!terms) {
    alert("Please agree to the terms and policy.");
    return;
  }

  fetch("http://localhost:3001/signup", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include", // allow cookies
    body: JSON.stringify({ username, email, password, role })
  })
    .then(res => res.json())
    .then(data => {
      if (data.message === "Signup successful") {
        window.location.replace("success1.html");
      } else {
        alert(data.message || "Signup failed");
      }
    })
    .catch(err => {
      alert("Server error. Please try again later.");
      console.error(err);
    });
}

// Handle login
function loginUser(event) {
  event.preventDefault();
  const email = document.getElementById("login-email").value.trim();
  const password = document.getElementById("login-password").value;

  if (email === "admin@rentup.com" && password === "admin123") {
    document.cookie = `user=${encodeURIComponent(JSON.stringify({ name: "Admin" }))}; path=/`;
    window.location.href = "/groupE/admin/admin.html";
    return;
  }

  fetch("http://localhost:3001/login", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include", // allow server cookie
    body: JSON.stringify({ email, password })
  })
    .then(res => res.json())
    .then(data => {
      if (data.message === "Login successful") {
        if (data.role === "landlord" || data.role === "both") {
          window.location.href = "../../property-upload-delete.html";
        } else if (data.role === "tenant") {
          window.location.href = "../../index.html";
        } else {
          alert("Unknown user role.");
        }
      } else {
        alert(data.message || "Invalid credentials");
      }
    })
    .catch(err => {
      alert("Server error. Please try again later.");
      console.error(err);
    });
}

// On page load, update displayed username
window.addEventListener("DOMContentLoaded", () => {
  const user = getCookie('user');

  if (user) {
    try {
      const parsed = JSON.parse(decodeURIComponent(user));

      const nameSpan = document.getElementById('username');
      if (nameSpan) {
        nameSpan.textContent = parsed.name;
      }

      const authSection = document.getElementById('auth-buttons');
      if (authSection) {
        authSection.innerHTML = `
          <div class="d-flex align-items-center gap-3">
            <img src="images/user-avatar.png" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
            <span class="fw-semibold text-dark">Welcome, ${parsed.name}</span>
            <a href="#" class="btn btn-outline-danger btn-sm rounded-pill" onclick="logoutUser()">Logout</a>
          </div>
        `;
      }
    } catch (err) {
      console.error("Cookie parsing failed:", err);
    }
  }
});

// Logout: Clear cookie and redirect
function logoutUser() {
  document.cookie = "user=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
  window.location.href = "landingpage.html";
}

// Utility to get cookie value
function getCookie(name) {
  const match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
  return match ? match[2] : null;
}
