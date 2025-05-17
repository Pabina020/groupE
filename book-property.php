<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'rentup'
];

// Function to validate email
function isValidEmail($email) {
    return filter_var(trim(strtolower($email)), FILTER_VALIDATE_EMAIL);
}

// Function to validate date format
function isValidDate($date) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strtotime($date);
}

try {
    // Connect to database
    $conn = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database']
    );

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Get input data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Fallback to POST if JSON parsing fails
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }

    // Required fields validation
    $required = ['name', 'email', 'phone', 'family_members', 'children', 'booking_date', 'property_id'];
    $missing = array_diff($required, array_keys(array_filter($data, function($v) { 
        return $v !== '' && $v !== null; 
    })));
    
    if (!empty($missing)) {
        throw new Exception("Missing required fields: " . implode(', ', $missing));
    }

    // Sanitize and validate input
    $name = $conn->real_escape_string(trim($data['name']));
    if (empty($name)) throw new Exception("Please enter your full name");

    $email = $conn->real_escape_string(strtolower(trim($data['email'])));
    if (!isValidEmail($email)) throw new Exception("Please enter a valid email address");

    $phone = $conn->real_escape_string(preg_replace('/[^0-9+]/', '', trim($data['phone'])));
    if (strlen($phone) < 5) throw new Exception("Please enter a valid phone number (at least 5 digits)");

    $family_members = (int)$data['family_members'];
    if ($family_members < 1) throw new Exception("At least 1 family member required");

    $children = (int)$data['children'];
    if ($children < 0) throw new Exception("Invalid number of children");

    $booking_date = $conn->real_escape_string(trim($data['booking_date']));
    if (!isValidDate($booking_date)) throw new Exception("Invalid date format (use YYYY-MM-DD)");

    $message = isset($data['message']) ? $conn->real_escape_string(trim($data['message'])) : '';
    $property_id = (int)$data['property_id'];
    if ($property_id < 1) throw new Exception("Invalid property ID");

    // Start transaction
    $conn->begin_transaction();

    try {
        // Verify property exists and is available
        $stmt = $conn->prepare("SELECT property_id, name FROM properties WHERE property_id = ? AND is_booked = 0");
        if (!$stmt) throw new Exception("Database error: " . $conn->error);

        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Get alternative available properties
            $alt_stmt = $conn->prepare("SELECT property_id, name FROM properties WHERE is_booked = 0 LIMIT 3");
            $alt_stmt->execute();
            $alt_result = $alt_stmt->get_result();
            $alternatives = $alt_result->fetch_all(MYSQLI_ASSOC);
            
            throw new Exception(json_encode([
                'error' => 'Property not available or already booked',
                'alternatives' => $alternatives
            ]));
        }

        $property = $result->fetch_assoc();

        // Create booking
        $stmt = $conn->prepare("INSERT INTO bookings (
            property_id, name, email, phone, family_members, children, booking_date, message, booking_time
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        if (!$stmt) throw new Exception("Database error: " . $conn->error);

        $stmt->bind_param("isssiiss", 
            $property_id,
            $name, 
            $email, 
            $phone, 
            $family_members, 
            $children, 
            $booking_date, 
            $message
        );

        if (!$stmt->execute()) {
            throw new Exception("Booking failed: " . $stmt->error);
        }

        // Update property status
        $update_stmt = $conn->prepare("UPDATE properties SET is_booked = 1 WHERE property_id = ?");
$landlordQuery = $conn->prepare("SELECT landlord_id, name FROM properties WHERE property_id = ?");
$landlordQuery->bind_param("i", $property_id);
$landlordQuery->execute();
$landlordResult = $landlordQuery->get_result();

if ($landlordResult->num_rows > 0) {
    $property = $landlordResult->fetch_assoc();
    $landlord_id = $property['landlord_id'];

    $notif_message = "Your property '{$property['name']}' has been booked for $booking_date.";

    $notifInsert = $conn->prepare("INSERT INTO notifications (landlord_id, message) VALUES (?, ?)");
    $notifInsert->bind_param("is", $landlord_id, $notif_message);
    $notifInsert->execute();
}



        if (!$update_stmt) throw new Exception("Database error: " . $conn->error);
        
        $update_stmt->bind_param("i", $property_id);
        $update_stmt->execute();

        // Commit transaction
        $conn->commit();

        // Success response
        echo json_encode([
            'success' => true,
            'message' => 'Booking successful!',
            'booking_id' => $conn->insert_id,
            'property_id' => $property_id,
            'property_name' => $property['name'],
            'confirmation_email' => $email,
            'booking_details' => [
                'date' => $booking_date,
                'family_members' => $family_members,
                'children' => $children
            ]
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    $errorData = json_decode($e->getMessage(), true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $errorData['error'],
            'alternatives' => $errorData['alternatives']
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} finally {
    if (isset($conn)) $conn->close();
}
?>