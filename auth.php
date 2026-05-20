<?php
// auth.php
// User & Admin session helper

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function registerUser($name, $email, $password) {
    $db = getDB();
    $hashedPass = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPass]);
        return true;
    } catch (PDOException $e) {
        // Typically duplicate email
        return false;
    }
}

function loginUser($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = 'candidate';
        return true;
    }
    return false;
}

function loginAdmin($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, password FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['role'] = 'admin';
        return true;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'candidate';
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && $_SESSION['role'] === 'admin';
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserName() {
    return $_SESSION['user_name'] ?? '';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: admin_login.php");
        exit;
    }
}

function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
