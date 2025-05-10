<?php
include "config.php";
session_start();

// Sanitize helper
function sanitizeInput($conn, $data) {
  return mysqli_real_escape_string($conn, trim($data));
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
  $email = sanitizeInput($conn, $_POST['email']);
  $otp = sanitizeInput($conn, $_POST['otp']);

  $stmt = $conn->prepare("SELECT * FROM opt_verification WHERE email = ? AND otp = ? AND is_verified = 0 AND expires_at > NOW()");
  $stmt->bind_param("ss", $email, $otp);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    $conn->query("UPDATE opt_verification SET is_verified = 1 WHERE email = '$email' AND otp = '$otp'");
    $user = $conn->query("SELECT * FROM users WHERE email = '$email'")->fetch_assoc();

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    switch ($user['role']) {
      case 'admin':
        header("Location: ../../admin/admin.html");
        break;
      case 'landlord':
      case 'both':
        header("Location: ../../landlord.html");
        break;
      default:
        header("Location: ../../index.html");
        break;
    }
    exit();
  } else {
    $error = "Invalid OTP or expired.";
  }
}

// Handle login and generate OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
  $email = sanitizeInput($conn, $_POST['email']);
  $password = sanitizeInput($conn, $_POST['password']);

  if ($email === "admin@rentup.com" && $password === "admin123") {
    $_SESSION['username'] = 'Admin';
    $_SESSION['role'] = 'admin';
    header("Location: ../../admin/admin.html");
    exit();
  }

  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
      $otp = rand(100000, 999999);
      $otpStmt = $conn->prepare("INSERT INTO opt_verification (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
      $otpStmt->bind_param("ss", $email, $otp);
      $otpStmt->execute();

      $otpMessage = "Your OTP is: <strong>$otp</strong>";
      $showOtpForm = true;
    } else {
      $error = "Invalid credentials.";
    }
  } else {
    $error = "Account not found.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Rentup</title>
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

        <?php if(isset($error)): ?>
          <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if(isset($otpMessage)): ?>
          <div class="success-message"><?php echo $otpMessage; ?></div>
        <?php endif; ?>

        <?php if(isset($showOtpForm) && $showOtpForm): ?>
          <form method="POST" autocomplete="off">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <div class="form-group">
              <label for="otp">Enter OTP</label>
              <input type="text" id="otp" name="otp" placeholder="Enter the 6-digit OTP" required />
            </div>
            <button type="submit" name="verify_otp" class="primary-button">Verify OTP</button>
          </form>
        <?php else: ?>
          <form method="POST" autocomplete="on">
            <div class="form-group">
              <label for="login-email">Email</label>
              <input type="email" id="login-email" name="email" placeholder="Enter your email here" required autocomplete="email" />
            </div>
            <div class="form-group">
              <label for="login-password">Password</label>
              <div class="password-input">
                <input type="password" id="login-password" name="password" placeholder="Enter your password" required autocomplete="current-password" />
                <button type="button" class="toggle-password" onclick="togglePassword('login-password')">
                  <img src="https://api.iconify.design/lucide:eye.svg" alt="Show password" />
                </button>
              </div>
            </div>
            <div class="form-group checkbox">
              <input type="checkbox" id="remember-me" />
              <label for="remember-me">Remember Me</label>
            </div>
            <button type="submit" name="login" class="primary-button">Login</button>

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
              Don't have an account? <a href="Signup.html">Sign up</a>
            </p>
          </form>
        <?php endif; ?>
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
