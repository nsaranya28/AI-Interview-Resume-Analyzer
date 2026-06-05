<?php
// portfolio_generator.php - AI Portfolio Website Generator
require_once __DIR__ . '/auth.php';
requireLogin();

require_once __DIR__ . '/db.php';

$userId = getCurrentUserId();
$db = getDB();

$error = '';
$success = '';
$slug = '';
$theme = 'modern';
$bio = '';
$email = '';
$github = '';
$linkedin = '';

// Load active resume profile
$stmtRes = $db->prepare("SELECT id, summary, full_name, job_title, email, github, linkedin FROM resume_profiles WHERE user_id=? ORDER BY updated_at DESC LIMIT 1");
$stmtRes->execute([$userId]);
$profile = $stmtRes->fetch();

// Load existing portfolio config
$stmtPort = $db->prepare("SELECT * FROM portfolios WHERE user_id = ?");
$stmtPort->execute([$userId]);
$portfolio = $stmtPort->fetch();

if ($portfolio) {
    $slug = $portfolio['slug'];
    $theme = $portfolio['theme'];
    $bio = $portfolio['about_bio'];
    $email = $portfolio['contact_email'];
    $socials = json_decode($portfolio['social_links'], true) ?: [];
    $github = $socials['github'] ?? '';
    $linkedin = $socials['linkedin'] ?? '';
} else {
    // defaults from profile
    if ($profile) {
        $bio = $profile['summary'];
        $email = $profile['email'];
        $github = $profile['github'];
        $linkedin = $profile['linkedin'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = trim($_POST['slug'] ?? '');
    $theme = trim($_POST['theme'] ?? 'modern');
    $bio = trim($_POST['bio'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $github = trim($_POST['github'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    
    // clean slug
    $slug = preg_replace("/[^a-zA-Z0-9-]/", "", strtolower($slug));
    
    if (empty($slug)) {
        $error = 'Slug is required to generate portfolio URL.';
    } else {
        // Verify unique slug
        $stmtChk = $db->prepare("SELECT id FROM portfolios WHERE slug = ? AND user_id != ?");
        $stmtChk->execute([$slug, $userId]);
        if ($stmtChk->fetch()) {
            $error = 'This URL slug is already taken. Please try another one.';
        } else {
            $socialLinks = json_encode(['github' => $github, 'linkedin' => $linkedin]);
            $resumeId = $profile ? $profile['id'] : null;
            
            if ($portfolio) {
                // Update
                $stmtUpd = $db->prepare("UPDATE portfolios SET slug = ?, theme = ?, about_bio = ?, social_links = ?, contact_email = ?, resume_id = ? WHERE id = ?");
                $stmtUpd->execute([$slug, $theme, $bio, $socialLinks, $email, $resumeId, $portfolio['id']]);
            } else {
                // Insert
                $stmtIns = $db->prepare("INSERT INTO portfolios (user_id, resume_id, slug, theme, about_bio, social_links, contact_email) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtIns->execute([$userId, $resumeId, $slug, $theme, $bio, $socialLinks, $email]);
            }
            $success = 'Portfolio settings saved successfully!';
            
            // Re-fetch portfolio
            $stmtPort->execute([$userId]);
            $portfolio = $stmtPort->fetch();
        }
    }
}

$pageTitle = 'AI Personal Portfolio Generator - AI Career Companion';
include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top: 40px; padding-bottom: 60px;">
    <div style="margin-bottom:30px;">
        <h1 style="font-size:32px; font-weight:800; background:linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">AI Portfolio Website Generator</h1>
        <p class="text-muted">Launch your personal portfolio site instantly from your resume sections. Choose a styling theme, customize links, and share.</p>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="grid grid-2" style="grid-template-columns:1fr 1.3fr; gap:30px; align-items:start;">
        <!-- Config Section -->
        <section class="card">
            <h2 class="card-title">⚙️ Design Config</h2>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="slug">URL Slug Keyword *</label>
                    <div class="input-group d-flex" style="gap:5px;">
                        <span style="background:rgba(255,255,255,0.05); padding:12px; border:1px solid var(--border-color); border-radius:var(--radius-sm); font-size:13px; color:var(--text-muted);">/portfolio.php?url=</span>
                        <input type="text" name="slug" id="slug" class="form-control" placeholder="john-doe" value="<?= htmlspecialchars($slug) ?>" required style="flex:1;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="theme">Portfolio Theme</label>
                    <select name="theme" id="theme" class="form-control">
                        <option value="modern" <?= $theme === 'modern' ? 'selected' : '' ?>>✨ Modern Glow</option>
                        <option value="dark" <?= $theme === 'dark' ? 'selected' : '' ?>>💻 Terminal Dark</option>
                        <option value="minimal" <?= $theme === 'minimal' ? 'selected' : '' ?>>📄 Minimalist Crisp</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="bio">Personal Biography / Summary</label>
                    <textarea name="bio" id="bio" class="form-control" rows="4" placeholder="Brief professional bio..."><?= htmlspecialchars($bio) ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Contact Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="john@email.com" value="<?= htmlspecialchars($email) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="github">GitHub Link</label>
                    <input type="url" name="github" id="github" class="form-control" placeholder="https://github.com/..." value="<?= htmlspecialchars($github) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="linkedin">LinkedIn Link</label>
                    <input type="url" name="linkedin" id="linkedin" class="form-control" placeholder="https://linkedin.com/in/..." value="<?= htmlspecialchars($linkedin) ?>">
                </div>

                <button type="submit" class="btn btn-primary w-100">🚀 Save & Publish Portfolio</button>
            </form>
        </section>

        <!-- Preview Section -->
        <section class="card">
            <h2 class="card-title">👁️ Live Link & Share Details</h2>
            
            <?php if (!$portfolio): ?>
                <div style="text-align:center; padding:60px 20px; color:var(--text-muted);">
                    <span style="font-size:40px; display:block; margin-bottom:15px; opacity:0.6;">🌐</span>
                    Build and save your configuration to create your public portfolio link.
                </div>
            <?php else: ?>
                <div class="alert alert-success" style="margin-bottom:24px;">
                    <strong>Your Portfolio is Live!</strong><br>
                    URL: <a href="portfolio.php?url=<?= htmlspecialchars($portfolio['slug']) ?>" target="_blank" style="color:var(--text-dark); text-decoration:underline;">/portfolio.php?url=<?= htmlspecialchars($portfolio['slug']) ?></a>
                </div>
                <div class="mb-4">
                    <h4 style="font-size:14.5px; font-weight:700; color:var(--text-dark); margin-bottom:12px;">Share QR Code</h4>
                    <!-- Dynamic simulated QR Code wrapper -->
                    <div style="background:#ffffff; padding:15px; border-radius:10px; display:inline-block; border:1px solid #ddd; box-shadow:0 4px 10px rgba(0,0,0,0.05);">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/portfolio.php?url=' . $portfolio['slug']) ?>" alt="Portfolio QR Code" style="width:150px; height:150px; display:block;">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="portfolio.php?url=<?= htmlspecialchars($portfolio['slug']) ?>" target="_blank" class="btn btn-primary">👁️ View Portfolio Site</a>
                    <button class="btn btn-secondary" onclick="navigator.clipboard.writeText('<?= 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/portfolio.php?url=' . $portfolio['slug'] ?>'); alert('Copied URL!')">📋 Copy Link</button>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
