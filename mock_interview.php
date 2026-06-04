<?php
// mock_interview.php - AI Mock Interview Practice Module
require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/AiHelper.php';

$userId = getCurrentUserId();
$db = getDB();

// Handle AJAX score submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    if ($action === 'submit_answer') {
        $questionId = intval($_POST['question_id'] ?? 0);
        $answer = trim($_POST['answer'] ?? '');
        
        if (empty($answer)) { echo json_encode(['error' => 'Answer is required']); exit; }
        
        // Fetch question
        $stmt = $db->prepare("SELECT question FROM interview_questions WHERE id=?");
        $stmt->execute([$questionId]);
        $q = $stmt->fetch();
        if (!$q) { echo json_encode(['error' => 'Question not found']); exit; }
        
        // Evaluate with AI
        $eval = evaluateInterviewAnswer($q['question'], $answer);
        
        // Save 
        $stmt2 = $db->prepare("INSERT INTO interview_answers (question_id, user_id, user_answer, score, feedback) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE user_answer=VALUES(user_answer), score=VALUES(score), feedback=VALUES(feedback)");
        $stmt2->execute([$questionId, $userId, $answer, $eval['score'], $eval['feedback']]);
        
        echo json_encode(['success' => true, 'score' => $eval['score'], 'feedback' => $eval['feedback']]);
        exit;
    }
    exit;
}

// Fetch user's analyzed resumes (with interview questions)
$stmt = $db->prepare("SELECT r.id, r.file_name, r.target_role, r.ats_score, COUNT(iq.id) as q_count FROM resumes r LEFT JOIN interview_questions iq ON iq.resume_id=r.id WHERE r.user_id=? GROUP BY r.id ORDER BY r.uploaded_at DESC");
$stmt->execute([$userId]);
$analyzedResumes = $stmt->fetchAll();

// Also fetch built resumes
$stmt2 = $db->prepare("SELECT id, title FROM resume_profiles WHERE user_id=? ORDER BY updated_at DESC");
$stmt2->execute([$userId]);
$builtResumes = $stmt2->fetchAll();

$selectedResumeId = isset($_GET['resume_id']) ? intval($_GET['resume_id']) : null;
$questions = [];
$interviewResume = null;
$userAnswers = [];
$totalScore = 0;
$answeredCount = 0;

if ($selectedResumeId) {
    $stmt = $db->prepare("SELECT * FROM resumes WHERE id=? AND user_id=?");
    $stmt->execute([$selectedResumeId, $userId]);
    $interviewResume = $stmt->fetch();
    
    if ($interviewResume) {
        $stmt = $db->prepare("SELECT * FROM interview_questions WHERE resume_id=? ORDER BY id ASC");
        $stmt->execute([$selectedResumeId]);
        $questions = $stmt->fetchAll();
        
        if (!empty($questions)) {
            // Get answers
            $ids = array_column($questions, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("SELECT question_id, user_answer, score, feedback FROM interview_answers WHERE question_id IN ($placeholders) AND user_id=?");
            $stmt->execute(array_merge($ids, [$userId]));
            foreach ($stmt->fetchAll() as $ans) {
                $userAnswers[$ans['question_id']] = $ans;
                $totalScore += $ans['score'];
                $answeredCount++;
            }
        }
    }
}

$avgScore = $answeredCount > 0 ? round($totalScore / $answeredCount, 1) : 0;
$interviewReadiness = $answeredCount > 0 ? min(100, intval(($avgScore / 10) * 100)) : 0;

$pageTitle = 'Mock Interview - AI Resume Analyzer';
include __DIR__ . '/includes/header.php';
?>

<style>
.interview-layout { display:grid; grid-template-columns:300px 1fr; gap:0; min-height:calc(100vh - 80px); }
.interview-sidebar { background:#111111; border-right:1px solid var(--border-color); padding:24px 16px; position:sticky; top:80px; height:calc(100vh - 80px); overflow-y:auto; }
.interview-main { padding:32px; }
.q-card { background:var(--bg-surface); border:1px solid var(--border-color); border-radius:12px; padding:24px; margin-bottom:20px; transition:var(--transition); }
.q-card:hover { border-color:rgba(255,255,255,0.15); box-shadow: var(--shadow-main); }
.q-badge { display:inline-flex; align-items:center; gap:6px; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin-bottom:12px; }
.q-badge.technical { background:rgba(255,255,255,0.06); color:#cccccc; border: 1px solid rgba(255,255,255,0.12); }
.q-badge.hr { background:rgba(200,200,200,0.06); color:#aaaaaa; border: 1px solid rgba(200,200,200,0.12); }
.q-badge.project { background:rgba(255,255,255,0.08); color:#e0e0e0; border: 1px solid rgba(255,255,255,0.15); }
.q-badge.behavioral { background:rgba(150,150,150,0.06); color:#888888; border: 1px solid rgba(150,150,150,0.12); }
.score-ring { width:80px; height:80px; position:relative; }
.score-ring svg { transform:rotate(-90deg); }
.score-ring .ring-label { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.score-ring .ring-num { font-size:20px; font-weight:900; }
.score-ring .ring-lbl { font-size:9px; color:var(--text-muted); font-weight:700; text-transform:uppercase; }
.readiness-bar { height:10px; background:rgba(255,255,255,.05); border-radius:999px; overflow:hidden; margin-top:8px; }
.readiness-fill { height:100%; background:linear-gradient(90deg,#ffffff,#888888); border-radius:999px; transition:width 1s ease; }
.answer-area { display:flex; flex-direction:column; gap:10px; margin-top:12px; }
.answer-textarea { width:100%; padding:14px; background:rgba(255,255,255,.03); border:1px solid var(--border-color); border-radius:8px; color:var(--text-dark); font-family:var(--font-sans); font-size:14px; resize:vertical; min-height:80px; transition:var(--transition); }
.answer-textarea:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-glow); }
.feedback-box { background:rgba(255,255,255,.03); border:1px solid var(--border-color); border-radius:8px; padding:16px; margin-top:12px; }
.feedback-score { display:inline-flex; align-items:center; gap:8px; margin-bottom:8px; }
.score-stars { color:#bbbbbb; font-size:18px; }
.q-nav-item { padding:10px 12px; border-radius:8px; cursor:pointer; font-size:13px; margin-bottom:4px; display:flex; justify-content:space-between; align-items:center; }
.q-nav-item:hover { background:rgba(255,255,255,.05); }
.q-nav-item.answered { border-left:3px solid var(--success); }
.q-nav-item.unanswered { border-left:3px solid var(--border-color); }
</style>

<div class="interview-layout">
    <!-- Sidebar -->
    <aside class="interview-sidebar">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:16px;color:var(--text-dark);">🎤 Mock Interview</h3>
        
        <?php if ($interviewResume && !empty($questions)): ?>
        <!-- Score Overview -->
        <div style="background:rgba(255,255,255,.03);border:1px solid var(--border-color);border-radius:10px;padding:16px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);">Interview Score</div>
                    <div style="font-size:28px;font-weight:900;color:<?= $avgScore >= 7 ? 'var(--success)' : ($avgScore >= 5 ? 'var(--warning)' : 'var(--error)') ?>;"><?= $avgScore ?>/10</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px;font-weight:700;color:var(--text-muted);">Answered</div>
                    <div style="font-size:22px;font-weight:900;"><?= $answeredCount ?>/<?= count($questions) ?></div>
                </div>
            </div>
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">Interview Readiness</div>
            <div class="readiness-bar"><div class="readiness-fill" style="width:<?= $interviewReadiness ?>%;"></div></div>
            <div style="font-size:12px;font-weight:700;text-align:right;margin-top:4px;color:var(--primary);"><?= $interviewReadiness ?>%</div>
        </div>
        
        <div style="margin-bottom:16px;">
            <h4 style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:10px;">Questions</h4>
            <?php foreach ($questions as $i => $q): ?>
                <div class="q-nav-item <?= isset($userAnswers[$q['id']]) ? 'answered' : 'unanswered' ?>" onclick="document.getElementById('q-<?= $q['id'] ?>').scrollIntoView({behavior:'smooth'})">
                    <span>Q<?= $i+1 ?>: <?= htmlspecialchars(substr($q['question'],0,35)) ?>...</span>
                    <?php if (isset($userAnswers[$q['id']])): ?><span style="color:var(--success);font-size:12px;font-weight:700;"><?= $userAnswers[$q['id']]['score'] ?>/10</span><?php else: ?><span style="color:var(--text-muted);font-size:11px;">New</span><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php else: ?>
        <!-- Select Resume -->
        <div>
            <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">Select a resume to start your mock interview session.</p>
            <?php if (!empty($analyzedResumes)): ?>
                <h4 style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:10px;">Analyzed Resumes</h4>
                <?php foreach ($analyzedResumes as $ar): ?>
                    <a href="mock_interview.php?resume_id=<?= $ar['id'] ?>" class="q-nav-item unanswered" style="display:block;text-decoration:none;color:var(--text-dark);">
                        <div style="font-size:13px;font-weight:600;"><?= htmlspecialchars($ar['target_role']) ?></div>
                        <div style="font-size:11px;color:var(--text-muted);"><?= $ar['q_count'] ?> questions</div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-error" style="font-size:13px;">No analyzed resumes yet. <a href="dashboard.php" style="color:var(--secondary);">Upload one first →</a></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($interviewResume): ?>
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border-color);">
            <a href="mock_interview.php" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;margin-bottom:8px;">← Change Resume</a>
            <a href="dashboard.php?resume_id=<?= $selectedResumeId ?>" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;">📊 View ATS Report</a>
        </div>
        <?php endif; ?>
    </aside>

    <!-- Main Content -->
    <main class="interview-main">
        <?php if (!$interviewResume): ?>
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:400px;text-align:center;">
            <div style="font-size:64px;margin-bottom:20px;">🎙️</div>
            <h2 style="font-size:28px;font-weight:900;margin-bottom:12px;">AI Mock Interview</h2>
            <p style="max-width:500px;color:var(--text-muted);font-size:15px;margin-bottom:24px;">Practice answering interview questions tailored to your resume. Get instant AI feedback and improve your score.</p>
            <?php if (empty($analyzedResumes)): ?>
                <a href="dashboard.php" class="btn btn-primary">📄 Upload & Analyze Resume First</a>
            <?php else: ?>
                <p style="color:var(--text-muted);">← Select a resume from the sidebar to begin</p>
            <?php endif; ?>
        </div>

        <?php elseif (empty($questions)): ?>
        <div style="text-align:center;padding:60px 20px;">
            <div style="font-size:48px;margin-bottom:16px;">⏳</div>
            <h3>Generating Interview Questions...</h3>
            <p style="color:var(--text-muted);">AI-powered questions are being created for your resume. <a href="dashboard.php?resume_id=<?= $selectedResumeId ?>" style="color:var(--secondary);">Go to the analyzer to trigger this process.</a></p>
        </div>

        <?php else: ?>
        <div style="margin-bottom:28px;">
            <h2 style="font-size:26px;font-weight:900;margin-bottom:4px;">Interview: <?= htmlspecialchars($interviewResume['target_role']) ?></h2>
            <p style="color:var(--text-muted);font-size:14px;">Answer each question as you would in a real interview. AI will evaluate and score your responses.</p>
        </div>

        <?php foreach ($questions as $i => $q): ?>
        <div class="q-card" id="q-<?= $q['id'] ?>">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
                <div>
                    <span class="q-badge <?= $q['category'] ?? 'technical' ?>"><?= ucfirst($q['category'] ?? 'Technical') ?></span>
                    <div style="font-size:15px;font-weight:700;color:var(--text-dark);line-height:1.5;">
                        <span style="color:var(--primary);font-weight:900;margin-right:6px;">Q<?= $i+1 ?>.</span><?= htmlspecialchars($q['question']) ?>
                    </div>
                </div>
                <?php if (isset($userAnswers[$q['id']])): ?>
                <div style="background:rgba(255,255,255,.05);border:1px solid var(--border-color);border-radius:8px;padding:6px 14px;text-align:center;flex-shrink:0;margin-left:16px;">
                    <div style="font-size:20px;font-weight:900;color:var(--text-dark);"><?= $userAnswers[$q['id']]['score'] ?></div>
                    <div style="font-size:10px;color:var(--text-muted);font-weight:700;">/ 10</div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($q['answer'])): ?>
            <details style="margin-bottom:12px;">
                <summary style="cursor:pointer;font-size:13px;color:var(--text-muted);padding:4px 0;">💡 View Suggested Answer</summary>
                <div style="background:rgba(255,255,255,.02);border:1px solid var(--border-color);border-radius:8px;padding:12px;margin-top:8px;font-size:13px;color:var(--text-main);line-height:1.65;"><?= htmlspecialchars($q['answer']) ?></div>
            </details>
            <?php endif; ?>
            
            <div class="answer-area" id="answer-area-<?= $q['id'] ?>">
                <textarea class="answer-textarea" id="answer-<?= $q['id'] ?>" placeholder="Type your answer here... Be specific, use the STAR method (Situation, Task, Action, Result) for behavioral questions."><?= htmlspecialchars($userAnswers[$q['id']]['user_answer'] ?? '') ?></textarea>
                <button onclick="submitAnswer(<?= $q['id'] ?>)" class="btn btn-primary btn-sm" id="btn-<?= $q['id'] ?>" style="align-self:flex-start;">✅ Submit & Get AI Feedback</button>
            </div>
            
            <?php if (isset($userAnswers[$q['id']])): ?>
            <div class="feedback-box" id="feedback-<?= $q['id'] ?>">
                <div class="feedback-score">
                    <span class="score-stars"><?= str_repeat('★', intval($userAnswers[$q['id']]['score'])) ?><?= str_repeat('☆', 10 - intval($userAnswers[$q['id']]['score'])) ?></span>
                    <span style="font-size:14px;font-weight:800;color:var(--primary);"><?= $userAnswers[$q['id']]['score'] ?>/10</span>
                </div>
                <p style="font-size:13px;color:var(--text-main);line-height:1.65;"><?= htmlspecialchars($userAnswers[$q['id']]['feedback'] ?? '') ?></p>
            </div>
            <?php else: ?>
            <div class="feedback-box" id="feedback-<?= $q['id'] ?>" style="display:none;"></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <!-- Overall Score Summary -->
        <?php if ($answeredCount >= 3): ?>
        <div class="card" style="margin-top:30px;text-align:center;background:rgba(255,255,255,.02);border-color:var(--border-color);">
            <h3 style="font-size:22px;margin-bottom:8px;">🏆 Interview Performance Summary</h3>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin:20px 0;">
                <div><div style="font-size:36px;font-weight:900;color:var(--primary);"><?= $avgScore ?>/10</div><div style="font-size:12px;color:var(--text-muted);">Average Score</div></div>
                <div><div style="font-size:36px;font-weight:900;color:var(--text-dark);"><?= $interviewReadiness ?>%</div><div style="font-size:12px;color:var(--text-muted);">Readiness</div></div>
                <div><div style="font-size:36px;font-weight:900;"><?= $answeredCount ?>/<?= count($questions) ?></div><div style="font-size:12px;color:var(--text-muted);">Completed</div></div>
            </div>
            <a href="dashboard.php?resume_id=<?= $selectedResumeId ?>" class="btn btn-primary">View Full ATS Report →</a>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </main>
</div>

<script>
function submitAnswer(qId) {
    const textarea = document.getElementById('answer-' + qId);
    const btn = document.getElementById('btn-' + qId);
    const feedbackBox = document.getElementById('feedback-' + qId);
    const answer = textarea.value.trim();
    
    if (!answer) { alert('Please type your answer first.'); return; }
    
    btn.textContent = '⏳ Evaluating...';
    btn.disabled = true;
    
    fetch('mock_interview.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'ajax=1&action=submit_answer&question_id=' + qId + '&answer=' + encodeURIComponent(answer)
    })
    .then(r => r.json())
    .then(d => {
        btn.textContent = '✅ Re-submit';
        btn.disabled = false;
        if (d.success) {
            const stars = '★'.repeat(d.score) + '☆'.repeat(10 - d.score);
            feedbackBox.innerHTML = `<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;"><span style="color:#bbbbbb;font-size:18px;">${stars}</span><span style="font-size:14px;font-weight:800;color:var(--primary);">${d.score}/10</span></div><p style="font-size:13px;line-height:1.65;">${d.feedback}</p>`;
            feedbackBox.style.display = 'block';
            // Animate
            feedbackBox.style.animation = 'none';
            feedbackBox.offsetHeight;
            feedbackBox.style.animation = 'bubbleFadeIn 0.4s ease forwards';
        }
    }).catch(() => {
        btn.textContent = '✅ Submit & Get AI Feedback';
        btn.disabled = false;
    });
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
