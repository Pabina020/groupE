<?php
// Connect to database
$mysqli = new mysqli("localhost", "root", "", "rentup");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
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

    if ($first_name === '' || $email === '') {
        $error_message = "First name and email cannot be empty.";
    } else {
        // Check if user with id=1 exists
        $check = $mysqli->query("SELECT id FROM admin_users WHERE id = 1");
        if ($check && $check->num_rows === 0) {
            // Insert if not exists
            $stmt = $mysqli->prepare("INSERT INTO admin_users (id, full_name, email, phone) VALUES (1, ?, ?, ?)");
            if (!$stmt) {
                die("Prepare failed: " . $mysqli->error);
            }
            $stmt->bind_param("sss", $full_name, $email, $phone);
            $stmt->execute();
            $stmt->close();
        } else {
            // Update existing
            $stmt = $mysqli->prepare("UPDATE admin_users SET full_name = ?, email = ?, phone = ? WHERE id = 1");
            if (!$stmt) {
                die("Prepare failed: " . $mysqli->error);
            }
            $stmt->bind_param("sss", $full_name, $email, $phone);
            if ($stmt->execute()) {
                $success_message = "Profile updated successfully.";
            } else {
                $error_message = "Error updating profile: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch current user data
$result = $mysqli->query("SELECT * FROM admin_users WHERE id = 1");
if (!$result) {
    die("Fetch user failed: " . $mysqli->error);
}
$user = $result->fetch_assoc() ?: [];

// Extract names and email, phone fallback
$first_name = explode(' ', $user['full_name'] ?? 'Admin')[0] ?? 'Admin';
$last_name = explode(' ', $user['full_name'] ?? 'Admin')[1] ?? '';
$email = $user['email'] ?? '';
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
  <link href="style.css" rel="stylesheet">
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-3 col-lg-2 sidebar py-4">
        <h5 class="text-center text-uppercase mb-4">RentUp Admin</h5>
        <a href="admin.html"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="properties.php" class="active"><i class="bi bi-building"></i> Properties</a>
        <a href="bookings.php"><i class="bi bi-calendar-event"></i> Bookings</a>
        <a href="users.html"><i class="bi bi-people"></i> Users</a>
        <a href="payments.html"><i class="bi bi-currency-dollar"></i> Payments</a>
        <a href="settings.php"><i class="bi bi-gear"></i> Settings</a>
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
</body>
</html>