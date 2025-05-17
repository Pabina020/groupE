<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");

$conn = new mysqli("localhost", "root", "", "rentup");

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

try {
    // Check if requesting a single property
    if (isset($_GET['id'])) {
        $property_id = (int)$_GET['id'];
        
        $sql = "SELECT p.*, 
                (SELECT COUNT(*) FROM bookings WHERE property_id = p.property_id) AS booking_count
                FROM properties p 
                WHERE p.property_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['error' => 'Property not found']);
            exit;
        }
        
        $property = $result->fetch_assoc();
        $property['extra_images'] = json_decode($property['extra_images'], true) ?: [];
        
        // Correctly determine if property is booked
        $property['is_booked'] = ($property['is_booked'] == 1 || $property['booking_count'] > 0) ? 1 : 0;
        
        echo json_encode($property);
        exit;
    }

    // Default behavior - return all properties with correct booking status
    $sql = "SELECT 
            p.property_id,
            p.name,
            p.location,
            p.bedrooms,
            p.bathrooms,
            p.sqft,
            p.type,
            p.price,
            p.description,
            p.main_image,
            p.extra_images,
            p.is_booked,
            (SELECT COUNT(*) FROM bookings WHERE property_id = p.property_id) AS booking_count
        FROM properties p
        ORDER BY p.property_id DESC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }

    $properties = [];
    while ($row = $result->fetch_assoc()) {
        $row['extra_images'] = json_decode($row['extra_images'], true) ?: [];
        // Correctly determine if property is booked
        $row['is_booked'] = ($row['is_booked'] == 1 || $row['booking_count'] > 0) ? 1 : 0;
        $properties[] = $row;
    }

    echo json_encode($properties);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn->close();
}