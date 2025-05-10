<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bug ID is required']);
    exit;
}

$bugId = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM bug_reports WHERE id = ?");
    $stmt->execute([$bugId]);
    $bug = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bug) {
        http_response_code(404);
        echo json_encode(['error' => 'Bug report not found']);
        exit;
    }
    
    echo json_encode($bug);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch bug details']);
}
?>