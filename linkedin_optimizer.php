<?php
// linkedin_optimizer.php - AI LinkedIn Profile Optimizer
require_once __DIR__ . '/auth.php';
requireLogin();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/AiHelper.php';

$userId = getCurrentUserId();
$db = getDB();

$error = '';
$success = '';
$headlines = [];
$summary = '';
$suggestions = '';

// Load active resume profile
$stmtRes = $db->prepare("SELECT id, summary, full_name, job_title FROM resume_profiles WHERE user_id=? ORDER BY updated_at DESC LIMIT 1");
$stmtRes->execute([$userId]);
$profile = $stmtRes->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$profile) {
        $error = "Please build a resume first in the Builder before using the LinkedIn Optimizer.";
    } else {
        $resumeText = $profile['summary'] . "\nTarget Role: " . $profile['job_title'];
        
        $prompt = "You are a professional LinkedIn branding expert. Analyze the following candidate summary and job title to produce 3 high-impact LinkedIn Headlines (separated by lines starting with 'Headline [number]:') and a rich LinkedIn Summary/About section written in the first person.\n\n";
        $prompt .= "Candidate Info:\nName: {$profile['full_name']}\nHeadline: {$profile['job_title']}\nSummary:\n{$profile['summary']}\n\n";
        $prompt .= "Format the output clearly. First section: Headlines. Second section: Profile Summary/About Section. Do not write anything else.";
        
        try {
            $aiResponse = '';
            if (!$GLOBALS['demo_mode']) {
                $aiResponse = callGeminiAPI($prompt);
            }
            
            if (empty($aiResponse)) {
                // Demo fallback
                $headlines = [
                    "Senior Software Engineer | React, Node.js, Python | Building Scalable Web Architectures & AI Integrations",
                    "Senior Software Engineer at Google | Technical Lead & System Architect | Mentoring Teams & Delivering Enterprise APIs",
                    "Full Stack Engineering Leader | Helping Businesses Scale with Modern React, Next.js, Cloud Architectures"
                ];
                $summary = "I am a results-driven Senior Software Engineer with over 8 years of experience designing and optimizing high-performance web applications. I specialize in building robust APIs with Node.js and Python, developing user-friendly frontend systems with React/Next.js, and scaling secure cloud architectures.\n\nThroughout my career, I have successfully led team migrations, reduced query response times by 40%, and introduced CI/CD practices that significantly improved deployment reliability. I am passionate about mentoring developers and adopting AI innovations to solve complex technical challenges.\n\nKey Specialties: React, Next.js, Node.js, Python, AWS Cloud, microservices, gRPC, and system design.";
                $suggestions = "• Highlight quantitative metrics like '40% database speedup' in your profile summary.\n• Turn on LinkedIn 'Creator Mode' and list React, Python, Node.js as your top topics.\n• Request recommendations from previous managers at Google or Tech Solutions.";
            } else {
                // Parse headlines and summary
                $lines = explode("\n", $aiResponse);
                $isSummary = false;
                $summaryArr = [];
                foreach ($lines as $line) {
                    if (stripos($line, 'headline') !== false && stripos($line, ':') !== false) {
                        $headlines[] = trim(substr($line, strpos($line, ':') + 1));
                    } elseif (stripos($line, 'summary') !== false || stripos($line, 'about') !== false) {
                        $isSummary = true;
                    } elseif ($isSummary) {
                        $summaryArr[] = $line;
                    }
                }
                $summary = trim(implode("\n", $summaryArr));
                if (empty($headlines)) {
                    $headlines = ["Senior Software Engineer | React, Node.js | Cloud Architect"];
                }
                $suggestions = "• Add target keywords like React, Node.js, Python to your LinkedIn Skill Endorsements.\n• Customize your LinkedIn profile URL (e.g. linkedin.com/in/username).";
            }
            
            // Save optimization record
            $stmtSave = $db->prepare("INSERT INTO linkedin_profiles (user_id, resume_id, headline, summary, suggestions) VALUES (?, ?, ?, ?, ?)");
            $stmtSave->execute([$userId, $profile['id'], $headlines[0], $summary, $suggestions]);
            $success = "LinkedIn Optimization suggestions loaded successfully!";
        } catch (Exception $e) {
            $error = "Failed to run optimization: " . $e->getMessage();
        }
    }
}

$pageTitle = 'AI LinkedIn Profile Optimizer - AI Career Companion';
include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top: 40px; padding-bottom: 60px;">
    <div style="margin-bottom:30px;">
        <h1 style="font-size:32px; font-weight:800; background:linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">AI LinkedIn Profile Optimizer</h1>
        <p class="text-muted">Generate high-impact headlines, profile summaries, and action steps to build a 50x more visible professional brand.</p>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="grid grid-2" style="grid-template-columns:1fr 1.5fr; gap:30px; align-items:start;">
        <!-- Left Side: Profile overview -->
        <section class="card">
            <h2 class="card-title">👤 Professional Summary Source</h2>
            <?php if (!$profile): ?>
                <div class="alert alert-danger" style="margin-bottom:15px;">No active resume profile found!</div>
                <a href="resume_builder.php" class="btn btn-primary w-100">✏️ Create a Resume First</a>
            <?php else: ?>
                <div style="margin-bottom:20px;">
                    <div style="font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Source Name</div>
                    <div style="font-size:16px; font-weight:700; color:var(--text-dark);"><?= htmlspecialchars($profile['full_name']) ?></div>
                </div>
                <div style="margin-bottom:20px;">
                    <div style="font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Source Headline</div>
                    <div style="font-size:14.5px; color:var(--text-dark);"><?= htmlspecialchars($profile['job_title']) ?></div>
                </div>
                <div style="margin-bottom:20px;">
                    <div style="font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Resume Summary</div>
                    <p style="font-size:12.5px; line-height:1.5; color:var(--text-main); margin-bottom:0; max-height:150px; overflow-y:auto;"><?= htmlspecialchars($profile['summary']) ?></p>
                </div>
                <form method="POST">
                    <button type="submit" class="btn btn-primary w-100">✨ Optimize Profile Brand</button>
                </form>
            <?php endif; ?>
        </section>

        <!-- Right Side: Recommendations and outputs -->
        <section class="card">
            <h2 class="card-title">✨ AI Profile Suggestions</h2>
            
            <?php if (empty($headlines) && empty($summary)): ?>
                <div style="text-align:center; padding:60px 20px; color:var(--text-muted);">
                    <span style="font-size:42px; display:block; margin-bottom:15px; opacity:0.6;">⚡</span>
                    Click "Optimize Profile Brand" to build your custom headline and summaries.
                </div>
            <?php else: ?>
                <!-- Headlines -->
                <div class="mb-4">
                    <h4 style="font-size:14.5px; font-weight:700; color:var(--text-dark); margin-bottom:12px; border-left:3px solid var(--primary); padding-left:8px;">Optimized Profile Headlines</h4>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <?php foreach ($headlines as $idx => $headline): ?>
                            <div style="display:flex; justify-content:space-between; align-items:start; background:rgba(255,255,255,0.015); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:12px 16px; gap:12px;">
                                <span style="font-size:13px; color:var(--text-dark); font-weight:500; line-height:1.4;"><?= htmlspecialchars($headline) ?></span>
                                <button class="btn btn-secondary btn-sm py-1" onclick="navigator.clipboard.writeText('<?= addslashes($headline) ?>'); alert('Copied headline!')">Copy</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="mb-4">
                    <h4 style="font-size:14.5px; font-weight:700; color:var(--text-dark); margin-bottom:12px; border-left:3px solid var(--secondary); padding-left:8px;">Premium About/Summary Text</h4>
                    <div style="position:relative;">
                        <textarea id="linkedinAbout" class="form-control" rows="10" style="font-family:inherit; line-height:1.6; font-size:13px; padding-bottom:45px;" readonly><?= htmlspecialchars($summary) ?></textarea>
                        <button class="btn btn-secondary btn-sm" style="position:absolute; bottom:12px; right:12px;" onclick="copyToClipboard('linkedinAbout')">📋 Copy Summary</button>
                    </div>
                </div>

                <!-- Improvement Suggestions -->
                <div>
                    <h4 style="font-size:14.5px; font-weight:700; color:var(--text-dark); margin-bottom:12px; border-left:3px solid var(--success); padding-left:8px;">Profile Improvement Checklist</h4>
                    <div style="background:var(--success-bg); border:1px solid var(--success-border); border-radius:var(--radius-sm); padding:16px; font-size:13px; line-height:1.6; color:#34d399; white-space:pre-line;">
                        <?= $suggestions ?>
                    </div>
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
    alert("Copied summary text!");
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
