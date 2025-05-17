<?php
session_start();
header('Content-Type: application/json');

// Connect to DB
$conn = new mysqli("localhost", "root", "", "rentup");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$landlord_id = $_SESSION['user_id'];

$sql = "SELECT id, message, is_read, created_at FROM notifications WHERE landlord_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode($notifications);
?>
