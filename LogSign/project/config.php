<?php
$conn = mysqli_connect("localhost", "root", "", "opt_verification");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!function_exists('sanitizeInput')) {
    function sanitizeInput($conn, $data) {
        return mysqli_real_escape_string($conn, trim($data));
    }
}
?>