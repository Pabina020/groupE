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
                    p.billing_image,
                    p.billing_status,
                    p.is_booked,
                    (SELECT COUNT(*) FROM bookings WHERE property_id = p.property_id) AS booking_count
                FROM properties p 
                WHERE p.property_id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode(['message' => 'Property not found']);
        }
    } else {
        // Fetch all properties
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
                    p.billing_image,
                    p.billing_status,
                    p.is_booked,
                    (SELECT COUNT(*) FROM bookings WHERE property_id = p.property_id) AS booking_count
                FROM properties p 
                ORDER BY p.property_id DESC";
        
        $result = $conn->query($sql);
        $properties = [];

        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }

        echo json_encode($properties);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
