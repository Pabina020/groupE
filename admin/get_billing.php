<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "rentup");

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// SQL query
$sql = "SELECT property_id, name, price, service_charge, deposit, duration, status, is_booked, created_at, billing_proof FROM properties";
$result = $conn->query($sql);

// Prepare response
$billingData = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['price'] = (float) $row['price'];
        $row['service_charge'] = (float) $row['service_charge'];
        $row['deposit'] = (float) $row['deposit'];
        $row['total_estimated'] = $row['price'] + $row['service_charge'] + $row['deposit'];
        $billingData[] = $row;
    }
    echo json_encode($billingData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . $conn->error]);
}
?>
