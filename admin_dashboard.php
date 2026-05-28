<?php
// admin_dashboard.php
// Recruiter / HR Administrator Dashboard page

require_once __DIR__ . '/auth.php';
requireAdminLogin();

require_once __DIR__ . '/db.php';

$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'];

$success = '';
$error = '';

$db = getDB();

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $resumeId = intval($_POST['resume_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    
    if (in_array($newStatus, ['Applied', 'Review', 'Shortlisted', 'Rejected'])) {
        try {
            $stmt = $db->prepare("UPDATE resumes SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $resumeId]);
            $success = "Candidate status updated to '{$newStatus}' successfully!";
        } catch (Exception $e) {
            $error = "Failed to update status: " . $e->getMessage();
        }
    } else {
        $error = "Invalid status selection.";
    }
}

// Fetch Recruiter Metrics
$totalCount = $db->query("SELECT COUNT(*) FROM resumes")->fetchColumn();
$shortlistedCount = $db->query("SELECT COUNT(*) FROM resumes WHERE status = 'Shortlisted'")->fetchColumn();
$reviewCount = $db->query("SELECT COUNT(*) FROM resumes WHERE status = 'Review'")->fetchColumn();
$rejectedCount = $db->query("SELECT COUNT(*) FROM resumes WHERE status = 'Rejected'")->fetchColumn();

// Fetch All Resumes for Table
$stmtRes = $db->query("
    SELECT r.id, r.file_name, r.target_role, r.status, r.ats_score, r.uploaded_at, u.name as candidate_name 
    FROM resumes r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.uploaded_at DESC
");
$resumes = $stmtRes->fetchAll();

// Select active candidate inspection details
$selectedResume = null;
$selectedResumeId = isset($_GET['resume_id']) ? intval($_GET['resume_id']) : null;

if ($selectedResumeId) {
    $stmt = $db->prepare("
        SELECT r.*, u.name as candidate_name, u.email as candidate_email 
        FROM resumes r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.id = ?
    ");
    $stmt->execute([$selectedResumeId]);
    $selectedResume = $stmt->fetch();
}

$analysisData = null;

if ($selectedResume) {
    $analysisData = json_decode($selectedResume['analysis_result'], true);
}
?>
<?php
$pageTitle = 'Recruiter Dashboard - AI Resume Analyzer';
$pageDesc = 'HR administrative portal to oversee candidate rankings, adjust applicant status, and review AI-generated candidate preparation questions.';
include __DIR__ . '/includes/header.php';
?>

    <main class="container" style="padding-top: 40px; padding-bottom: 60px;">
        
        <?php if ($error): ?>
            <div class="alert alert-error" id="admin-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" id="admin-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Recruiter Metrics Counter -->
        <section class="stats-grid" style="margin-bottom: 40px;" aria-label="Recruiter Metrics">
            <div class="stat-card" style="border-color: rgba(255, 255, 255, 0.08);">
                <div class="stat-num"><?php echo $totalCount; ?></div>
                <div class="stat-lbl">Total Applicants</div>
            </div>
            <div class="stat-card" style="border-color: rgba(16, 185, 129, 0.25);">
                <div class="stat-num" style="color: var(--success);"><?php echo $shortlistedCount; ?></div>
                <div class="stat-lbl" style="color: var(--success);">Shortlisted</div>
            </div>
            <div class="stat-card" style="border-color: rgba(245, 158, 11, 0.25);">
                <div class="stat-num" style="color: var(--warning);"><?php echo $reviewCount; ?></div>
                <div class="stat-lbl" style="color: var(--warning);">Pending Review</div>
            </div>
            <div class="stat-card" style="border-color: rgba(239, 68, 68, 0.25);">
                <div class="stat-num" style="color: var(--error);"><?php echo $rejectedCount; ?></div>
                <div class="stat-lbl" style="color: var(--error);">Rejected</div>
            </div>
        </section>

        <!-- Grid layout: Left Master Table & Right Inspect Report Panel -->
        <div style="display: grid; grid-template-columns: 1.5fr 1.2fr; gap: 30px; align-items: start;">
            
            <!-- Left Side: Master Applicant Table Card -->
            <section class="card" aria-labelledby="applicants-heading">
                <h2 class="card-title" id="applicants-heading" style="margin-bottom: 20px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Job Applicants Pool
                </h2>
                
                <?php if (empty($resumes)): ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 40px 0; font-size: 15px;">No resumes uploaded by candidates yet.</p>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Candidate Name</th>
                                    <th>Target Role</th>
                                    <th>ATS</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resumes as $res): ?>
                                    <?php 
                                        $scoreVal = intval($res['ats_score']);
                                        $scoreClass = 'poor';
                                        if ($scoreVal >= 80) $scoreClass = 'excellent';
                                        elseif ($scoreVal >= 60) $scoreClass = 'good';
                                        
                                        $isInspecting = ($selectedResumeId === $res['id']);
                                    ?>
                                    <tr style="background: <?php echo $isInspecting ? 'rgba(255, 255, 255, 0.02)' : 'transparent'; ?>;">
                                        <td style="font-weight: 600; color: #fff;">
                                            <?php echo htmlspecialchars($res['candidate_name']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($res['target_role']); ?>
                                        </td>
                                        <td>
                                            <strong class="<?php echo $scoreClass; ?>" style="color: <?php echo ($scoreClass === 'excellent') ? 'var(--success)' : (($scoreClass === 'good') ? 'var(--warning)' : 'var(--error)'); ?>; font-weight: 800;">
                                                <?php echo $scoreVal; ?>%
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($res['status']); ?>" style="padding: 2px 8px; font-size: 11px;">
                                                <?php echo htmlspecialchars($res['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="admin_dashboard.php?resume_id=<?php echo $res['id']; ?>" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 12px; border-color: <?php echo $isInspecting ? 'var(--accent)' : 'var(--border-color)'; ?>; color: <?php echo $isInspecting ? 'var(--accent)' : 'var(--text-main)'; ?>;">
                                                Inspect
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Right Side: Inspect Report Panel -->
            <section class="card" style="min-height: 500px;" aria-labelledby="inspect-heading">
                <?php if (!$selectedResume): ?>
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; padding: 60px 20px;">
                        <span style="font-size: 50px; margin-bottom: 20px; display: block; opacity: 0.5;">🕵️‍♂️</span>
                        <h2 id="inspect-heading" style="font-size: 20px; font-weight: 700; margin-bottom: 10px;">Review Applicant Profile</h2>
                        <p style="max-width: 320px; color: var(--text-muted); font-size: 14px;">Select a candidate from the table list to audit their full ATS score report, skills gaps, and AI-generated preparation questions.</p>
                    </div>
                <?php else: ?>
                    <h2 class="card-title" id="inspect-heading" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                        <span>Review Profile</span>
                        <span style="font-size: 13px; color: var(--text-muted); font-weight: 500;">ID: #<?php echo $selectedResume['id']; ?></span>
                    </h2>

                    <!-- Candidate Summary info -->
                    <div style="margin-bottom: 25px;">
                        <h3 style="font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 4px;"><?php echo htmlspecialchars($selectedResume['candidate_name']); ?></h3>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Email: <strong style="color: #fff;"><?php echo htmlspecialchars($selectedResume['candidate_email']); ?></strong></p>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Target Role: <strong style="color: var(--accent);"><?php echo htmlspecialchars($selectedResume['target_role']); ?></strong></p>
                        
                        <!-- Modify status form -->
                        <form action="admin_dashboard.php?resume_id=<?php echo $selectedResume['id']; ?>" method="POST" style="background: rgba(255,255,255,0.02); padding: 16px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="resume_id" value="<?php echo $selectedResume['id']; ?>">
                            
                            <label class="form-label" for="status" style="margin-bottom: 6px; font-size: 13px;">Modify Application Status</label>
                            <div style="display: flex; gap: 10px;">
                                <select name="status" id="status" class="form-control" style="padding: 8px 12px; font-size: 14px; flex: 1;">
                                    <option value="Applied" <?php if ($selectedResume['status'] === 'Applied') echo 'selected'; ?>>Applied</option>
                                    <option value="Review" <?php if ($selectedResume['status'] === 'Review') echo 'selected'; ?>>Review</option>
                                    <option value="Shortlisted" <?php if ($selectedResume['status'] === 'Shortlisted') echo 'selected'; ?>>Shortlisted</option>
                                    <option value="Rejected" <?php if ($selectedResume['status'] === 'Rejected') echo 'selected'; ?>>Rejected</option>
                                </select>
                                <button type="submit" class="btn btn-secondary btn-sm" style="font-size: 13px; font-weight: 700; border-color: rgba(255,255,255,0.15);">Update</button>
                            </div>
                        </form>
                    </div>

                    <!-- Metrics -->
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; align-items: center; margin-bottom: 25px;">
                        <?php 
                            $ats = intval($selectedResume['ats_score']);
                            $circleClass = 'poor';
                            if ($ats >= 80) $circleClass = 'excellent';
                            elseif ($ats >= 60) $circleClass = 'good';
                        ?>
                        <div class="score-circle-wrapper" style="margin: 0; transform: scale(0.95);">
                            <div class="score-circle <?php echo $circleClass; ?>">
                                <span class="score-num"><?php echo $ats; ?>%</span>
                                <span class="score-lbl">ATS Grade</span>
                            </div>
                        </div>
                        <div>
                            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 4px;">Uploaded File:</p>
                            <a href="<?php echo htmlspecialchars($selectedResume['file_path']); ?>" target="_blank" class="nav-link" style="color: var(--accent); font-size: 13px; font-weight: 700; text-decoration: underline; word-break: break-all; display: inline-block; margin-bottom: 10px;">
                                📄 <?php echo htmlspecialchars($selectedResume['file_name']); ?>
                            </a>
                            <p style="font-size: 11px; color: var(--text-muted);">Timestamp: <?php echo date('M d, Y H:i', strtotime($selectedResume['uploaded_at'])); ?></p>
                        </div>
                    </div>

                    <!-- Tech skills matches -->
                    <div style="margin-bottom: 25px;">
                        <h4 style="font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 8px;">Technical Keywords Matching</h4>
                        <div style="background: rgba(255,255,255,0.015); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 12px 16px;">
                            <p style="font-size: 11px; font-weight: 700; color: var(--success); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.05em;">Matched Keywords (<?php echo count($analysisData['matched_skills'] ?? []); ?>)</p>
                            <div class="skill-tag-list" style="margin-top: 0; margin-bottom: 12px; gap: 6px;">
                                <?php if (empty($analysisData['matched_skills'])): ?>
                                    <span style="font-size: 11px; color: var(--text-muted);">None detected.</span>
                                <?php else: ?>
                                    <?php foreach ($analysisData['matched_skills'] as $skill): ?>
                                        <span class="skill-tag skill-tag-match" style="padding: 3px 8px; font-size: 11px;"><?php echo htmlspecialchars($skill); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <p style="font-size: 11px; font-weight: 700; color: var(--error); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.05em;">Missing Keywords (<?php echo count($analysisData['missing_skills'] ?? []); ?>)</p>
                            <div class="skill-tag-list" style="margin-top: 0; gap: 6px;">
                                <?php if (empty($analysisData['missing_skills'])): ?>
                                    <span style="font-size: 11px; color: var(--success); font-weight: 600;">None missing!</span>
                                <?php else: ?>
                                    <?php foreach ($analysisData['missing_skills'] as $skill): ?>
                                        <span class="skill-tag skill-tag-missing" style="padding: 3px 8px; font-size: 11px;"><?php echo htmlspecialchars($skill); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Interview Questions section -->
                    <div>
                        <h4 style="font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 8px;">AI-Generated Preparation Questions</h4>
                        <div style="background: rgba(255,255,255,0.015); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 16px; max-height: 400px; overflow-y: auto;">
                            <?php 
                            // Fetch generated questions
                            $stmtQ = $db->prepare("SELECT * FROM interview_questions WHERE resume_id = ? ORDER BY id ASC");
                            $stmtQ->execute([$selectedResume['id']]);
                            $candidateQuestions = $stmtQ->fetchAll();
                            ?>
                            <?php if (empty($candidateQuestions)): ?>
                                <p style="text-align: center; color: var(--text-muted); font-size: 13px; margin: 0; padding: 20px 0;">No interview questions generated for this candidate yet.</p>
                            <?php else: ?>
                                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: var(--text-muted); display: flex; flex-direction: column; gap: 10px;">
                                    <?php foreach ($candidateQuestions as $idx => $q): ?>
                                        <li style="line-height: 1.5; color: var(--text-main);">
                                            <strong style="color: var(--accent);">Q<?php echo ($idx + 1); ?>:</strong> <?php echo htmlspecialchars($q['question']); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
