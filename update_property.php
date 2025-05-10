<?php
// Enable error reporting (disable in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
error_reporting(E_ALL);

// Set headers for JSON response
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "property_db";

// Initialize response
$response = ['success' => false, 'error' => ''];

try {
    // Verify request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Validate required fields
    $required_fields = ['property_id', 'name', 'location', 'bedrooms', 'bathrooms', 'sqft', 'type', 'price', 'description'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Connect to database
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Sanitize and validate input
    $property_id = (int)$_POST['property_id'];
    $name = $conn->real_escape_string(trim($_POST['name']));
    $location = $conn->real_escape_string(trim($_POST['location']));
    $bedrooms = (int)$_POST['bedrooms'];
    $bathrooms = (int)$_POST['bathrooms'];
    $sqft = (int)$_POST['sqft'];
    $type = in_array($_POST['type'], ['Sale', 'Rent']) ? $_POST['type'] : 'Rent';
    $price = (float)preg_replace('/[^0-9.]/', '', $_POST['price']);
    $description = $conn->real_escape_string(trim($_POST['description']));

    // Validate numeric values
    if ($property_id <= 0 || $bedrooms <= 0 || $bathrooms <= 0 || $sqft <= 0 || $price <= 0) {
        throw new Exception("Invalid numeric values provided");
    }

    // File upload configuration
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Process main image upload if provided
    $main_image = null;
    if (!empty($_FILES['main_image']['name']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $file_info = $_FILES['main_image'];
        
        if (!in_array($file_info['type'], $allowed_types)) {
            throw new Exception("Invalid file type for main image. Only JPG, PNG, and GIF are allowed.");
        }

        if ($file_info['size'] > $max_size) {
            throw new Exception("Main image exceeds maximum file size of 5MB");
        }

        $main_image_name = uniqid() . '_' . basename($file_info['name']);
        $main_image_path = $upload_dir . $main_image_name;

        if (!move_uploaded_file($file_info['tmp_name'], $main_image_path)) {
            throw new Exception("Failed to upload main image");
        }

        $main_image = $main_image_path;
    }

    // Process extra images
    $extra_images = [];

    // Handle existing extra images
    if (!empty($_POST['existing_extra_images'])) {
        $existing_images = json_decode($_POST['existing_extra_images'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid existing images data");
        }
        if (is_array($existing_images)) {
            $extra_images = $existing_images;
        }
    }

    // Handle new extra images
    if (!empty($_FILES['extra_images']['name'][0])) {
        foreach ($_FILES['extra_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['extra_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_info = [
                    'tmp_name' => $tmp_name,
                    'type' => $_FILES['extra_images']['type'][$key],
                    'size' => $_FILES['extra_images']['size'][$key],
                    'name' => $_FILES['extra_images']['name'][$key]
                ];

                if (!in_array($file_info['type'], $allowed_types)) continue;
                if ($file_info['size'] > $max_size) continue;

                $extra_image_name = uniqid() . '_' . basename($file_info['name']);
                $extra_image_path = $upload_dir . $extra_image_name;

                if (move_uploaded_file($file_info['tmp_name'], $extra_image_path)) {
                    $extra_images[] = $extra_image_path;
                }
            }
        }
    }

    // Prepare SQL query
    $sql = "UPDATE properties SET 
            name = ?, 
            location = ?, 
            bedrooms = ?, 
            bathrooms = ?, 
            sqft = ?, 
            type = ?, 
            price = ?, 
            description = ?";
    
    $params = [$name, $location, $bedrooms, $bathrooms, $sqft, $type, $price, $description];
    $types = "ssiissss";

    // Add main image if provided
    if ($main_image) {
        $sql .= ", main_image = ?";
        $params[] = $main_image;
        $types .= "s";
    }

    // Add extra images
    $sql .= ", extra_images = ?";
    $params[] = json_encode($extra_images);
    $types .= "s";

    // Add WHERE clause
    $sql .= " WHERE property_id = ?";
    $params[] = $property_id;
    $types .= "i";

    // Prepare and execute statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("Update failed: " . $stmt->error);
    }

    // Success response
    $response['success'] = true;
    $response['property_id'] = $property_id;

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
} finally {
    // Close database connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}

// Send JSON response
echo json_encode($response);
exit;