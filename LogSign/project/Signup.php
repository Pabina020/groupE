<?php
include "config.php";
session_start();

// Handle AJAX-based signup (JSON)
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents("php://input"), true);

    $username = sanitizeInput($conn, $data['username'] ?? '');
    $email = sanitizeInput($conn, $data['email'] ?? '');
    $password = sanitizeInput($conn, $data['password'] ?? '');
    $role = sanitizeInput($conn, $data['role'] ?? '');

    if (!$username || !$email || !$password || !$role) {
        echo json_encode(['message' => 'All fields are required']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['message' => 'Email already exists']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $activationCode = md5(uniqid(rand(), true));

    $insertStmt = $conn->prepare("INSERT INTO users (username, email, password, role, activation_code) VALUES (?, ?, ?, ?, ?)");
    $insertStmt->bind_param("sssss", $username, $email, $hashedPassword, $role, $activationCode);

    if ($insertStmt->execute()) {
        echo json_encode(['message' => 'Signup successful']);
    } else {
        echo json_encode(['message' => 'Database error: ' . $conn->error]);
    }
    exit;
}

// Handle regular form-based signup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($conn, $_POST['username']);
    $email = sanitizeInput($conn, $_POST['email']);
    $password = sanitizeInput($conn, $_POST['password']);
    $role = sanitizeInput($conn, $_POST['role']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $checkResult = $stmt->get_result();

    if ($checkResult->num_rows > 0) {
        $error = "Email already exists.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $activationCode = md5(uniqid(rand(), true));

        $insertStmt = $conn->prepare("INSERT INTO users (username, email, password, role, activation_code) VALUES (?, ?, ?, ?, ?)");
        $insertStmt->bind_param("sssss", $username, $email, $hashedPassword, $role, $activationCode);

        if ($insertStmt->execute()) {
            $success = "Registration successful! You can now log in.";
            header("Location: login.php?success=" . urlencode($success));
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up - Rentup</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="container">
    <div class="auth-container">
      <div class="image-section">
        <img src="https://images.unsplash.com/photo-1570129477492-45c003edd2be?auto=format&fit=crop&q=80" alt="House" />
      </div>
      <div class="form-section">
        <h1>Welcome to Rentup</h1>

        <?php if (isset($error)): ?>
          <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
          <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="form-group">
            <label for="username">Enter username</label>
            <input type="text" id="username" name="username" placeholder="John Doe" required />
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email here" required />
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <div class="password-input">
              <input type="password" id="password" name="password" placeholder="Enter your password" required />
              <button type="button" class="toggle-password" onclick="togglePassword('password')">
                <img src="https://api.iconify.design/lucide:eye.svg" alt="Show password" />
              </button>
            </div>
          </div>
          <div class="form-group">
            <label for="role">Select Role</label>
            <select id="role" name="role" required>
              <option value="">Choose a role</option>
              <option value="tenant">Tenant</option>
              <option value="landlord">Landlord</option>
              <option value="both">Both</option>
            </select>
          </div>
          <div class="form-group checkbox">
            <input type="checkbox" id="terms" required />
            <label for="terms">I agree to the <a href="terms.html">terms & policy</a></label>
          </div>
          <button type="submit" class="primary-button">Signup</button>

          <div class="divider">Or</div>

          <div class="social-buttons">
            <button type="button" class="social-button" onclick="window.location.href='google-auth.html'">
              <img src="https://api.iconify.design/flat-color-icons:google.svg" alt="Google" />
              Sign in with Google
            </button>
            <button type="button" class="social-button" onclick="window.location.href='apple-auth.html'">
              <img src="https://api.iconify.design/ic:baseline-apple.svg" alt="Apple" />
              Sign in with Apple
            </button>
          </div>

          <p class="switch-auth">
            Already have an account? <a href="login.php">Log in</a>
          </p>
        </form>
      </div>
    </div>
  </div>

  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      input.type = input.type === "password" ? "text" : "password";
    }
  </script>
</body>
</html>