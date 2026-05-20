<?php
// login.php
// Candidate Log In page

require_once __DIR__ . '/auth.php';

// Handle logout request
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    $wasAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
    logout();
    if ($wasAdmin) {
        header("Location: admin_login.php");
    } else {
        header("Location: login.php");
    }
    exit;
}

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        if (loginUser($email, $password)) {
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - AI Interview & Resume Analyzer</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Log in to your candidate account to access your resume reports and mock interviews.">
</head>
<body>
    <div class="auth-wrapper">
        <div class="card auth-card">
            <a href="index.php" class="logo" style="justify-content: center; margin-bottom: 25px;">
                <span>AI Resume Analyzer</span>
            </a>
            <h2 style="text-align: center; margin-bottom: 25px; font-weight: 800;">Candidate Log In</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error" id="error-alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="name@domain.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" id="btn-login" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Log In</button>
            </form>
            
            <div class="auth-footer">
                Don't have an account? <a href="register.php" id="link-register">Sign Up</a>
                <br><br>
                <a href="admin_login.php" style="color: var(--accent); font-size: 13px;">Recruiter / HR Login Portal &rarr;</a>
            </div>
        </div>
    </div>
</body>
</html>
