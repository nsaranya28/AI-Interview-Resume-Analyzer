<?php
function get_db_safe() {
    $host = 'localhost';
    $db_name = 'resume_analyzer';
    $user = 'root';
    $passwords = ['', 'pass'];
    
    foreach ($passwords as $pass) {
        try {
            $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (Exception $e) {}
    }
    return null;
}

$db = get_db_safe();
if (!$db) die("Connection failed\n");

$email = 'admin@resume.com';
$password = 'adminpassword';
$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("UPDATE admins SET password = ? WHERE email = ?");
$stmt->execute([$hashed, $email]);

if ($stmt->rowCount() > 0) {
    echo "Admin password reset successfully for $email\n";
} else {
    $chk = $db->prepare("SELECT id FROM admins WHERE email = ?");
    $chk->execute([$email]);
    if (!$chk->fetch()) {
        $stmt = $db->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['HR Manager', $email, $hashed]);
        echo "Admin account created successfully for $email\n";
    } else {
        echo "Admin exists, but password was already correct or no changes made.\n";
    }
}
