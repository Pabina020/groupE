<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "property_db");

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'message' => $conn->connect_error
    ]));
}

// Optional: check if specific property_id or landlord_id is requested
$propertyId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$landlordId = isset($_GET['landlord_id']) ? intval($_GET['landlord_id']) : 0;

try {
    if ($propertyId > 0) {
        // Get one specific property
        $stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ?");
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param("i", $propertyId);
        if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $property = $result->fetch_assoc();
            if (isset($property['extra_images'])) {
                $property['extra_images'] = json_decode($property['extra_images'], true) ?: [];
            }
            echo json_encode($property);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Property not found',
                'message' => 'No property found with ID: ' . $propertyId
            ]);
        }

    } elseif ($landlordId > 0) {
        // Optional enhancement: fetch properties by landlord
        $stmt = $conn->prepare("SELECT * FROM properties WHERE landlord_id = ?");
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param("i", $landlordId);
        if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);

        $result = $stmt->get_result();
        $properties = [];

        while ($row = $result->fetch_assoc()) {
            if (isset($row['extra_images'])) {
                $row['extra_images'] = json_decode($row['extra_images'], true) ?: [];
            }
            $properties[] = $row;
        }
        echo json_encode($properties);

    } else {
        // Get all properties
        $result = $conn->query("SELECT * FROM properties");
        if (!$result) throw new Exception("Query failed: " . $conn->error);

        $properties = [];
        while ($row = $result->fetch_assoc()) {
            if (isset($row['extra_images'])) {
                $row['extra_images'] = json_decode($row['extra_images'], true) ?: [];
            }
            $properties[] = $row;
        }
        echo json_encode($properties);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
