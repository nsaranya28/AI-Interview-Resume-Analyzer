<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($name)) $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered.';
        }
    }

    // Insert user
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);
        $userId = $pdo->lastInsertId();
        
        // Log the user in with correct session variables compatible with auth.php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['role'] = 'candidate';
        
        header('Location: dashboard.php');
        exit();
    }
}

$pageTitle = 'Create Account - AI Resume Analyzer';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="card auth-card">
        <h2 style="text-align: center; margin-bottom: 25px; font-weight: 800; background: linear-gradient(135deg, #ffffff 0%, #888888 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Candidate Register
        </h2>
        <p style="text-align: center; font-size: 14.5px; color: var(--text-muted); margin-bottom: 30px;">
            Join to analyze your resume and practice custom AI interviews.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px; font-size: 13.5px;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php" class="needs-validation" novalidate>
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. N Saranya" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="name@domain.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Must be at least 6 characters" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px; padding: 14px;">
                Create Account
            </button>
        </form>
        
        <div class="auth-footer" style="margin-top: 30px; border-top: 1px solid var(--border-color); padding-top: 20px;">
            <p style="color: var(--text-muted); margin: 0; font-size: 13.5px;">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
