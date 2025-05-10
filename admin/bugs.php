<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM bug_reports ORDER BY created_at DESC LIMIT 5");
    $bugReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($bugReports);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch bug reports']);
}
?>