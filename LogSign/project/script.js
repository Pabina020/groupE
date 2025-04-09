function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleButton = passwordInput.nextElementSibling;
    const toggleIcon = toggleButton.querySelector('img');

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
  
    const adminEmail = "admin@rentup.com";
    const adminPassword = "admin123";
  
    if (email === adminEmail && password === adminPassword) {
      // Admin login
      window.location.href = "/groupE/admin/admin.html";
    } else {
      window.location.href = "index.html"; // rental website
    }
  
    return false;
  }
  