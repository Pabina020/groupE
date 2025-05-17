<?php

$host = 'localhost';       // usually localhost for XAMPP
$user = 'root';            // default XAMPP username
$password = '';            // default XAMPP password is empty
$database = 'rentup'; // your database name

// Create connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


// Handle booking confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $booking_id = $_POST['booking_id'];
    $query = "UPDATE bookings SET status = 'Confirmed' WHERE booking_id = $booking_id";
    mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rental Admin - Bookings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="style.css" rel="stylesheet" />
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-3 col-lg-2 sidebar py-4">
        <h5 class="text-center text-uppercase mb-4">RentUp Admin</h5>
        <a href="admin.html"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="properties.php"><i class="bi bi-building"></i> Properties</a>
        <a href="bookings.php" class="active"><i class="bi bi-calendar-event"></i> Bookings</a>
        <a href="users.html"><i class="bi bi-people"></i> Users</a>
        <a href="payments.html"><i class="bi bi-currency-dollar"></i> Payments</a>
        <a href="settings.php"><i class="bi bi-gear"></i> Settings</a>
      </aside>

      <main class="col-md-9 col-lg-10">
        <div class="dashboard-header d-flex justify-content-between align-items-center mt-3">
          <h4>Bookings</h4>
          <div class="d-flex gap-2">
            <input type="text" class="form-control" placeholder="Search bookings..." style="max-width: 300px" />
            <button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Filter</button>
          </div>
        </div>

        <div class="container-fluid py-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Booking ID</th>
                    <th>Property ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Family</th>
                    <th>Children</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $query = "SELECT * FROM bookings ORDER BY booking_id DESC";
                  $result = mysqli_query($conn, $query);

                  while ($row = mysqli_fetch_assoc($result)) {
                      $status = isset($row['status']) ? $row['status'] : 'Pending';
                      echo "<tr>
                          <td>{$row['booking_id']}</td>
                          <td>{$row['property_id']}</td>
                          <td>{$row['name']}</td>
                          <td>{$row['phone']}</td>
                          <td>{$row['family_members']}</td>
                          <td>{$row['children']}</td>
                          <td>{$row['booking_date']}</td>
                          <td>{$row['booking_time']}</td>
                          <td>
                            <span class='badge " . ($status == 'Confirmed' ? "bg-success" : "bg-warning text-dark") . "'>$status</span>
                          </td>
                          <td>";
                      if ($status != 'Confirmed') {
                          echo "<form method='POST'>
                                  <input type='hidden' name='booking_id' value='{$row['booking_id']}'>
                                  <button type='submit' name='confirm_booking' class='btn btn-sm btn-success'>Confirm</button>
                                </form>";
                      } else {
                          echo "<button class='btn btn-sm btn-secondary' disabled>Confirmed</button>";
                      }
                      echo "</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
