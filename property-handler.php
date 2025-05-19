<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = "localhost";
$user = "root";
$password = "";
$database = "rentup";

// Connect to database
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => "Connection failed: " . $conn->connect_error
    ]));
}

// Check session for landlord ID
if (!isset($_SESSION['user_id'])) {
    die(json_encode([
        'status' => 'error',
        'message' => 'User session not found. Please log in first.'
    ]));
}
$landlord_id = intval($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Required fields
    $required = ['property_id', 'property_name', 'location', 'bedrooms', 'bathrooms', 'sqft', 'type', 'price', 'property_description'];
    $errors = [];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Missing required field: $field";
        }
    }
    if (!empty($errors)) {
        die(json_encode(['status' => 'error', 'message' => implode("\n", $errors)]));
    }

    // Sanitize input
    $property_id = $conn->real_escape_string($_POST['property_id']);
    $property_name = $conn->real_escape_string($_POST['property_name']);
    $location = $conn->real_escape_string($_POST['location']);
    $bedrooms = (int)$_POST['bedrooms'];
    $bathrooms = (int)$_POST['bathrooms'];
    $sqft = (int)$_POST['sqft'];
    $property_type = $conn->real_escape_string($_POST['type']);
    $property_description = $conn->real_escape_string($_POST['property_description']);

    // Format price
   $rawPrice = trim($_POST['price']);
    $cleanPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
    $numeric_price = is_numeric($cleanPrice) ? (float)$cleanPrice : 0.00;


    // Directories
    $upload_dir = "uploads/";
    $billing_dir = "uploads/billing/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    if (!is_dir($billing_dir)) mkdir($billing_dir, 0755, true);

    // Validate main image
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024;
    if (empty($_FILES['main_image']['name'])) {
        die(json_encode(['status' => 'error', 'message' => 'Main image is required']));
    }
    if (!in_array($_FILES['main_image']['type'], $allowed_types) || $_FILES['main_image']['size'] > $max_size) {
        die(json_encode(['status' => 'error', 'message' => 'Invalid main image']));
    }
    $main_image_name = uniqid() . '_' . basename($_FILES['main_image']['name']);
    $main_image_path = $upload_dir . $main_image_name;
    move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image_path);

    // Process extra images
    $extra_images = [];
    if (!empty($_FILES['extra_images']['name'][0])) {
        foreach ($_FILES['extra_images']['tmp_name'] as $i => $tmp) {
            if (in_array($_FILES['extra_images']['type'][$i], $allowed_types) && $_FILES['extra_images']['size'][$i] <= $max_size) {
                $filename = uniqid() . '_' . basename($_FILES['extra_images']['name'][$i]);
                $path = $upload_dir . $filename;
                if (move_uploaded_file($tmp, $path)) {
                    $extra_images[] = $path;
                }
            }
        }
    }
    $extra_images_serialized = json_encode($extra_images);

    // Process billing proof
    $billing_image_path = '';
    if (!empty($_FILES['billing_proof']['name'])) {
        $billing_name = uniqid() . '_' . basename($_FILES['billing_proof']['name']);
        $billing_image_path = $billing_dir . $billing_name;
        move_uploaded_file($_FILES['billing_proof']['tmp_name'], $billing_image_path);
    }

    // Insert into database
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO properties (property_id, name, location, bedrooms, bathrooms, sqft, type, price, description, main_image, extra_images, billing_image, billing_status, landlord_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Not Verified', ?)");
        $stmt->bind_param("sssiissdssssi",
            $property_id,
            $property_name,
            $location,
            $bedrooms,
            $bathrooms,
            $sqft,
            $property_type,
            $numeric_price,
            $property_description,
            $main_image_path,
            $extra_images_serialized,
            $billing_image_path,
            $landlord_id
        );
      if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // Commit transaction if everything succeeded
        $conn->commit();

        header("Location: properties.html");
exit();
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Property uploaded successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        if (file_exists($main_image_path)) unlink($main_image_path);
        foreach ($extra_images as $img) if (file_exists($img)) unlink($img);
        if (file_exists($billing_image_path)) unlink($billing_image_path);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    $stmt->close();
    $conn->close();
} else {
    header("HTTP/1.1 405 Method Not Allowed");
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
