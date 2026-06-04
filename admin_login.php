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

$pageTitle = 'Recruiter Portal - AI Resume Analyzer';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="card auth-card" style="border-color: rgba(255, 255, 255, 0.15); box-shadow: var(--shadow-main), 0 0 50px rgba(255, 255, 255, 0.02);">
        <h2 style="text-align: center; margin-bottom: 25px; font-weight: 800; background: linear-gradient(135deg, #ffffff 0%, #888888 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Recruiter Login
        </h2>
        <p style="text-align: center; font-size: 14.5px; color: var(--text-muted); margin-bottom: 30px;">
            Access the HR portal to inspect applicant metrics, check ATS scores, and update candidate stages.
        </p>
        
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
            
            <button type="submit" id="btn-admin-login" class="btn btn-primary" style="background: #ffffff; color: #000000; box-shadow: 0 8px 24px rgba(255, 255, 255, 0.1); width: 100%; margin-top: 15px; padding: 14px;">
                Access Dashboard
            </button>
        </form>
        
        <div class="auth-footer" style="margin-top: 30px; border-top: 1px solid var(--border-color); padding-top: 20px;">
            <a href="login.php" style="color: var(--primary); font-size: 13.5px;">&larr; Back to Candidate Portal</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
