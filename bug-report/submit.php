<?php
session_start();
require_once '../includes/db.php';

// Create uploads directory if it doesn't exist
if (!is_dir('../uploads')) {
    mkdir('../uploads', 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data with proper sanitization
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $extra_info = trim(htmlspecialchars($_POST['extra_info'] ?? ''));
    $severity = isset($_POST['severity']) ? ucfirst(strtolower(trim($_POST['severity']))) : 'Low';

    // Validate required fields
    $errors = [];
    
    // Email validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    // Description validation
    if (empty($description)) {
        $errors[] = "Description is required";
    } elseif (strlen($description) < 10) {
        $errors[] = "Description must be at least 10 characters";
    }
    
    // Severity validation
    $allowed_severities = ['Low', 'Medium', 'High', 'Critical'];
    if (!in_array($severity, $allowed_severities)) {
        $errors[] = "Please select a valid severity level";
    }

    // Handle file upload
    $screenshot_path = null;
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['screenshot'];
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        // Get actual file type (not just relying on client-provided type)
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $file['tmp_name']);
        finfo_close($file_info);
        
        if (!in_array($mime_type, $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF files are allowed";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "File size must be less than 2MB";
        } else {
            // Generate unique filename with original extension
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'bug_' . uniqid() . '.' . strtolower($ext);
            $target_path = '../uploads/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $screenshot_path = 'uploads/' . $filename;
            } else {
                $errors[] = "Failed to upload file. Please try again.";
            }
        }
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO bug_reports 
                                  (email, description, extra_info, severity, screenshot_path, created_at) 
                                  VALUES (:email, :description, :extra_info, :severity, :screenshot_path, NOW())");
            
            $stmt->execute([
                ':email' => $email,
                ':description' => $description,
                ':extra_info' => $extra_info,
                ':severity' => $severity,
                ':screenshot_path' => $screenshot_path
            ]);

            // Success - redirect to thank you page
            $_SESSION['success_message'] = "Bug report submitted successfully!";
            header('Location: sucess.html');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Error submitting bug report: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bug Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .required-field::after {
            content: " *";
            color: red;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">Bug Report</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h5 class="alert-heading">Please fix these errors:</h5>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label required-field">Your email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required
                                       placeholder="Enter your email address">
                                <div class="invalid-feedback">
                                    Please provide a valid email address.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label required-field">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required
                                          placeholder="Describe the bug in detail"><?= 
                                    htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                <div class="invalid-feedback">
                                    Please provide a detailed description (at least 10 characters).
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="extra_info" class="form-label">Any other extra information</label>
                                <textarea class="form-control" id="extra_info" name="extra_info" rows="2"
                                          placeholder="Additional context or information"><?= 
                                    htmlspecialchars($_POST['extra_info'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label required-field">Severity of the issue</label>
                                <div class="btn-group-vertical w-100" role="group" aria-label="Severity levels">
                                    <?php 
                                    $severities = [
                                        'Low' => 'Minor issue with low impact',
                                        'Medium' => 'Noticeable issue affecting some functionality',
                                        'High' => 'Major issue affecting core functionality',
                                        'Critical' => 'System crash or data loss issue'
                                    ];
                                    $current_severity = $_POST['severity'] ?? 'Low';
                                    foreach ($severities as $value => $label): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="severity" 
                                                   id="severity-<?= strtolower($value) ?>" 
                                                   value="<?= $value ?>"
                                                   <?= $current_severity === $value ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="severity-<?= strtolower($value) ?>">
                                                <strong><?= $value ?></strong>: <?= $label ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="screenshot" class="form-label">Screenshot of the issue</label>
                                <input class="form-control" type="file" id="screenshot" name="screenshot" accept="image/*">
                                <div class="form-text">
                                    Upload a JPG, PNG, or GIF file (max 2MB) to help us understand the issue.
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-bug-fill"></i> Submit Bug Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side form validation
        (function() {
            'use strict';
            
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
            
            // Validate file size client-side
            const fileInput = document.getElementById('screenshot');
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    if (this.files[0] && this.files[0].size > maxSize) {
                        this.setCustomValidity('File must be less than 2MB');
                        this.reportValidity();
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        })();
    </script>
</body>
</html>