<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database config
$host = "localhost";
$user = "root";
$password = "";
$database = "property_db";

// Connect to DB
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required = [
        'property_id', 
        'property_name',
        'location',
        'bedrooms',
        'bathrooms',
        'sqft',
        'type',
        'price',
        'property_description'
    ];

    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("Error: Missing required field - $field");
        }
    }

    // Collect ALL form data
    $property_id = $_POST['property_id'];
    $property_name = $_POST['property_name'];
    $location = $_POST['location'];
    $bedrooms = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $sqft = $_POST['sqft'];
    $property_type = $_POST['type'];
    $price = $_POST['price'];
    $property_description = $_POST['property_description'];

    // File upload handling
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Main image handling
    if (empty($_FILES['main_image']['name'])) {
        die("Error: Main image is required");
    }

    $main_image_name = uniqid() . '_' . basename($_FILES['main_image']['name']);
    $main_image_path = $upload_dir . $main_image_name;

    if (!move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image_path)) {
        die("Failed to upload main image");
    }

    // Extra images handling
    $extra_images = [];
    if (!empty($_FILES['extra_images']['name'][0])) {
        foreach ($_FILES['extra_images']['tmp_name'] as $index => $tmpName) {
            $filename = uniqid() . '_' . basename($_FILES['extra_images']['name'][$index]);
            $destination = $upload_dir . $filename;

            if (move_uploaded_file($tmpName, $destination)) {
                $extra_images[] = $destination;
            }
        }
    }

    $extra_images_serialized = json_encode($extra_images);

    // Modified SQL query
    $stmt = $conn->prepare("INSERT INTO properties 
        (property_id, name, location, bedrooms, bathrooms, sqft, type, price, description, main_image, extra_images) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssiiissdss", 
        $property_id, 
        $property_name, 
        $location,
        $bedrooms,
        $bathrooms,
        $sqft,
        $property_type,
        $price,
        $property_description,
        $main_image_path,
        $extra_images_serialized
    );

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Property uploaded successfully!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    header("HTTP/1.1 405 Method Not Allowed");
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>
