<?php
// resume_builder.php - Full AI Resume Builder
require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/AiHelper.php';

$userId = getCurrentUserId();
$db = getDB();
$success = '';
$error = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    
    if ($action === 'create_resume') {
        $title = trim($_POST['title'] ?? 'My Resume');
        $template = $_POST['template'] ?? 'ats';
        $stmt = $db->prepare("INSERT INTO resume_profiles (user_id, title, template, full_name, email, job_title) SELECT ?, ?, ?, name, email, '' FROM users WHERE id=?");
        $stmt->execute([$userId, $title, $template, $userId]);
        $newId = $db->lastInsertId();
        header("Location: resume_builder.php?id=$newId&success=created");
        exit;
    }
    
    if ($action === 'save_resume') {
        $resumeId = intval($_POST['resume_id'] ?? 0);
        // Verify ownership
        $chk = $db->prepare("SELECT id FROM resume_profiles WHERE id=? AND user_id=?");
        $chk->execute([$resumeId, $userId]);
        if (!$chk->fetch()) { $error = 'Access denied.'; goto render; }
        
        // Save personal info + summary + languages
        $languages = array_filter(array_map('trim', explode(',', $_POST['languages'] ?? '')));
        $langJson = !empty($languages) ? json_encode(array_values($languages)) : null;
        
        $stmt = $db->prepare("UPDATE resume_profiles SET title=?,template=?,full_name=?,job_title=?,email=?,phone=?,address=?,linkedin=?,github=?,portfolio=?,summary=?,languages=?,updated_at=NOW() WHERE id=?");
        $stmt->execute([
            trim($_POST['title'] ?? 'My Resume'),
            $_POST['template'] ?? 'ats',
            trim($_POST['full_name'] ?? ''),
            trim($_POST['job_title'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['phone'] ?? ''),
            trim($_POST['address'] ?? ''),
            trim($_POST['linkedin'] ?? ''),
            trim($_POST['github'] ?? ''),
            trim($_POST['portfolio'] ?? ''),
            trim($_POST['summary'] ?? ''),
            $langJson,
            $resumeId
        ]);
        
        // Save Education
        $db->prepare("DELETE FROM education WHERE resume_id=?")->execute([$resumeId]);
        if (!empty($_POST['edu_institution'])) {
            $stmtEdu = $db->prepare("INSERT INTO education (resume_id,institution,degree,field_of_study,start_year,end_year,gpa,description,sort_order) VALUES (?,?,?,?,?,?,?,?,?)");
            foreach ($_POST['edu_institution'] as $i => $inst) {
                if (empty(trim($inst))) continue;
                $stmtEdu->execute([$resumeId, trim($inst), trim($_POST['edu_degree'][$i]??''), trim($_POST['edu_field'][$i]??''), trim($_POST['edu_start'][$i]??''), trim($_POST['edu_end'][$i]??''), trim($_POST['edu_gpa'][$i]??''), trim($_POST['edu_desc'][$i]??''), $i]);
            }
        }
        
        // Save Skills
        $db->prepare("DELETE FROM resume_skills WHERE resume_id=?")->execute([$resumeId]);
        if (!empty($_POST['skill_name'])) {
            $stmtSkill = $db->prepare("INSERT INTO resume_skills (resume_id,skill_name,level,category,sort_order) VALUES (?,?,?,?,?)");
            foreach ($_POST['skill_name'] as $i => $sk) {
                if (empty(trim($sk))) continue;
                $stmtSkill->execute([$resumeId, trim($sk), $_POST['skill_level'][$i]??'intermediate', trim($_POST['skill_cat'][$i]??''), $i]);
            }
        }
        
        // Save Experience
        $db->prepare("DELETE FROM experience WHERE resume_id=?")->execute([$resumeId]);
        if (!empty($_POST['exp_company'])) {
            $stmtExp = $db->prepare("INSERT INTO experience (resume_id,company,position,location,start_date,end_date,is_current,description,sort_order) VALUES (?,?,?,?,?,?,?,?,?)");
            foreach ($_POST['exp_company'] as $i => $co) {
                if (empty(trim($co))) continue;
                $isCurrent = isset($_POST['exp_current'][$i]) ? 1 : 0;
                $stmtExp->execute([$resumeId, trim($co), trim($_POST['exp_position'][$i]??''), trim($_POST['exp_location'][$i]??''), trim($_POST['exp_start'][$i]??''), $isCurrent ? 'Present' : trim($_POST['exp_end'][$i]??''), $isCurrent, trim($_POST['exp_desc'][$i]??''), $i]);
            }
        }
        
        // Save Projects
        $db->prepare("DELETE FROM projects WHERE resume_id=?")->execute([$resumeId]);
        if (!empty($_POST['proj_name'])) {
            $stmtProj = $db->prepare("INSERT INTO projects (resume_id,project_name,role,technologies,description,url,github_url,sort_order) VALUES (?,?,?,?,?,?,?,?)");
            foreach ($_POST['proj_name'] as $i => $pn) {
                if (empty(trim($pn))) continue;
                $stmtProj->execute([$resumeId, trim($pn), trim($_POST['proj_role'][$i]??''), trim($_POST['proj_tech'][$i]??''), trim($_POST['proj_desc'][$i]??''), trim($_POST['proj_url'][$i]??''), trim($_POST['proj_github'][$i]??''), $i]);
            }
        }
        
        // Save Certifications
        $db->prepare("DELETE FROM certifications WHERE resume_id=?")->execute([$resumeId]);
        if (!empty($_POST['cert_name'])) {
            $stmtCert = $db->prepare("INSERT INTO certifications (resume_id,cert_name,issuer,issue_date,expiry_date,credential_id,cert_url,sort_order) VALUES (?,?,?,?,?,?,?,?)");
            foreach ($_POST['cert_name'] as $i => $cn) {
                if (empty(trim($cn))) continue;
                $stmtCert->execute([$resumeId, trim($cn), trim($_POST['cert_issuer'][$i]??''), trim($_POST['cert_date'][$i]??''), trim($_POST['cert_expiry'][$i]??''), trim($_POST['cert_id'][$i]??''), trim($_POST['cert_url'][$i]??''), $i]);
            }
        }
        
        // Save Achievements
        $db->prepare("DELETE FROM achievements WHERE resume_id=?")->execute([$resumeId]);
        if (!empty($_POST['ach_title'])) {
            $stmtAch = $db->prepare("INSERT INTO achievements (resume_id,title,description,date,sort_order) VALUES (?,?,?,?,?)");
            foreach ($_POST['ach_title'] as $i => $at) {
                if (empty(trim($at))) continue;
                $stmtAch->execute([$resumeId, trim($at), trim($_POST['ach_desc'][$i]??''), trim($_POST['ach_date'][$i]??''), $i]);
            }
        }
        
        header("Location: resume_builder.php?id=$resumeId&success=saved");
        exit;
    }
    
    if ($action === 'ai_improve') {
        $resumeId = intval($_POST['resume_id'] ?? 0);
        $section = $_POST['section'] ?? 'summary';
        $content = trim($_POST['content'] ?? '');
        
        // Call AI
        $improved = '';
        if (!$GLOBALS['demo_mode'] && !empty($content)) {
            $prompt = "You are a professional resume writer. Improve the following resume {$section} section for better ATS scores, clarity and impact. Return ONLY the improved text, no explanation:\n\n{$content}";
            $improved = callGeminiAPI($prompt) ?? getDemoImprovement($section, $content);
        } else {
            $improved = getDemoImprovement($section, $content);
        }
        echo json_encode(['success' => true, 'improved' => $improved]);
        exit;
    }
}

render:

// Load existing resumes list
$stmt = $db->prepare("SELECT id, title, template, updated_at FROM resume_profiles WHERE user_id=? ORDER BY updated_at DESC");
$stmt->execute([$userId]);
$myResumes = $stmt->fetchAll();

// Load selected resume
$currentId = isset($_GET['id']) ? intval($_GET['id']) : null;
$resume = null;
$education = $experience = $projects = $certifications = $achievements = $skills = [];

if (!$currentId && !empty($myResumes)) {
    $currentId = $myResumes[0]['id'];
}

if ($currentId) {
    $stmt = $db->prepare("SELECT * FROM resume_profiles WHERE id=? AND user_id=?");
    $stmt->execute([$currentId, $userId]);
    $resume = $stmt->fetch();
    if ($resume) {
        $stmtEdu = $db->prepare("SELECT * FROM education WHERE resume_id=? ORDER BY sort_order");
        $stmtEdu->execute([$currentId]); $education = $stmtEdu->fetchAll();
        
        $stmtExp = $db->prepare("SELECT * FROM experience WHERE resume_id=? ORDER BY sort_order");
        $stmtExp->execute([$currentId]); $experience = $stmtExp->fetchAll();
        
        $stmtSkill = $db->prepare("SELECT * FROM resume_skills WHERE resume_id=? ORDER BY sort_order");
        $stmtSkill->execute([$currentId]); $skills = $stmtSkill->fetchAll();
        
        $stmtProj = $db->prepare("SELECT * FROM projects WHERE resume_id=? ORDER BY sort_order");
        $stmtProj->execute([$currentId]); $projects = $stmtProj->fetchAll();
        
        $stmtCert = $db->prepare("SELECT * FROM certifications WHERE resume_id=? ORDER BY sort_order");
        $stmtCert->execute([$currentId]); $certifications = $stmtCert->fetchAll();
        
        $stmtAch = $db->prepare("SELECT * FROM achievements WHERE resume_id=? ORDER BY sort_order");
        $stmtAch->execute([$currentId]); $achievements = $stmtAch->fetchAll();
    }
}

if (isset($_GET['success'])) {
    $success = $_GET['success'] === 'saved' ? 'Resume saved successfully!' : 'New resume created!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error)) { /* already set */ }

function getDemoImprovement($section, $content) {
    $demos = [
        'summary' => "Results-driven professional with demonstrated expertise in delivering high-impact solutions. Proven track record of leveraging technical skills to drive innovation and achieve outstanding business outcomes. Adept at cross-functional collaboration and committed to continuous professional development.",
        'description' => "• Led end-to-end development of scalable solutions, resulting in 30% performance improvement\n• Collaborated with cross-functional teams to deliver projects on time and within budget\n• Implemented best practices and code reviews, reducing bug count by 40%",
    ];
    return $demos[$section] ?? "Enhanced version: " . $content . " [Optimized for ATS and impact]";
}

$pageTitle = 'Resume Builder - AI Resume Analyzer';
include __DIR__ . '/includes/header.php';
?>

<style>
.builder-layout { display: grid; grid-template-columns: 260px 1fr; min-height: calc(100vh - 80px); }
.builder-sidebar { background: #ffffff; border-right: 1px solid var(--border-color); padding: 24px 16px; position: sticky; top: 80px; max-height: calc(100vh - 80px); overflow-y: auto; }
.builder-content { padding: 32px; max-width: 900px; }
.section-nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; color: var(--text-muted); transition: var(--transition); text-decoration: none; margin-bottom: 4px; }
.section-nav-item:hover, .section-nav-item.active { background: #f1f5f9; color: var(--text-dark); border-left: 3px solid var(--primary); }
.section-block { scroll-margin-top: 100px; }
.section-block + .section-block { margin-top: 36px; }
.section-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border-color); }
.section-title { font-size:18px; font-weight:800; color:var(--text-dark); display:flex; align-items:center; gap:10px; }
.repeatable-item { background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin-bottom: 16px; position: relative; }
.remove-item { position:absolute; top:12px; right:12px; background:none; border:none; color:var(--error); cursor:pointer; font-size:18px; line-height:1; }
.remove-item:hover { color:#de2c2e; }
.form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }
.ai-btn { background: linear-gradient(135deg,#6366f1,#a855f7); color:#fff; border:none; border-radius:6px; padding:6px 14px; font-size:12px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
.ai-btn:hover { opacity:0.9; transform: translateY(-1px); }
.template-card { border:1px solid var(--border-color); border-radius:10px; padding:14px; cursor:pointer; transition:var(--transition); text-align:center; background: #ffffff; }
.template-card.selected, .template-card:hover { border-color:var(--primary); background:#eef2ff; }
.template-label { font-size:12px; font-weight:700; margin-top:8px; }
.skill-level-badge { padding:2px 8px; border-radius:999px; font-size:11px; font-weight:700; }
.level-beginner { background:#fef2f2; color:#dc2626; }
.level-intermediate { background:#fffbeb; color:#d97706; }
.level-advanced { background:#eef2ff; color:#4f46e5; }
.level-expert { background:#ecfdf5; color:#059669; }
</style>

<div class="builder-layout">
    <!-- Sidebar -->
    <aside class="builder-sidebar">
        <div style="margin-bottom:20px;">
            <a href="resume_builder.php" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;margin-bottom:12px;">+ New Resume</a>
            <h4 style="font-size:13px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">My Resumes</h4>
            <?php if (empty($myResumes)): ?>
                <p style="font-size:13px;color:var(--text-muted);">No resumes yet.</p>
            <?php else: ?>
                <?php foreach ($myResumes as $r): ?>
                    <a href="resume_builder.php?id=<?= $r['id'] ?>" class="section-nav-item <?= $currentId == $r['id'] ? 'active' : '' ?>">
                        <span>📄</span>
                        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;"><?= htmlspecialchars($r['title']) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($resume): ?>
        <div style="border-top:1px solid var(--border-color);padding-top:20px;margin-top:8px;">
            <h4 style="font-size:13px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Sections</h4>
            <a href="#personal" class="section-nav-item">👤 Personal Info</a>
            <a href="#objective" class="section-nav-item">🎯 Career Objective</a>
            <a href="#education" class="section-nav-item">🎓 Education</a>
            <a href="#skills" class="section-nav-item">⚡ Skills</a>
            <a href="#experience" class="section-nav-item">💼 Experience</a>
            <a href="#projects" class="section-nav-item">🚀 Projects</a>
            <a href="#certifications" class="section-nav-item">🏆 Certifications</a>
            <a href="#achievements" class="section-nav-item">⭐ Achievements</a>
            <a href="#languages" class="section-nav-item">🌐 Languages</a>
        </div>
        <div style="margin-top:20px;">
            <a href="resume_preview.php?id=<?= $currentId ?>" target="_blank" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;margin-bottom:8px;">👁️ Preview Resume</a>
        </div>
        <?php endif; ?>
    </aside>

    <!-- Main Content -->
    <main class="builder-content">
        
        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom:24px;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom:24px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php
        $templates = [
            'ats' => ['emoji' => '🤖', 'label' => 'ATS Friendly', 'color' => '#10b981'],
            'professional' => ['emoji' => '💼', 'label' => 'Professional', 'color' => '#6366f1'],
            'modern' => ['emoji' => '✨', 'label' => 'Modern', 'color' => '#a855f7'],
            'creative' => ['emoji' => '🎨', 'label' => 'Creative', 'color' => '#f59e0b'],
        ];
        ?>

        <?php if (!$resume): ?>
        <!-- Create New Resume -->
        <div class="card">
            <h2 class="card-title">✏️ Create Your First Resume</h2>
            <p style="color:var(--text-muted);margin-bottom:24px;">Choose a template and give your resume a name to get started.</p>
            <form method="POST">
                <input type="hidden" name="action" value="create_resume">
                <div class="form-group">
                    <label class="form-label">Resume Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Software Engineer Resume" value="My Resume" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Choose Template</label>
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:8px;">
                        <?php foreach ($templates as $key => $t): ?>
                            <label class="template-card" style="cursor:pointer;">
                                <input type="radio" name="template" value="<?= $key ?>" style="display:none;" <?= $key === 'ats' ? 'checked' : '' ?> onchange="document.querySelectorAll('.template-card').forEach(c=>c.classList.remove('selected'));this.closest('.template-card').classList.add('selected')">
                                <div style="font-size:28px;"><?= $t['emoji'] ?></div>
                                <div class="template-label" style="color:<?= $t['color'] ?>;"><?= $t['label'] ?></div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">🚀 Create Resume</button>
            </form>
        </div>

        <?php else: ?>
        
        <!-- Edit Resume Form -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h1 style="font-size:28px;margin:0;"><?= htmlspecialchars($resume['title']) ?></h1>
            <div style="display:flex;gap:10px;">
                <a href="resume_preview.php?id=<?= $currentId ?>" target="_blank" class="btn btn-secondary btn-sm">👁️ Preview</a>
                <button form="resume-form" type="submit" class="btn btn-primary btn-sm">💾 Save All</button>
            </div>
        </div>

        <form id="resume-form" method="POST" action="resume_builder.php?id=<?= $currentId ?>">
            <input type="hidden" name="action" value="save_resume">
            <input type="hidden" name="resume_id" value="<?= $currentId ?>">
            
            <!-- Template & Title -->
            <div class="card section-block" id="personal" style="margin-bottom:24px;">
                <div class="section-header">
                    <span class="section-title">👤 Personal Information</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Resume Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($resume['title']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Template</label>
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;">
                        <?php foreach ($templates as $key => $t): ?>
                            <label class="template-card <?= $resume['template'] === $key ? 'selected' : '' ?>">
                                <input type="radio" name="template" value="<?= $key ?>" style="display:none;" <?= $resume['template'] === $key ? 'checked' : '' ?> onchange="document.querySelectorAll('.template-card').forEach(c=>c.classList.remove('selected'));this.closest('.template-card').classList.add('selected')">
                                <div style="font-size:24px;"><?= $t['emoji'] ?></div>
                                <div class="template-label" style="color:<?= $t['color'] ?>;font-size:11px;"><?= $t['label'] ?></div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="N Saranya" value="<?= htmlspecialchars($resume['full_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Job Title / Headline</label>
                        <input type="text" name="job_title" class="form-control" placeholder="Full Stack Developer" value="<?= htmlspecialchars($resume['job_title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($resume['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 99999 99999" value="<?= htmlspecialchars($resume['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address / Location</label>
                        <input type="text" name="address" class="form-control" placeholder="Chennai, Tamil Nadu, India" value="<?= htmlspecialchars($resume['address'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">LinkedIn Profile</label>
                        <input type="url" name="linkedin" class="form-control" placeholder="https://linkedin.com/in/..." value="<?= htmlspecialchars($resume['linkedin'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">GitHub Profile</label>
                        <input type="url" name="github" class="form-control" placeholder="https://github.com/..." value="<?= htmlspecialchars($resume['github'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Portfolio Website</label>
                        <input type="url" name="portfolio" class="form-control" placeholder="https://portfolio.dev" value="<?= htmlspecialchars($resume['portfolio'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Career Objective -->
            <div class="card section-block" id="objective" style="margin-bottom:24px;">
                <div class="section-header">
                    <span class="section-title">🎯 Career Objective / Summary</span>
                    <button type="button" class="ai-btn" onclick="aiImprove('summary', 'summary-text')">✨ AI Improve</button>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <textarea id="summary-text" name="summary" class="form-control" rows="5" placeholder="A results-driven professional with..."><?= htmlspecialchars($resume['summary'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Education -->
            <div class="card section-block" id="education" style="margin-bottom:24px;">
                <div class="section-header">
                    <span class="section-title">🎓 Education</span>
                    <button type="button" class="ai-btn" onclick="addItem('education-list')">+ Add</button>
                </div>
                <div id="education-list">
                    <?php if (empty($education)): ?>
                    <div class="repeatable-item">
                        <button type="button" class="remove-item" onclick="this.closest('.repeatable-item').remove()">×</button>
                        <div class="form-grid-2">
                            <div class="form-group"><label class="form-label">Institution *</label><input type="text" name="edu_institution[]" class="form-control" placeholder="Anna University"></div>
                            <div class="form-group"><label class="form-label">Degree *</label><input type="text" name="edu_degree[]" class="form-control" placeholder="B.E. Computer Science"></div>
                            <div class="form-group"><label class="form-label">Field of Study</label><input type="text" name="edu_field[]" class="form-control" placeholder="Computer Science"></div>
                            <div class="form-group"><label class="form-label">GPA</label><input type="text" name="edu_gpa[]" class="form-control" placeholder="8.5/10"></div>
                            <div class="form-group"><label class="form-label">Start Year</label><input type="text" name="edu_start[]" class="form-control" placeholder="2020"></div>
                            <div class="form-group"><label class="form-label">End Year</label><input type="text" name="edu_end[]" class="form-control" placeholder="2024"></div>
                            <div class="form-group" style="grid-column:1/-1;"><label class="form-label">Description</label><textarea name="edu_desc[]" class="form-control" rows="2" placeholder="Relevant coursework, honors..."></textarea></div>
                        </div>
                    </div>
                    <?php else: ?>
                        <?php foreach ($education as $edu): ?>
                        <div class="repeatable-item">
                            <button type="button" class="remove-item" onclick="this.closest('.repeatable-item').remove()">×</button>
                            <div class="form-grid-2">
                                <div class="form-group"><label class="form-label">Institution *</label><input type="text" name="edu_institution[]" class="form-control" value="<?= htmlspecialchars($edu['institution']) ?>"></div>
                                <div class="form-group"><label class="form-label">Degree *</label><input type="text" name="edu_degree[]" class="form-control" value="<?= htmlspecialchars($edu['degree']) ?>"></div>
                                <div class="form-group"><label class="form-label">Field of Study</label><input type="text" name="edu_field[]" class="form-control" value="<?= htmlspecialchars($edu['field_of_study']??'') ?>"></div>
                                <div class="form-group"><label class="form-label">GPA</label><input type="text" name="edu_gpa[]" class="form-control" value="<?= htmlspecialchars($edu['gpa']??'') ?>"></div>
                                <div class="form-group"><label class="form-label">Start Year</label><input type="text" name="edu_start[]" class="form-control" value="<?= htmlspecialchars($edu['start_year']??'') ?>"></div>
                                <div class="form-group"><label class="form-label">End Year</label><input type="text" name="edu_end[]" class="form-control" value="<?= htmlspecialchars($edu['end_year']??'') ?>"></div>
                                <div class="form-group" style="grid-column:1/-1;"><label class="form-label">Description</label><textarea name="edu_desc[]" class="form-control" rows="2"><?= htmlspecialchars($edu['description']??'') ?></textarea></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Skills -->
            <div class="card section-block" id="skills" style="margin-bottom:24px;">
                <div class="section-header">
                    <span class="section-title">⚡ Skills</span>
                    <button type="button" class="ai-btn" onclick="addSkill()">+ Add Skill</button>
                </div>
                <div id="skills-list" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-start;">
                    <?php foreach ($skills as $sk): ?>
                    <div class="repeatable-item" style="display:inline-flex;align-items:center;gap:10px;padding:8px 12px;min-width:250px;">
                        <button type="button" class="remove-item" style="position:static;" onclick="this.closest('.repeatable-item').remove()">×</button>
                        <input type="text" name="skill_name[]" class="form-control" value="<?= htmlspecialchars($sk['skill_name']) ?>" style="flex:1;padding:6px 10px;font-size:13px;">
                        <select name="skill_level[]" class="form-control" style="width:120px;padding:6px 10px;font-size:13px;">
                            <?php foreach (['beginner','intermediate','advanced','expert'] as $lvl): ?>
                                <option value="<?= $lvl ?>" <?= $sk['level'] === $lvl ? 'selected' : '' ?>><?= ucfirst($lvl) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="skill_cat[]" class="form-control" value="<?= htmlspecialchars($sk['category']??'') ?>" placeholder="Category" style="width:110px;padding:6px 10px;font-size:13px;">
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($skills)): ?>
                    <div class="repeatable-item" style="display:inline-flex;align-items:center;gap:10px;padding:8px 12px;min-width:250px;">
                        <button type="button" class="remove-item" style="position:static;" onclick="this.closest('.repeatable-item').remove()">×</button>
                        <input type="text" name="skill_name[]" class="form-control" placeholder="Python" style="flex:1;padding:6px 10px;font-size:13px;">
                        <select name="skill_level[]" class="form-control" style="width:120px;padding:6px 10px;font-size:13px;">
                            <option>beginner</option><option selected>intermediate</option><option>advanced</option><option>expert</option>
                        </select>
                        <input type="text" name="skill_cat[]" class="form-control" placeholder="Category" style="width:110px;padding:6px 10px;font-size:13px;">
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Experience -->
            <div class="card section-block" id="experience" style="margin-bottom:24px;">
                <div class="section-header">
                    <span class="section-title">💼 Work Experience</span>
                    <button type="button" class="ai-btn" onclick="addExp()">+ Add</button>
                </div>
                <div id="experience-list">
                    <?php if (empty($experience)): ?>
                    <div class="repeatable-item">
                        <button type="button" class="remove-item" onclick="this.closest('.repeatable-item').remove()">×</button>
                        <div class="form-grid-2">
                            <div class="form-group"><label class="form-label">Company *</label><input type="text" name="exp_company[]" class="form-control" placeholder="Google Inc."></div>
                            <div class="form-group"><label class="form-label">Position *</label><input type="text" name="exp_position[]" class="form-control" placeholder="Software Engineer"></div>
                            <div class="form-group"><label class="form-label">Location</label><input type="text" name="exp_location[]" class="form-control" placeholder="Bangalore, India"></div>
                            <div class="form-group"><label class="form-label">Start Date</label><input type="text" name="exp_start[]" class="form-control" placeholder="Jan 2022"></div>
                            <div class="form-group"><label class="form-label">End Date</label><input type="text" name="exp_end[]" class="form-control" placeholder="Mar 2024"></div>
                            <div class="form-group" style="display:flex;align-items:center;gap:8px;padding-top:28px;"><input type="checkbox" name="exp_current[0]" id="cur0" style="width:16px;height:16px;"><label for="cur0" style="margin:0;font-size:14px;">Currently Working</label></div>
                            <div class="form-group" style="grid-column:1/-1;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;"><label class="form-label" style="margin:0;">Description / Responsibilities</label><button type="button" class="ai-btn" onclick="aiImproveField(this, 'description')">✨ AI Improve</button></div>
                                <textarea name="exp_desc[]" class="form-control" rows="4" placeholder="• Led development of..."></textarea>
                            </div>
                        </div>
                    </div>
                    <?php else: foreach ($experience as $i => $exp): ?>
                    <div class="repeatable-item">
                        <button type="button" class="remove-item" onclick="this.closest('.repeatable-item').remove()">×</button>
                        <div class="form-grid-2">
                            <div class="form-group"><label class="form-label">Company *</label><input type="text" name="exp_company[]" class="form-control" value="<?= htmlspecialchars($exp['company']) ?>"></div>
                            <div class="form-group"><label class="form-label">Position *</label><input type="text" name="exp_position[]" class="form-control" value="<?= htmlspecialchars($exp['position']) ?>"></div>
                            <div class="form-group"><label class="form-label">Location</label><input type="text" name="exp_location[]" class="form-control" value="<?= htmlspecialchars($exp['location']??'') ?>"></div>
                            <div class="form-group"><label class="form-label">Start Date</label><input type="text" name="exp_start[]" class="form-control" value="<?= htmlspecialchars($exp['start_date']??'') ?>"></div>
                            <div class="form-group"><label class="form-label">End Date</label><input type="text" name="exp_end[]" class="form-control" value="<?= htmlspecialchars($exp['end_date']??'') ?>"></div>
                            <div class="form-group" style="display:flex;align-items:center;gap:8px;padding-top:28px;"><input type="checkbox" name="exp_current[<?=$i?>]" id="cur<?=$i?>" style="width:16px;height:16px;" <?= $exp['is_current'] ? 'checked' : '' ?>><label for="cur<?=$i?>" style="margin:0;font-size:14px;">Currently Working</label></div>
                            <div class="form-group" style="grid-column:1/-1;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;"><label class="form-label" style="margin:0;">Description / Responsibilities</label><button type="button" class="ai-btn" onclick="aiImproveField(this, 'description')">✨ AI Improve</button></div>
                                <textarea name="exp_desc[]" class="form-control" rows="4"><?= htmlspecialchars($exp['description']??'') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Projects -->
            <div class="card section-block" id="projects" style="margin-bottom:24px;">
                <div class="section-header">
                    <span class="section-title">🚀 Projects</span>
                    <button type="button" class="ai-btn" onclick="addProject()">+ Add</button>
                </div>
                <div id="projects-list">
                    <?php if (empty($projects)): ?>
                    <div class="repeatable-item">
                        <button type="button" class="remove-item" onclick="this.closest('.repeatable-item').remove()">×</button>
                        <div class="form-grid-2">
                            <div class="form-group"><label class="form-label">Project Name *</label><input type="text" name="proj_name[]" class="form-control" placeholder="AI Resume Analyzer"></div>
                            <div class="form-group"><label class="form-label">Your Role</label><input type="text" name="proj_role[]" class="form-control" placeholder="Full Stack Developer"></div>
                            <div class="form-group" style="grid-column:1/-1;"><label class="form-label">Technologies Used</label><input type="text" name="proj_tech[]" class="form-control" placeholder="React, Node.js, Python, MySQL"></div>
                            <div class="form-group"><label class="form-label">Live URL</label><input type="url" name="proj_url[]" class="form-control" placeholder="https://..."></div>
                            <div class="form-group"><label class="form-label">GitHub URL</label><input type="url" name="proj_github[]" class="form-control" placeholder="https://github.com/..."></div>
                            <div class="form-group" style="grid-column:1/-1;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;"><label class="form-label" style="margin:0;">Project Description</label><button type="button" class="ai-btn" onclick="aiImproveField(this,'description')">✨ AI Improve</button></div>
                                <textarea name="proj_desc[]" class="form-control" rows="3" placeholder="A web application that..."></textarea>
                            </div>
                        </div>
                    </div>
                    <?php else: foreach ($projects as $proj): ?>
                    <div class="repeatable-item">
                        <button type="button" class="remove-item" onclick="this.closest('.repeatable-item').remove()">×</button>
                        <div class="form-grid-2">
                            <div class="form-group"><label class="form-label">Project Name *</label><input type="text" name="proj_name[]" class="form-control" value="<?= htmlspecialchars($proj['project_name']) ?>"></div>
                            <div class="form-group"><label class="form-label">Your Role</label><input type="text" name="proj_role[]" class="form-control" value="<?= htmlspecialchars($proj['role']??'') ?>"></div>
                            <div class="form-group" style="grid-column:1/-1;"><label class="form-label">Technologies Used</label><input type="text" name="proj_tech[]" class="form-control" value="<?= htmlspecialchars($proj['technologies']??'') ?>"></div>
                            <div class="form-group"><label class="form-label">Live URL</label><input type="url" name="proj_url[]" class="form-control" value="<?= htmlspecialchars($proj['url']??'') ?>"></div>
                            <div class="form-group"><label class="form-label">GitHub URL</label><input type="url" name="proj_github[]" class="form-control" value="<?= htmlspecialchars($proj['github_url']??'') ?>"></div>
                            <div class="form-group" style="grid-column:1/-1;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;"><label class="form-label" style="margin:0;">Project Description</label><button type="button" class="ai-btn" onclick="aiImproveField(this,'description')">✨ AI Improve</button></div>
                                <textarea name="proj_desc[]" class="form-control" rows="3"><?= htmlspecialchars($proj['description']??'') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Certifications -->
            <div class="card section-block" id="certifications" style="margin-bottom:24px;">
                <div class="section-header">
                    <span class="section-title">🏆 Certifications</span>
                    <button type="button" class="ai-btn" onclick="addCert()">+ Add</button>
                </div>
                <div id="certifications-list">
                    <?php if (empty($certifications)): ?>
                    <div class="repeatable-item">
                        <button type="button" class="remove-item" onclick="this.closest('.repeatable-item').remove()">×</button>
                        <div class="form-grid-3">
                            <div class="form-group"><label class="form-label">Certification Name *</label><input type="text" name="cert_name[]" class="form-control" placeholder="AWS Certified Developer"></div>
                            <div class="form-group"><label class="form-label">Issuing Organization</label><input type="text" name="cert_issuer[]" class="form-control" placeholder="Amazon Web Services"></div>
                            <div class="form-group"><label class="form-label">Issue Date</label><input type="text" name="cert_date[]" class="form-control" placeholder="Jan 2024"></div>
                            <div class="form-group"><label class="form-label">Expiry Date</label><input type="text" name="cert_expiry[]" class="form-control" placeholder="Jan 2027"></div>
                            <div class="form-group"><label class="form-label">Credential ID</label><input type="text" name="cert_id[]" class="form-control" placeholder="ABC123XYZ"></div>
                            <div class="form-group"><label class="form-label">Credential URL</label><input type="url" name="cert_url[]" class="form-control" placeholder="https://..."></div>
                        </div>
                    </div>
                    <?php else: foreach ($certifications as $cert): ?>
                    <div class="repeatable-item">
                        <button type="button" class="remove-item" onclick="this.closest('.repeatable-item').remove()">×</button>
                        <div class="form-grid-3">
                            <div class="form-group"><label class="form-label">Certification Name *</label><input type="text" name="cert_name[]" class="form-control" value="<?= htmlspecialchars($cert['cert_name']) ?>"></div>
                            <div class="form-group"><label class="form-label">Issuing Organization</label><input type="text" name="cert_issuer[]" class="form-control" value="<?= htmlspecialchars($cert['issuer']??'') ?>"></div>
                            <div class="form-group"><label class="form-label">Issue Date</label><input type="text" name="cert_date[]" class="form-control" value="<?= htmlspecialchars($cert['issue_date']??'') ?>"></div>
                            <div class="form-group"><label class="form-label">Expiry Date</label><input type="text" name="cert_expiry[]" class="form-control" value="<?= htmlspecialchars($cert['expiry_date']??'') ?>"></div>
                            <div class="form-group"><label class="form-label">Credential ID</label><input type="text" name="cert_id[]" class="form-control" value="<?= htmlspecialchars($cert['credential_id']??'') ?>"></div>
                            <div class="form-group"><label class="form-label">Credential URL</label><input type="url" name="cert_url[]" class="form-control" value="<?= htmlspecialchars($cert['cert_url']??'') ?>"></div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Achievements -->
            <div class="card section-block" id="achievements" style="margin-bottom:24px;">
                <div class="section-header">
                    <span class="section-title">⭐ Achievements / Awards</span>
                    <button type="button" class="ai-btn" onclick="addAchievement()">+ Add</button>
                </div>
                <div id="achievements-list">
                    <?php if (empty($achievements)): ?>
                    <div class="repeatable-item">
                        <button type="button" class="remove-item" onclick="this.closest('.repeatable-item').remove()">×</button>
                        <div class="form-grid-2">
                            <div class="form-group"><label class="form-label">Title *</label><input type="text" name="ach_title[]" class="form-control" placeholder="First Place - Hackathon 2024"></div>
                            <div class="form-group"><label class="form-label">Date</label><input type="text" name="ach_date[]" class="form-control" placeholder="March 2024"></div>
                            <div class="form-group" style="grid-column:1/-1;"><label class="form-label">Description</label><textarea name="ach_desc[]" class="form-control" rows="2" placeholder="Won first place among 200+ teams..."></textarea></div>
                        </div>
                    </div>
                    <?php else: foreach ($achievements as $ach): ?>
                    <div class="repeatable-item">
                        <button type="button" class="remove-item" onclick="this.closest('.repeatable-item').remove()">×</button>
                        <div class="form-grid-2">
                            <div class="form-group"><label class="form-label">Title *</label><input type="text" name="ach_title[]" class="form-control" value="<?= htmlspecialchars($ach['title']) ?>"></div>
                            <div class="form-group"><label class="form-label">Date</label><input type="text" name="ach_date[]" class="form-control" value="<?= htmlspecialchars($ach['date']??'') ?>"></div>
                            <div class="form-group" style="grid-column:1/-1;"><label class="form-label">Description</label><textarea name="ach_desc[]" class="form-control" rows="2"><?= htmlspecialchars($ach['description']??'') ?></textarea></div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Languages -->
            <div class="card section-block" id="languages" style="margin-bottom:24px;">
                <div class="section-header">
                    <span class="section-title">🌐 Languages</span>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Languages (comma separated)</label>
                    <input type="text" name="languages" class="form-control" placeholder="English, Tamil, Hindi, French" value="<?= htmlspecialchars(implode(', ', json_decode($resume['languages'] ?? '[]', true) ?: [])) ?>">
                    <p style="font-size:12px;color:var(--text-muted);margin-top:6px;">Separate each language with a comma.</p>
                </div>
            </div>

            <!-- Save Button -->
            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary" style="flex:1;">💾 Save Resume</button>
                <a href="resume_preview.php?id=<?= $currentId ?>" target="_blank" class="btn btn-secondary">👁️ Preview</a>
            </div>
        </form>

        <!-- AI Improvement Modal -->
        <div id="ai-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;display:none;align-items:center;justify-content:center;">
            <div class="card" style="max-width:600px;width:100%;margin:20px;">
                <h3 style="margin-bottom:16px;">✨ AI Improved Version</h3>
                <div id="ai-result" style="background:rgba(99,102,241,.08);border:1px solid var(--primary);border-radius:8px;padding:16px;font-size:14px;line-height:1.7;min-height:100px;white-space:pre-wrap;"></div>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button onclick="applyImprovement()" class="btn btn-primary" style="flex:1;">✅ Apply to Field</button>
                    <button onclick="closeAiModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </main>
</div>

<script>
let aiTargetEl = null;

function aiImprove(section, fieldId) {
    const el = document.getElementById(fieldId);
    if (!el) return;
    aiTargetEl = el;
    fetch('resume_builder.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `action=ai_improve&section=${section}&content=${encodeURIComponent(el.value)}&resume_id=<?= $currentId ?>`
    }).then(r => r.json()).then(d => {
        if (d.success) {
            document.getElementById('ai-result').textContent = d.improved;
            document.getElementById('ai-modal').style.display = 'flex';
        }
    });
}

function aiImproveField(btn, section) {
    const textarea = btn.closest('.repeatable-item').querySelector('textarea');
    if (!textarea) return;
    aiTargetEl = textarea;
    const content = textarea.value;
    btn.textContent = '⏳ Improving...';
    fetch('resume_builder.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `action=ai_improve&section=${section}&content=${encodeURIComponent(content)}&resume_id=<?= $currentId ?>`
    }).then(r => r.json()).then(d => {
        btn.textContent = '✨ AI Improve';
        if (d.success) {
            document.getElementById('ai-result').textContent = d.improved;
            document.getElementById('ai-modal').style.display = 'flex';
        }
    });
}

function applyImprovement() {
    if (aiTargetEl) aiTargetEl.value = document.getElementById('ai-result').textContent;
    closeAiModal();
}
function closeAiModal() { document.getElementById('ai-modal').style.display = 'none'; }

function addItem(listId) {
    const list = document.getElementById(listId);
    const first = list.querySelector('.repeatable-item');
    if (first) {
        const clone = first.cloneNode(true);
        clone.querySelectorAll('input, textarea').forEach(el => el.value = '');
        clone.querySelectorAll('select').forEach(el => el.selectedIndex = 1);
        list.appendChild(clone);
    }
}

function addSkill() {
    const list = document.getElementById('skills-list');
    const item = document.createElement('div');
    item.className = 'repeatable-item';
    item.style.cssText = 'display:inline-flex;align-items:center;gap:10px;padding:8px 12px;min-width:250px;';
    item.innerHTML = `<button type="button" class="remove-item" style="position:static;" onclick="this.closest('.repeatable-item').remove()">×</button>
        <input type="text" name="skill_name[]" class="form-control" placeholder="Skill Name" style="flex:1;padding:6px 10px;font-size:13px;">
        <select name="skill_level[]" class="form-control" style="width:120px;padding:6px 10px;font-size:13px;"><option>beginner</option><option selected>intermediate</option><option>advanced</option><option>expert</option></select>
        <input type="text" name="skill_cat[]" class="form-control" placeholder="Category" style="width:110px;padding:6px 10px;font-size:13px;">`;
    list.appendChild(item);
}

function addExp() { addItem('experience-list'); }
function addProject() { addItem('projects-list'); }
function addCert() { addItem('certifications-list'); }
function addAchievement() { addItem('achievements-list'); }
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
