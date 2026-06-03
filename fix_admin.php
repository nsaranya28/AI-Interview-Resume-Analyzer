<?php
require_once __DIR__ . '/db.php';
$db = getDB();

$email = 'admin@resume.com';
$password = 'adminpassword';
$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("UPDATE admins SET password = ? WHERE email = ?");
$stmt->execute([$hashed, $email]);

if ($stmt->rowCount() > 0) {
    echo "Admin password reset successfully for $email\n";
} else {
    // Maybe the admin doesn't exist?
    $chk = $db->prepare("SELECT id FROM admins WHERE email = ?");
    $chk->execute([$email]);
    if (!$chk->fetch()) {
        $stmt = $db->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['HR Manager', $email, $hashed]);
        echo "Admin account created successfully for $email\n";
    } else {
        echo "Password was already set to $password or no changes made.\n";
    }
}
