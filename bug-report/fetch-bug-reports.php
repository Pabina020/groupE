<?php
$conn = new mysqli("localhost", "root", "", "rentup");

$result = $conn->query("SELECT * FROM bug_reports ORDER BY created_at DESC");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
$conn->close();
?>