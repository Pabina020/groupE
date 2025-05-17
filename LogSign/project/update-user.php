<?php
session_start();
require 'config.php'; // your DB config

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user']['id'] ?? null;

if (!$user_id) {
  echo json_encode(["success" => false, "message" => "Not authenticated"]);
  exit;
}

$username = trim($data['username']);
$role = $data['role'];
$password = trim($data['password']);

// Validate input...
if (!$username || !$role) {
  echo json_encode(["success" => false, "message" => "Missing fields"]);
  exit;
}

if ($password) {
  $hashed = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?");
  $stmt->bind_param("sssi", $username, $role, $hashed, $user_id);
} else {
  $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
  $stmt->bind_param("ssi", $username, $role, $user_id);
}

if ($stmt->execute()) {
  $_SESSION['user']['username'] = $username;
  $_SESSION['user']['role'] = $role;
  echo json_encode(["success" => true]);
} else {
  echo json_encode(["success" => false, "message" => "Update failed"]);
}
?>
