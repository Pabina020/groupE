// Toggle password visibility
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const icon = passwordField.nextElementSibling.querySelector('img');

    if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.src = 'https://api.iconify.design/lucide:eye-off.svg';
        icon.alt = 'Hide password';
    } else {
        passwordField.type = "password";
        icon.src = 'https://api.iconify.design/lucide:eye.svg';
        icon.alt = 'Show password';
    }
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

    fetch("signup.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, email, password, role })
    })
    .then(res => res.json())
    .then(data => {
        if (data.message === "Signup successful") {
            document.cookie = `user=${encodeURIComponent(JSON.stringify({ name: username }))}; path=/`;
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
        window.location.href = "../../admin/admin.html";
        return;
    }
    fetch("login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
    })
    .then(res => res.json())
    .then(data => {
        if (data.message === "Login successful") {
            const name = email.split('@')[0];
            document.cookie = `user=${encodeURIComponent(JSON.stringify({ name }))}; path=/`;

            if (data.role === "landlord" || data.role === "both") {
                window.location.href = "../../landlord.html";
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

// Handle OTP verification
function verifyOtp(event) {
    event.preventDefault(); // Prevent default form submission

    const email = document.getElementById("otpForm").email.value.trim();
    const otp = document.getElementById("otp").value.trim();

    // Basic validation
    if (!otp) {
        alert("Please enter the OTP");
        return;
    }

    // Sending OTP to the server for verification
    fetch("http://localhost:3001/verify-otp", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, otp })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // OTP verified successfully, display success message and redirect
            const successMessage = document.createElement("div");
            successMessage.classList.add("success-message");
            successMessage.innerText = "OTP verified successfully!";
            document.body.appendChild(successMessage);

            setTimeout(() => {
                window.location.href = "../../index.html"; // Redirect after 2 seconds
            }, 2000);
        } else {
            // Error in OTP verification
            const errorMessage = document.createElement("div");
            errorMessage.classList.add("error-message");
            errorMessage.innerText = data.message || "Invalid OTP or OTP expired.";
            document.body.appendChild(errorMessage);
        }
    })
    .catch(err => {
        alert("Server error. Please try again later.");
        console.error(err);
    });
}

// ✅ Updated: Handle OTP verification (PHP form submit)
function verifyOtp(event) {
    event.preventDefault();

    const form = document.getElementById("otpForm");
    if (!form.otp.value.trim()) {
        alert("Please enter the OTP");
        return;
    }

    // Submit form normally to PHP backend (login.php handles OTP)
    form.submit();
}

// Update UI with logged-in user from cookie
window.addEventListener("DOMContentLoaded", () => {
    const user = getCookie('user');
    const authSection = document.getElementById('auth-buttons');

    if (user && authSection) {
        try {
            const parsed = JSON.parse(decodeURIComponent(user));
            authSection.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <img src="images/user-avatar.png" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
                    <span class="fw-semibold text-dark">Welcome, ${parsed.name}</span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill">Logout</a>
                </div>
            `;
        } catch (err) {
            console.error("Cookie parsing failed:", err);
        }
    }
});

// Get cookie by name
function getCookie(name) {
    const match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
    return match ? match[2] : null;
}
