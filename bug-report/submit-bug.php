<?php
$targetDir = "uploads/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$targetFile = $targetDir . basename($_FILES["bug_image"]["name"]);
$imagePath = "";

if (isset($_FILES["bug_image"]) && $_FILES["bug_image"]["size"] > 0) {
    move_uploaded_file($_FILES["bug_image"]["tmp_name"], $targetFile);
    $imagePath = $targetFile;
}

$conn = new mysqli("localhost", "root", "", "rentup");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $conn->real_escape_string($_POST['email']);
$description = $conn->real_escape_string($_POST['description']);
$severity = $conn->real_escape_string($_POST['severity']);

$sql = "INSERT INTO bug_reports (email, description, severity, image_path) 
        VALUES ('$email', '$description', '$severity', '$imagePath')";

if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Bug Report Submitted Successfully'); window.location.href='../index.html';</script>";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
?>