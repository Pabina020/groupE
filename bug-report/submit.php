<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $description = $_POST['description'];
    $steps = $_POST['steps'];
    $severity = $_POST['severity'];
    
    // Handle file upload
    $screenshot_path = null;
    if (isset($_FILES['screenshot'])) {
        $file = $_FILES['screenshot'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('bug_') . '.' . $file_ext;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $screenshot_path = 'uploads/' . $file_name;
            }
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO bug_reports (email, description, steps, severity, screenshot_path) 
                              VALUES (:email, :description, :steps, :severity, :screenshot_path)");
        $stmt->execute([
            ':email' => $email,
            ':description' => $description,
            ':steps' => $steps,
            ':severity' => $severity,
            ':screenshot_path' => $screenshot_path
        ]);
        
        header('Location: sucess.html');
        exit();
    } catch (PDOException $e) {
        die("Error submitting bug report: " . $e->getMessage());
    }
}
?>