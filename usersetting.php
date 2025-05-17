<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'rentup';
$dbuser = 'root';
$dbpass = '';

// Create connection
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please login first");
}

$userId = $_SESSION['user_id'];
$statusMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validate inputs
    if (empty($username) || empty($email)) {
        $statusMessage = "Username and email are required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $statusMessage = "Please enter a valid email address.";
    } else {
        // Prepare and execute update
        $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
        if ($stmt === false) {
            $statusMessage = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param('ssi', $username, $email, $userId);
            if ($stmt->execute()) {
                $statusMessage = "Settings updated successfully!";
            } else {
                $statusMessage = "Error updating settings: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch current user data
$userData = ['username' => '', 'email' => ''];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id=?");
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $userData = $result->fetch_assoc() ?: $userData;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Settings - RentUp Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    .sidebar {
      background-color: #f8f9fa;
      height: 100vh;
      position: fixed;
      width: 250px;
    }
    .sidebar a {
      display: block;
      padding: 10px 15px;
      color: #333;
      text-decoration: none;
      border-left: 3px solid transparent;
    }
    .sidebar a.active {
      background-color: #e9ecef;
      border-left: 3px solid #0d6efd;
      font-weight: 500;
    }
    .sidebar a:hover {
      background-color: #e9ecef;
    }
    .sidebar i {
      margin-right: 10px;
    }
    main {
      margin-left: 250px;
      padding: 20px;
    }
    .dashboard-header {
      padding: 15px 0;
      border-bottom: 1px solid #dee2e6;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-3 col-lg-2 sidebar py-4">
        <a href="usersetting.php" class="active"><i class="bi bi-gear"></i> User Settings</a>
      </aside>

      <main class="col-md-9 col-lg-10">
        <div class="dashboard-header d-flex justify-content-between align-items-center">
          <h4>User Settings</h4>
          <a href="index.html" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <div class="container py-4">
          <?php if ($statusMessage): ?>
            <div class="alert alert-<?php echo strpos($statusMessage, 'successfully') !== false ? 'success' : 'danger'; ?>">
              <?php echo htmlspecialchars($statusMessage); ?>
            </div>
          <?php endif; ?>

          <div class="card shadow-sm">
            <div class="card-body">
              <form id="userSettingsForm" method="POST" action="usersetting.php">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                  </div>
                </div>

                <div class="text-end">
                  <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save"></i> Save Changes
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('userSettingsForm').addEventListener('submit', function(e) {
      const username = document.getElementById('username').value.trim();
      const email = document.getElementById('email').value.trim();
      
      if (!username) {
        alert('Username is required');
        e.preventDefault();
        return;
      }
      
      if (!email) {
        alert('Email address is required');
        e.preventDefault();
        return;
      }
      
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Please enter a valid email address');
        e.preventDefault();
      }
    });
  </script>
</body>
</html>