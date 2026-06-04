<?php
// resume_preview.php - Live Resume Preview & PDF Export
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$userId = getCurrentUserId();
$db = getDB();

$resumeId = isset($_GET['id']) ? $_GET['id'] : 0;
$shareToken = $_GET['token'] ?? null;

$resume = null;
$education = $experience = $skills = $projects = $certs = $achievements = [];
$languages = [];

if ($resumeId === 'guest') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $resumeData = $_SESSION['guest_resume_data'] ?? null;
    if (!$resumeData) {
        echo "<p>Guest sandbox data not found. Please start from the builder.</p>";
        exit;
    }
    $resume = $resumeData['resume'];
    $education = $resumeData['education'];
    $experience = $resumeData['experience'];
    $skills = $resumeData['skills'];
    $projects = $resumeData['projects'];
    $certs = $resumeData['certifications'];
    $achievements = $resumeData['achievements'];
    $languages = json_decode($resume['languages'] ?? '[]', true) ?: [];
    $template = $resume['template'] ?? 'ats';
} else {
    $resumeId = intval($resumeId);
    // Load resume
    if ($shareToken) {
        $stmt = $db->prepare("SELECT * FROM resume_profiles WHERE share_token=? AND is_public=1");
        $stmt->execute([$shareToken]);
        $resume = $stmt->fetch();
    } elseif ($userId && $resumeId) {
        requireLogin();
        $stmt = $db->prepare("SELECT * FROM resume_profiles WHERE id=? AND user_id=?");
        $stmt->execute([$resumeId, $userId]);
        $resume = $stmt->fetch();
    } else {
        requireLogin();
        header("Location: resume_builder.php");
        exit;
    }

    if (!$resume) {
        echo "<p>Resume not found or access denied.</p>"; exit;
    }

    $rId = $resume['id'];
    $stmtEdu = $db->prepare("SELECT * FROM education WHERE resume_id=? ORDER BY sort_order"); $stmtEdu->execute([$rId]); $education = $stmtEdu->fetchAll();
    $stmtExp = $db->prepare("SELECT * FROM experience WHERE resume_id=? ORDER BY sort_order"); $stmtExp->execute([$rId]); $experience = $stmtExp->fetchAll();
    $stmtSkill = $db->prepare("SELECT * FROM resume_skills WHERE resume_id=? ORDER BY sort_order"); $stmtSkill->execute([$rId]); $skills = $stmtSkill->fetchAll();
    $stmtProj = $db->prepare("SELECT * FROM projects WHERE resume_id=? ORDER BY sort_order"); $stmtProj->execute([$rId]); $projects = $stmtProj->fetchAll();
    $stmtCert = $db->prepare("SELECT * FROM certifications WHERE resume_id=? ORDER BY sort_order"); $stmtCert->execute([$rId]); $certs = $stmtCert->fetchAll();
    $stmtAch = $db->prepare("SELECT * FROM achievements WHERE resume_id=? ORDER BY sort_order"); $stmtAch->execute([$rId]); $achievements = $stmtAch->fetchAll();
    $languages = json_decode($resume['languages'] ?? '[]', true) ?: [];
    $template = $resume['template'] ?? 'ats';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($resume['full_name'] ?: $resume['title']) ?> - Resume</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background: #fcf8f8; font-family: 'Inter', sans-serif; padding: 20px; }
        
        .controls { max-width:850px; margin:0 auto 20px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
        .controls .btn { padding:8px 18px; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; border:none; }
        .btn-pdf { background:#dc2626; color:#ffffff; }
        .btn-back { background:#ffffff; color:#4a3e3e; border:1px solid #f5e3e3; }
        .btn-pdf:hover, .btn-back:hover { opacity:.85; }
        
        /* Resume Paper */
        .resume-paper { max-width:850px; margin:0 auto; background:#ffffff; box-shadow:0 25px 60px rgba(220, 38, 38, 0.05); border-radius:4px; overflow:hidden; }
        
        /* ====== ATS Template ====== */
        .tpl-ats { font-family:'Inter',sans-serif; color:#1f1111; }
        .tpl-ats .header { background:#991b1b; color:#ffffff; padding:32px 40px; }
        .tpl-ats .header h1 { font-size:30px; font-weight:800; letter-spacing:-.5px; margin-bottom:4px; }
        .tpl-ats .header .headline { font-size:14px; color:#fca5a5; margin-bottom:12px; }
        .tpl-ats .header .contacts { display:flex; flex-wrap:wrap; gap:16px; font-size:12px; color:#fee2e2; }
        .tpl-ats .header .contact-item { display:flex; align-items:center; gap:5px; }
        .tpl-ats .body { padding:28px 40px; }
        .tpl-ats .section { margin-bottom:22px; }
        .tpl-ats .section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#dc2626; padding-bottom:6px; border-bottom:2px solid #fee2e2; margin-bottom:14px; }
        .tpl-ats .summary-text { font-size:13px; line-height:1.7; color:#4a3e3e; }
        .tpl-ats .item-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:4px; }
        .tpl-ats .item-title { font-size:14px; font-weight:700; color:#1f1111; }
        .tpl-ats .item-subtitle { font-size:13px; color:#8c7a7a; }
        .tpl-ats .item-date { font-size:12px; color:#8c7a7a; white-space:nowrap; }
        .tpl-ats .item-desc { font-size:13px; color:#4a3e3e; line-height:1.6; margin-top:6px; white-space:pre-line; }
        .tpl-ats .skills-grid { display:flex; flex-wrap:wrap; gap:6px; }
        .tpl-ats .skill-chip { background:#fff5f5; border-radius:4px; padding:4px 10px; font-size:12px; font-weight:600; color:#dc2626; }
        .tpl-ats .skill-chip.expert { border-left:3px solid #7f1d1d; }
        .tpl-ats .skill-chip.advanced { border-left:3px solid #dc2626; }
        .tpl-ats .skill-chip.intermediate { border-left:3px solid #f87171; }
        .tpl-ats .skill-chip.beginner { border-left:3px solid #fca5a5; }
        
        /* ====== Professional Template ====== */
        .tpl-professional { font-family:'Inter',sans-serif; display:grid; grid-template-columns:250px 1fr; min-height:900px; }
        .tpl-professional .sidebar { background:#7f1d1d; color:#ffffff; padding:32px 24px; }
        .tpl-professional .sidebar .name { font-size:20px; font-weight:800; margin-bottom:4px; line-height:1.2; }
        .tpl-professional .sidebar .headline { font-size:12px; color:#fca5a5; margin-bottom:20px; }
        .tpl-professional .sidebar .s-section { margin-bottom:20px; }
        .tpl-professional .sidebar .s-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#ffffff; margin-bottom:10px; }
        .tpl-professional .sidebar .contact-row { font-size:11px; color:#fee2e2; margin-bottom:6px; word-break:break-all; }
        .tpl-professional .sidebar .skill-bar { margin-bottom:8px; }
        .tpl-professional .sidebar .skill-name { font-size:11px; color:#fee2e2; margin-bottom:3px; }
        .tpl-professional .sidebar .bar { height:4px; background:rgba(255,255,255,.1); border-radius:2px; overflow:hidden; }
        .tpl-professional .sidebar .bar-fill { height:100%; background:#ffffff; border-radius:2px; }
        .tpl-professional .main { padding:32px 28px; color:#1f1111; background:#ffffff; }
        .tpl-professional .main .section { margin-bottom:22px; }
        .tpl-professional .main .section-title { font-size:14px; font-weight:700; color:#7f1d1d; border-bottom:2px solid #dc2626; padding-bottom:6px; margin-bottom:14px; }
        .tpl-professional .item-header { display:flex; justify-content:space-between; align-items:flex-start; }
        .tpl-professional .item-title { font-size:13px; font-weight:700; color:#1f1111; }
        .tpl-professional .item-sub { font-size:12px; color:#8c7a7a; }
        .tpl-professional .item-date { font-size:11px; color:#8c7a7a; }
        .tpl-professional .item-desc { font-size:12px; color:#4a3e3e; line-height:1.65; margin-top:5px; white-space:pre-line; }
        
        /* ====== Modern Template ====== */
        .tpl-modern { font-family:'Inter',sans-serif; color:#1f1111; }
        .tpl-modern .header { background:linear-gradient(135deg,#dc2626 0%,#991b1b 100%); color:#ffffff; padding:36px 40px; position:relative; }
        .tpl-modern .header::after { content:''; position:absolute; bottom:-20px; left:0; right:0; height:40px; background:#ffffff; clip-path:ellipse(100% 100% at 50% 100%); }
        .tpl-modern .header h1 { font-size:32px; font-weight:800; margin-bottom:4px; }
        .tpl-modern .header .hl { font-size:15px; opacity:.85; margin-bottom:14px; }
        .tpl-modern .header .contacts { display:flex; flex-wrap:wrap; gap:18px; font-size:12px; }
        .tpl-modern .body { padding:34px 40px 28px; }
        .tpl-modern .section { margin-bottom:24px; }
        .tpl-modern .section-title { font-size:16px; font-weight:800; color:#dc2626; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
        .tpl-modern .section-title::before { content:''; width:4px; height:18px; background:#dc2626; border-radius:2px; }
        .tpl-modern .item { padding:12px 0; border-bottom:1px solid #fbebeb; }
        .tpl-modern .item:last-child { border-bottom:none; }
        .tpl-modern .item-header { display:flex; justify-content:space-between; }
        .tpl-modern .item-title { font-size:14px; font-weight:700; }
        .tpl-modern .item-sub { font-size:13px; color:#991b1b; }
        .tpl-modern .item-date { font-size:12px; color:#8c7a7a; background:#fff5f5; padding:2px 8px; border-radius:4px; }
        .tpl-modern .item-desc { font-size:13px; color:#4a3e3e; line-height:1.65; margin-top:6px; white-space:pre-line; }
        .tpl-modern .skill-tags { display:flex; flex-wrap:wrap; gap:8px; }
        .tpl-modern .skill-tag { background:#fff5f5; border:1px solid #fee2e2; color:#dc2626; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        
        /* ====== Creative Template ====== */
        .tpl-creative { font-family:'Playfair Display','Inter',serif; }
        .tpl-creative .header { background:#7f1d1d; padding:40px; color:#ffffff; display:flex; justify-content:space-between; align-items:flex-start; }
        .tpl-creative .header h1 { font-size:36px; font-weight:700; margin-bottom:6px; background:linear-gradient(135deg,#ffffff,#fca5a5); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .tpl-creative .header .hl { font-family:'Inter',sans-serif; font-size:14px; color:#fca5a5; }
        .tpl-creative .header .contacts { font-family:'Inter',sans-serif; font-size:11px; color:#fee2e2; text-align:right; line-height:2; }
        .tpl-creative .body { padding:32px 40px; font-family:'Inter',sans-serif; color:#1f1111; background:#ffffff; }
        .tpl-creative .section { margin-bottom:24px; }
        .tpl-creative .section-title { font-size:18px; font-weight:700; font-family:'Playfair Display',serif; color:#7f1d1d; margin-bottom:14px; padding-bottom:8px; border-bottom:2px solid #dc2626; }
        .tpl-creative .item-title { font-size:14px; font-weight:700; color:#1f1111; }
        .tpl-creative .item-sub { font-size:12px; color:#dc2626; font-weight:600; }
        .tpl-creative .item-date { font-size:11px; color:#8c7a7a; }
        .tpl-creative .item-desc { font-size:13px; color:#4a3e3e; line-height:1.65; margin-top:5px; white-space:pre-line; }
        .tpl-creative .skill-tags { display:flex; flex-wrap:wrap; gap:8px; }
        .tpl-creative .skill-tag { background:#dc2626; color:#ffffff; padding:4px 12px; border-radius:4px; font-size:12px; font-weight:600; }
 
        @media print {
            body { background:#ffffff; padding:0; }
            .controls { display:none !important; }
            .resume-paper { box-shadow:none; }
        }
    </style>
</head>
<body>
<div class="controls no-print">
    <?php if ($userId): ?>
        <a href="resume_builder.php?id=<?= $rId ?>" class="btn btn-back">← Back to Editor</a>
    <?php endif; ?>
    <button onclick="window.print()" class="btn btn-pdf">🖨️ Print / Save as PDF</button>
    <span style="font-size:13px;color:#94a3b8;">Tip: Use browser's "Save as PDF" option when printing.</span>
</div>

<div class="resume-paper">
<?php
$n = htmlspecialchars($resume['full_name'] ?: 'Your Name');
$jt = htmlspecialchars($resume['job_title'] ?: '');
$em = htmlspecialchars($resume['email'] ?: '');
$ph = htmlspecialchars($resume['phone'] ?: '');
$ad = htmlspecialchars($resume['address'] ?: '');
$li = htmlspecialchars($resume['linkedin'] ?: '');
$gh = htmlspecialchars($resume['github'] ?: '');
$po = htmlspecialchars($resume['portfolio'] ?: '');
$sum = htmlspecialchars($resume['summary'] ?: '');

if ($template === 'ats'): ?>
<div class="tpl-ats">
    <div class="header">
        <h1><?= $n ?></h1>
        <?php if ($jt): ?><div class="headline"><?= $jt ?></div><?php endif; ?>
        <div class="contacts">
            <?php if ($em): ?><span class="contact-item">✉ <?= $em ?></span><?php endif; ?>
            <?php if ($ph): ?><span class="contact-item">📞 <?= $ph ?></span><?php endif; ?>
            <?php if ($ad): ?><span class="contact-item">📍 <?= $ad ?></span><?php endif; ?>
            <?php if ($li): ?><span class="contact-item">🔗 <?= $li ?></span><?php endif; ?>
            <?php if ($gh): ?><span class="contact-item">⚡ <?= $gh ?></span><?php endif; ?>
            <?php if ($po): ?><span class="contact-item">🌐 <?= $po ?></span><?php endif; ?>
        </div>
    </div>
    <div class="body">
        <?php if ($sum): ?>
        <div class="section"><div class="section-title">Professional Summary</div><p class="summary-text"><?= $sum ?></p></div>
        <?php endif; ?>
        
        <?php if (!empty($skills)): ?>
        <div class="section"><div class="section-title">Core Skills</div>
        <div class="skills-grid">
            <?php foreach ($skills as $sk): ?>
            <span class="skill-chip <?= $sk['level'] ?>"><?= htmlspecialchars($sk['skill_name']) ?></span>
            <?php endforeach; ?>
        </div></div>
        <?php endif; ?>
        
        <?php if (!empty($experience)): ?>
        <div class="section"><div class="section-title">Work Experience</div>
        <?php foreach ($experience as $exp): ?>
        <div style="margin-bottom:14px;">
            <div class="item-header"><div><div class="item-title"><?= htmlspecialchars($exp['position']) ?> — <?= htmlspecialchars($exp['company']) ?></div><?php if ($exp['location']): ?><div class="item-subtitle">📍 <?= htmlspecialchars($exp['location']) ?></div><?php endif; ?></div><div class="item-date"><?= htmlspecialchars($exp['start_date']) ?> – <?= htmlspecialchars($exp['end_date'] ?: 'Present') ?></div></div>
            <?php if ($exp['description']): ?><div class="item-desc"><?= $exp['description'] ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($education)): ?>
        <div class="section"><div class="section-title">Education</div>
        <?php foreach ($education as $edu): ?>
        <div style="margin-bottom:12px;">
            <div class="item-header"><div><div class="item-title"><?= htmlspecialchars($edu['degree']) ?><?= $edu['field_of_study'] ? ' in '.$edu['field_of_study'] : '' ?></div><div class="item-subtitle"><?= htmlspecialchars($edu['institution']) ?><?= $edu['gpa'] ? ' | GPA: '.$edu['gpa'] : '' ?></div></div><div class="item-date"><?= htmlspecialchars($edu['start_year'] ?? '') ?> – <?= htmlspecialchars($edu['end_year'] ?? '') ?></div></div>
        </div>
        <?php endforeach; ?></div>
        <?php endif; ?>

        <?php if (!empty($projects)): ?>
        <div class="section"><div class="section-title">Projects</div>
        <?php foreach ($projects as $proj): ?>
        <div style="margin-bottom:14px;">
            <div class="item-header"><div class="item-title"><?= htmlspecialchars($proj['project_name']) ?><?= $proj['role'] ? ' — '.$proj['role'] : '' ?></div></div>
            <?php if ($proj['technologies']): ?><div class="item-subtitle" style="font-size:12px;color:#555555;margin:3px 0;">Tech: <?= htmlspecialchars($proj['technologies']) ?></div><?php endif; ?>
            <?php if ($proj['description']): ?><div class="item-desc"><?= $proj['description'] ?></div><?php endif; ?>
            <?php if ($proj['url'] || $proj['github_url']): ?><div style="font-size:11px;color:#64748b;margin-top:4px;"><?= $proj['url'] ? '🔗 '.$proj['url'] : '' ?> <?= $proj['github_url'] ? '⚡ '.$proj['github_url'] : '' ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?></div>
        <?php endif; ?>
 
        <?php if (!empty($certs)): ?>
        <div class="section"><div class="section-title">Certifications</div>
        <?php foreach ($certs as $cert): ?>
        <div style="margin-bottom:8px;"><span class="item-title" style="font-size:13px;"><?= htmlspecialchars($cert['cert_name']) ?></span><?php if ($cert['issuer']): ?> — <?= htmlspecialchars($cert['issuer']) ?><?php endif; ?><span class="item-date" style="float:right;"><?= htmlspecialchars($cert['issue_date']??'') ?></span></div>
        <?php endforeach; ?></div>
        <?php endif; ?>
 
        <?php if (!empty($achievements)): ?>
        <div class="section"><div class="section-title">Achievements</div>
        <?php foreach ($achievements as $ach): ?>
        <div style="margin-bottom:8px;"><span class="item-title" style="font-size:13px;">⭐ <?= htmlspecialchars($ach['title']) ?></span><?php if ($ach['date']): ?> <span style="font-size:11px;color:#64748b;">(<?= $ach['date'] ?>)</span><?php endif; ?><?php if ($ach['description']): ?><div class="item-desc" style="font-size:12px;"><?= $ach['description'] ?></div><?php endif; ?></div>
        <?php endforeach; ?></div>
        <?php endif; ?>
 
        <?php if (!empty($languages)): ?>
        <div class="section"><div class="section-title">Languages</div><div class="skills-grid"><?php foreach ($languages as $lng): ?><span class="skill-chip"><?= htmlspecialchars($lng) ?></span><?php endforeach; ?></div></div>
        <?php endif; ?>
    </div>
</div>
 
<?php elseif ($template === 'professional'): ?>
<div class="tpl-professional">
    <div class="sidebar">
        <div class="name"><?= $n ?></div>
        <div class="headline"><?= $jt ?></div>
        <div class="s-section">
            <div class="s-title">Contact</div>
            <?php if ($em): ?><div class="contact-row">✉ <?= $em ?></div><?php endif; ?>
            <?php if ($ph): ?><div class="contact-row">📞 <?= $ph ?></div><?php endif; ?>
            <?php if ($ad): ?><div class="contact-row">📍 <?= $ad ?></div><?php endif; ?>
            <?php if ($li): ?><div class="contact-row">🔗 <?= $li ?></div><?php endif; ?>
        </div>
        <?php if (!empty($skills)): ?>
        <div class="s-section"><div class="s-title">Skills</div>
        <?php foreach ($skills as $sk): $pct = $sk['level']==='expert'?95:($sk['level']==='advanced'?80:($sk['level']==='intermediate'?60:40)); ?>
        <div class="skill-bar"><div class="skill-name"><?= htmlspecialchars($sk['skill_name']) ?></div><div class="bar"><div class="bar-fill" style="width:<?= $pct ?>%;"></div></div></div>
        <?php endforeach; ?>
        </div><?php endif; ?>
        <?php if (!empty($languages)): ?>
        <div class="s-section"><div class="s-title">Languages</div><?php foreach ($languages as $lng): ?><div class="contact-row"><?= htmlspecialchars($lng) ?></div><?php endforeach; ?></div>
        <?php endif; ?>
    </div>
    <div class="main">
        <?php if ($sum): ?><div class="section"><div class="section-title">About Me</div><p style="font-size:13px;line-height:1.65;color:#374151;"><?= $sum ?></p></div><?php endif; ?>
        <?php if (!empty($experience)): ?><div class="section"><div class="section-title">Experience</div><?php foreach ($experience as $exp): ?><div style="margin-bottom:12px;"><div class="item-header"><div><div class="item-title"><?= htmlspecialchars($exp['position']) ?></div><div class="item-sub"><?= htmlspecialchars($exp['company']) ?><?= $exp['location'] ? ', '.$exp['location'] : '' ?></div></div><div class="item-date"><?= $exp['start_date'] ?> – <?= $exp['end_date']?:'Present' ?></div></div><?php if ($exp['description']): ?><div class="item-desc"><?= $exp['description'] ?></div><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
        <?php if (!empty($education)): ?><div class="section"><div class="section-title">Education</div><?php foreach ($education as $edu): ?><div style="margin-bottom:10px;"><div class="item-header"><div><div class="item-title"><?= htmlspecialchars($edu['degree']) ?></div><div class="item-sub"><?= htmlspecialchars($edu['institution']) ?></div></div><div class="item-date"><?= $edu['start_year'] ?> – <?= $edu['end_year'] ?></div></div></div><?php endforeach; ?></div><?php endif; ?>
        <?php if (!empty($projects)): ?><div class="section"><div class="section-title">Projects</div><?php foreach ($projects as $proj): ?><div style="margin-bottom:12px;"><div class="item-title"><?= htmlspecialchars($proj['project_name']) ?></div><?php if ($proj['technologies']): ?><div class="item-sub" style="font-size:11px;color:#555555;">Stack: <?= htmlspecialchars($proj['technologies']) ?></div><?php endif; ?><?php if ($proj['description']): ?><div class="item-desc"><?= $proj['description'] ?></div><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
        <?php if (!empty($certs)): ?><div class="section"><div class="section-title">Certifications</div><?php foreach ($certs as $c): ?><div style="margin-bottom:6px;font-size:12px;color:#1e293b;"><strong><?= htmlspecialchars($c['cert_name']) ?></strong><?php if ($c['issuer']): ?> — <?= htmlspecialchars($c['issuer']) ?><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
        <?php if (!empty($achievements)): ?><div class="section"><div class="section-title">Achievements</div><?php foreach ($achievements as $ach): ?><div style="margin-bottom:6px;font-size:12px;"><strong><?= htmlspecialchars($ach['title']) ?></strong><?php if ($ach['description']): ?> — <?= htmlspecialchars($ach['description']) ?><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
    </div>
</div>
 
<?php elseif ($template === 'modern'): ?>
<div class="tpl-modern">
    <div class="header"><h1><?= $n ?></h1><?php if ($jt): ?><div class="hl"><?= $jt ?></div><?php endif; ?><div class="contacts"><?php if ($em): ?><span>✉ <?= $em ?></span><?php endif; ?><?php if ($ph): ?><span>📞 <?= $ph ?></span><?php endif; ?><?php if ($ad): ?><span>📍 <?= $ad ?></span><?php endif; ?><?php if ($li): ?><span>🔗 <?= $li ?></span><?php endif; ?></div></div>
    <div class="body">
        <?php if ($sum): ?><div class="section"><div class="section-title">About</div><p style="font-size:14px;line-height:1.7;color:#374151;"><?= $sum ?></p></div><?php endif; ?>
        <?php if (!empty($skills)): ?><div class="section"><div class="section-title">Skills</div><div class="skill-tags"><?php foreach ($skills as $sk): ?><span class="skill-tag"><?= htmlspecialchars($sk['skill_name']) ?></span><?php endforeach; ?></div></div><?php endif; ?>
        <?php if (!empty($experience)): ?><div class="section"><div class="section-title">Experience</div><?php foreach ($experience as $exp): ?><div class="item"><div class="item-header"><div><div class="item-title"><?= htmlspecialchars($exp['position']) ?></div><div class="item-sub"><?= htmlspecialchars($exp['company']) ?></div></div><span class="item-date"><?= $exp['start_date'] ?> – <?= $exp['end_date']?:'Present' ?></span></div><?php if ($exp['description']): ?><div class="item-desc"><?= $exp['description'] ?></div><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
        <?php if (!empty($education)): ?><div class="section"><div class="section-title">Education</div><?php foreach ($education as $edu): ?><div class="item"><div class="item-header"><div><div class="item-title"><?= htmlspecialchars($edu['degree']) ?><?= $edu['field_of_study'] ? ' – '.$edu['field_of_study'] : '' ?></div><div class="item-sub"><?= htmlspecialchars($edu['institution']) ?></div></div><span class="item-date"><?= $edu['start_year'] ?> – <?= $edu['end_year'] ?></span></div></div><?php endforeach; ?></div><?php endif; ?>
        <?php if (!empty($projects)): ?><div class="section"><div class="section-title">Projects</div><?php foreach ($projects as $proj): ?><div class="item"><div class="item-title"><?= htmlspecialchars($proj['project_name']) ?></div><?php if ($proj['technologies']): ?><div class="item-sub" style="font-size:12px;"><?= htmlspecialchars($proj['technologies']) ?></div><?php endif; ?><?php if ($proj['description']): ?><div class="item-desc"><?= $proj['description'] ?></div><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
        <?php if (!empty($certs)): ?><div class="section"><div class="section-title">Certifications</div><div class="skill-tags"><?php foreach ($certs as $c): ?><span class="skill-tag"><?= htmlspecialchars($c['cert_name']) ?></span><?php endforeach; ?></div></div><?php endif; ?>
        <?php if (!empty($languages)): ?><div class="section"><div class="section-title">Languages</div><div class="skill-tags"><?php foreach ($languages as $l): ?><span class="skill-tag"><?= htmlspecialchars($l) ?></span><?php endforeach; ?></div></div><?php endif; ?>
    </div>
</div>
 
<?php else: // Creative ?>
<div class="tpl-creative">
    <div class="header"><div><h1><?= $n ?></h1><div class="hl"><?= $jt ?></div></div><div class="contacts"><?php if ($em): ?>✉ <?= $em ?><br><?php endif; ?><?php if ($ph): ?>📞 <?= $ph ?><br><?php endif; ?><?php if ($ad): ?>📍 <?= $ad ?><br><?php endif; ?><?php if ($li): ?>🔗 LinkedIn<br><?php endif; ?></div></div>
    <div class="body">
        <?php if ($sum): ?><div class="section"><div class="section-title">Career Objective</div><p style="font-size:14px;line-height:1.7;"><?= $sum ?></p></div><?php endif; ?>
        <?php if (!empty($skills)): ?><div class="section"><div class="section-title">Skills</div><div class="skill-tags"><?php foreach ($skills as $sk): ?><span class="skill-tag"><?= htmlspecialchars($sk['skill_name']) ?></span><?php endforeach; ?></div></div><?php endif; ?>
        <?php if (!empty($experience)): ?><div class="section"><div class="section-title">Experience</div><?php foreach ($experience as $exp): ?><div style="margin-bottom:14px;"><div class="item-title"><?= htmlspecialchars($exp['position']) ?></div><div class="item-sub"><?= htmlspecialchars($exp['company']) ?> <?= $exp['location']?'• '.$exp['location']:'' ?></div><div class="item-date"><?= $exp['start_date'] ?> – <?= $exp['end_date']?:'Present' ?></div><?php if ($exp['description']): ?><div class="item-desc"><?= $exp['description'] ?></div><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
        <?php if (!empty($education)): ?><div class="section"><div class="section-title">Education</div><?php foreach ($education as $edu): ?><div style="margin-bottom:10px;"><div class="item-title"><?= htmlspecialchars($edu['degree']) ?></div><div class="item-sub"><?= htmlspecialchars($edu['institution']) ?></div><div class="item-date"><?= $edu['start_year'] ?> – <?= $edu['end_year'] ?></div></div><?php endforeach; ?></div><?php endif; ?>
        <?php if (!empty($projects)): ?><div class="section"><div class="section-title">Projects</div><?php foreach ($projects as $proj): ?><div style="margin-bottom:14px;"><div class="item-title"><?= htmlspecialchars($proj['project_name']) ?></div><?php if ($proj['technologies']): ?><div style="font-size:12px;color:#111111;margin:3px 0;"><?= htmlspecialchars($proj['technologies']) ?></div><?php endif; ?><?php if ($proj['description']): ?><div class="item-desc"><?= $proj['description'] ?></div><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
        <?php if (!empty($certs)): ?><div class="section"><div class="section-title">Certifications</div><div class="skill-tags"><?php foreach ($certs as $c): ?><span class="skill-tag"><?= htmlspecialchars($c['cert_name']) ?></span><?php endforeach; ?></div></div><?php endif; ?>
        <?php if (!empty($achievements)): ?><div class="section"><div class="section-title">Achievements</div><?php foreach ($achievements as $ach): ?><div style="margin-bottom:8px;"><strong><?= htmlspecialchars($ach['title']) ?></strong><?php if ($ach['description']): ?> — <?= htmlspecialchars($ach['description']) ?><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
        <?php if (!empty($languages)): ?><div class="section"><div class="section-title">Languages</div><div class="skill-tags"><?php foreach ($languages as $l): ?><span class="skill-tag"><?= htmlspecialchars($l) ?></span><?php endforeach; ?></div></div><?php endif; ?>
    </div>
</div>
<?php endif; ?>
</div>
</body>
</html>
