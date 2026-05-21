<?php
// dashboard.php
// Candidate Dashboard page

require_once __DIR__ . '/auth.php';
requireLogin();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/PdfHelper.php';
require_once __DIR__ . '/AiHelper.php';

$userId = getCurrentUserId();
$userName = getCurrentUserName();

$error = '';
$success = '';

// Handle File Upload / Parsing / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'upload';
    
    if ($action === 'update_resume') {
        $resumeId = intval($_POST['resume_id'] ?? 0);
        $targetRole = trim($_POST['target_role'] ?? '');
        $textContent = trim($_POST['text_content'] ?? '');
        
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
        $stmt->execute([$resumeId, $userId]);
        $resumeRecord = $stmt->fetch();
        
        if (!$resumeRecord) {
            $error = "Resume not found or access denied.";
        } elseif (empty($targetRole)) {
            $error = "Target job role is required.";
        } elseif (empty($textContent)) {
            $error = "Resume text content is required.";
        } else {
            $fileName = $resumeRecord['file_name'];
            $filePath = $resumeRecord['file_path'];
            
            // Check if user uploaded a new PDF to overwrite this resume
            if (isset($_FILES['resume_pdf']) && $_FILES['resume_pdf']['error'] === UPLOAD_ERR_OK) {
                $uploadFileName = basename($_FILES['resume_pdf']['name']);
                $fileSize = $_FILES['resume_pdf']['size'];
                $fileTmpPath = $_FILES['resume_pdf']['tmp_name'];
                $fileExt = strtolower(pathinfo($uploadFileName, PATHINFO_EXTENSION));
                
                if ($fileExt !== 'pdf') {
                    $error = "Only PDF files are supported.";
                } elseif ($fileSize > 5 * 1024 * 1024) {
                    $error = "The file exceeds the maximum size limit of 5MB.";
                } else {
                    $uploadDir = __DIR__ . '/uploads';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Unique destination path
                    $uniqueName = uniqid('res_', true) . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $uploadFileName);
                    $destPath = $uploadDir . '/' . $uniqueName;
                    
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        try {
                            $extractedText = extractTextFromPdf($destPath);
                            if (empty(trim($extractedText))) {
                                $error = "Could not extract text from the PDF. Please make sure the PDF is not scanned or protected.";
                                unlink($destPath);
                            } else {
                                // Delete old file if it exists
                                $oldFilePath = __DIR__ . '/' . $resumeRecord['file_path'];
                                if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                                    @unlink($oldFilePath);
                                }
                                $textContent = $extractedText;
                                $fileName = $uploadFileName;
                                $filePath = 'uploads/' . $uniqueName;
                            }
                        } catch (Exception $e) {
                            $error = "Failed to parse the PDF: " . $e->getMessage();
                            @unlink($destPath);
                        }
                    } else {
                        $error = "Failed to save the uploaded file. Please verify directory permissions.";
                    }
                }
            }
            
            if (empty($error)) {
                try {
                    // Re-run AI analysis
                    $analysis = analyzeResume($textContent, $targetRole);
                    $atsScore = $analysis['ats_score'] ?? 0;
                    $analysisJson = json_encode($analysis);
                    
                    // Update in database
                    $stmtUpdate = $db->prepare("UPDATE resumes SET file_name = ?, file_path = ?, target_role = ?, text_content = ?, ats_score = ?, analysis_result = ?, status = 'Review' WHERE id = ?");
                    $stmtUpdate->execute([
                        $fileName,
                        $filePath,
                        $targetRole,
                        $textContent,
                        $atsScore,
                        $analysisJson,
                        $resumeId
                    ]);
                    
                    // Delete existing skills
                    $stmtDelSkills = $db->prepare("DELETE FROM skills WHERE resume_id = ?");
                    $stmtDelSkills->execute([$resumeId]);
                    
                    // Insert updated skills
                    if (!empty($analysis['matched_skills'])) {
                        $stmtSkill = $db->prepare("INSERT INTO skills (resume_id, skill_name, match_status) VALUES (?, ?, 'matched')");
                        foreach ($analysis['matched_skills'] as $skill) {
                            $stmtSkill->execute([$resumeId, trim($skill)]);
                        }
                    }
                    if (!empty($analysis['missing_skills'])) {
                        $stmtSkill = $db->prepare("INSERT INTO skills (resume_id, skill_name, match_status) VALUES (?, ?, 'missing')");
                        foreach ($analysis['missing_skills'] as $skill) {
                            $stmtSkill->execute([$resumeId, trim($skill)]);
                        }
                    }
                    
                    // Delete old generated questions since the resume has changed
                    $stmtDelQuestions = $db->prepare("DELETE FROM interview_questions WHERE resume_id = ?");
                    $stmtDelQuestions->execute([$resumeId]);
                    
                    header("Location: dashboard.php?success=2&resume_id=" . $resumeId);
                    exit;
                } catch (Exception $e) {
                    $error = "Analysis update failed: " . $e->getMessage();
                }
            }
        }
    } else {
        // Standard Upload Logic
        $targetRole = trim($_POST['target_role'] ?? '');
        
        if (empty($targetRole)) {
            $error = "Target job role is required.";
        } elseif (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            $error = "Please select a valid resume PDF file to upload.";
        } else {
            $fileName = basename($_FILES['resume']['name']);
            $fileSize = $_FILES['resume']['size'];
            $fileTmpPath = $_FILES['resume']['tmp_name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if ($fileExt !== 'pdf') {
                $error = "Only PDF files are supported.";
            } elseif ($fileSize > 5 * 1024 * 1024) {
                $error = "The file exceeds the maximum size limit of 5MB.";
            } else {
                // Setup upload folder
                $uploadDir = __DIR__ . '/uploads';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Unique destination path
                $uniqueName = uniqid('res_', true) . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $fileName);
                $destPath = $uploadDir . '/' . $uniqueName;
                
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    try {
                        // 1. Extract plain text from PDF
                        $textContent = extractTextFromPdf($destPath);
                        
                        if (empty(trim($textContent))) {
                            $error = "Could not extract text from the PDF. Please make sure the PDF is not scanned or protected.";
                            // Remove invalid file
                            unlink($destPath);
                        } else {
                            // 2. Analyze with AI
                            $analysis = analyzeResume($textContent, $targetRole);
                            $atsScore = $analysis['ats_score'] ?? 0;
                            $analysisJson = json_encode($analysis);
                            
                            // 3. Save to database
                            $db = getDB();
                            $stmt = $db->prepare("INSERT INTO resumes (user_id, file_name, file_path, target_role, status, text_content, ats_score, analysis_result) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $userId,
                                $fileName,
                                'uploads/' . $uniqueName,
                                $targetRole,
                                'Review',
                                $textContent,
                                $atsScore,
                                $analysisJson
                            ]);
                            
                            $resumeId = $db->lastInsertId();
                            
                            // Save parsed skills
                            if (!empty($analysis['matched_skills'])) {
                                $stmtSkill = $db->prepare("INSERT INTO skills (resume_id, skill_name, match_status) VALUES (?, ?, 'matched')");
                                foreach ($analysis['matched_skills'] as $skill) {
                                    $stmtSkill->execute([$resumeId, trim($skill)]);
                                }
                            }
                            if (!empty($analysis['missing_skills'])) {
                                $stmtSkill = $db->prepare("INSERT INTO skills (resume_id, skill_name, match_status) VALUES (?, ?, 'missing')");
                                foreach ($analysis['missing_skills'] as $skill) {
                                    $stmtSkill->execute([$resumeId, trim($skill)]);
                                }
                            }
                            
                            header("Location: dashboard.php?success=1&resume_id=" . $resumeId);
                            exit;
                        }
                    } catch (Exception $e) {
                        $error = "Analysis failed: " . $e->getMessage();
                        if (file_exists($destPath)) {
                            unlink($destPath);
                        }
                    }
                } else {
                    $error = "Failed to save the uploaded file. Please verify directory permissions.";
                }
            }
        }
    }
}

// Fetch success query notices
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) {
        $success = "Resume uploaded and analyzed successfully!";
    } elseif ($_GET['success'] == 2) {
        $success = "Resume updated and re-analyzed successfully! Custom interview questions have been refreshed below.";
    }
}

// Fetch historical resumes
$db = getDB();
$stmt = $db->prepare("SELECT id, file_name, target_role, status, ats_score, uploaded_at FROM resumes WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$userId]);
$resumes = $stmt->fetchAll();

// Select active resume
$selectedResume = null;
$selectedResumeId = isset($_GET['resume_id']) ? intval($_GET['resume_id']) : null;

if ($selectedResumeId) {
    $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$selectedResumeId, $userId]);
    $selectedResume = $stmt->fetch();
} elseif (!empty($resumes)) {
    $selectedResumeId = $resumes[0]['id'];
    $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$selectedResumeId, $userId]);
    $selectedResume = $stmt->fetch();
}

$analysisData = null;
$questions = [];
if ($selectedResume) {
    $analysisData = json_decode($selectedResume['analysis_result'], true);
    
    // Fetch or generate interview questions
    $stmtQ = $db->prepare("SELECT * FROM interview_questions WHERE resume_id = ? ORDER BY id ASC");
    $stmtQ->execute([$selectedResume['id']]);
    $questions = $stmtQ->fetchAll();
    
    if (empty($questions) && !empty($selectedResume['text_content'])) {
        // Generate Q&A list
        $qaList = generateInterviewQuestions($selectedResume['text_content'], $selectedResume['target_role']);
        $stmtInsert = $db->prepare("INSERT INTO interview_questions (resume_id, question, answer) VALUES (?, ?, ?)");
        foreach ($qaList as $qa) {
            $stmtInsert->execute([$selectedResume['id'], trim($qa['question']), $qa['answer'] ?? '']);
        }
        // Re-fetch questions
        $stmtQ->execute([$selectedResume['id']]);
        $questions = $stmtQ->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Dashboard - AI Resume Analyzer</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Candidate profile portal to review ATS reports, check tech skill alignments, and study custom AI-generated interview preparation questions.">
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="container nav-container">
            <a href="index.php" class="logo">
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
                <span style="color: var(--text-muted); font-size: 14px; font-weight: 500;">Welcome, <strong style="color: #fff;"><?php echo htmlspecialchars($userName); ?></strong></span>
                <a href="index.php" class="nav-link">Home</a>
                <a href="login.php?logout=1" class="btn btn-danger btn-sm" id="logout-btn">Log Out</a>
            </nav>
        </div>
    </header>

    <main class="container" style="padding-top: 40px; padding-bottom: 60px;">
        
        <?php if ($error): ?>
            <div class="alert alert-error" id="dashboard-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" id="dashboard-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="grid grid-2" style="grid-template-columns: 1fr 1.8fr; align-items: start; gap: 30px;">
            
            <!-- Left Side Column: Upload Form & History List -->
            <div style="display: flex; flex-direction: column; gap: 30px;">
                
                <!-- Upload Card -->
                <section class="card" aria-labelledby="upload-heading">
                    <h2 class="card-title" id="upload-heading" style="margin-bottom: 15px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        Upload Resume
                    </h2>
                    
                    <form action="dashboard.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-label" for="target_role">Target Job Role</label>
                            <input type="text" name="target_role" id="target_role" class="form-control" placeholder="e.g. Senior Software Engineer" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Resume PDF File</label>
                            <div class="file-upload-wrapper">
                                <span class="file-upload-icon">📁</span>
                                <p style="margin-bottom: 5px; font-weight: 600; color: #fff;">Click to select your PDF</p>
                                <p style="font-size: 12px; margin-bottom: 0; color: var(--text-muted);">PDF formats only. Max 5MB.</p>
                                <input type="file" name="resume" accept=".pdf" required onchange="updateFileName(this)">
                            </div>
                            <p id="file-name-display" style="font-size: 13px; color: var(--accent); margin-top: 8px; text-align: center; font-weight: 600; display: none;"></p>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" id="btn-upload-submit" style="width: 100%; margin-top: 10px;">Upload & Analyze</button>
                    </form>
                </section>

                <!-- History List -->
                <section class="card" aria-labelledby="history-heading">
                    <h2 class="card-title" id="history-heading" style="margin-bottom: 15px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--secondary);"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Analysis History
                    </h2>
                    
                    <?php if (empty($resumes)): ?>
                        <p style="text-align: center; color: var(--text-muted); font-size: 14px; padding: 20px 0;">No resumes analyzed yet. Upload your first PDF to get started!</p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 12px; max-height: 380px; overflow-y: auto; padding-right: 4px;">
                            <?php foreach ($resumes as $res): ?>
                                <?php 
                                    $isActive = ($selectedResumeId == $res['id']); 
                                    $scoreColor = 'poor';
                                    if ($res['ats_score'] >= 80) $scoreColor = 'excellent';
                                    elseif ($res['ats_score'] >= 60) $scoreColor = 'good';
                                ?>
                                <a href="dashboard.php?resume_id=<?php echo $res['id']; ?>" class="card-history-item" style="display: flex; justify-content: space-between; align-items: center; text-decoration: none; padding: 12px 16px; background: <?php echo $isActive ? 'rgba(255,255,255,0.06)' : 'rgba(255,255,255,0.02)'; ?>; border: 1px solid <?php echo $isActive ? 'var(--primary-glow)' : 'var(--border-color)'; ?>; border-radius: var(--radius-sm); transition: var(--transition);">
                                    <div style="max-width: 75%;">
                                        <h4 style="font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($res['target_role']); ?></h4>
                                        <p style="font-size: 11px; color: var(--text-muted); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($res['file_name']); ?></p>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                        <span class="score-num-sm <?php echo $scoreColor; ?>" style="font-size: 14px; font-weight: 800; color: <?php echo ($scoreColor === 'excellent') ? 'var(--success)' : (($scoreColor === 'good') ? 'var(--warning)' : 'var(--error)'); ?>;"><?php echo $res['ats_score']; ?>%</span>
                                        <span style="font-size: 9px; color: var(--text-muted);"><?php echo date('d M y', strtotime($res['uploaded_at'])); ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Right Side Column: Detailed Analysis Report View -->
            <section class="card" style="min-height: 600px;" aria-labelledby="report-heading">
                <?php if (!$selectedResume): ?>
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; padding: 40px 20px;">
                        <span style="font-size: 50px; margin-bottom: 20px; display: block; opacity: 0.5;">📊</span>
                        <h2 id="report-heading" style="font-size: 22px; font-weight: 700; margin-bottom: 10px;">Select or Upload a Resume</h2>
                        <p style="max-width: 400px; color: var(--text-muted);">Please select a previous analysis report from the list on the left or upload a brand-new resume file to calculate your ATS scores.</p>
                    </div>
                <?php else: ?>
                    <div id="report-view-container">
                        <h2 class="card-title" id="report-heading" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                            <span style="display: flex; align-items: center; gap: 10px;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                Analysis Report Details
                            </span>
                            <!-- Edit/Update Resume Link -->
                            <button type="button" class="btn btn-secondary btn-sm" id="btn-edit-resume" onclick="toggleEditForm(true)" style="background: #ffffff; color: var(--primary); border: 1.5px solid var(--primary); font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"></path></svg>
                                Edit / Update Resume
                            </button>
                        </h2>

                        <!-- Top Summary Statistics -->
                        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-bottom: 35px; align-items: center;">
                            <!-- Score Circle -->
                            <?php 
                                $ats = intval($selectedResume['ats_score']);
                                $circleClass = 'poor';
                                if ($ats >= 80) $circleClass = 'excellent';
                                elseif ($ats >= 60) $circleClass = 'good';
                            ?>
                            <div class="score-circle-wrapper" style="margin: 0;">
                                <div class="score-circle <?php echo $circleClass; ?>">
                                    <span class="score-num"><?php echo $ats; ?>%</span>
                                    <span class="score-lbl">ATS Score</span>
                                </div>
                            </div>

                            <!-- Technical info metadata -->
                            <div>
                                <h3 style="font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 6px;"><?php echo htmlspecialchars($selectedResume['target_role']); ?></h3>
                                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                    <strong style="color: #fff;">File:</strong> <?php echo htmlspecialchars($selectedResume['file_name']); ?>
                                </p>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <span class="badge badge-<?php echo strtolower($selectedResume['status']); ?>">
                                        Status: <?php echo htmlspecialchars($selectedResume['status']); ?>
                                    </span>
                                    <span style="font-size: 12px; color: var(--text-muted);">
                                        Scanned on <?php echo date('F d, Y at H:i', strtotime($selectedResume['uploaded_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Skills gap matches tabs -->
                        <div style="margin-bottom: 30px;">
                            <h4 style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 12l10 5 10-5M2 17l10 5 10-5"></path></svg>
                                Key Skills Mapping
                            </h4>
                            
                            <div style="background: rgba(255,255,255,0.015); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 18px;">
                                <div style="margin-bottom: 15px;">
                                    <p style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--success); letter-spacing: 0.05em; margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        Matched Keywords (<?php echo count($analysisData['matched_skills'] ?? []); ?>)
                                    </p>
                                    <div class="skill-tag-list" style="margin-top: 0;">
                                        <?php if (empty($analysisData['matched_skills'])): ?>
                                            <span style="font-size: 12px; color: var(--text-muted);">No key matched skills detected. Try adding explicit keywords in your resume.</span>
                                        <?php else: ?>
                                            <?php foreach ($analysisData['matched_skills'] as $skill): ?>
                                                <span class="skill-tag skill-tag-match"><?php echo htmlspecialchars($skill); ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <p style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--error); letter-spacing: 0.05em; margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        Missing Role Keywords (<?php echo count($analysisData['missing_skills'] ?? []); ?>)
                                    </p>
                                    <div class="skill-tag-list" style="margin-top: 0;">
                                        <?php if (empty($analysisData['missing_skills'])): ?>
                                            <span style="font-size: 12px; color: var(--success); font-weight: 600;">Perfect! All key target skills exist in your resume.</span>
                                        <?php else: ?>
                                            <?php foreach ($analysisData['missing_skills'] as $skill): ?>
                                                <span class="skill-tag skill-tag-missing"><?php echo htmlspecialchars($skill); ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Suggestions -->
                        <div style="margin-bottom: 30px;">
                            <h4 style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                Actionable Resume Suggestions
                            </h4>
                            <div style="background: rgba(255,255,255,0.015); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 18px;">
                                <?php if (empty($analysisData['suggestions'])): ?>
                                    <p style="font-size: 13px; color: var(--text-muted); margin: 0;">No suggestions needed. Your resume matches very well!</p>
                                <?php else: ?>
                                    <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: var(--text-muted); display: flex; flex-direction: column; gap: 8px;">
                                        <?php foreach ($analysisData['suggestions'] as $sug): ?>
                                            <li style="line-height: 1.5;"><strong style="color: #fff;">Improvement:</strong> <?php echo htmlspecialchars($sug); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Spelling, Typos & Formatting checks -->
                        <div style="margin-bottom: 30px;">
                            <h4 style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M22 12h-4"></path><path d="M6 12H2"></path><path d="M12 2v4"></path><path d="M12 18v4"></path></svg>
                                Spelling, Formatting & Grammar Audit
                            </h4>
                            <div style="background: rgba(255,255,255,0.015); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 18px;">
                                <?php if (empty($analysisData['grammar_issues'])): ?>
                                    <p style="font-size: 13px; color: var(--success); font-weight: 600; margin: 0; display: flex; align-items: center; gap: 4px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        Grammar audit clean! Zero grammatical or formatting issues detected.
                                    </p>
                                <?php else: ?>
                                    <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: var(--text-muted); display: flex; flex-direction: column; gap: 8px;">
                                        <?php foreach ($analysisData['grammar_issues'] as $issue): ?>
                                            <li style="line-height: 1.5;"><span style="color: #fda4af;">⚠</span> <?php echo htmlspecialchars($issue); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- AI Generated Interview Questions preparation -->
                        <div>
                            <h4 style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                AI-Generated Interview Questions (Preparation Guide)
                            </h4>
                            <div style="background: rgba(255,255,255,0.015); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 18px;">
                                <?php if (empty($questions)): ?>
                                    <p style="font-size: 13px; color: var(--text-muted); margin: 0;">No interview questions generated yet or analysis in progress.</p>
                                <?php else: ?>
                                    <ul style="margin: 0; padding-left: 20px; font-size: 13.5px; color: var(--text-muted); display: flex; flex-direction: column; gap: 10px;">
                                        <?php foreach ($questions as $idx => $q): ?>
                                            <li style="line-height: 1.5; color: var(--text-main);">

                                                    <br><em style="color: var(--text-muted);">Answer: <?php echo htmlspecialchars($q['answer'] ?: 'No answer available.'); ?></em>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Toggleable Edit & Update Form Container -->
                    <div id="edit-form-container" style="display: none;">
                        <h2 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="display: flex; align-items: center; gap: 10px;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"></path></svg>
                                Edit & Update Resume Details
                            </span>
                            <button type="button" class="btn btn-danger btn-sm" onclick="toggleEditForm(false)">Cancel</button>
                        </h2>
                        
                        <form action="dashboard.php?resume_id=<?php echo $selectedResume['id']; ?>" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_resume">
                            <input type="hidden" name="resume_id" value="<?php echo $selectedResume['id']; ?>">
                            
                            <div class="form-group">
                                <label class="form-label" for="update_target_role">Target Job Role</label>
                                <input type="text" name="target_role" id="update_target_role" class="form-control" value="<?php echo htmlspecialchars($selectedResume['target_role']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="update_text_content">Resume Text Content</label>
                                <textarea name="text_content" id="update_text_content" class="form-control" rows="12" style="font-family: monospace; font-size: 13px; line-height: 1.5; resize: vertical;" required><?php echo htmlspecialchars($selectedResume['text_content']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Overwrite with New PDF File (Optional)</label>
                                <div class="file-upload-wrapper" style="padding: 20px;">
                                    <span class="file-upload-icon" style="font-size: 24px; margin-bottom: 5px;">📁</span>
                                    <p style="margin-bottom: 5px; font-weight: 600; font-size: 13px; color: var(--text-dark);">Select a new PDF if you'd like to replace the resume text completely</p>
                                    <input type="file" name="resume_pdf" accept=".pdf" onchange="updateNewFileName(this)">
                                </div>
                                <p id="new-file-name-display" style="font-size: 13px; color: var(--accent); margin-top: 8px; text-align: center; font-weight: 600; display: none;"></p>
                            </div>
                            
                            <div style="display: flex; gap: 15px; margin-top: 25px;">
                                <button type="submit" class="btn btn-primary" style="flex: 1;">Save & Re-analyze</button>
                                <button type="button" class="btn btn-secondary" onclick="toggleEditForm(false)" style="flex: 0.5;">Cancel</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container footer-grid" style="justify-content: center;">
            <p>&copy; <?php echo date('Y'); ?> AI Resume Analyzer. All rights reserved.</p>
        </div>
    </footer>

    <!-- JS Helper for Upload UI and Toggle Edit Form -->
    <script>
        function updateFileName(input) {
            const display = document.getElementById('file-name-display');
            if (input.files && input.files[0]) {
                display.textContent = 'Selected file: ' + input.files[0].name;
                display.style.display = 'block';
            } else {
                display.style.display = 'none';
            }
        }
        
        function updateNewFileName(input) {
            const display = document.getElementById('new-file-name-display');
            if (input.files && input.files[0]) {
                display.textContent = 'Selected replacement file: ' + input.files[0].name;
                display.style.display = 'block';
            } else {
                display.style.display = 'none';
            }
        }
        
        function toggleEditForm(show) {
            const reportView = document.getElementById('report-view-container');
            const editForm = document.getElementById('edit-form-container');
            if (show) {
                reportView.style.display = 'none';
                editForm.style.display = 'block';
            } else {
                reportView.style.display = 'block';
                editForm.style.display = 'none';
            }
        }
    </script>
</body>
</html>
