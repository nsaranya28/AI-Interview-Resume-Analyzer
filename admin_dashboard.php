<?php
// admin_dashboard.php - Enhanced Admin Panel
require_once __DIR__ . '/auth.php';
requireAdminLogin();
require_once __DIR__ . '/db.php';

$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'];
$success = '';
$error = '';
$db = getDB();

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $resumeId = intval($_POST['resume_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');
        if (in_array($newStatus, ['Applied', 'Review', 'Shortlisted', 'Rejected'])) {
            $db->prepare("UPDATE resumes SET status = ? WHERE id = ?")->execute([$newStatus, $resumeId]);
            $success = "Status updated to '$newStatus'!";
        }
    }
    if ($_POST['action'] === 'delete_user') {
        $uid = intval($_POST['user_id'] ?? 0);
        if ($uid) { $db->prepare("DELETE FROM users WHERE id=?")->execute([$uid]); $success = 'User deleted.'; }
    }
}

// Stats
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalResumes = $db->query("SELECT COUNT(*) FROM resumes")->fetchColumn();
$totalBuilt = $db->query("SELECT COUNT(*) FROM resume_profiles")->fetchColumn();
$shortlistedCount = $db->query("SELECT COUNT(*) FROM resumes WHERE status='Shortlisted'")->fetchColumn();
$reviewCount = $db->query("SELECT COUNT(*) FROM resumes WHERE status='Review'")->fetchColumn();
$rejectedCount = $db->query("SELECT COUNT(*) FROM resumes WHERE status='Rejected'")->fetchColumn();
$avgAts = round($db->query("SELECT AVG(ats_score) FROM resumes WHERE ats_score IS NOT NULL")->fetchColumn(), 1);
$totalInterviews = $db->query("SELECT COUNT(*) FROM interview_answers")->fetchColumn();

// Active tab
$tab = $_GET['tab'] ?? 'overview';

// Fetch data based on tab
$resumes = $users = [];
if ($tab === 'resumes' || $tab === 'overview') {
    $stmtRes = $db->query("SELECT r.id, r.file_name, r.target_role, r.status, r.ats_score, r.uploaded_at, u.name as candidate_name, u.email as candidate_email FROM resumes r JOIN users u ON r.user_id = u.id ORDER BY r.uploaded_at DESC LIMIT 100");
    $resumes = $stmtRes->fetchAll();
}
if ($tab === 'users') {
    $users = $db->query("SELECT u.*, (SELECT COUNT(*) FROM resumes WHERE user_id=u.id) as resume_count, (SELECT COUNT(*) FROM resume_profiles WHERE user_id=u.id) as built_count FROM users u ORDER BY u.created_at DESC")->fetchAll();
}

$selectedResumeId = isset($_GET['resume_id']) ? intval($_GET['resume_id']) : null;
$selectedResume = null;
$analysisData = null;
if ($selectedResumeId) {
    $stmt = $db->prepare("SELECT r.*, u.name as candidate_name, u.email as candidate_email FROM resumes r JOIN users u ON r.user_id = u.id WHERE r.id = ?");
    $stmt->execute([$selectedResumeId]);
    $selectedResume = $stmt->fetch();
    if ($selectedResume) $analysisData = json_decode($selectedResume['analysis_result'], true);
}

$pageTitle = 'Admin Panel - AI Resume Analyzer';
include __DIR__ . '/includes/header.php';
?>

<style>
.admin-layout { display: grid; grid-template-columns: 240px 1fr; min-height: calc(100vh - 80px); }
.admin-sidebar { background: #ffffff; border-right: 1px solid var(--border-color); padding: 24px 16px; }
.admin-nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; transition: var(--transition); }
.admin-nav-item:hover, .admin-nav-item.active { background: #f1f5f9; color: var(--text-dark); }
.admin-content { padding: 32px; }
.metric-cards { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 32px; }
.metric-card { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; transition: var(--transition); }
.metric-card:hover { transform: translateY(-2px); }
.metric-num { font-size: 32px; font-weight: 900; margin-bottom: 4px; }
.metric-lbl { font-size: 12px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
.metric-change { font-size: 12px; margin-top: 8px; }
.admin-table-wrapper { overflow-x: auto; border-radius: var(--radius-sm); border: 1px solid var(--border-color); margin-top: 20px; }
.admin-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 14px; }
.admin-table th { padding: 14px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); background: rgba(255,255,255,.01); border-bottom: 1px solid var(--border-color); }
.admin-table td { padding: 14px 16px; border-bottom: 1px solid var(--border-color); color: var(--text-dark); vertical-align: middle; }
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: rgba(255,255,255,.02); }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.section-title { font-size: 20px; font-weight: 800; color: var(--text-dark); }
</style>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div style="margin-bottom:24px;">
            <div style="width:50px;height:50px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#a855f7);display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:10px;">👑</div>
            <div style="font-size:15px;font-weight:800;color:var(--text-dark);"><?= htmlspecialchars($adminName) ?></div>
            <div style="font-size:12px;color:var(--text-muted);">Administrator</div>
        </div>
        <nav>
            <a href="admin_dashboard.php?tab=overview" class="admin-nav-item <?= ($tab === 'overview') ? 'active' : '' ?>">📊 Overview</a>
            <a href="admin_dashboard.php?tab=resumes" class="admin-nav-item <?= ($tab === 'resumes') ? 'active' : '' ?>">📄 Manage Resumes</a>
            <a href="admin_dashboard.php?tab=users" class="admin-nav-item <?= ($tab === 'users') ? 'active' : '' ?>">👥 Manage Users</a>
        </nav>
        <div style="border-top:1px solid var(--border-color);padding-top:16px;margin-top:24px;">
            <a href="setup_v2.php" class="admin-nav-item" target="_blank">🔧 Run DB Setup</a>
            <a href="logout.php" class="admin-nav-item" style="color:var(--error);">🚪 Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <?php if ($tab === 'overview'): ?>
        <!-- Overview -->
        <h1 style="font-size:28px;font-weight:900;margin-bottom:8px;">Dashboard Overview</h1>
        <p style="color:var(--text-muted);margin-bottom:28px;">Platform-wide statistics and recent activity.</p>
        
        <div class="metric-cards">
            <div class="metric-card" style="border-color:rgba(99,102,241,.3);">
                <div class="metric-num" style="color:var(--primary);"><?= $totalUsers ?></div>
                <div class="metric-lbl">Total Users</div>
                <div class="metric-change" style="color:var(--success);">👥 Registered Candidates</div>
            </div>
            <div class="metric-card" style="border-color:rgba(6,182,212,.3);">
                <div class="metric-num" style="color:var(--secondary);"><?= $totalResumes ?></div>
                <div class="metric-lbl">Analyzed Resumes</div>
                <div class="metric-change" style="color:var(--text-muted);">📄 Uploaded & Scored</div>
            </div>
            <div class="metric-card" style="border-color:rgba(168,85,247,.3);">
                <div class="metric-num" style="color:var(--accent);"><?= $totalBuilt ?></div>
                <div class="metric-lbl">Built Resumes</div>
                <div class="metric-change" style="color:var(--text-muted);">✏️ Using AI Builder</div>
            </div>
            <div class="metric-card" style="border-color:rgba(16,185,129,.3);">
                <div class="metric-num" style="color:var(--success);"><?= $avgAts ?>%</div>
                <div class="metric-lbl">Average ATS Score</div>
                <div class="metric-change" style="color:var(--text-muted);">📊 Platform Average</div>
            </div>
            <div class="metric-card" style="border-color:rgba(245,158,11,.3);">
                <div class="metric-num" style="color:var(--warning);"><?= $reviewCount ?></div>
                <div class="metric-lbl">Pending Review</div>
                <div class="metric-change" style="color:var(--warning);">⏳ Needs Attention</div>
            </div>
            <div class="metric-card" style="border-color:rgba(16,185,129,.3);">
                <div class="metric-num" style="color:var(--success);"><?= $shortlistedCount ?></div>
                <div class="metric-lbl">Shortlisted</div>
                <div class="metric-change" style="color:var(--success);">✅ Approved</div>
            </div>
            <div class="metric-card" style="border-color:rgba(239,68,68,.3);">
                <div class="metric-num" style="color:var(--error);"><?= $rejectedCount ?></div>
                <div class="metric-lbl">Rejected</div>
                <div class="metric-change" style="color:var(--error);">❌ Not Passed</div>
            </div>
            <div class="metric-card" style="border-color:rgba(168,85,247,.3);">
                <div class="metric-num" style="color:var(--accent);"><?= $totalInterviews ?></div>
                <div class="metric-lbl">Interview Answers</div>
                <div class="metric-change" style="color:var(--text-muted);">🎤 Mock Practice</div>
            </div>
        </div>
        
        <!-- Recent Resumes -->
        <div class="section-header">
            <div class="section-title">Recent Resume Submissions</div>
            <a href="admin_dashboard.php?tab=resumes" class="btn btn-secondary btn-sm">View All →</a>
        </div>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead><tr><th>Candidate</th><th>Target Role</th><th>ATS Score</th><th>Status</th><th>Submitted</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach (array_slice($resumes, 0, 10) as $res): ?>
                    <?php $sc = intval($res['ats_score']); $sc_color = $sc >= 80 ? 'var(--success)' : ($sc >= 60 ? 'var(--warning)' : 'var(--error)'); ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($res['candidate_name']) ?></strong><div style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($res['candidate_email']) ?></div></td>
                        <td><?= htmlspecialchars($res['target_role']) ?></td>
                        <td><strong style="color:<?= $sc_color ?>;"><?= $sc ?>%</strong></td>
                        <td><span class="badge badge-<?= strtolower($res['status']) ?>"><?= $res['status'] ?></span></td>
                        <td style="font-size:12px;"><?= date('d M Y', strtotime($res['uploaded_at'])) ?></td>
                        <td><a href="admin_dashboard.php?tab=resumes&resume_id=<?= $res['id'] ?>" class="btn btn-secondary btn-sm" style="font-size:12px;padding:4px 10px;">Inspect</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($tab === 'resumes'): ?>
        <!-- Resumes Management -->
        <div style="display:grid;grid-template-columns:<?= $selectedResume ? '1fr 400px' : '1fr' ?>;gap:24px;align-items:start;">
            <div>
                <div class="section-header">
                    <div class="section-title">All Resume Submissions</div>
                    <div style="font-size:13px;color:var(--text-muted);"><?= $totalResumes ?> total</div>
                </div>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead><tr><th>Candidate</th><th>Target Role</th><th>ATS</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($resumes as $res): ?>
                            <?php $sc = intval($res['ats_score']); $sc_color = $sc >= 80 ? 'var(--success)' : ($sc >= 60 ? 'var(--warning)' : 'var(--error)'); $isActive = ($selectedResumeId == $res['id']); ?>
                            <tr style="<?= $isActive ? 'background:rgba(99,102,241,.05);' : '' ?>">
                                <td><strong><?= htmlspecialchars($res['candidate_name']) ?></strong></td>
                                <td><?= htmlspecialchars($res['target_role']) ?></td>
                                <td><strong style="color:<?= $sc_color ?>;"><?= $sc ?>%</strong></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="resume_id" value="<?= $res['id'] ?>">
                                        <select name="status" onchange="this.form.submit()" class="form-control" style="padding:3px 8px;font-size:12px;width:110px;">
                                            <?php foreach (['Applied','Review','Shortlisted','Rejected'] as $s): ?>
                                                <option value="<?= $s ?>" <?= $res['status']===$s?'selected':'' ?>><?= $s ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td style="font-size:12px;"><?= date('d M y', strtotime($res['uploaded_at'])) ?></td>
                                <td><a href="admin_dashboard.php?tab=resumes&resume_id=<?= $res['id'] ?>" class="btn btn-secondary btn-sm" style="font-size:12px;padding:4px 10px;">Inspect</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($selectedResume): ?>
            <div>
                <div class="card" style="position:sticky;top:100px;">
                    <h3 style="font-size:17px;font-weight:800;margin-bottom:16px;">Candidate Profile</h3>
                    <div style="margin-bottom:16px;">
                        <div style="font-size:18px;font-weight:800;"><?= htmlspecialchars($selectedResume['candidate_name']) ?></div>
                        <div style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($selectedResume['candidate_email']) ?></div>
                        <div style="font-size:13px;color:var(--accent);margin-top:4px;">→ <?= htmlspecialchars($selectedResume['target_role']) ?></div>
                    </div>
                    
                    <?php $ats = intval($selectedResume['ats_score']); $cc = $ats >= 80 ? 'excellent' : ($ats >= 60 ? 'good' : 'poor'); ?>
                    <div class="score-circle-wrapper" style="margin-bottom:16px;">
                        <div class="score-circle <?= $cc ?>">
                            <span class="score-num"><?= $ats ?>%</span>
                            <span class="score-lbl">ATS Score</span>
                        </div>
                    </div>
                    
                    <?php if ($analysisData): ?>
                    <div style="font-size:12px;font-weight:700;color:var(--success);text-transform:uppercase;margin-bottom:6px;">Matched (<?= count($analysisData['matched_skills']??[]) ?>)</div>
                    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:12px;">
                        <?php foreach (($analysisData['matched_skills'] ?? []) as $sk): ?><span class="skill-tag skill-tag-match" style="font-size:11px;padding:2px 8px;"><?= htmlspecialchars($sk) ?></span><?php endforeach; ?>
                    </div>
                    <div style="font-size:12px;font-weight:700;color:var(--error);text-transform:uppercase;margin-bottom:6px;">Missing (<?= count($analysisData['missing_skills']??[]) ?>)</div>
                    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:16px;">
                        <?php foreach (($analysisData['missing_skills'] ?? []) as $sk): ?><span class="skill-tag skill-tag-missing" style="font-size:11px;padding:2px 8px;"><?= htmlspecialchars($sk) ?></span><?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div style="border-top:1px solid var(--border-color);padding-top:12px;">
                        <a href="admin_dashboard.php?tab=resumes" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;margin-bottom:8px;">← Back to List</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php elseif ($tab === 'users'): ?>
        <!-- Users Management -->
        <div class="section-header">
            <div class="section-title">All Registered Users</div>
            <div style="font-size:13px;color:var(--text-muted);"><?= $totalUsers ?> total</div>
        </div>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Resumes</th><th>Built</th><th>Joined</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td style="color:var(--text-muted);"><?= $u['id'] ?></td>
                        <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                        <td style="font-size:13px;"><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= $u['resume_count'] ?></td>
                        <td><?= $u['built_count'] ?></td>
                        <td style="font-size:12px;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Delete this user and all their data?');">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" style="font-size:12px;padding:4px 10px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
