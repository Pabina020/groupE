<?php
session_start();

// --- CONFIGURE THESE ---
$host = 'localhost';
$dbname = 'property_db';  // your database name
$dbuser = 'root';         // your db username
$dbpass = '';             // your db password
// ------------------------

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// For demo purposes, set a logged in user ID here (replace with your auth system)
if (!isset($_SESSION['user_id'])) {
    // For testing only: simulate a logged in user with ID = 1
    $_SESSION['user_id'] = 1;
}

$userId = $_SESSION['user_id'];
$statusMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $conn->real_escape_string(trim($_POST['firstName']));
    $lastName = $conn->real_escape_string(trim($_POST['lastName']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $sql = "UPDATE users SET first_name=?, last_name=?, email=?, phone=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssi', $firstName, $lastName, $email, $phone, $userId);

        if ($stmt->execute()) {
            $statusMessage = "User settings updated successfully!";
        } else {
            $statusMessage = "Error updating user settings: " . $conn->error;
        }
        $stmt->close();
    } else {
        $statusMessage = "Invalid email format.";
    }
}

// Fetch current user data
$sql = "SELECT first_name, last_name, email, phone FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

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
  <link href="style.css" rel="stylesheet" />
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar (reuse from admin) -->
      <aside class="col-md-3 col-lg-2 sidebar py-4">
        <h5 class="text-center text-uppercase mb-4">RentUp Admin</h5>
        
        <a href="usersetting.php" class="active"><i class="bi bi-gear"></i> User Settings</a>
      </aside>

      <main class="col-md-9 col-lg-10">
        <div class="dashboard-header d-flex justify-content-between align-items-center">
          <h4>User Settings</h4>
          <a href="index.html" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <div class="container py-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <form id="userSettingsForm" method="POST" action="usersetting.php">
                <div class="mb-3">
                  <label for="firstName" class="form-label">First Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="firstName"
                    name="firstName"
                    value="<?php echo htmlspecialchars($userData['first_name'] ?? ''); ?>"
                    required
                  />
                </div>
                <div class="mb-3">
                  <label for="lastName" class="form-label">Last Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="lastName"
                    name="lastName"
                    value="<?php echo htmlspecialchars($userData['last_name'] ?? ''); ?>"
                    required
                  />
                </div>
                <div class="mb-3">
                  <label for="email" class="form-label">Email Address</label>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>"
                    required
                  />
                </div>
                <div class="mb-3">
                  <label for="phone" class="form-label">Phone Number</label>
                  <input
                    type="text"
                    class="form-control"
                    id="phone"
                    name="phone"
                    value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>"
                  />
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
              </form>

              <div id="statusMessage" class="mt-3">
                <?php if ($statusMessage): ?>
                  <div class="alert alert-info"><?php echo htmlspecialchars($statusMessage); ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
