<?php
// interview.php
// Interactive AI Mock Interview page

require_once __DIR__ . '/auth.php';
requireLogin();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/AiHelper.php';

$userId = getCurrentUserId();
$userName = getCurrentUserName();

$resumeId = isset($_GET['resume_id']) ? intval($_GET['resume_id']) : 0;
if (!$resumeId) {
    header("Location: dashboard.php");
    exit;
}

$db = getDB();

// 1. Fetch resume and verify ownership
$stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
$stmt->execute([$resumeId, $userId]);
$resume = $stmt->fetch();

if (!$resume) {
    header("Location: dashboard.php");
    exit;
}

// 2. Fetch or Generate 5 Interview Questions
$stmtQ = $db->prepare("SELECT * FROM interview_questions WHERE resume_id = ? ORDER BY id ASC");
$stmtQ->execute([$resumeId]);
$questions = $stmtQ->fetchAll();

if (empty($questions)) {
    // Generate questions using AI
    try {
        $qList = generateInterviewQuestions($resume['text_content'], $resume['target_role']);
        
        $stmtInsert = $db->prepare("INSERT INTO interview_questions (resume_id, question) VALUES (?, ?)");
        foreach ($qList as $qText) {
            $stmtInsert->execute([$resumeId, trim($qText)]);
        }
        
        // Re-fetch
        $stmtQ->execute([$resumeId]);
        $questions = $stmtQ->fetchAll();
    } catch (Exception $e) {
        $error = "Failed to generate interview questions: " . $e->getMessage();
    }
}

$error = '';
$success = '';

// 3. Handle Answer Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_answer') {
    $questionId = intval($_POST['question_id'] ?? 0);
    $userAnswer = trim($_POST['answer'] ?? '');
    
    if ($questionId && !empty($userAnswer)) {
        // Find the question to get the text
        $questionText = '';
        foreach ($questions as $q) {
            if (intval($q['id']) === $questionId) {
                $questionText = $q['question'];
                break;
            }
        }
        
        if (!empty($questionText)) {
            try {
                // Evaluate answer via AI
                $evaluation = evaluateInterviewAnswer($questionText, $userAnswer);
                $score = intval($evaluation['score'] ?? 5);
                $feedback = trim($evaluation['feedback'] ?? 'Good effort.');
                
                // Save answer in interview_answers
                $stmtAns = $db->prepare("INSERT INTO interview_answers (question_id, user_answer, score, feedback) VALUES (?, ?, ?, ?)");
                $stmtAns->execute([$questionId, $userAnswer, $score, $feedback]);
                
                // Mark question as answered in interview_questions
                $stmtUp = $db->prepare("UPDATE interview_questions SET answer = ? WHERE id = ?");
                $stmtUp->execute([$userAnswer, $questionId]);
                
                header("Location: interview.php?resume_id=" . $resumeId);
                exit;
            } catch (Exception $e) {
                $error = "Failed to evaluate your answer: " . $e->getMessage();
            }
        } else {
            $error = "Question not found.";
        }
    } else {
        $error = "Please type a valid response before submitting.";
    }
}

// 4. Load answered transcripts and find current pending question
$answeredQuestions = [];
$currentQuestion = null;
$currentQuestionNum = 0;

$stmtTrans = $db->prepare("
    SELECT q.id as question_id, q.question, q.answer, a.score, a.feedback 
    FROM interview_questions q
    LEFT JOIN interview_answers a ON a.question_id = q.id
    WHERE q.resume_id = ?
    ORDER BY q.id ASC
");
$stmtTrans->execute([$resumeId]);
$transcripts = $stmtTrans->fetchAll();

foreach ($transcripts as $idx => $t) {
    if ($t['answer'] !== null) {
        $answeredQuestions[] = $t;
    } else {
        if ($currentQuestion === null) {
            $currentQuestion = $t;
            $currentQuestionNum = $idx + 1;
        }
    }
}

$totalQuestionsCount = count($transcripts);
$answeredCount = count($answeredQuestions);
$isComplete = ($answeredCount === $totalQuestionsCount && $totalQuestionsCount > 0);

// Calculate overall score if complete
$avgScore = 0;
if ($isComplete) {
    $sumScore = 0;
    foreach ($answeredQuestions as $aq) {
        $sumScore += intval($aq['score']);
    }
    $avgScore = round($sumScore / $totalQuestionsCount, 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Mock Interview - AI Resume Analyzer</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Engage in a live, interactive, AI-driven mock interview tailored to your resume's technical and behavioral parameters.">
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
                <a href="dashboard.php" class="nav-link">&larr; Return to Dashboard</a>
                <a href="login.php?logout=1" class="btn btn-danger btn-sm" id="logout-btn">Log Out</a>
            </nav>
        </div>
    </header>

    <main class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 900px;">
        
        <?php if ($error): ?>
            <div class="alert alert-error" id="interview-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Interview Progress Header -->
        <section class="card" style="margin-bottom: 25px; padding: 20px 30px;" aria-labelledby="interview-summary-heading">
            <h2 id="interview-summary-heading" style="font-size: 20px; font-weight: 700; margin-bottom: 6px; color: #fff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <span>AI Mock Interview Session</span>
                <span style="font-size: 13px; color: var(--accent); font-weight: 600; text-transform: uppercase;">
                    Target: <?php echo htmlspecialchars($resume['target_role']); ?>
                </span>
            </h2>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">Candidate Resume: <strong><?php echo htmlspecialchars($resume['file_name']); ?></strong></p>
            
            <!-- Progress Bar -->
            <?php 
                $progPercent = $totalQuestionsCount > 0 ? round(($answeredCount / $totalQuestionsCount) * 100) : 0;
            ?>
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; font-weight: 600; margin-bottom: 6px;">
                <span>Session Completion Progress</span>
                <span style="color: var(--primary);"><?php echo $answeredCount; ?> / <?php echo $totalQuestionsCount; ?> Questions</span>
            </div>
            <div class="progress-bar-container" style="margin-bottom: 0;">
                <div class="progress-bar-fill" style="width: <?php echo $progPercent; ?>%;"></div>
            </div>
        </section>

        <!-- Dynamic Interview Content -->
        <?php if ($isComplete): ?>
            
            <!-- Overall evaluation report on complete -->
            <section class="card" aria-labelledby="congrats-heading" style="animation: bubbleFadeIn 0.5s ease;">
                <div style="text-align: center; margin-bottom: 35px; border-bottom: 1px solid var(--border-color); padding-bottom: 30px;">
                    <span style="font-size: 60px; display: block; margin-bottom: 15px;">🎉</span>
                    <h2 id="congrats-heading" style="font-size: 28px; font-weight: 800; margin-bottom: 8px;">Mock Interview Completed!</h2>
                    <p style="max-width: 500px; margin: 0 auto 20px auto; color: var(--text-muted); font-size: 15px;">You have answered all 5 technical and behavioral questions based on your resume. Here is your overall artificial intelligence score card.</p>
                    
                    <?php 
                        $scoreClass = 'poor';
                        if ($avgScore >= 8.0) $scoreClass = 'excellent';
                        elseif ($avgScore >= 5.5) $scoreClass = 'good';
                    ?>
                    <div class="score-circle-wrapper" style="transform: scale(1.1); margin: 25px 0;">
                        <div class="score-circle <?php echo $scoreClass; ?>">
                            <span class="score-num"><?php echo $avgScore; ?>/10</span>
                            <span class="score-lbl">Avg Score</span>
                        </div>
                    </div>

                    <a href="dashboard.php?resume_id=<?php echo $resumeId; ?>" class="btn btn-primary" id="btn-done-back" style="margin-top: 10px;">Return to Dashboard</a>
                </div>

                <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    Question-by-Question Transcript Report
                </h3>
                
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <?php foreach ($answeredQuestions as $idx => $t): ?>
                        <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 20px;<?php if ($idx === count($answeredQuestions)-1) echo 'border-bottom: none; padding-bottom: 0;'; ?>">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <strong style="font-size: 13px; color: var(--accent);">Question #<?php echo ($idx + 1); ?></strong>
                                <span class="badge" style="background-color: <?php echo ($t['score'] >= 8) ? 'var(--success-bg)' : (($t['score'] >= 5.5) ? 'var(--warning-bg)' : 'var(--error-bg)'); ?>; color: <?php echo ($t['score'] >= 8) ? 'var(--success)' : (($t['score'] >= 5.5) ? 'var(--warning)' : 'var(--error)'); ?>; padding: 2px 8px; font-size: 11px;">
                                    Score: <?php echo $t['score']; ?>/10
                                </span>
                            </div>
                            <p style="font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 6px; line-height: 1.4;">Q: "<?php echo htmlspecialchars($t['question']); ?>"</p>
                            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px; padding-left: 14px; border-left: 2px solid rgba(255,255,255,0.08); line-height: 1.5;"><strong style="color: rgba(255,255,255,0.8);">Your Answer:</strong> "<?php echo htmlspecialchars($t['answer']); ?>"</p>
                            <div style="background: rgba(6, 182, 212, 0.04); border: 1px solid rgba(6, 182, 212, 0.1); border-radius: 6px; padding: 12px 16px; font-size: 12px; line-height: 1.5; color: var(--accent);">
                                <strong style="font-weight: 700; color: #22d3ee;">AI Feedback:</strong> <?php echo htmlspecialchars($t['feedback']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php else: ?>
            
            <!-- Live Active Chat Window -->
            <section class="chat-container" id="chat-window-box" aria-label="Mock Interview Chat window">
                <!-- Chat message history scroll -->
                <div class="chat-messages" id="chat-messages-scroll">
                    <!-- Standard Welcome Bubble -->
                    <div class="chat-bubble chat-bubble-ai">
                        <p style="margin: 0;">Hello <strong><?php echo htmlspecialchars($userName); ?></strong>! I have parsed your resume and generated 5 tailor-made interview questions for the role of <strong><?php echo htmlspecialchars($resume['target_role']); ?></strong>.</p>
                        <p style="margin: 5px 0 0 0;">Please answer each question to the best of your ability. We will score your response and provide instant structural feedback.</p>
                    </div>

                    <!-- Answered Questions Feed -->
                    <?php foreach ($answeredQuestions as $idx => $aq): ?>
                        <!-- Q -->
                        <div class="chat-bubble chat-bubble-ai">
                            <span class="score-tag" style="background: var(--primary);">Question <?php echo ($idx + 1); ?></span>
                            <p style="margin: 0; font-weight: 500;"><?php echo htmlspecialchars($aq['question']); ?></p>
                        </div>
                        <!-- A -->
                        <div class="chat-bubble chat-bubble-user">
                            <p style="margin: 0;"><?php echo htmlspecialchars($aq['answer']); ?></p>
                        </div>
                        <!-- Feedback -->
                        <div class="chat-bubble chat-bubble-ai">
                            <span class="score-tag" style="background-color: <?php echo ($aq['score'] >= 8) ? 'var(--success)' : (($aq['score'] >= 5) ? 'var(--warning)' : 'var(--error)'); ?>;">Grade: <?php echo $aq['score']; ?>/10</span>
                            <p style="margin: 0; font-weight: 500; font-size: 13px; color: var(--accent); text-transform: uppercase;">AI RESPONSE FEEDBACK</p>
                            <p style="margin: 4px 0 0 0; font-size: 13px; line-height: 1.4; color: var(--text-main);"><?php echo htmlspecialchars($aq['feedback']); ?></p>
                        </div>
                    <?php endforeach; ?>

                    <!-- Current Active Question -->
                    <?php if ($currentQuestion): ?>
                        <div class="chat-bubble chat-bubble-ai" style="border-color: var(--primary-glow); background: rgba(168, 85, 247, 0.02);">
                            <span class="score-tag" style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); font-weight: 800;">ACTIVE QUESTION <?php echo $currentQuestionNum; ?> OF 5</span>
                            <p style="margin: 0; font-weight: 600; font-size: 15px; color: #fff; line-height: 1.4;"><?php echo htmlspecialchars($currentQuestion['question']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Input area for answering -->
                <?php if ($currentQuestion): ?>
                    <form action="interview.php?resume_id=<?php echo $resumeId; ?>" method="POST" class="chat-input-area" id="interview-form">
                        <input type="hidden" name="action" value="submit_answer">
                        <input type="hidden" name="question_id" value="<?php echo $currentQuestion['question_id']; ?>">
                        
                        <input name="answer" class="form-control" placeholder="Type your detailed interview answer here... (be thorough to earn a high score!)" required autocomplete="off" id="chat-input-text">
                        <button type="submit" class="btn btn-primary" id="btn-chat-send" style="height: 48px; padding: 0 24px;">Send Answer</button>
                    </form>
                <?php endif; ?>
            </section>

            <!-- Small script to keep scroll at bottom of chat -->
            <script>
                window.onload = function() {
                    const scrollContainer = document.getElementById('chat-messages-scroll');
                    if (scrollContainer) {
                        scrollContainer.scrollTop = scrollContainer.scrollHeight;
                    }
                    const inputText = document.getElementById('chat-input-text');
                    if (inputText) {
                        inputText.focus();
                    }
                };
            </script>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container footer-grid" style="justify-content: center;">
            <p>&copy; <?php echo date('Y'); ?> AI Resume Analyzer. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
