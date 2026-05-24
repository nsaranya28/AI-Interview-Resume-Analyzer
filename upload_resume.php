<?php
// upload_resume.php – Handles resume upload, validates, stores file, inserts DB record
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Unauthenticated');
}
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['resume'])) {
    header('Location: dashboard.php');
    exit();
}

$file = $_FILES['resume'];
$originalName = $file['name'];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

// Validation
if (!in_array($ext, ALLOWED_EXT)) {
    $_SESSION['upload_error'] = 'Invalid file type. Allowed: pdf, doc, docx';
    header('Location: dashboard.php');
    exit();
}
if ($file['size'] > MAX_FILE_SIZE) {
    $_SESSION['upload_error'] = 'File too large (max 5 MB).';
    header('Location: dashboard.php');
    exit();
}

// Ensure uploads folder exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

$uniqueName = uniqid('resume_', true) . '.' . $ext;
$destPath = UPLOAD_DIR . DIRECTORY_SEPARATOR . $uniqueName;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    $_SESSION['upload_error'] = 'Failed to move uploaded file.';
    header('Location: dashboard.php');
    exit();
}

// Insert record
$stmt = $pdo->prepare('INSERT INTO resumes (user_id, resume_name, file_path) VALUES (?, ?, ?)');
$stmt->execute([$_SESSION['user_id'], $originalName, $destPath]);

$_SESSION['upload_success'] = 'Resume uploaded successfully.';
header('Location: dashboard.php');
exit();
?>
