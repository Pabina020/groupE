<?php
$conn = new mysqli("localhost", "root", "", "rentup");

if ($conn->connect_error) {
    die("Database connection failed");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE bug_reports SET status='$status' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "Status updated successfully";
    } else {
        echo "Error updating status: " . $conn->error;
    }
} else {
    echo "Invalid request";
}

$conn->close();
?>