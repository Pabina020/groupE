function togglePassword(inputId) {
  const passwordInput = document.getElementById(inputId);
  const toggleIcon = passwordInput.nextElementSibling.querySelector('img');

  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    toggleIcon.src = 'https://api.iconify.design/lucide:eye.svg';
    toggleIcon.alt = 'Hide password';
  } else {
    passwordInput.type = 'password';
    toggleIcon.src = 'https://api.iconify.design/lucide:eye-off.svg';
    toggleIcon.alt = 'Show password';
  }
}
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
    body: JSON.stringify({ username, email, password, role })
  })
    .then(async (res) => {
      const data = await res.json();
      console.log("Signup response:", data);

      if (res.ok && data.message === "Signup successful") {
        // ✅ Instead of alert + redirect, try direct redirect
        window.location.replace("success1.html");
      } else {
        alert(data.message || "Signup failed");
      }
    })
    .catch(err => {
      alert("Server error. Please try again later.");
      console.error("Fetch error:", err);
    });
}

function loginUser(event) {
  event.preventDefault();

  const email = document.getElementById("login-email").value.trim();
  const password = document.getElementById("login-password").value;

  const adminEmail = "admin@rentup.com";
  const adminPassword = "admin123";

  if (email === adminEmail && password === adminPassword) {
    window.location.href = "/groupE/admin/admin.html";
    return;
  }

  fetch("http://localhost:3001/login", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
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
        alert(data.message); // Invalid credentials
      }
    })
    .catch(err => {
      alert("Server error");
      console.error(err);
    });
}

