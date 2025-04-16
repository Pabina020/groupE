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

function loginUser(event) {
  event.preventDefault();

  const email = document.getElementById("login-email").value.trim();
  const password = document.getElementById("login-password").value;

  // First, check if admin
  const adminEmail = "admin@rentup.com";
  const adminPassword = "admin123";

  if (email === adminEmail && password === adminPassword) {
    // Redirect to admin dashboard
    window.location.href = "/groupE/admin/admin.html";
    return;
  }

  // Else, check via backend
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
        } else {
          window.location.href = "../../index.html";

        }
      } else {
        alert(data.message);
      }
    })
    .catch(err => {
      alert("Server error");
      console.error(err);
    });
}
