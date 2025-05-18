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

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => "Connection failed: " . $conn->connect_error
    ]));
}
$landlord_id = intval($_SESSION['user_id']);

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

    $errors = [];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Missing required field: $field";
        }
    }

    if (!empty($errors)) {
        die(json_encode([
            'status' => 'error',
            'message' => implode("\n", $errors)
        ]));
    }

    // Process and sanitize form data
    $property_id = $conn->real_escape_string($_POST['property_id']);
    $property_name = $conn->real_escape_string($_POST['property_name']);
    $location = $conn->real_escape_string($_POST['location']);
    $bedrooms = (int)$_POST['bedrooms'];
    $bathrooms = (int)$_POST['bathrooms'];
    $sqft = (int)$_POST['sqft'];
    $property_type = $conn->real_escape_string($_POST['type']);
    $property_description = $conn->real_escape_string($_POST['property_description']);

    // Process price - clean and format
    $rawPrice = $_POST['price'];
    $cleanPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
    
    if ($_POST['type'] === 'Rent') {
        $price = '$' . number_format($cleanPrice) . '/mo';
    } else {
        $price = '$' . number_format($cleanPrice);
    }
    
    $price = $conn->real_escape_string($price);

    // File upload handling
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            die(json_encode([
                'status' => 'error',
                'message' => 'Failed to create upload directory'
            ]));
        }
    }

    // Initialize file paths
    $main_image_path = '';
    $extra_images_serialized = json_encode([]);

    // Validate and process main image
    if (empty($_FILES['main_image']['name'])) {
        die(json_encode([
            'status' => 'error',
            'message' => 'Main image is required'
        ]));
    }

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['main_image']['type'];
    if (!in_array($file_type, $allowed_types)) {
        die(json_encode([
            'status' => 'error',
            'message' => 'Only JPG, PNG, and GIF images are allowed'
        ]));
    }

    // Validate file size (5MB max)
    $max_size = 5 * 1024 * 1024;
    if ($_FILES['main_image']['size'] > $max_size) {
        die(json_encode([
            'status' => 'error',
            'message' => 'File too large. Maximum size is 5MB'
        ]));
    }

    // Generate unique filename and move uploaded file
    $main_image_name = uniqid() . '_' . basename($_FILES['main_image']['name']);
    $main_image_path = $upload_dir . $main_image_name;
    
    if (!move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image_path)) {
        die(json_encode([
            'status' => 'error',
            'message' => 'Failed to upload main image'
        ]));
    }

    // Process extra images
    $extra_images = [];
    if (!empty($_FILES['extra_images']['name'][0])) {
        foreach ($_FILES['extra_images']['tmp_name'] as $index => $tmpName) {
            // Validate each extra image
            $current_type = $_FILES['extra_images']['type'][$index];
            if (!in_array($current_type, $allowed_types)) {
                continue;
            }

            if ($_FILES['extra_images']['size'][$index] > $max_size) {
                continue;
            }

            $filename = uniqid() . '_' . basename($_FILES['extra_images']['name'][$index]);
            $destination = $upload_dir . $filename;

            if (move_uploaded_file($tmpName, $destination)) {
                $extra_images[] = $destination;
            }
        }
        $extra_images_serialized = json_encode($extra_images);
    }

    // Process Billing Proof Upload
$billing_image_path = '';
if (!empty($_FILES['billing_proof']['name'])) {
    $billing_upload_dir = "uploads/billing/";
    if (!is_dir($billing_upload_dir)) {
        mkdir($billing_upload_dir, 0755, true);
    }
    $billing_image_name = uniqid() . '_' . basename($_FILES['billing_proof']['name']);
    $billing_image_path = $billing_upload_dir . $billing_image_name;
    move_uploaded_file($_FILES['billing_proof']['tmp_name'], $billing_image_path);
}

    // Begin database transaction
    $conn->begin_transaction();
// Get landlord ID from session
if (!isset($_SESSION['user_id'])) {
    die(json_encode([
        'status' => 'error',
        'message' => 'User session not found. Please log in first.'
    ]));
}

$landlord_id = $_SESSION['user_id'];

    try {
        // Prepare and execute SQL statement
        $stmt = $conn->prepare("INSERT INTO properties 
    (property_id, name, location, bedrooms, bathrooms, sqft, type, price, description, main_image, extra_images, landlord_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt = $conn->prepare("INSERT INTO properties 
    (property_id, name, location, bedrooms, bathrooms, sqft, type, price, description, main_image, extra_images, billing_image, billing_status, landlord_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Not Verified', ?)");

$stmt->bind_param("sssiisssssssi", 
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


    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Delete any uploaded files
        if (file_exists($main_image_path)) {
            unlink($main_image_path);
        }
        foreach ($extra_images as $img) {
            if (file_exists($img)) {
                unlink($img);
            }
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
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
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = "localhost";
$user = "root";
$password = "";
$database = "property_db";

// Connect to database
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => "Connection failed: " . $conn->connect_error
    ]));
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

    $errors = [];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Missing required field: $field";
        }
    }

    if (!empty($errors)) {
        die(json_encode([
            'status' => 'error',
            'message' => implode("\n", $errors)
        ]));
    }


    // Process and sanitize form data
    $property_id = $conn->real_escape_string($_POST['property_id']);
    $property_name = $conn->real_escape_string($_POST['property_name']);
    $location = $conn->real_escape_string($_POST['location']);
    $bedrooms = (int)$_POST['bedrooms'];
    $bathrooms = (int)$_POST['bathrooms'];
    $sqft = (int)$_POST['sqft'];
    $property_type = $conn->real_escape_string($_POST['type']);
    $property_description = $conn->real_escape_string($_POST['property_description']);

    // Process price - clean and format
    $rawPrice = $_POST['price'];
    $cleanPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
    
    if ($_POST['type'] === 'Rent') {
        $price = '$' . number_format($cleanPrice) . '/mo';
    } else {
        $price = '$' . number_format($cleanPrice);
    }
    
    $price = $conn->real_escape_string($price);

    // File upload handling
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            die(json_encode([
                'status' => 'error',
                'message' => 'Failed to create upload directory'
            ]));
        }
    }

    // Initialize file paths
    $main_image_path = '';
    $extra_images_serialized = json_encode([]);

    // Validate and process main image
    if (empty($_FILES['main_image']['name'])) {
        die(json_encode([
            'status' => 'error',
            'message' => 'Main image is required'
        ]));
    }

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['main_image']['type'];
    if (!in_array($file_type, $allowed_types)) {
        die(json_encode([
            'status' => 'error',
            'message' => 'Only JPG, PNG, and GIF images are allowed'
        ]));
    }

    // Validate file size (5MB max)
    $max_size = 5 * 1024 * 1024;
    if ($_FILES['main_image']['size'] > $max_size) {
        die(json_encode([
            'status' => 'error',
            'message' => 'File too large. Maximum size is 5MB'
        ]));
    }

    // Generate unique filename and move uploaded file
    $main_image_name = uniqid() . '_' . basename($_FILES['main_image']['name']);
    $main_image_path = $upload_dir . $main_image_name;
    
    if (!move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image_path)) {
        die(json_encode([
            'status' => 'error',
            'message' => 'Failed to upload main image'
        ]));
    }

    // Process extra images
    $extra_images = [];
    if (!empty($_FILES['extra_images']['name'][0])) {
        foreach ($_FILES['extra_images']['tmp_name'] as $index => $tmpName) {
            // Validate each extra image
            $current_type = $_FILES['extra_images']['type'][$index];
            if (!in_array($current_type, $allowed_types)) {
                continue;
            }

            if ($_FILES['extra_images']['size'][$index] > $max_size) {
                continue;
            }

            $filename = uniqid() . '_' . basename($_FILES['extra_images']['name'][$index]);
            $destination = $upload_dir . $filename;

            if (move_uploaded_file($tmpName, $destination)) {
                $extra_images[] = $destination;
            }
        }
        $extra_images_serialized = json_encode($extra_images);
    }

    // Begin database transaction
    $conn->begin_transaction();

    try {
        // Prepare and execute SQL statement
        $stmt = $conn->prepare("INSERT INTO properties 
    (property_id, name, location, bedrooms, bathrooms, sqft, type, price, description, main_image, extra_images, landlord_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sssiissssssi", 
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
    $extra_images_serialized,
    $landlord_id
);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // Commit transaction if everything succeeded
        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Property uploaded successfully!',
            'property_id' => $property_id
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Delete any uploaded files
        if (file_exists($main_image_path)) {
            unlink($main_image_path);
        }
        foreach ($extra_images as $img) {
            if (file_exists($img)) {
                unlink($img);
            }
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
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
// Process Billing Proof Upload
$billing_image_path = '';
if (!empty($_FILES['billing_proof']['name'])) {
    $billing_upload_dir = "uploads/billing/"; 

    if (!file_exists($billing_upload_dir)) {
        mkdir($billing_upload_dir, 0755, true);
    }

    $billing_image_name = uniqid() . '_' . basename($_FILES['billing_proof']['name']);
    $billing_image_path = $billing_upload_dir . $billing_image_name;

    if (!move_uploaded_file($_FILES['billing_proof']['tmp_name'], $billing_image_path)) {
        die(json_encode([
            'status' => 'error',
            'message' => 'Failed to upload billing image'
        ]));
    }
}
$stmt = $conn->prepare("INSERT INTO properties 
    (property_id, name, location, bedrooms, bathrooms, sqft, type, price, description, main_image, extra_images, billing_image, billing_status, landlord_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Not Verified', ?)");

$stmt->bind_param("sssiisssssssi", 
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
    $extra_images_serialized,
    $billing_image_path,   
    $landlord_id
);

// Handle Billing Proof Upload
$billingProofPath = '';
if (!empty($_FILES['billing_proof']['name'])) {
    $targetDir = "uploads/billing_proofs/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $billingProofPath = $targetDir . basename($_FILES['billing_proof']['name']);
    move_uploaded_file($_FILES['billing_proof']['tmp_name'], $billingProofPath);
}

// Insert into database with billing proof path
$sql = "INSERT INTO properties (property_id, property_name, location, bedrooms, bathrooms, sqft, type, price, property_description, main_image, billing_image, billing_status)
        VALUES ('$property_id', '$property_name', '$location', '$bedrooms', '$bathrooms', '$sqft', '$type', '$price', '$property_description', '$mainImagePath', '$billingProofPath', 'Not Verified')";
?>