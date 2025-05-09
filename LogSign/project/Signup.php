<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($conn, $_POST['username']);
    $email = sanitizeInput($conn, $_POST['email']);
    $password = sanitizeInput($conn, $_POST['password']);
    $role = sanitizeInput($conn, $_POST['role']);
    
    // Check if email already exists
    $checkSql = "SELECT * FROM users WHERE email = '$email'";
    $checkResult = mysqli_query($conn, $checkSql);
    
    if (mysqli_num_rows($checkResult) > 0) {
        $error = "Email already exists";
    } else {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate activation code
        $activationCode = md5(uniqid(rand(), true));
        
        // Insert user into database
        $insertSql = "INSERT INTO users (username, email, password, role, activation_code) 
                      VALUES ('$username', '$email', '$hashedPassword', '$role', '$activationCode')";
        
        if (mysqli_query($conn, $insertSql)) {
            $success = "Registration successful! You can now login.";
            header("Location: login.php?success=" . urlencode($success));
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
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
        
        <?php if(isset($error)): ?>
          <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(isset($_GET['success'])): ?>
          <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        
        <form id="signupForm" method="POST">
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
          <a href="../../property-upload-delete.html" style="text-decoration: none;">
                        <button type="button" class="primary-button">
                            Signup as Landlord
                        </button>
          </a>
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

  <script src="script.js"></script>
</body>
</html>