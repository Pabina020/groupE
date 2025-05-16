<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "property_db");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$landlord_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, message, created_at FROM notifications WHERE landlord_id = ? AND is_read = 0 ORDER BY created_at DESC");
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode($notifications);
$conn->close();
?>
