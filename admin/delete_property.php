<?php
// Database connection settings
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'property_db';

$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = $_POST['property_id'] ?? null;
    if ($property_id) {
        // Prepare and execute DELETE query
        $stmt = $conn->prepare("DELETE FROM properties WHERE property_id = ?");
        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to properties.php after deletion
header("Location: properties.php");
exit();
?>
