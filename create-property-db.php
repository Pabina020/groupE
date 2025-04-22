<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "property_db";

// Connect to MySQL
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create table with all necessary fields
$sql = "CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    bedrooms INT NOT NULL,
    bathrooms INT NOT NULL,
    sqft INT NOT NULL,
    type ENUM('Rent', 'Sale') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,
    main_image VARCHAR(255) NOT NULL,
    extra_images TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Execute query
if ($conn->query($sql) === TRUE) {
    echo "Table 'properties' created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>