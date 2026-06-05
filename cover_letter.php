<?php
// cover_letter.php - AI Cover Letter Generator
require_once __DIR__ . '/auth.php';
requireLogin();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/AiHelper.php';

$userId = getCurrentUserId();
$db = getDB();

$error = '';
$success = '';
$generatedContent = '';
$companyName = '';
$jobTitle = '';

// Check if user has an active resume profile to extract summary details
$stmtRes = $db->prepare("SELECT id, title, summary, full_name, job_title FROM resume_profiles WHERE user_id=? ORDER BY updated_at DESC LIMIT 1");
$stmtRes->execute([$userId]);
$profile = $stmtRes->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'generate';
    $companyName = trim($_POST['company_name'] ?? '');
    $jobTitle = trim($_POST['job_title'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $tone = trim($_POST['tone'] ?? 'professional');
    
    if (empty($companyName) || empty($jobTitle)) {
        $error = 'Please fill in the Company Name and Job Title fields.';
    } else {
        // Build Prompt for Gemini
        $profileSummary = $profile ? $profile['summary'] : 'An aspiring and motivated candidate looking to deliver high-quality outcomes.';
        $profileName = $profile ? $profile['full_name'] : 'Candidate';
        
        $prompt = "You are an expert career advisor. Write a highly professional, tailored, and persuasive Cover Letter for {$profileName} applying for the position of '{$jobTitle}' at '{$companyName}'.\n\n";
        $prompt .= "Candidate Profile Summary:\n{$profileSummary}\n\n";
        if (!empty($requirements)) {
            $prompt .= "Key Job Requirements & Description:\n{$requirements}\n\n";
        }
        $prompt .= "Tone Style: {$tone}\n";
        $prompt .= "Please output ONLY the cover letter text, properly structured with date, address placeholders, formal greeting, body paragraphs highlighting fit, and a professional closing statement.";
        
        try {
            if (!$GLOBALS['demo_mode']) {
                $generatedContent = callGeminiAPI($prompt);
            }
            
            if (empty($generatedContent)) {
                // Fallback demo Cover Letter
                $dateStr = date('F d, Y');
                $generatedContent = "[Your Name]\n[Your Address]\n\n{$dateStr}\n\nHiring Team\n{$companyName}\n\nDear Hiring Team,\n\nI am writing to express my strong interest in the {$jobTitle} position at {$companyName}. With a solid background as described in my professional profile, combined with my passion for driving innovation, I am confident that I can make a meaningful impact on your team.\n\nThroughout my career, I have focused on delivering high-impact solutions, collaborating with cross-functional partners, and maintaining a commitment to professional growth. My summary profiles my capability in system architecture and designing scalable platforms, which align perfectly with the goals of {$companyName}.\n\nThank you for your time and consideration. I welcome the opportunity to discuss how my qualifications align with your requirements in more detail.\n\nSincerely,\n\n{$profileName}";
            }
            
            // Save to database
            $stmtSave = $db->prepare("INSERT INTO cover_letters (user_id, company_name, job_title, content) VALUES (?, ?, ?, ?)");
            $stmtSave->execute([$userId, $companyName, $jobTitle, $generatedContent]);
            $success = "Cover Letter generated successfully!";
        } catch (Exception $e) {
            $error = "Failed to generate: " . $e->getMessage();
        }
    }
}

$pageTitle = 'AI Cover Letter Generator - AI Career Companion';
include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top: 40px; padding-bottom: 60px;">
    <div style="margin-bottom:30px;">
        <h1 style="font-size:32px; font-weight:800; background:linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">AI Cover Letter Generator</h1>
        <p class="text-muted">Draft tailored, company-specific cover letters that capture recruiter attention in one click.</p>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="grid grid-2" style="grid-template-columns:1fr 1.5fr; gap:30px; align-items:start;">
        <!-- Configuration Card -->
        <section class="card">
            <h2 class="card-title">📝 Configuration</h2>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="company_name">Company Name *</label>
                    <input type="text" name="company_name" id="company_name" class="form-control" placeholder="e.g. Google" value="<?= htmlspecialchars($companyName) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="job_title">Job Title *</label>
                    <input type="text" name="job_title" id="job_title" class="form-control" placeholder="e.g. Frontend Engineer" value="<?= htmlspecialchars($jobTitle) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="tone">Tone of Letter</label>
                    <select name="tone" id="tone" class="form-control">
                        <option value="professional" selected>Professional / Confident</option>
                        <option value="creative">Creative / Passionate</option>
                        <option value="minimal">Minimal / Concise</option>
                        <option value="executive">Executive / Leader</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="requirements">Job Requirements / Description (Optional)</label>
                    <textarea name="requirements" id="requirements" class="form-control" rows="5" placeholder="Paste specific requirements from job description to match skills..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">✨ Generate Cover Letter</button>
            </form>
        </section>

        <!-- Preview & Editor Card -->
        <section class="card">
            <h2 class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
                <span>👁️ Generated Cover Letter</span>
                <?php if ($generatedContent): ?>
                    <button class="btn btn-secondary btn-sm" onclick="copyToClipboard('letterText')">📋 Copy Text</button>
                <?php endif; ?>
            </h2>

            <?php if (!$generatedContent): ?>
                <div style="text-align:center; padding:50px 20px; color:var(--text-muted);">
                    <span style="font-size:40px; display:block; margin-bottom:15px; opacity:0.6;">✉️</span>
                    Fill in the details on the left and click "Generate" to create your custom cover letter.
                </div>
            <?php else: ?>
                <div style="background:rgba(255,255,255,0.02); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:20px;">
                    <textarea id="letterText" class="form-control" rows="18" style="font-family:monospace; line-height:1.6; resize:vertical; background:none; border:none; padding:0; outline:none; color:var(--text-dark);"><?= htmlspecialchars($generatedContent) ?></textarea>
                </div>
                <div style="display:flex; gap:12px; margin-top:20px;">
                    <button class="btn btn-primary" onclick="printLetter()">🖨️ Print / Save PDF</button>
                    <button class="btn btn-secondary" onclick="window.location.reload()">Reset</button>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<script>
function copyToClipboard(id) {
    const copyText = document.getElementById(id);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    alert("Copied to clipboard!");
}
function printLetter() {
    const content = document.getElementById('letterText').value;
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Cover Letter</title><style>body{font-family:serif;line-height:1.6;padding:40px;color:#111;white-space:pre-line;}</style></head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
