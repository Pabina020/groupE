<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "property_db");

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$sql = "SELECT 
        property_id,
        name,
        location,
        bedrooms,
        bathrooms,
        sqft,
        type,
        price,
        description,
        main_image,
        extra_images 
    FROM properties";

$result = $conn->query($sql);

if (!$result) {
    die(json_encode(['error' => 'Query failed: ' . $conn->error]));
}

$properties = [];
while ($row = $result->fetch_assoc()) {
    // Convert JSON string to array for extra images
    $row['extra_images'] = json_decode($row['extra_images'], true);
    $properties[] = $row;
}

echo json_encode($properties);
$conn->close();
?>