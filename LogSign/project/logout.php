<?php
session_start();

// Destroy the session
session_unset();
session_destroy();

// Clear client-side cookie if you're using it
setcookie('user', '', time() - 3600, '/');

// Respond with JSON
header('Content-Type: application/json');
echo json_encode(['message' => 'Logged out']);
?>
