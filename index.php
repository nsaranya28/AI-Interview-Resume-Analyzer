<?php
// index.php
// Beautiful landing page for AI Interview & Resume Analyzer

require_once __DIR__ . '/auth.php';

$is_candidate = isLoggedIn();
$is_admin = isAdminLoggedIn();
$user_name = getCurrentUserName();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Interview & Resume Analyzer - Elevate Your Career</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Optimize your resume for ATS algorithms, identify crucial skill gaps, and practice interactive, AI-tailored mock interviews generated from your experience.">
    
    <!-- Custom styling to augment style.css for high-impact landing page elements -->
    <style>
        .hero-container {
            position: relative;
            overflow: hidden;
            padding: 80px 0 100px 0;
        }

        /* Floating glow blobs */
        .blob {
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            pointer-events: none;
            opacity: 0.65;
            animation: floatBlob 20s infinite alternate ease-in-out;
        }
        .blob-1 {
            top: -10%;
            left: -15%;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.15) 0%, transparent 70%);
        }
        .blob-2 {
            bottom: 10%;
            right: -15%;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.15) 0%, transparent 70%);
            animation-delay: -7s;
        }
        @keyframes floatBlob {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(60px, -40px) scale(1.15); }
            100% { transform: translate(-40px, 80px) scale(0.9); }
        }

        /* Hero grid layout */
        .hero-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            align-items: center;
            gap: 60px;
        }
        
        .hero-tagline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(168, 85, 247, 0.1);
            border: 1px solid rgba(168, 85, 247, 0.2);
            padding: 6px 16px;
            border-radius: 100px;
            color: #d8b4fe;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .hero-title {
            font-size: 54px;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff 30%, #9ca3af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .hero-title span {
            background: linear-gradient(135deg, #a855f7 0%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 35px;
            color: var(--text-muted);
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        /* Mock App Dashboard Preview */
        .preview-card {
            position: relative;
            background: rgba(15, 17, 26, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            animation: cardFloat 6s infinite ease-in-out;
        }
        @keyframes cardFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 12px;
        }
        
        .preview-dots {
            display: flex;
            gap: 6px;
        }
        
        .preview-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .dot-red { background: #ef4444; }
        .dot-yellow { background: #f59e0b; }
        .dot-green { background: #10b981; }

        .preview-content {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Live Score Demo */
        .preview-score-box {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: var(--radius-md);
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-around;
        }

        .preview-score-info h4 {
            font-size: 16px;
            color: #fff;
            margin-bottom: 4px;
        }
        
        .preview-score-info p {
            font-size: 12px;
            margin: 0;
        }

        /* Skills Match Mock */
        .preview-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        /* Features Section */
        .section-title-wrapper {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 60px auto;
        }

        .section-tag {
            color: var(--primary);
            text-transform: uppercase;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.15em;
            margin-bottom: 12px;
            display: block;
        }

        .section-title {
            font-size: 38px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 80px;
        }

        .feature-item {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 40px 30px;
            transition: var(--transition);
        }
        
        .feature-item:hover {
            border-color: var(--border-color-active);
            background: var(--bg-surface-hover);
            transform: translateY(-5px);
        }

        .feature-icon-box {
            width: 54px;
            height: 54px;
            border-radius: var(--radius-sm);
            background: rgba(168, 85, 247, 0.1);
            border: 1px solid rgba(168, 85, 247, 0.2);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.1);
        }
        
        .feature-item:nth-child(2) .feature-icon-box {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            color: var(--secondary);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
        }
        
        .feature-item:nth-child(3) .feature-icon-box {
            background: rgba(6, 182, 212, 0.1);
            border: 1px solid rgba(6, 182, 212, 0.2);
            color: var(--accent);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.1);
        }

        .feature-item h3 {
            font-size: 20px;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .feature-item p {
            margin-bottom: 0;
            line-height: 1.6;
        }

        /* How it works */
        .steps-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 80px;
        }

        .step-item {
            background: rgba(255, 255, 255, 0.015);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 35px 25px;
            position: relative;
            transition: var(--transition);
        }
        
        .step-item:hover {
            border-color: rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.03);
            transform: translateY(-4px);
        }

        .step-number {
            position: absolute;
            top: -20px;
            left: 24px;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #fff;
            box-shadow: 0 4px 10px var(--primary-glow);
            font-size: 16px;
        }

        .step-item h4 {
            font-size: 18px;
            margin-top: 10px;
            margin-bottom: 12px;
            font-weight: 700;
        }
        
        .step-item p {
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 0;
        }

        /* Stats Grid Extra Styles */
        .stats-highlight {
            padding: 60px 0;
            background: rgba(255, 255, 255, 0.01);
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            margin: 80px 0;
        }

        /* CTA Section */
        .cta-wrapper {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.08) 0%, rgba(99, 102, 241, 0.08) 100%);
            border: 1px solid var(--border-color-active);
            border-radius: var(--radius-lg);
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 100px;
            box-shadow: 0 10px 40px rgba(168, 85, 247, 0.05);
        }
        
        .cta-wrapper::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.05) 0%, transparent 60%);
            z-index: -1;
            pointer-events: none;
        }

        .cta-wrapper h2 {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .cta-wrapper p {
            max-width: 600px;
            margin: 0 auto 30px auto;
            font-size: 16px;
            line-height: 1.6;
        }

        /* Footer portal links */
        footer {
            border-top: 1px solid var(--border-color);
            padding: 40px 0;
            margin-top: 60px;
            color: var(--text-muted);
            font-size: 14px;
        }
        
        .footer-grid {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .footer-links {
            display: flex;
            gap: 24px;
        }
        
        .footer-link {
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-link:hover {
            color: var(--primary);
        }

        /* Responsiveness adjustments */
        @media (max-width: 992px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 50px;
                text-align: center;
            }
            .hero-buttons {
                justify-content: center;
            }
            .preview-card {
                max-width: 500px;
                margin: 0 auto;
            }
            .features-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            .steps-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 40px 24px;
            }
        }
        
        @media (max-width: 600px) {
            .hero-title {
                font-size: 38px;
            }
            .steps-container {
                grid-template-columns: 1fr;
                gap: 35px 0;
            }
            .footer-grid {
                flex-direction: column;
                text-align: center;
            }
            .footer-links {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>
<body>

    <!-- Header & Navigation -->
    <header>
        <div class="container nav-container">
            <a href="index.php" class="logo" id="header-logo">
                <!-- Sleek dynamic SVG logo mark -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 4px;">
                    <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="url(#logoGrad)" />
                    <path d="M2 17L12 22L22 17" stroke="url(#logoGrad)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M2 12L12 17L22 12" stroke="url(#logoGrad)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <defs>
                        <linearGradient id="logoGrad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#a855f7" />
                            <stop offset="1" stop-color="#06b6d4" />
                        </linearGradient>
                    </defs>
                </svg>
                AI Resume Analyzer
            </a>
            
            <nav class="nav-links">
                <a href="#features" class="nav-link">Features</a>
                <a href="#how-it-works" class="nav-link">How It Works</a>
                <a href="#stats" class="nav-link">Impact</a>
                
                <?php if ($is_candidate): ?>
                    <a href="dashboard.php" class="btn btn-secondary btn-sm" id="nav-btn-dashboard">Go to Dashboard</a>
                    <a href="login.php?logout=1" class="btn btn-danger btn-sm" id="nav-btn-logout">Log Out</a>
                <?php elseif ($is_admin): ?>
                    <a href="admin_dashboard.php" class="btn btn-secondary btn-sm" id="nav-btn-admin-dashboard" style="border-color: rgba(6, 182, 212, 0.3); color: var(--accent);">Recruiter Portal</a>
                    <a href="login.php?logout=1" class="btn btn-danger btn-sm" id="nav-btn-admin-logout">Log Out</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link" id="nav-link-login">Candidate Log In</a>
                    <a href="register.php" class="btn btn-primary btn-sm" id="nav-btn-register">Get Started</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="hero-container">
        <!-- Glow effects -->
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        
        <div class="container">
            <!-- Hero Grid -->
            <section class="hero-grid" aria-label="Introduction Section">
                <div>
                    <div class="hero-tagline">
                        <span>✨ Powered by Gemini 3.5 Flash AI</span>
                    </div>
                    <h1 class="hero-title" id="main-hero-title">
                        Elevate Your Career with <span>AI Insights</span> & Interviews
                    </h1>
                    <p class="hero-desc">
                        Unlock recruiter secrets. Upload your resume to calculate your exact ATS score, automatically discover critical skill gaps, and practice live mock interviews generated directly from your real-world experience.
                    </p>
                    
                    <div class="hero-buttons">
                        <?php if ($is_candidate): ?>
                            <a href="dashboard.php" class="btn btn-primary" id="hero-btn-dashboard">Enter Dashboard &rarr;</a>
                            <a href="#features" class="btn btn-secondary" id="hero-btn-explore">Explore Features</a>
                        <?php elseif ($is_admin): ?>
                            <a href="admin_dashboard.php" class="btn btn-primary" style="background: linear-gradient(135deg, var(--accent) 0%, var(--secondary) 100%); box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);" id="hero-btn-admin">Access Recruiter Portal &rarr;</a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary" id="hero-btn-signup">Scan Your Resume Free</a>
                            <a href="admin_login.php" class="btn btn-secondary" id="hero-btn-recruiter">Recruiter Portal</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Beautiful Mock App Interface Preview -->
                <div class="preview-card" id="mockup-preview-card">
                    <div class="preview-header">
                        <div class="preview-dots">
                            <span class="preview-dot dot-red"></span>
                            <span class="preview-dot dot-yellow"></span>
                            <span class="preview-dot dot-green"></span>
                        </div>
                        <span style="font-size: 11px; color: var(--text-muted); font-weight: 600; letter-spacing: 0.05em;">LIVE ANALYSIS REPORT</span>
                    </div>
                    
                    <div class="preview-content">
                        <!-- Score Display -->
                        <div class="preview-score-box">
                            <div class="score-circle excellent" style="width: 80px; height: 80px; border-width: 5px; margin: 0;">
                                <span class="score-num" style="font-size: 22px;">84%</span>
                                <span class="score-lbl" style="font-size: 8px;">ATS Match</span>
                            </div>
                            <div class="preview-score-info">
                                <h4>Senior Web Engineer</h4>
                                <p style="color: var(--success); font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    High Shortlist Potential
                                </p>
                                <p style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">Resume contains 14 key technologies</p>
                            </div>
                        </div>

                        <!-- Skills Showcase -->
                        <div class="preview-skills">
                            <span class="skill-tag skill-tag-match" style="padding: 3px 8px; font-size: 11px;">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                PHP / PDO
                            </span>
                            <span class="skill-tag skill-tag-match" style="padding: 3px 8px; font-size: 11px;">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                MySQL
                            </span>
                            <span class="skill-tag skill-tag-match" style="padding: 3px 8px; font-size: 11px;">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Gemini API
                            </span>
                            <span class="skill-tag skill-tag-missing" style="padding: 3px 8px; font-size: 11px;">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                Redis Cache
                            </span>
                        </div>

                        <!-- Mini Mock Chat Bubble -->
                        <div class="chat-bubble chat-bubble-ai" style="padding: 10px 14px; font-size: 13px; max-width: 100%; border-bottom-left-radius: 4px; margin-top: 5px;">
                            <span class="score-tag" style="font-size: 9px; padding: 1px 6px; top: -8px; right: 10px;">Question 3</span>
                            <p style="margin: 0; color: #fff; font-weight: 500; font-size: 13px;">"How do you optimize slow query transactions inside your MySQL databases using indexing?"</p>
                            
                            <div class="feedback-box" style="margin-top: 8px; padding-top: 8px; font-size: 11px; display: flex; align-items: flex-start; gap: 4px;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--accent); flex-shrink: 0; margin-top: 2px;"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                <span>AI Feedback: Excellent, focus on Composite Indexing for multiple filters.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Core Features Grid -->
    <main class="container" id="features">
        <section class="section-title-wrapper" aria-labelledby="features-heading">
            <span class="section-tag">Powerful Utilities</span>
            <h2 class="section-title" id="features-heading">What AI Resume Analyzer Does</h2>
            <p>Our comprehensive framework leverages advanced Large Language Models to evaluate applicants' capabilities, test critical thinking, and report detailed feedback instantly.</p>
        </section>

        <div class="features-grid">
            <!-- Feature 1 -->
            <div class="feature-item" id="feature-ats">
                <div class="feature-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
                <h3>ATS Score Analysis</h3>
                <p>Calculates database keyword scores and matches your details against recruiter filters to ensure your resume never gets lost in automated portals.</p>
            </div>

            <!-- Feature 2 -->
            <div class="feature-item" id="feature-skills">
                <div class="feature-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                </div>
                <h3>Skill Gap Finder</h3>
                <p>Identifies both matching and missing professional keywords for your target role, giving you clear guidelines to optimize and update your technical skills.</p>
            </div>

            <!-- Feature 3 -->
            <div class="feature-item" id="feature-interview">
                <div class="feature-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <h3>Interactive AI Mock Chats</h3>
                <p>Generates technical and behavioral questions specific to your work history. Conducts a fully interactive mock chat interview with real-time feedback scoring.</p>
            </div>
        </div>
    </main>

    <!-- How It Works Timeline -->
    <section class="container" id="how-it-works" aria-labelledby="how-it-works-heading">
        <div class="section-title-wrapper">
            <span class="section-tag">Intuitive Flow</span>
            <h2 class="section-title" id="how-it-works-heading">Four Easy Steps to Mastery</h2>
            <p>Go from candidate profile creation to mock interview feedback in less than five minutes.</p>
        </div>

        <div class="steps-container">
            <!-- Step 1 -->
            <div class="step-item" id="step-upload">
                <span class="step-number">1</span>
                <h4>Upload Resume</h4>
                <p>Register your account and upload your professional experience in PDF format.</p>
            </div>

            <!-- Step 2 -->
            <div class="step-item" id="step-role">
                <span class="step-number">2</span>
                <h4>Define Target Role</h4>
                <p>Select your target job title (e.g. Web Engineer, Product Manager) to calibrate the AI model.</p>
            </div>

            <!-- Step 3 -->
            <div class="step-item" id="step-score">
                <span class="step-number">3</span>
                <h4>Review ATS Report</h4>
                <p>Analyze your automated ATS matching score, matching tech stacks, and core recommendations.</p>
            </div>

            <!-- Step 4 -->
            <div class="step-item" id="step-chat">
                <span class="step-number">4</span>
                <h4>Simulate Live Chat</h4>
                <p>Answer custom interview questions and receive instantaneous, constructive grading.</p>
            </div>
        </div>
    </section>

    <!-- Impact & Statistics -->
    <section class="stats-highlight" id="stats">
        <div class="container">
            <div class="stats-grid" id="statistics-counters">
                <div class="stat-card" id="stat-resumes">
                    <div class="stat-num">12,500+</div>
                    <div class="stat-lbl">Scans Completed</div>
                </div>
                <div class="stat-card" id="stat-rate">
                    <div class="stat-num">94.2%</div>
                    <div class="stat-lbl">Interview Rate Boost</div>
                </div>
                <div class="stat-card" id="stat-rating">
                    <div class="stat-num">4.9 / 5</div>
                    <div class="stat-lbl">Candidate Rating</div>
                </div>
                <div class="stat-card" id="stat-time">
                    <div class="stat-num">&lt; 3 Min</div>
                    <div class="stat-lbl">Average Analysis Time</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Banner -->
    <div class="container">
        <section class="cta-wrapper" aria-labelledby="cta-heading">
            <h2 id="cta-heading">Ready to Secure Your Next Role?</h2>
            <p>Stop guessing how recruiters see your credentials. Get a full diagnostic evaluation and prepare yourself for hard technical discussions today.</p>
            
            <div>
                <?php if ($is_candidate): ?>
                    <a href="dashboard.php" class="btn btn-primary" id="cta-btn-dashboard">Open Your Profile Dashboard &rarr;</a>
                <?php elseif ($is_admin): ?>
                    <a href="admin_dashboard.php" class="btn btn-primary" id="cta-btn-admin-dashboard" style="background: linear-gradient(135deg, var(--accent) 0%, var(--secondary) 100%);">Enter Recruiter Dashboard &rarr;</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary" id="cta-btn-signup">Create Free Account</a>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container footer-grid">
            <p>&copy; <?php echo date('Y'); ?> AI Resume Analyzer. All rights reserved.</p>
            <div class="footer-links">
                <a href="#features" class="footer-link">Features</a>
                <a href="#how-it-works" class="footer-link">How it Works</a>
                <a href="login.php" class="footer-link">Candidate Portal</a>
                <a href="admin_login.php" class="footer-link" style="color: var(--accent);">Recruiter Console</a>
            </div>
        </div>
    </footer>

</body>
</html>
