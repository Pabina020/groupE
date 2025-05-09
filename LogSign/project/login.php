<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging (optional in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$data = json_decode(file_get_contents("php://input"), true);

$email    = trim($data["email"] ?? '');
$password = $data["password"] ?? '';

if (!$email || !$password) {
    echo json_encode(["message" => "Email and password required"]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password"])) {
    echo json_encode(["message" => "Invalid email or password"]);
    exit;
}

// Store user info in session
$_SESSION["user"] = [
    "username" => $user["username"],
    "email" => $user["email"],
    "role" => $user["role"]
];

// ✅ Return username in response so JS can store it in cookie
echo json_encode([
    "message" => "Login successful",
    "role" => $user["role"],
    "username" => $user["username"]
]);
?>
