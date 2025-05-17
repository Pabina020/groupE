<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$response = ['success' => false, 'message' => ''];

try {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Only POST requests are allowed", 405);
    }

    // Get JSON input
    $json = file_get_contents('php://input');
    if (empty($json)) {
        throw new Exception("No input data received", 400);
    }

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON input: " . json_last_error_msg(), 400);
    }

    // Validate input
    if (empty($data['property_id']) || !is_numeric($data['property_id'])) {
        throw new Exception("Valid property ID is required", 400);
    }
    
    if (empty($data['email']) && empty($data['phone'])) {
        throw new Exception("Either email or phone is required", 400);
    }

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'rentup');
    if ($conn->connect_error) {
        throw new Exception("Database connection failed", 500);
    }

    // Verify and cancel booking
    $propertyId = (int)$data['property_id'];
    $email = $conn->real_escape_string(strtolower(trim($data['email'] ?? '')));
    $phone = $conn->real_escape_string(preg_replace('/[^0-9+]/', '', trim($data['phone'] ?? '')));

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if booking exists
        $checkSql = "SELECT booking_id FROM bookings 
                    WHERE property_id = ? 
                    AND (LOWER(email) = ? OR phone = ?)";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("iss", $propertyId, $email, $phone);
        $stmt->execute();
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("No matching booking found with the provided details", 404);
        }
        
        $booking = $result->fetch_assoc();
        $bookingId = $booking['booking_id'];
        $stmt->close();

        // Delete booking
        $deleteSql = "DELETE FROM bookings 
                     WHERE booking_id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("i", $bookingId);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to cancel booking", 500);
        }
        $stmt->close();

        // Update property status - changed from status to is_booked
        $updateSql = "UPDATE properties SET is_booked = 0 WHERE property_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("i", $propertyId);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update property status", 500);
        }
        $stmt->close();

        $conn->commit();
        $response = [
            'success' => true,
            'message' => "Booking cancelled successfully",
            'property_id' => $propertyId,
            'booking_id' => $bookingId
        ];
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 400);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
    echo json_encode($response);
} 