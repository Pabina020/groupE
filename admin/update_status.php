<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'rentup';

$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = $_POST['property_id'] ?? null;
    $is_approved = isset($_POST['is_approved']) ? intval($_POST['is_approved']) : 0;

    if ($property_id !== null) {
        $stmt = $conn->prepare("UPDATE properties SET is_approved = ? WHERE property_id = ?");
        $stmt->bind_param("ii", $is_approved, $property_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to the properties page after update
header('Location: properties.php');
exit;
?>
