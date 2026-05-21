<?php
// register.php
// Candidate Sign Up page

require_once __DIR__ . '/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        if (registerUser($name, $email, $password)) {
            $success = "Registration successful! You can now log in.";
        } else {
            $error = "Email already registered.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - AI Interview & Resume Analyzer</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Register a candidate account to analyze your resume and study role-based technical preparation questions.">
</head>
<body>
    <div class="auth-wrapper">
        <div class="card auth-card">
            <a href="index.php" class="logo" style="justify-content: center; margin-bottom: 25px;">
                <span>AI Resume Analyzer</span>
            </a>
            <h2 style="text-align: center; margin-bottom: 25px; font-weight: 800;">Candidate Sign Up</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error" id="error-alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success" id="success-alert"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="name@domain.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat password" required>
                </div>
                <button type="submit" id="btn-register" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Create Account</button>
            </form>
            
            <div class="auth-footer">
                Already have an account? <a href="login.php" id="link-login">Log In</a>
            </div>
        </div>
    </div>
</body>
</html>
