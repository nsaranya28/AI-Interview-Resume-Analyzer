<?php
// admin_login.php
// Recruiter / Admin Log In page

require_once __DIR__ . '/auth.php';

if (isAdminLoggedIn()) {
    header("Location: admin_dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        if (loginAdmin($email, $password)) {
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Invalid admin/recruiter email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruiter Portal - AI Interview & Resume Analyzer</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Recruiter portal to manage candidates, review resumes, and evaluate mock interview transcripts.">
</head>
<body>
    <div class="auth-wrapper">
        <div class="card auth-card" style="border-color: rgba(6, 182, 212, 0.2);">
            <a href="index.php" class="logo" style="justify-content: center; margin-bottom: 25px;">
                <span>Recruiter Portal</span>
            </a>
            <h2 style="text-align: center; margin-bottom: 25px; font-weight: 800; color: var(--accent);">HR Log In</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error" id="error-alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form action="admin_login.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="email">Recruiter Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="admin@resume.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" id="btn-admin-login" class="btn btn-primary" style="background: linear-gradient(135deg, var(--accent) 0%, var(--secondary) 100%); box-shadow: 0 4px 15px rgba(6, 182, 212, 0.2); width: 100%; margin-top: 10px;">Access Dashboard</button>
            </form>
            
            <div class="auth-footer">
                <a href="login.php" style="color: var(--primary); font-size: 13px;">&larr; Back to Candidate Portal</a>
            </div>
        </div>
    </div>
</body>
</html>
