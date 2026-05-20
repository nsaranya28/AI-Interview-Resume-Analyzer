<?php
// index.php
// Highly elegant, light-themed landing page styled exactly like the BetterCV reference UI

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
    <title>AI Interview & Resume Analyzer - Create your Resume with an AI-powered CV maker</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Optimize your resume for ATS algorithms, identify crucial skill gaps, and practice interactive, AI-tailored mock interviews generated from your experience.">
    
    <!-- Custom styling to replicate the specific high-fidelity BetterCV landing page UI -->
    <style>
        .hero-container {
            position: relative;
            overflow: hidden;
            padding: 90px 0 110px 0;
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
        }

        /* Subtle light blue glowing backgrounds */
        .glow-bg {
            position: absolute;
            width: 700px;
            height: 700px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.05) 0%, rgba(255, 255, 255, 0) 70%);
            z-index: 0;
            pointer-events: none;
        }
        .glow-1 {
            top: -20%;
            right: -10%;
        }
        .glow-2 {
            bottom: -30%;
            left: -10%;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            align-items: center;
            gap: 60px;
            position: relative;
            z-index: 1;
        }

        /* BetterCV Pill Tagline */
        .better-tagline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 24px;
        }
        .better-tagline .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            display: inline-block;
            box-shadow: 0 0 8px #10b981;
        }

        /* Bold BetterCV Headline */
        .better-title {
            font-size: 56px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            color: #1e293b;
            margin-bottom: 24px;
        }
        .better-title span {
            color: #0284c7;
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .better-desc {
            font-size: 18px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 35px;
            max-width: 540px;
        }

        /* BetterCV Dual CTA Buttons */
        .better-buttons {
            display: flex;
            gap: 16px;
            margin-bottom: 45px;
            flex-wrap: wrap;
        }
        
        .btn-better-primary {
            background: #0284c7;
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            padding: 14px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
            transition: var(--transition);
        }
        .btn-better-primary:hover {
            background: #0369a1;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.3);
        }
        
        .btn-better-secondary {
            background: #ffffff;
            border: 1.5px solid #0284c7;
            color: #0284c7;
            font-size: 16px;
            font-weight: 700;
            padding: 14px 30px;
            border-radius: 8px;
            transition: var(--transition);
        }
        .btn-better-secondary:hover {
            background: rgba(2, 132, 199, 0.03);
            transform: translateY(-1px);
        }

        /* Highlight Stats row */
        .better-stats-row {
            display: flex;
            gap: 40px;
            border-top: 1px solid #f1f5f9;
            padding-top: 30px;
        }

        .better-stat-box {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .better-stat-badge {
            font-size: 24px;
            font-weight: 800;
            line-height: 1;
            display: inline-flex;
            align-items: center;
        }
        .stat-green { color: #10b981; }
        .stat-amber { color: #d97706; }

        .better-stat-label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
        }

        /* Right Side: Realistic Floating Resume Showcase */
        .resume-showcase-container {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* White Resume Sheet Card */
        .resume-sheet {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            width: 100%;
            max-width: 440px;
            padding: 30px 24px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.08);
            position: relative;
            z-index: 2;
            transition: var(--transition);
        }
        
        /* Resume Header layout */
        .resume-sheet-header {
            display: flex;
            gap: 16px;
            align-items: center;
            border-bottom: 1.5px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .resume-sheet-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            background: #f1f5f9;
        }

        .resume-sheet-meta h3 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 2px;
        }
        
        .resume-sheet-meta p {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0;
        }

        /* Fake Resume Details */
        .resume-section-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #3b82f6;
            margin-bottom: 8px;
        }

        .resume-paragraph {
            font-size: 11px;
            line-height: 1.5;
            color: #475569;
            margin-bottom: 16px;
        }

        .resume-experience-item {
            margin-bottom: 16px;
        }

        .resume-experience-title {
            font-size: 11px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
        }

        /* Color Dots Floating Selector */
        .floating-colors-bar {
            position: absolute;
            left: -30px;
            top: 50%;
            transform: translateY(-50%);
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 100px;
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
            z-index: 3;
        }

        .color-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .color-dot:hover {
            transform: scale(1.2);
        }
        .dot-1 { background-color: #fda4af; } /* pink */
        .dot-2 { background-color: #c084fc; } /* purple */
        .dot-3 { background-color: #cbd5e1; } /* grey */
        .dot-4 { background-color: #fed7aa; } /* orange */
        .dot-5 { background-color: #38bdf8; border: 2px solid #0284c7; } /* active blue */

        /* Floating green ATS Perfect badge */
        .floating-ats-badge {
            position: absolute;
            left: -20px;
            bottom: 40px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            color: #16a34a;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.08);
            z-index: 3;
        }

        /* Floating Download PDF/Word Icons */
        .floating-downloads {
            position: absolute;
            right: -20px;
            top: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 3;
        }

        .download-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
            transition: var(--transition);
            text-decoration: none;
        }
        .download-icon-btn:hover {
            border-color: #3b82f6;
            transform: scale(1.05);
        }

        /* Floating AI-powered ideas bubble */
        .floating-ai-ideas {
            position: absolute;
            right: -40px;
            bottom: -30px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            width: 250px;
            padding: 16px;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
            z-index: 3;
            animation: ideaFloat 5s infinite ease-in-out;
        }
        @keyframes ideaFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .ai-ideas-header {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 800;
            color: #0284c7;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .ai-idea-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 11px;
            line-height: 1.4;
            color: #334155;
            margin-bottom: 10px;
        }
        .ai-idea-item:last-child {
            margin-bottom: 0;
        }

        .ai-idea-circle {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #0ea5e9;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 1px;
            font-size: 8px;
            font-weight: 900;
        }

        /* General Features Overhaul style */
        .section-tag {
            color: #0284c7;
            text-transform: uppercase;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.15em;
            margin-bottom: 12px;
            display: block;
        }

        .section-title-wrapper {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 60px auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 80px;
        }

        .feature-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 40px 30px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.02);
            transition: var(--transition);
        }
        
        .feature-item:hover {
            border-color: rgba(2, 132, 199, 0.2);
            background: #f8fafc;
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.04);
        }

        .feature-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            background: rgba(2, 132, 199, 0.08);
            border: 1px solid rgba(2, 132, 199, 0.15);
            color: #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            font-size: 22px;
        }

        /* How it works light timeline */
        .steps-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 80px;
        }

        .step-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 35px 25px;
            position: relative;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.02);
            transition: var(--transition);
        }
        
        .step-item:hover {
            border-color: rgba(2, 132, 199, 0.15);
            transform: translateY(-4px);
        }

        .step-number {
            position: absolute;
            top: -20px;
            left: 24px;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #fff;
            box-shadow: 0 4px 10px rgba(2, 132, 199, 0.2);
            font-size: 16px;
        }

        /* Light CTA section */
        .cta-wrapper {
            background: linear-gradient(135deg, rgba(2, 132, 199, 0.04) 0%, rgba(14, 165, 233, 0.04) 100%);
            border: 1px solid rgba(2, 132, 199, 0.15);
            border-radius: 16px;
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 100px;
            box-shadow: 0 15px 40px rgba(2, 132, 199, 0.04);
        }

        /* Footer styling */
        footer {
            border-top: 1px solid #e2e8f0;
            padding: 40px 0;
            background: #ffffff;
        }

        /* Responsiveness adjustments */
        @media (max-width: 992px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 70px;
                text-align: center;
            }
            .better-buttons {
                justify-content: center;
            }
            .better-stats-row {
                justify-content: center;
            }
            .better-desc {
                margin-left: auto;
                margin-right: auto;
            }
            .resume-showcase-container {
                max-width: 440px;
                margin: 0 auto;
            }
            .floating-colors-bar {
                left: -20px;
            }
            .features-grid {
                grid-template-columns: 1fr;
            }
            .steps-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 40px 24px;
            }
        }
        
        @media (max-width: 600px) {
            .better-title {
                font-size: 38px;
            }
            .better-stats-row {
                flex-direction: column;
                gap: 20px;
                align-items: center;
            }
            .steps-container {
                grid-template-columns: 1fr;
                gap: 35px 0;
            }
            .floating-colors-bar, .floating-ats-badge, .floating-ai-ideas, .floating-downloads {
                position: static;
                transform: none;
                margin: 15px auto;
                flex-direction: row;
                justify-content: center;
                width: auto;
                animation: none;
            }
        }
    </style>
</head>
<body>

    <!-- Header & Navigation -->
    <header>
        <div class="container nav-container">
            <a href="index.php" class="logo" id="header-logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 4px;">
                    <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="url(#logoGrad)" />
                    <path d="M2 17L12 22L22 17" stroke="url(#logoGrad)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M2 12L12 17L22 12" stroke="url(#logoGrad)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <defs>
                        <linearGradient id="logoGrad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#0284c7" />
                            <stop offset="1" stop-color="#0ea5e9" />
                        </linearGradient>
                    </defs>
                </svg>
                AI Resume Analyzer
            </a>
            
            <nav class="nav-links">
                <a href="#features" class="nav-link">Features</a>
                <a href="#how-it-works" class="nav-link">How It Works</a>
                
                <?php if ($is_candidate): ?>
                    <a href="dashboard.php" class="btn btn-secondary btn-sm" id="nav-btn-dashboard">Go to Dashboard</a>
                    <a href="login.php?logout=1" class="btn btn-danger btn-sm" id="nav-btn-logout">Log Out</a>
                <?php elseif ($is_admin): ?>
                    <a href="admin_dashboard.php" class="btn btn-secondary btn-sm" id="nav-btn-admin-dashboard">Recruiter Portal</a>
                    <a href="login.php?logout=1" class="btn btn-danger btn-sm" id="nav-btn-admin-logout">Log Out</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link" id="nav-link-login">Candidate Log In</a>
                    <a href="register.php" class="btn btn-primary btn-sm" id="nav-btn-register" style="padding: 8px 18px;">Build My Resume</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="hero-container">
        <!-- Glowing background decorations -->
        <div class="glow-bg glow-1"></div>
        <div class="glow-bg glow-2"></div>
        
        <div class="container">
            <!-- Hero BetterCV Replicated Grid -->
            <section class="hero-grid" aria-label="Resume Intro Section">
                <div>
                    <div class="better-tagline">
                        <span class="dot"></span>
                        <span>48,797 Resumes Scanned Today</span>
                    </div>
                    
                    <h1 class="better-title" id="main-hero-title">
                        Create your Resume with an <span>AI-powered Resume Analyzer</span>
                    </h1>
                    
                    <p class="better-desc">
                        The first step to a better job? A perfect resume. Only 2% of resumes win ATS screening, and yours will be one of them. Scan, identify skill gaps, and practice live interviews tailored to your experience now!
                    </p>
                    
                    <div class="better-buttons">
                        <?php if ($is_candidate): ?>
                            <a href="dashboard.php" class="btn-better-primary" style="text-decoration: none;" id="hero-btn-dashboard">Enter Dashboard &rarr;</a>
                            <a href="#features" class="btn-better-secondary" style="text-decoration: none;" id="hero-btn-explore">Explore Features</a>
                        <?php elseif ($is_admin): ?>
                            <a href="admin_dashboard.php" class="btn-better-primary" style="text-decoration: none;" id="hero-btn-admin">Access Recruiter Console &rarr;</a>
                        <?php else: ?>
                            <a href="register.php" class="btn-better-primary" style="text-decoration: none;" id="hero-btn-signup">Create a New Resume</a>
                            <a href="login.php" class="btn-better-secondary" style="text-decoration: none;" id="hero-btn-improve">Improve My Resume</a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Highlight Stat Badges -->
                    <div class="better-stats-row">
                        <div class="better-stat-box">
                            <span class="better-stat-badge stat-green">48%</span>
                            <span class="better-stat-label">more likely to get hired</span>
                        </div>
                        <div class="better-stat-box">
                            <span class="better-stat-badge stat-amber">12%</span>
                            <span class="better-stat-label">better pay with your next job</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Replicating the Floating Resume Mock UI -->
                <div class="resume-showcase-container">
                    
                    <!-- Color dot floating picker -->
                    <div class="floating-colors-bar">
                        <span class="color-dot dot-1"></span>
                        <span class="color-dot dot-2"></span>
                        <span class="color-dot dot-3"></span>
                        <span class="color-dot dot-4"></span>
                        <span class="color-dot dot-5"></span>
                    </div>

                    <!-- PDF/Word floating actions -->
                    <div class="floating-downloads">
                        <a href="#" class="download-icon-btn" onclick="return false;" title="Download PDF" style="font-size: 11px; font-weight: 800; color: #ef4444;">PDF</a>
                        <a href="#" class="download-icon-btn" onclick="return false;" title="Download Word Doc" style="font-size: 11px; font-weight: 800; color: #3b82f6;">DOC</a>
                    </div>

                    <!-- ATS Perfect floating tag -->
                    <div class="floating-ats-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>ATS Perfect</span>
                    </div>

                    <!-- White mock resume sheet -->
                    <div class="resume-sheet" id="better-mockup-resume">
                        <div class="resume-sheet-header">
                            <!-- Clean SVG avatar representing Samantha -->
                            <svg class="resume-sheet-avatar" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                <rect width="100%" height="100%" fill="#e0f2fe" />
                                <circle cx="50" cy="40" r="20" fill="#0284c7" />
                                <path d="M20 85 C20 65, 80 65, 80 85" fill="#0284c7" />
                            </svg>
                            <div class="resume-sheet-meta">
                                <h3>Samantha Williams</h3>
                                <p>Senior Analyst</p>
                            </div>
                        </div>

                        <div class="resume-section-title">Professional Summary</div>
                        <p class="resume-paragraph">
                            Senior Analyst with 5+ years of experience in data analysis, business intelligence, and process optimization. Skilled in driving operational efficiency, forecasting, and leading data-driven strategies to support business decisions.
                        </p>

                        <div class="resume-section-title">Experience</div>
                        
                        <div class="resume-experience-item">
                            <div class="resume-experience-title">
                                <span>Senior Analyst</span>
                                <span style="color: #64748b; font-weight: 500;">2023 - Present</span>
                            </div>
                            <p style="font-size: 10px; color: #64748b; font-weight: 600; margin-bottom: 4px;">Loom & Larsson Co. — New York, NY</p>
                            <p class="resume-paragraph" style="margin-bottom: 0;">
                                • Synthesized data analysis and reporting for key business functions, identifying trends and providing insights to improve company performance.
                            </p>
                        </div>
                    </div>

                    <!-- AI-powered suggestion bubble on bottom right -->
                    <div class="floating-ai-ideas">
                        <div class="ai-ideas-header">
                            <!-- SVG Sparkle icon -->
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                            <span>AI-powered ideas</span>
                        </div>
                        <div class="ai-idea-item">
                            <span class="ai-idea-circle">&rarr;</span>
                            <span>Analyzed market trends to identify new growth opportunities.</span>
                        </div>
                        <div class="ai-idea-item">
                            <span class="ai-idea-circle">&rarr;</span>
                            <span>Reduced operational costs by 15% through process optimization.</span>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>

    <!-- Core Features Grid -->
    <main class="container" id="features" style="padding-top: 80px;">
        <section class="section-title-wrapper" aria-labelledby="features-heading">
            <span class="section-tag">Intelligent Capabilities</span>
            <h2 class="section-title" id="features-heading" style="font-size: 34px;">What the AI Analyzer Can Do</h2>
            <p>Our comprehensive framework leverages advanced Large Language Models to evaluate applicants' capabilities, test critical thinking, and report detailed feedback instantly.</p>
        </section>

        <div class="features-grid">
            <!-- Feature 1 -->
            <div class="feature-item" id="feature-ats">
                <div class="feature-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
                <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 12px;">ATS Score Analysis</h3>
                <p style="font-size: 14px; line-height: 1.6; margin-bottom: 0;">Calculates database keyword scores and matches your details against recruiter filters to ensure your resume never gets lost in automated portals.</p>
            </div>

            <!-- Feature 2 -->
            <div class="feature-item" id="feature-skills">
                <div class="feature-icon-box" style="background: rgba(14, 165, 233, 0.08); border-color: rgba(14, 165, 233, 0.15); color: #0ea5e9;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                </div>
                <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 12px;">Skill Gap Finder</h3>
                <p style="font-size: 14px; line-height: 1.6; margin-bottom: 0;">Identifies both matching and missing professional keywords for your target role, giving you clear guidelines to optimize and update your technical skills.</p>
            </div>

            <!-- Feature 3 -->
            <div class="feature-item" id="feature-interview">
                <div class="feature-icon-box" style="background: rgba(99, 102, 241, 0.08); border-color: rgba(99, 102, 241, 0.15); color: #6366f1;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 12px;">Interactive AI Mock Chats</h3>
                <p style="font-size: 14px; line-height: 1.6; margin-bottom: 0;">Generates technical and behavioral questions specific to your work history. Conducts a fully interactive mock chat interview with real-time feedback scoring.</p>
            </div>
        </div>
    </main>

    <!-- How It Works Timeline -->
    <section class="container" id="how-it-works" aria-labelledby="how-it-works-heading">
        <div class="section-title-wrapper">
            <span class="section-tag">Intuitive Flow</span>
            <h2 class="section-title" id="how-it-works-heading" style="font-size: 34px;">Four Easy Steps to Mastery</h2>
            <p>Go from candidate profile creation to mock interview feedback in less than five minutes.</p>
        </div>

        <div class="steps-container">
            <!-- Step 1 -->
            <div class="step-item" id="step-upload">
                <span class="step-number">1</span>
                <h4 style="font-size: 16px; font-weight: 800; margin-top: 10px;">Upload Resume</h4>
                <p style="color: var(--text-muted);">Register your account and upload your professional experience in PDF format.</p>
            </div>

            <!-- Step 2 -->
            <div class="step-item" id="step-role">
                <span class="step-number">2</span>
                <h4 style="font-size: 16px; font-weight: 800; margin-top: 10px;">Define Target Role</h4>
                <p style="color: var(--text-muted);">Select your target job title (e.g. Web Engineer, Product Manager) to calibrate the AI model.</p>
            </div>

            <!-- Step 3 -->
            <div class="step-item" id="step-score">
                <span class="step-number">3</span>
                <h4 style="font-size: 16px; font-weight: 800; margin-top: 10px;">Review ATS Report</h4>
                <p style="color: var(--text-muted);">Analyze your automated ATS matching score, matching tech stacks, and core recommendations.</p>
            </div>

            <!-- Step 4 -->
            <div class="step-item" id="step-chat">
                <span class="step-number">4</span>
                <h4 style="font-size: 16px; font-weight: 800; margin-top: 10px;">Simulate Live Chat</h4>
                <p style="color: var(--text-muted);">Answer custom interview questions and receive instantaneous, constructive grading.</p>
            </div>
        </div>
    </section>

    <!-- Call to Action Banner -->
    <div class="container">
        <section class="cta-wrapper" aria-labelledby="cta-heading">
            <h2 id="cta-heading" style="font-size: 32px; font-weight: 800; color: #1e293b;">Ready to Secure Your Next Role?</h2>
            <p style="color: var(--text-muted); font-size: 16px;">Stop guessing how recruiters see your credentials. Get a full diagnostic evaluation and prepare yourself for hard technical discussions today.</p>
            
            <div>
                <?php if ($is_candidate): ?>
                    <a href="dashboard.php" class="btn-better-primary" style="text-decoration: none;" id="cta-btn-dashboard">Open Your Profile Dashboard &rarr;</a>
                <?php elseif ($is_admin): ?>
                    <a href="admin_dashboard.php" class="btn-better-primary" style="text-decoration: none;" id="cta-btn-admin-dashboard">Enter Recruiter Dashboard &rarr;</a>
                <?php else: ?>
                    <a href="register.php" class="btn-better-primary" style="text-decoration: none;" id="cta-btn-signup">Create Free Account</a>
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
                <a href="admin_login.php" class="footer-link" style="color: #0284c7; font-weight: 600;">Recruiter Console</a>
            </div>
        </div>
    </footer>

</body>
</html>
