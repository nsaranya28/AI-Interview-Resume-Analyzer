<?php
// portfolio.php - Public Portfolio Page
require_once __DIR__ . '/db.php';

$slug = $_GET['url'] ?? '';
if (empty($slug)) {
    echo "<p>Portfolio not found. Invalid slug URL.</p>";
    exit;
}

$db = getDB();

// Fetch portfolio configuration
$stmtPort = $db->prepare("SELECT * FROM portfolios WHERE slug = ?");
$stmtPort->execute([$slug]);
$portfolio = $stmtPort->fetch();

if (!$portfolio) {
    echo "<p>Portfolio site not found.</p>";
    exit;
}

// Track view count
$db->prepare("UPDATE portfolios SET views = views + 1 WHERE id = ?")->execute([$portfolio['id']]);

$userId = $portfolio['user_id'];
$theme = $portfolio['theme'] ?? 'modern';
$bio = $portfolio['about_bio'] ?? '';
$email = $portfolio['contact_email'] ?? '';
$socials = json_decode($portfolio['social_links'], true) ?: [];

// Fetch user name
$stmtUser = $db->prepare("SELECT name, profile_photo FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();
$name = $user ? $user['name'] : 'Candidate';

// Fetch associated resume profile detail
$resumeId = $portfolio['resume_id'];
$education = $experience = $skills = $projects = $certs = [];

if ($resumeId) {
    $stmtEdu = $db->prepare("SELECT * FROM education WHERE resume_id=? ORDER BY sort_order"); $stmtEdu->execute([$resumeId]); $education = $stmtEdu->fetchAll();
    $stmtExp = $db->prepare("SELECT * FROM experience WHERE resume_id=? ORDER BY sort_order"); $stmtExp->execute([$resumeId]); $experience = $stmtExp->fetchAll();
    $stmtSkill = $db->prepare("SELECT * FROM resume_skills WHERE resume_id=? ORDER BY sort_order"); $stmtSkill->execute([$resumeId]); $skills = $stmtSkill->fetchAll();
    $stmtProj = $db->prepare("SELECT * FROM projects WHERE resume_id=? ORDER BY sort_order"); $stmtProj->execute([$resumeId]); $projects = $stmtProj->fetchAll();
    $stmtCert = $db->prepare("SELECT * FROM certifications WHERE resume_id=? ORDER BY sort_order"); $stmtCert->execute([$resumeId]); $certs = $stmtCert->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($name) ?> - Personal Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Modern Portfolio Themes */
        <?php if ($theme === 'dark'): ?>
            /* Terminal Dark Theme */
            :root {
                --bg: #0f172a;
                --text: #f8fafc;
                --muted: #94a3b8;
                --accent: #10b981;
                --card-bg: rgba(255,255,255,0.02);
                --border: rgba(255,255,255,0.08);
            }
        <?php elseif ($theme === 'minimal'): ?>
            /* Minimalist Crisp */
            :root {
                --bg: #ffffff;
                --text: #1e293b;
                --muted: #64748b;
                --accent: #2563eb;
                --card-bg: #f8fafc;
                --border: #e2e8f0;
            }
        <?php else: ?>
            /* Modern Glow Theme */
            :root {
                --bg: #0b1220;
                --text: #cbd5e1;
                --muted: #64748b;
                --accent: #7c3aed;
                --card-bg: rgba(255,255,255,0.05);
                --border: rgba(255,255,255,0.12);
            }
        <?php endif; ?>

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            line-height: 1.6;
            padding: 40px 20px;
        }
        .portfolio-wrapper { max-width: 900px; margin: 0 auto; }
        
        /* Glassmorphism/Layout Headers */
        header { text-align: center; margin-bottom: 50px; }
        .avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 3px solid var(--accent); }
        h1 { font-size: 32px; font-weight: 800; margin-bottom: 8px; color: var(--text); }
        .subtitle { font-size: 16px; color: var(--accent); font-weight: 600; margin-bottom: 20px; }
        .social-row { display: flex; justify-content: center; gap: 15px; margin-top: 10px; }
        .social-link { color: var(--muted); text-decoration: none; font-size: 13px; font-weight: 600; transition: color 0.3s; }
        .social-link:hover { color: var(--accent); }

        .section { margin-bottom: 45px; }
        .section-title { font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: var(--accent); border-bottom: 1.5px solid var(--border); padding-bottom: 6px; margin-bottom: 20px; }
        
        .bio-text { font-size: 14.5px; color: var(--text); line-height: 1.7; }
        
        /* Grid layouts */
        .grid { display: grid; gap: 20px; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); }
        .port-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 20px; transition: transform 0.3s, border-color 0.3s; }
        .port-card:hover { transform: translateY(-4px); border-color: var(--accent); }
        .card-title { font-size: 15px; font-weight: 700; margin-bottom: 6px; color: var(--text); }
        .card-meta { font-size: 12px; color: var(--muted); margin-bottom: 8px; }
        .card-desc { font-size: 13px; color: var(--muted); }
        
        /* Skills badges */
        .skills-flex { display: flex; flex-wrap: wrap; gap: 8px; }
        .skill-tag { background: var(--card-bg); border: 1px solid var(--border); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; color: var(--text); }
        
        /* Contact Form */
        .contact-form { display: flex; flex-direction: column; gap: 15px; max-width: 500px; margin: 0 auto; }
        .form-control { width: 100%; background: var(--card-bg); border: 1px solid var(--border); padding: 12px; border-radius: 8px; color: var(--text); font-family: inherit; }
        .btn { background: var(--accent); color: #fff; padding: 12px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; text-align: center; }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
<div class="portfolio-wrapper">
    <!-- Header -->
    <header>
        <?php if ($user && $user['profile_photo']): ?>
            <img src="<?= htmlspecialchars($user['profile_photo']) ?>" class="avatar" alt="<?= htmlspecialchars($name) ?>">
        <?php endif; ?>
        <h1><?= htmlspecialchars($name) ?></h1>
        <?php if ($resumeId && !empty($experience)): ?>
            <div class="subtitle"><?= htmlspecialchars($experience[0]['position']) ?></div>
        <?php endif; ?>
        <div class="social-row">
            <?php if (!empty($socials['github'])): ?><a href="<?= htmlspecialchars($socials['github']) ?>" target="_blank" class="social-link">GitHub</a><?php endif; ?>
            <?php if (!empty($socials['linkedin'])): ?><a href="<?= htmlspecialchars($socials['linkedin']) ?>" target="_blank" class="social-link">LinkedIn</a><?php endif; ?>
            <?php if ($email): ?><a href="mailto:<?= htmlspecialchars($email) ?>" class="social-link">Email</a><?php endif; ?>
        </div>
    </header>

    <!-- Biography / Bio -->
    <?php if ($bio): ?>
    <section class="section">
        <h2 class="section-title">About Me</h2>
        <p class="bio-text"><?= nl2br(htmlspecialchars($bio)) ?></p>
    </section>
    <?php endif; ?>

    <!-- Projects -->
    <?php if (!empty($projects)): ?>
    <section class="section">
        <h2 class="section-title">Featured Projects</h2>
        <div class="grid">
            <?php foreach ($projects as $proj): ?>
                <div class="port-card">
                    <h3 class="card-title"><?= htmlspecialchars($proj['project_name']) ?></h3>
                    <?php if ($proj['technologies']): ? class="card-meta"><div class="card-meta">Tech: <?= htmlspecialchars($proj['technologies']) ?></div><?php endif; ?>
                    <p class="card-desc"><?= htmlspecialchars($proj['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Experience -->
    <?php if (!empty($experience)): ?>
    <section class="section">
        <h2 class="section-title">Work Experience</h2>
        <div class="grid">
            <?php foreach ($experience as $exp): ?>
                <div class="port-card">
                    <h3 class="card-title"><?= htmlspecialchars($exp['position']) ?></h3>
                    <div class="card-meta"><?= htmlspecialchars($exp['company']) ?> | <?= htmlspecialchars($exp['start_date']) ?> – <?= htmlspecialchars($exp['end_date'] ?: 'Present') ?></div>
                    <p class="card-desc"><?= htmlspecialchars($exp['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Skills -->
    <?php if (!empty($skills)): ?>
    <section class="section">
        <h2 class="section-title">Skills & Expertise</h2>
        <div class="skills-flex">
            <?php foreach ($skills as $sk): ?>
                <span class="skill-tag"><?= htmlspecialchars($sk['skill_name']) ?></span>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Education -->
    <?php if (!empty($education)): ?>
    <section class="section">
        <h2 class="section-title">Education</h2>
        <div class="grid">
            <?php foreach ($education as $edu): ?>
                <div class="port-card">
                    <h3 class="card-title"><?= htmlspecialchars($edu['degree']) ?></h3>
                    <div class="card-meta"><?= htmlspecialchars($edu['institution']) ?> (<?= htmlspecialchars($edu['start_year']) ?> – <?= htmlspecialchars($edu['end_year']) ?>)</div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Contact Form -->
    <section class="section">
        <h2 class="section-title">Get In Touch</h2>
        <form class="contact-form" action="#" method="POST" onsubmit="event.preventDefault(); alert('Message sent successfully!');">
            <input type="text" class="form-control" placeholder="Name" required>
            <input type="email" class="form-control" placeholder="Email" required>
            <textarea class="form-control" rows="5" placeholder="Your Message" required></textarea>
            <button type="submit" class="btn">Send Message</button>
        </form>
    </section>
</div>
</body>
</html>
