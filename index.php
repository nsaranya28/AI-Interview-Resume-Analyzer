<?php
// index.php - Landing Page
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    require_once __DIR__ . '/includes/config.php';
}
require_once __DIR__ . '/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
} elseif (isAdminLoggedIn()) {
    header('Location: admin_dashboard.php');
    exit;
}

$pageTitle = 'AI ResumeAI - Build, Analyze & Ace Your Interviews';
$pageDesc = 'The most powerful AI-driven resume builder and ATS analyzer. Build stunning resumes, get ATS scores, and practice mock interviews with AI.';
include __DIR__ . '/includes/header.php';
?>

<style>
.hero { padding: 100px 0 80px; text-align: center; position: relative; }
.hero-title { font-size: 62px; font-weight: 900; line-height: 1.1; letter-spacing: -2px; margin-bottom: 24px; background: linear-gradient(135deg, var(--text-dark) 0%, var(--primary) 50%, var(--accent) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.hero-sub { font-size: 18px; color: var(--text-muted); max-width: 620px; margin: 0 auto 40px; line-height: 1.6; }
.hero-btns { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
.hero-badge { display: inline-flex; align-items: center; gap: 8px; background: var(--primary-glow); border: 1px solid rgba(220, 38, 38, 0.15); border-radius: 999px; padding: 6px 16px; font-size: 13px; font-weight: 600; color: var(--primary); margin-bottom: 24px; }
.features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.feature-card { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 28px; transition: var(--transition); position: relative; overflow: hidden; }
.feature-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: var(--gradient); opacity: 0; transition: var(--transition); }
.feature-card:hover { transform: translateY(-6px); border-color: rgba(220, 38, 38, 0.2); box-shadow: 0 20px 40px rgba(220, 38, 38, 0.05); }
.feature-card:hover::before { opacity: 1; }
.feature-icon { font-size: 36px; margin-bottom: 16px; display: block; }
.feature-title { font-size: 18px; font-weight: 800; margin-bottom: 8px; color: var(--text-dark); }
.feature-desc { font-size: 14px; color: var(--text-muted); line-height: 1.65; }
.stats-section { text-align: center; padding: 60px 0; }
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; }
.stat-item .num { font-size: 48px; font-weight: 900; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.stat-item .label { font-size: 14px; color: var(--text-muted); font-weight: 600; margin-top: 4px; }
.cta-section { background: linear-gradient(135deg, rgba(220, 38, 38, 0.03) 0%, rgba(220, 38, 38, 0.06) 100%); border: 1px solid rgba(220, 38, 38, 0.1); border-radius: var(--radius-lg); padding: 60px 40px; text-align: center; margin: 60px 0; }
.steps-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
.step-card { text-align: center; padding: 20px; }
.step-num { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900; color: #fff; margin: 0 auto 16px; }
.step-title { font-size: 15px; font-weight: 700; margin-bottom: 6px; }
.step-desc { font-size: 13px; color: var(--text-muted); }
.section-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--primary); margin-bottom: 12px; }
.section-heading { font-size: 36px; font-weight: 900; margin-bottom: 16px; }
@media (max-width: 768px) {
    .hero-title { font-size: 36px; }
    .features-grid, .stats-row, .steps-grid { grid-template-columns: 1fr; }
}
</style>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="hero-badge">✨ Powered by Google Gemini AI</div>
        <h1 class="hero-title">Build. Analyze. Ace<br>Every Interview.</h1>
        <p class="hero-sub">The most intelligent AI-powered career platform. Create ATS-optimized resumes, analyze your job fit, and practice mock interviews with real-time AI coaching.</p>
        <div class="hero-btns">
            <a href="register.php" class="btn btn-primary" style="padding: 16px 36px; font-size: 16px;">🚀 Get Started Free</a>
            <a href="login.php" class="btn btn-secondary" style="padding: 16px 36px; font-size: 16px;">→ Sign In</a>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="stats-section">
    <div class="container">
        <div class="stats-row">
            <div class="stat-item"><div class="num">100+</div><div class="label">Resume Templates</div></div>
            <div class="stat-item"><div class="num">100%</div><div class="label">AI Powered Analysis</div></div>
            <div class="stat-item"><div class="num">10+</div><div class="label">Resume Sections</div></div>
            <div class="stat-item"><div class="num">∞</div><div class="label">Interview Practice</div></div>
        </div>
    </div>
</section>

<!-- Features -->
<section style="padding: 60px 0;">
    <div class="container">
        <div style="text-align:center;margin-bottom:48px;">
            <div class="section-label">Everything You Need</div>
            <h2 class="section-heading">A Complete Career Platform</h2>
            <p style="color:var(--text-muted);max-width:500px;margin:0 auto;">From building your first resume to landing your dream job — we've got every step covered.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card" style="--gradient:linear-gradient(90deg,#ef4444,#7f1d1d);">
                <span class="feature-icon">✏️</span>
                <div class="feature-title">AI Resume Builder</div>
                <div class="feature-desc">Create professional resumes with 10+ sections including experience, projects, certifications, achievements, and more. Choose from 4 beautiful templates.</div>
            </div>
            <div class="feature-card" style="--gradient:linear-gradient(90deg,#b91c1c,#fca5a5);">
                <span class="feature-icon">📊</span>
                <div class="feature-title">ATS Score Analyzer</div>
                <div class="feature-desc">Upload your resume PDF and get an instant ATS score (0-100) with keyword analysis, missing skills detection, grammar check, and improvement suggestions.</div>
            </div>
            <div class="feature-card" style="--gradient:linear-gradient(90deg,#991b1b,#ef4444);">
                <span class="feature-icon">🎤</span>
                <div class="feature-title">Mock Interview AI</div>
                <div class="feature-desc">Practice answering AI-generated interview questions tailored to your resume and target role. Get instant scores and detailed feedback on every answer.</div>
            </div>
            <div class="feature-card" style="--gradient:linear-gradient(90deg,#dc2626,#f87171);">
                <span class="feature-icon">🎯</span>
                <div class="feature-title">Job Role Matching</div>
                <div class="feature-desc">Enter your target role and see exactly how your resume matches — with match percentage, missing keywords, and specific improvement recommendations.</div>
            </div>
            <div class="feature-card" style="--gradient:linear-gradient(90deg,#7f1d1d,#fca5a5);">
                <span class="feature-icon">🚀</span>
                <div class="feature-title">AI Suggestions Engine</div>
                <div class="feature-desc">Get AI-powered improvements for your career summary, experience descriptions, projects, and keywords to maximize your ATS and interview scores.</div>
            </div>
            <div class="feature-card" style="--gradient:linear-gradient(90deg,#dc2626,#991b1b);">
                <span class="feature-icon">🖨️</span>
                <div class="feature-title">Export & Share</div>
                <div class="feature-desc">Preview your resume in a live editor, export to PDF using your browser, and share via a unique public link. 4 stunning templates to choose from.</div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section style="padding: 60px 0; background: rgba(255,255,255,.01);">
    <div class="container">
        <div style="text-align:center;margin-bottom:48px;">
            <div class="section-label">Simple Process</div>
            <h2 class="section-heading">Get Started in 4 Steps</h2>
        </div>
        <div class="steps-grid">
            <div class="step-card"><div class="step-num">1</div><div class="step-title">Create Account</div><div class="step-desc">Register for free. No credit card required.</div></div>
            <div class="step-card"><div class="step-num">2</div><div class="step-title">Build Resume</div><div class="step-desc">Use AI Builder to create a polished resume in minutes.</div></div>
            <div class="step-card"><div class="step-num">3</div><div class="step-title">Analyze & Optimize</div><div class="step-desc">Upload your resume to get an ATS score and AI recommendations.</div></div>
            <div class="step-card"><div class="step-num">4</div><div class="step-title">Practice & Apply</div><div class="step-desc">Practice mock interviews with AI and land your dream job.</div></div>
        </div>
    </div>
</section>

<!-- CTA -->
<section style="padding: 60px 0 100px;">
    <div class="container">
        <div class="cta-section">
            <h2 style="font-size:38px;font-weight:900;margin-bottom:16px;">Ready to Land Your Dream Job?</h2>
            <p style="color:var(--text-muted);max-width:500px;margin:0 auto 32px;font-size:16px;">Join thousands of job seekers who have improved their resumes and interview skills with AI ResumeAI.</p>
            <a href="register.php" class="btn btn-primary" style="padding:18px 48px;font-size:17px;">🚀 Start For Free — No Credit Card</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
