<?php
require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/db.php';

$userId = getCurrentUserId();
$db = getDB();

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';
    
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $linkedin = trim($_POST['linkedin_url'] ?? '');
        $github = trim($_POST['github_url'] ?? '');
        $portfolio = trim($_POST['portfolio_url'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $country = trim($_POST['country'] ?? '');
        
        if (empty($name)) {
            $error = 'Name is required.';
        } else {
            $stmt = $db->prepare("UPDATE users SET name=?, phone=?, bio=?, linkedin_url=?, github_url=?, portfolio_url=?, city=?, country=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$name, $phone, $bio, $linkedin, $github, $portfolio, $city, $country, $userId]);
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully!';
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        $stmt = $db->prepare("SELECT password FROM users WHERE id=?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        
        if (!password_verify($current, $row['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->execute([$hash, $userId]);
            $success = 'Password changed successfully!';
        }
    }
}

// Fetch user
$stmt = $db->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Stats
$resumeCount = $db->prepare("SELECT COUNT(*) FROM resumes WHERE user_id=?");
$resumeCount->execute([$userId]);
$totalResumes = $resumeCount->fetchColumn();

$builderCount = $db->prepare("SELECT COUNT(*) FROM resume_profiles WHERE user_id=?");
$builderCount->execute([$userId]);
$totalBuilt = $builderCount->fetchColumn();

$avgScore = $db->prepare("SELECT ROUND(AVG(ats_score),0) FROM resumes WHERE user_id=? AND ats_score IS NOT NULL");
$avgScore->execute([$userId]);
$avgAts = $avgScore->fetchColumn() ?: 0;

$pageTitle = 'My Profile - AI Resume Analyzer';
include __DIR__ . '/includes/header.php';
?>
<main class="container" style="padding-top:40px;padding-bottom:60px;">
    <div class="page-header" style="margin-bottom:32px;">
        <h1 style="font-size:32px;margin-bottom:6px;">My Profile</h1>
        <p style="color:var(--text-muted);">Manage your account information and security settings.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:300px 1fr;gap:30px;align-items:start;">
        <!-- Left: Profile Card -->
        <div>
            <div class="card" style="text-align:center;">
                <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:36px;font-weight:900;color:#fff;">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <h3 style="font-size:20px;margin-bottom:4px;"><?= htmlspecialchars($user['name']) ?></h3>
                <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;"><?= htmlspecialchars($user['email']) ?></p>
                <?php $userCity = $user['city'] ?? ''; $userCountry = $user['country'] ?? ''; ?>
                <?php if ($userCity || $userCountry): ?>
                    <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">📍 <?= htmlspecialchars(implode(', ', array_filter([$userCity, $userCountry]))) ?></p>
                <?php endif; ?>
                <div style="border-top:1px solid var(--border-color);padding-top:16px;margin-top:8px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;text-align:center;">
                    <div>
                        <div style="font-size:22px;font-weight:900;color:var(--text-dark);"><?= $totalResumes ?></div>
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Analyzed</div>
                    </div>
                    <div>
                        <div style="font-size:22px;font-weight:900;color:var(--text-dark);"><?= $totalBuilt ?></div>
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Built</div>
                    </div>
                    <div>
                        <div style="font-size:22px;font-weight:900;color:<?= $avgAts >= 80 ? 'var(--success)' : ($avgAts >= 60 ? 'var(--warning)' : 'var(--error)') ?>;"><?= $avgAts ?>%</div>
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Avg ATS</div>
                    </div>
                </div>
                <div style="margin-top:20px;border-top:1px solid var(--border-color);padding-top:16px;">
                    <p style="font-size:12px;color:var(--text-muted);">Member since <?= date('M Y', strtotime($user['created_at'])) ?></p>
                </div>
            </div>
            
            <!-- Quick Nav -->
            <div class="card" style="margin-top:20px;padding:16px;">
                <h4 style="font-size:14px;font-weight:700;margin-bottom:12px;">Quick Links</h4>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <a href="dashboard.php" class="btn btn-secondary btn-sm" style="justify-content:flex-start;gap:8px;">📊 Resume Analyzer</a>
                    <a href="resume_builder.php" class="btn btn-secondary btn-sm" style="justify-content:flex-start;gap:8px;">✏️ Resume Builder</a>
                    <a href="mock_interview.php" class="btn btn-secondary btn-sm" style="justify-content:flex-start;gap:8px;">🎤 Mock Interview</a>
                    <a href="logout.php" class="btn btn-danger btn-sm" style="justify-content:flex-start;gap:8px;">🚪 Logout</a>
                </div>
            </div>
        </div>

        <!-- Right: Forms -->
        <div style="display:flex;flex-direction:column;gap:24px;">
            <!-- Profile Info -->
            <div class="card">
                <h2 class="card-title" style="margin-bottom:24px;">Personal Information</h2>
                <form method="POST" action="profile.php">
                    <input type="hidden" name="action" value="update_profile">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 99999 99999" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" placeholder="Chennai" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" placeholder="India" value="<?= htmlspecialchars($user['country'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">LinkedIn URL</label>
                            <input type="url" name="linkedin_url" class="form-control" placeholder="https://linkedin.com/in/..." value="<?= htmlspecialchars($user['linkedin_url'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">GitHub URL</label>
                            <input type="url" name="github_url" class="form-control" placeholder="https://github.com/..." value="<?= htmlspecialchars($user['github_url'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="grid-column:1/-1;margin-bottom:0;">
                            <label class="form-label">Portfolio URL</label>
                            <input type="url" name="portfolio_url" class="form-control" placeholder="https://yourportfolio.com" value="<?= htmlspecialchars($user['portfolio_url'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="grid-column:1/-1;margin-bottom:0;">
                            <label class="form-label">Bio / About Me</label>
                            <textarea name="bio" class="form-control" rows="3" placeholder="A brief professional summary..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:24px;">💾 Save Profile</button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card">
                <h2 class="card-title" style="margin-bottom:24px;">🔒 Change Password</h2>
                <form method="POST" action="profile.php">
                    <input type="hidden" name="action" value="change_password">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                        <div class="form-group" style="grid-column:1/-1;margin-bottom:0;">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:24px;">🔑 Update Password</button>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
@media (max-width: 768px) {
    .container > div { grid-template-columns: 1fr !important; }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
