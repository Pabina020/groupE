<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "property_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed."]);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);
$propertyId = isset($input['property_id']) ? $input['property_id'] : '';

if ($propertyId) {
    $stmt = $conn->prepare("DELETE FROM properties WHERE property_id = ?");
    $stmt->bind_param("i", $propertyId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Property deleted."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete property."]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid Property ID."]);
}

$conn->close();
?>
