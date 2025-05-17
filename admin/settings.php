<?php
// Connect to database
$mysqli = new mysqli("localhost", "root", "", "rentup");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create admin_users table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$mysqli->query($create_table)) {
    die("Error creating table: " . $mysqli->error);
}

// Insert default admin if none exists
$check_admin = $mysqli->query("SELECT id FROM admin_users WHERE id = 1");
if ($check_admin && $check_admin->num_rows === 0) {
    $mysqli->query("INSERT INTO admin_users (id, full_name, email) VALUES (1, 'Admin User', 'admin@example.com')");
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $full_name = $first_name . ' ' . $last_name;

    // Validate inputs
    if (empty($first_name) || empty($email)) {
        $error_message = "First name and email cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Update admin user
        $stmt = $mysqli->prepare("UPDATE admin_users SET full_name = ?, email = ?, phone = ? WHERE id = 1");
        if (!$stmt) {
            $error_message = "Database error: " . $mysqli->error;
        } else {
            $stmt->bind_param("sss", $full_name, $email, $phone);
            if ($stmt->execute()) {
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Error updating profile: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch current admin data
$result = $mysqli->query("SELECT * FROM admin_users WHERE id = 1");
if (!$result) {
    die("Error fetching admin data: " . $mysqli->error);
}

$user = $result->fetch_assoc();
$names = explode(' ', $user['full_name'] ?? 'Admin User');
$first_name = $names[0] ?? 'Admin';
$last_name = $names[1] ?? '';
$email = $user['email'] ?? 'admin@example.com';
$phone = $user['phone'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Rental Admin - Settings</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
        <h5 class="text-center text-uppercase mb-4">RentUp Admin</h5>
        <a href="admin.html"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="properties.php"><i class="bi bi-building"></i> Properties</a>
        <a href="bookings.php"><i class="bi bi-calendar-event"></i> Bookings</a>
        <a href="users.html"><i class="bi bi-people"></i> Users</a>
        <a href="payments.html"><i class="bi bi-currency-dollar"></i> Payments</a>
        <a href="settings.php" class="active"><i class="bi bi-gear"></i> Settings</a>
      </aside>

      <main class="col-md-9 col-lg-10">
        <div class="dashboard-header">
          <h4>Settings</h4>
        </div>

        <div class="container-fluid py-4">
          <?php if ($success_message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
          <?php elseif ($error_message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
          <?php endif; ?>

          <form method="POST" action="settings.php" novalidate>
            <div class="row mb-4">
              <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                  <div class="card-body">
                    <h5 class="mb-3">Profile Settings</h5>
                    <div class="d-flex align-items-center mb-3">
                      <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Profile Picture" class="rounded-circle" width="60" height="60">
                      <button type="button" class="btn btn-outline-secondary ms-3">Change</button>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label" for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($first_name) ?>" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label" for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($last_name) ?>">
                      </div>
                      <div class="col-12">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                      </div>
                      <div class="col-12">
                        <label class="form-label" for="phone">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card shadow-sm">
                  <div class="card-body">
                    <h5 class="mb-3">Notification Settings</h5>
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                      <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                      <div class="form-text">Receive email notifications for new bookings</div>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="smsNotifications">
                      <label class="form-check-label" for="smsNotifications">SMS Notifications</label>
                      <div class="form-text">Receive SMS alerts for urgent updates</div>
                    </div>
                  </div>
                </div>

                <div class="text-end mt-4">
                  <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const firstName = document.getElementById('first_name').value.trim();
      const email = document.getElementById('email').value.trim();
      
      if (!firstName) {
        alert('First name is required');
        e.preventDefault();
        return;
      }
      
      if (!email) {
        alert('Email is required');
        e.preventDefault();
        return;
      }
    });
  </script>
</body>
</html>

<?php
$mysqli->close();
?>