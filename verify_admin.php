<?php
function get_db_safe() {
    $host = 'localhost'; $db_name = 'resume_analyzer'; $user = 'root';
    $passwords = ['', 'pass'];
    foreach ($passwords as $pass) {
        try {
            $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
            return new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (Exception $e) {}
    }
    return null;
}

$db = get_db_safe();
if (!$db) die("Connection failed\n");

$email = 'admin@resume.com';
$password = 'adminpassword';

$stmt = $db->prepare("SELECT password FROM admins WHERE email = ?");
$stmt->execute([$email]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    if (password_verify($password, $row['password'])) {
        echo "VERIFY SUCCESS: Password matches for $email\n";
    } else {
        echo "VERIFY FAILED: Password does NOT match for $email\n";
    }
} else {
    echo "ERROR: Admin user not found.\n";
}
