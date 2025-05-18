<?php
// Database connection and query
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'rentup';

$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['is_approved'])) {
    $property_id = intval($_POST['property_id']);
    $is_approved = intval($_POST['is_approved']);
    
    $update_query = "UPDATE properties SET is_approved = $is_approved WHERE property_id = $property_id";
    mysqli_query($conn, $update_query);
}

// Handle property deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_property'])) {
    $property_id = intval($_POST['property_id']);
    $delete_query = "DELETE FROM properties WHERE property_id = $property_id";
    mysqli_query($conn, $delete_query);
}

// Fetch properties
$result = mysqli_query($conn, "SELECT * FROM properties");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Rental Admin - Properties</title>
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
        
        .form-select-sm {
            width: auto;
            display: inline-block;
        }
    </style>
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
                    <a href="add_property.php" class="btn btn-primary">Add New Property</a>
                </div>

                <div class="container-fluid py-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search properties..." />
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success">Operation completed successfully!</div>
                            <?php endif; ?>
                            
                            <?php if (isset($_GET['error'])): ?>
                                <div class="alert alert-danger">An error occurred. Please try again.</div>
                            <?php endif; ?>

                            <div class="table-responsive">
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
                                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                            <?php while ($property = mysqli_fetch_assoc($result)): ?>
                                                <?php 
                                                $is_approved = isset($property['is_approved']) ? (int)$property['is_approved'] : 0;
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($property['property_id']) ?></td>
                                                    <td><?= htmlspecialchars($property['name'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($property['location'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($property['type'] ?? '') ?></td>
                                                    <td><?= (int)($property['bedrooms'] ?? 0) ?></td>
                                                    <td><?= (int)($property['bathrooms'] ?? 0) ?></td>
                                                    <td><?= (int)($property['sqft'] ?? 0) ?></td>
                                                    <td>$<?= number_format((float)($property['price'] ?? 0), 2) ?></td>
                                                    <td>
                                                        <form action="properties.php" method="POST" class="status-form">
                                                            <input type="hidden" name="property_id" value="<?= $property['property_id'] ?>">
                                                            <select name="is_approved" class="form-select form-select-sm">
                                                                <option value="1" <?= $is_approved ? 'selected' : '' ?>>Approved</option>
                                                                <option value="0" <?= !$is_approved ? 'selected' : '' ?>>Pending</option>
                                                            </select>
                                                        </form>
                                                    </td>
                                                   <td>
  <?php if (!empty($property['billing_image'])): ?>
    <a href="../<?= htmlspecialchars($property['billing_image']) ?>" target="_blank">View Billing</a><br>
  <?php else: ?>
    <em>No billing proof uploaded.</em><br>
  <?php endif; ?>

  <?php if ($property['billing_status'] == 'Not Verified'): ?>
    <button class="btn btn-sm btn-primary" onclick="verifyBilling(<?= $property['property_id'] ?>)">Verify</button>
  <?php else: ?>
    <span class="badge bg-success">Verified</span>
  <?php endif; ?>
</td>
                                                    <td>
                                                        <a href="edit_property.php?id=<?= $property['property_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                        <form action="properties.php" method="POST" style="display:inline;">
                                                            <input type="hidden" name="property_id" value="<?= $property['property_id'] ?>">
                                                            <input type="hidden" name="delete_property" value="1">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="10" class="text-center">No properties found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Live Search Functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const search = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(search) ? '' : 'none';
            });
        });

        // Auto-submit status forms when changed
        document.querySelectorAll('.status-form select').forEach(select => {
            select.addEventListener('change', function() {
                if (confirm('Are you sure you want to change the status?')) {
                    this.form.submit();
                } else {
                    // Reset to original value
                    this.value = this.dataset.originalValue;
                }
            });
            
            // Store original value
            select.dataset.originalValue = select.value;
        });
        //Billing Verification Script 
        function verifyBilling(propertyId) {
  fetch('verify-billing.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${propertyId}`
  })
  .then(response => response.text())
  .then(result => {
    alert(result);
    location.reload();
  })
  .catch(err => console.error(err));
}
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>