<?php
// includes/header.php – common header and start of body
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../auth.php';

// Support dynamic titles and descriptions
$pageTitle = isset($pageTitle) ? $pageTitle : 'AI Resume Analyzer';
$pageDesc = isset($pageDesc) ? $pageDesc : 'Ultra-premium AI resume score calculation and interview preparation portal.';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Core Glassmorphism Dark Theme Stylesheet -->
    <link href="<?= BASE_URL ?>style.css" rel="stylesheet">
</head>
<body>
    <!-- Top Navigation Header Bar -->
    <header>
        <div class="container nav-container">
            <!-- Gradient Logo Icon & Text Link -->
            <a href="<?= BASE_URL ?>index.php" class="logo">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="url(#headerLogoGrad)" />
                    <path d="M2 17L12 22L22 17" stroke="url(#headerLogoGrad)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M2 12L12 17L22 12" stroke="url(#headerLogoGrad)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <defs>
                        <linearGradient id="headerLogoGrad" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#a855f7" />
                            <stop offset="1" stop-color="#06b6d4" />
                        </linearGradient>
                    </defs>
                </svg>
                AI Resume Analyzer
            </a>
            
            <!-- Conditional Navigation Links -->
            <nav class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <span style="color: var(--text-muted); font-size: 14.5px; font-weight: 500;">
                        Welcome, <strong style="color: var(--text-dark); text-shadow: var(--text-glow);"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Candidate') ?></strong>
                    </span>
                    <a href="<?= BASE_URL ?>dashboard.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">Dashboard</a>
                    <a href="<?= BASE_URL ?>login.php?logout=1" class="btn btn-danger btn-sm" id="logout-btn">Log Out</a>
                <?php elseif (isAdminLoggedIn()): ?>
                    <span style="color: var(--text-muted); font-size: 14.5px; font-weight: 500;">
                        Console: <strong style="color: var(--secondary);"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Recruiter') ?></strong>
                    </span>
                    <a href="<?= BASE_URL ?>admin_dashboard.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') ? 'active' : '' ?>">Recruiter Portal</a>
                    <a href="<?= BASE_URL ?>login.php?logout=1" class="btn btn-danger btn-sm" id="admin-logout-btn">Log Out</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : '' ?>">Login</a>
                    <a href="<?= BASE_URL ?>register.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'active' : '' ?>">Register</a>
                    <a href="<?= BASE_URL ?>admin_login.php" class="btn btn-secondary btn-sm" style="border-radius: var(--radius-sm); font-weight: 700;">Recruiter Portal</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
