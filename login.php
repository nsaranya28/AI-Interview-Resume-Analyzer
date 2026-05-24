<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
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
        $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            header('Location: dashboard.php');
            exit();
        } else {
            $errors[] = 'Incorrect email or password.';
        }
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="container mt-5">
    <h2 class="mb-4">Login</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post" action="login.php" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p class="mt-3">Don't have an account? <a href="register.php">Register here</a>.</p>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
