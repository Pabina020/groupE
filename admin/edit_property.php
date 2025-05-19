<?php
$conn = new mysqli("localhost", "root", "", "rentup");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("No property ID provided.");
}

$property_id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM properties WHERE property_id = $property_id");

if ($result->num_rows === 0) {
    die("Property not found.");
}

$property = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Property</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-5">
    <h1>Property Details</h1>

    <p><strong>Name:</strong> <?= htmlspecialchars($property['name']) ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($property['location']) ?></p>
    <p><strong>Type:</strong> <?= htmlspecialchars($property['type']) ?></p>
    <p><strong>Price:</strong> $<?= htmlspecialchars($property['price']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($property['billing_status']) ?></p>

    <p><strong>Billing Proof:</strong><br>
      <?php if (!empty($property['billing_image'])): ?>
        <a href="../<?= htmlspecialchars($property['billing_image']) ?>" target="_blank">
          <img src="../<?= htmlspecialchars($property['billing_image']) ?>" style="max-width: 300px; border: 1px solid #ccc;">
        </a>
      <?php else: ?>
        <em>No billing proof uploaded.</em>
      <?php endif; ?>
    </p>

    <?php if ($property['billing_status'] !== 'Verified'): ?>
      <button class="btn btn-success" onclick="verifyBilling(<?= $property['property_id'] ?>)">Mark as Verified</button>
    <?php else: ?>
      <span class="badge bg-success">Already Verified</span>
    <?php endif; ?>

    <br><br>
    <a href="properties.php" class="btn btn-secondary">Back to Properties</a>
  </div>

  <script>
    function verifyBilling(propertyId) {
      fetch('verify-billing.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + propertyId
      })
      .then(response => response.text())
      .then(result => {
        alert(result);
        window.location.href = 'properties.php';
      })
      .catch(err => console.error(err));
    }
  </script>
</body>
</html>

<?php $conn->close(); ?>