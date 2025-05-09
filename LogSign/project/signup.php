<?php
require 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$username = trim($data["username"] ?? '');
$email    = trim($data["email"] ?? '');
$password = $data["password"] ?? '';
$role     = $data["role"] ?? '';

if (!$username || !$email || !$password || !$role) {
    echo json_encode(["message" => "All fields required"]);
    exit;
}

// Check if email exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->rowCount() > 0) {
    echo json_encode(["message" => "User already exists"]);
    exit;
}

// Hash and insert
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->execute([$username, $email, $hashedPassword, $role]);

echo json_encode(["message" => "Signup successful"]);
?>
