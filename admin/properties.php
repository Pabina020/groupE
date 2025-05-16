<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Rental Admin - Properties</title>
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
        <a href="properties.php" class="active"><i class="bi bi-building"></i> Properties</a>
        <a href="bookings.php"><i class="bi bi-calendar-event"></i> Bookings</a>
        <a href="users.html"><i class="bi bi-people"></i> Users</a>
        <a href="payments.html"><i class="bi bi-currency-dollar"></i> Payments</a>
        <a href="settings.php"><i class="bi bi-gear"></i> Settings</a>
      </aside>

      <main class="col-md-9 col-lg-10">
        <div class="dashboard-header d-flex justify-content-between align-items-center">
          <h4>Properties</h4>
        </div>

        <div class="container-fluid py-4">
          <div class="row mb-3">
            <div class="col-md-6">
              <input type="text" id="searchInput" class="form-control" placeholder="Search properties..." />
            </div>
          </div>

          <div class="card shadow-sm">
            <div class="card-body">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Bedrooms</th>
                    <th>Bathrooms</th>
                    <th>Sqft</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $host = 'localhost';
                  $user = 'root';
                  $password = '';
                  $database = 'property_db';

                  $conn = mysqli_connect($host, $user, $password, $database);
                  if (!$conn) die("Connection failed: " . mysqli_connect_error());

                  $result = mysqli_query($conn, "SELECT * FROM properties");
                  while ($property = mysqli_fetch_assoc($result)) {
                      echo "<tr>
                        <td>{$property['property_id']}</td>
                        <td>" . htmlspecialchars($property['name']) . "</td>
                        <td>" . htmlspecialchars($property['location']) . "</td>
                        <td>" . htmlspecialchars($property['type']) . "</td>
                        <td>" . intval($property['bedrooms']) . "</td>
                        <td>" . intval($property['bathrooms']) . "</td>
                        <td>" . intval($property['sqft']) . "</td>
                        <td>$" . htmlspecialchars($property['price']) . "</td>
                        <td>
                          <form action='update_status.php' method='POST' onsubmit='return confirm(\"Change status?\")'>
                            <input type='hidden' name='property_id' value='{$property['property_id']}'>
                            <select name='is_approved' class='form-select form-select-sm' onchange='this.form.submit()'>
                              <option value='1'" . ($property['is_approved'] ? " selected" : "") . ">Approved</option>
                              <option value='0'" . (!$property['is_approved'] ? " selected" : "") . ">Pending</option>
                            </select>
                          </form>
                        </td>
                        <td>
                          <form action='delete_property.php' method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this property?\")'>
                            <input type='hidden' name='property_id' value='{$property['property_id']}'>
                            <button type='submit' class='btn btn-sm btn-outline-danger'>Delete</button>
                          </form>
                        </td>
                      </tr>";
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
  <script>
    // Live Search Functionality
    document.getElementById('searchInput').addEventListener('input', function () {
      const search = this.value.toLowerCase();
      document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(search) ? '' : 'none';
      });
    });
  </script>
</body>

</html>
