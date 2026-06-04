<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];

// Handle logout query parameter
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    logout();
    header("Location: login.php");
    exit;
}

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
} elseif (isAdminLoggedIn()) {
    header('Location: admin_dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        if (loginUser($email, $password)) {
            header('Location: dashboard.php');
            exit();
        } else {
            if ($email === 'admin@resume.com') {
                $errors[] = 'This is an Admin account. Please use the <a href="admin_login.php" style="font-weight:bold;color:inherit;text-decoration:underline;">Admin Portal</a> to log in.';
            } else {
                $errors[] = 'Incorrect email or password.';
            }
        }
    }
}

$pageTitle = 'Candidate Login - AI Resume Analyzer';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="card auth-card">
        <h2 style="text-align: center; margin-bottom: 25px; font-weight: 800; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Candidate Login
        </h2>
        <p style="text-align: center; font-size: 14.5px; color: var(--text-muted); margin-bottom: 30px;">
            Access your AI-powered resume analyzer and interview prep dashboard.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px; font-size: 13.5px;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= $e ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php" class="needs-validation" novalidate>
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="name@domain.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px; padding: 14px;">
                Login to Dashboard
            </button>
        </form>
        
        <div class="auth-footer" style="margin-top: 30px; border-top: 1px solid var(--border-color); padding-top: 20px;">
            <p style="color: var(--text-muted); margin: 0; font-size: 13.5px;">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
