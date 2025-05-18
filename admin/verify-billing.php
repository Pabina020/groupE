<?php
// Connect to database
$conn = new mysqli("localhost", "root", "", "rentup");

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if POST request has an 'id'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $property_id = intval($_POST['id']);

    // Update billing_status using correct column 'property_id'
    $sql = "UPDATE properties SET billing_status = 'Verified' WHERE property_id = $property_id";

    if ($conn->query($sql) === TRUE) {
        echo "Billing status updated to Verified";
    } else {
        echo "Error updating billing status: " . $conn->error;
    }
} else {
    echo "Invalid request";
}

// Close connection
$conn->close();
?>