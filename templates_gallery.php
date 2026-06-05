<?php
// templates_gallery.php - Premium Resume Template Gallery
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Premium Resume Templates Gallery - AI Resume Analyzer';
include __DIR__ . '/includes/header.php';

// Definition of 52 templates covering all categories and styles
$templates = [
    // ATS Friendly (8 templates)
    ['id' => 'ats_1', 'name' => 'ATS Standard Pro', 'category' => 'ATS Friendly', 'color' => 'Red', 'color_code' => '#dc2626', 'pages' => 1, 'ats_score' => 98, 'popularity' => 98, 'date' => '2026-01-10', 'layout' => 'ats', 'badge' => 'Top Choice', 'desc' => 'Classic single-column layout optimized for high ATS parser accuracy.'],
    ['id' => 'ats_2', 'name' => 'ATS Corporate Elite', 'category' => 'ATS Friendly', 'color' => 'Blue', 'color_code' => '#0284c7', 'pages' => 1, 'ats_score' => 96, 'popularity' => 94, 'date' => '2026-02-15', 'layout' => 'ats', 'badge' => '', 'desc' => 'Conservative design suited for corporate finance and legal positions.'],
    ['id' => 'ats_3', 'name' => 'ATS Minimal Grid', 'category' => 'ATS Friendly', 'color' => 'Green', 'color_code' => '#16a34a', 'pages' => 1, 'ats_score' => 97, 'popularity' => 92, 'date' => '2026-03-01', 'layout' => 'ats', 'badge' => '', 'desc' => 'Clean grid formatting for easy parsing by modern AI screeners.'],
    ['id' => 'ats_4', 'name' => 'ATS Tech Executive', 'category' => 'ATS Friendly', 'color' => 'Dark', 'color_code' => '#1f2937', 'pages' => 2, 'ats_score' => 95, 'popularity' => 89, 'date' => '2026-03-20', 'layout' => 'ats', 'badge' => '2 Pages', 'desc' => 'Double-page professional layout for seasoned technical leads.'],
    ['id' => 'ats_5', 'name' => 'ATS Healthcare Specialist', 'category' => 'ATS Friendly', 'color' => 'Teal', 'color_code' => '#0d9488', 'pages' => 1, 'ats_score' => 97, 'popularity' => 90, 'date' => '2026-04-05', 'layout' => 'ats', 'badge' => '', 'desc' => 'Clear structured medical format prioritizing clinical credentials.'],
    ['id' => 'ats_6', 'name' => 'ATS Academic Scholar', 'category' => 'ATS Friendly', 'color' => 'Purple', 'color_code' => '#7c3aed', 'pages' => 2, 'ats_score' => 94, 'popularity' => 87, 'date' => '2026-04-18', 'layout' => 'academic', 'badge' => '', 'desc' => 'Clean chronological layout suitable for long CV structures.'],
    ['id' => 'ats_7', 'name' => 'ATS Clean Slate', 'category' => 'ATS Friendly', 'color' => 'Slate', 'color_code' => '#475569', 'pages' => 1, 'ats_score' => 99, 'popularity' => 99, 'date' => '2026-05-01', 'layout' => 'ats', 'badge' => 'Highest Score', 'desc' => 'Super high-compatibility layout that guarantees a perfect parser score.'],
    ['id' => 'ats_8', 'name' => 'ATS Sales Master', 'category' => 'ATS Friendly', 'color' => 'Orange', 'color_code' => '#ea580c', 'pages' => 1, 'ats_score' => 96, 'popularity' => 91, 'date' => '2026-05-12', 'layout' => 'ats', 'badge' => '', 'desc' => 'Perfect for showcasing quantitative sales targets and metrics.'],

    // Modern (8 templates)
    ['id' => 'mod_1', 'name' => 'Modern Gradient Edge', 'category' => 'Modern', 'color' => 'Red', 'color_code' => '#dc2626', 'pages' => 1, 'ats_score' => 85, 'popularity' => 97, 'date' => '2026-01-02', 'layout' => 'modern', 'badge' => 'Popular', 'desc' => 'Features clean sections with a sleek colored left border.'],
    ['id' => 'mod_2', 'name' => 'Modern Indigo Splash', 'category' => 'Modern', 'color' => 'Indigo', 'color_code' => '#4f46e5', 'pages' => 1, 'ats_score' => 88, 'popularity' => 95, 'date' => '2026-01-18', 'layout' => 'modern', 'badge' => 'Trending', 'desc' => 'Stunning header block with bright indigo highlights.'],
    ['id' => 'mod_3', 'name' => 'Modern Emerald Hub', 'category' => 'Modern', 'color' => 'Green', 'color_code' => '#059669', 'pages' => 1, 'ats_score' => 84, 'popularity' => 89, 'date' => '2026-02-10', 'layout' => 'modern', 'badge' => '', 'desc' => 'Balanced modern style featuring a green sidebar header.'],
    ['id' => 'mod_4', 'name' => 'Modern Charcoal Minimalist', 'category' => 'Modern', 'color' => 'Dark', 'color_code' => '#374151', 'pages' => 1, 'ats_score' => 90, 'popularity' => 93, 'date' => '2026-02-28', 'layout' => 'modern', 'badge' => '', 'desc' => 'High contrast layout with charcoal headings and modern spacing.'],
    ['id' => 'mod_5', 'name' => 'Modern Rose Elegance', 'category' => 'Modern', 'color' => 'Rose', 'color_code' => '#e11d48', 'pages' => 1, 'ats_score' => 86, 'popularity' => 91, 'date' => '2026-03-12', 'layout' => 'modern', 'badge' => '', 'desc' => 'Stylish design with delicate pink elements and modern typography.'],
    ['id' => 'mod_6', 'name' => 'Modern Bold Header', 'category' => 'Modern', 'color' => 'Blue', 'color_code' => '#2563eb', 'pages' => 1, 'ats_score' => 89, 'popularity' => 94, 'date' => '2026-03-29', 'layout' => 'modern', 'badge' => '', 'desc' => 'Heavy top bar layout that captures immediate visual interest.'],
    ['id' => 'mod_7', 'name' => 'Modern Orange Accent', 'category' => 'Modern', 'color' => 'Orange', 'color_code' => '#d97706', 'pages' => 2, 'ats_score' => 82, 'popularity' => 86, 'date' => '2026-04-15', 'layout' => 'modern', 'badge' => '', 'desc' => 'Double-page modern template highlighting skill graphics.'],
    ['id' => 'mod_8', 'name' => 'Modern Tech Glow', 'category' => 'Modern', 'color' => 'Purple', 'color_code' => '#9333ea', 'pages' => 1, 'ats_score' => 87, 'popularity' => 96, 'date' => '2026-05-02', 'layout' => 'tech_sleek', 'badge' => 'New', 'desc' => 'Futuristic layout with dynamic tech category tag clouds.'],

    // Professional (7 templates)
    ['id' => 'prof_1', 'name' => 'Professional Executive Column', 'category' => 'Professional', 'color' => 'Red', 'color_code' => '#991b1b', 'pages' => 1, 'ats_score' => 92, 'popularity' => 96, 'date' => '2026-01-08', 'layout' => 'professional', 'badge' => 'Best Seller', 'desc' => 'Sleek dark red left sidebar designed for corporate officers.'],
    ['id' => 'prof_2', 'name' => 'Professional Navy Corporate', 'category' => 'Professional', 'color' => 'Blue', 'color_code' => '#1e3a8a', 'pages' => 1, 'ats_score' => 94, 'popularity' => 95, 'date' => '2026-02-04', 'layout' => 'professional', 'badge' => '', 'desc' => 'Traditional navy left-sidebar design representing trust and security.'],
    ['id' => 'prof_3', 'name' => 'Professional Forest Officer', 'category' => 'Professional', 'color' => 'Green', 'color_code' => '#064e3b', 'pages' => 1, 'ats_score' => 93, 'popularity' => 88, 'date' => '2026-02-22', 'layout' => 'professional', 'badge' => '', 'desc' => 'Rich green sidebar formatting for environment and operations.'],
    ['id' => 'prof_4', 'name' => 'Professional Dark Slate', 'category' => 'Professional', 'color' => 'Dark', 'color_code' => '#0f172a', 'pages' => 2, 'ats_score' => 91, 'popularity' => 92, 'date' => '2026-03-10', 'layout' => 'professional', 'badge' => '2 Pages', 'desc' => 'Extended sidebar template for experienced directors.'],
    ['id' => 'prof_5', 'name' => 'Professional Royal Purple', 'category' => 'Professional', 'color' => 'Purple', 'color_code' => '#581c87', 'pages' => 1, 'ats_score' => 92, 'popularity' => 87, 'date' => '2026-04-02', 'layout' => 'professional', 'badge' => '', 'desc' => 'Sophisticated template color layout for premium branding.'],
    ['id' => 'prof_6', 'name' => 'Professional Deep Teal', 'category' => 'Professional', 'color' => 'Teal', 'color_code' => '#115e59', 'pages' => 1, 'ats_score' => 94, 'popularity' => 90, 'date' => '2026-04-20', 'layout' => 'professional', 'badge' => '', 'desc' => 'Clean blue-teal combo that looks elegant when printed.'],
    ['id' => 'prof_7', 'name' => 'Professional Classic Black', 'category' => 'Professional', 'color' => 'Dark', 'color_code' => '#111827', 'pages' => 1, 'ats_score' => 95, 'popularity' => 93, 'date' => '2026-05-18', 'layout' => 'professional', 'badge' => '', 'desc' => 'Traditional black sidebar layout for legal and operational roles.'],

    // Minimalist (6 templates)
    ['id' => 'min_1', 'name' => 'Minimalist Crisp White', 'category' => 'Minimalist', 'color' => 'Dark', 'color_code' => '#4b5563', 'pages' => 1, 'ats_score' => 98, 'popularity' => 95, 'date' => '2026-01-20', 'layout' => 'minimal', 'badge' => 'Elegant', 'desc' => 'Ultra-clean look with high whitespace and delicate typography.'],
    ['id' => 'min_2', 'name' => 'Minimalist Red Stroke', 'category' => 'Minimalist', 'color' => 'Red', 'color_code' => '#ef4444', 'pages' => 1, 'ats_score' => 96, 'popularity' => 91, 'date' => '2026-02-12', 'layout' => 'minimal', 'badge' => '', 'desc' => 'Sleek details with bright red divider accents.'],
    ['id' => 'min_3', 'name' => 'Minimalist Slate Note', 'category' => 'Minimalist', 'color' => 'Slate', 'color_code' => '#64748b', 'pages' => 1, 'ats_score' => 97, 'popularity' => 88, 'date' => '2026-03-05', 'layout' => 'minimal', 'badge' => '', 'desc' => 'Soft slate accents that look highly modern and quiet.'],
    ['id' => 'min_4', 'name' => 'Minimalist Sky Clean', 'category' => 'Minimalist', 'color' => 'Blue', 'color_code' => '#0ea5e9', 'pages' => 1, 'ats_score' => 98, 'popularity' => 93, 'date' => '2026-03-24', 'layout' => 'minimal', 'badge' => '', 'desc' => 'Compact and elegant styling for high-information density.'],
    ['id' => 'min_5', 'name' => 'Minimalist Sage Line', 'category' => 'Minimalist', 'color' => 'Green', 'color_code' => '#84cc16', 'pages' => 1, 'ats_score' => 95, 'popularity' => 86, 'date' => '2026-04-11', 'layout' => 'minimal', 'badge' => '', 'desc' => 'Fresh light-green lines designed for organic/nature professionals.'],
    ['id' => 'min_6', 'name' => 'Minimalist Executive Plain', 'category' => 'Minimalist', 'color' => 'Dark', 'color_code' => '#18181b', 'pages' => 1, 'ats_score' => 99, 'popularity' => 94, 'date' => '2026-05-10', 'layout' => 'minimal', 'badge' => 'Highest Score', 'desc' => 'No distractions, clear headers, high compatibility score.'],

    // Creative (5 templates)
    ['id' => 'cre_1', 'name' => 'Creative Playfair Bold', 'category' => 'Creative', 'color' => 'Red', 'color_code' => '#7f1d1d', 'pages' => 1, 'ats_score' => 78, 'popularity' => 94, 'date' => '2026-01-15', 'layout' => 'creative', 'badge' => 'Artistic', 'desc' => 'Uses beautiful serif headers for writers, authors and publicists.'],
    ['id' => 'cre_2', 'name' => 'Creative Violet Accent', 'category' => 'Creative', 'color' => 'Purple', 'color_code' => '#7e22ce', 'pages' => 1, 'ats_score' => 76, 'popularity' => 90, 'date' => '2026-02-18', 'layout' => 'creative', 'badge' => '', 'desc' => 'Stylish layout highlighting creative portfolio and design history.'],
    ['id' => 'cre_3', 'name' => 'Creative Amber Bold', 'category' => 'Creative', 'color' => 'Orange', 'color_code' => '#b45309', 'pages' => 1, 'ats_score' => 79, 'popularity' => 88, 'date' => '2026-03-14', 'layout' => 'creative', 'badge' => '', 'desc' => 'Warm styling designed for culinary, retail, and event coordinators.'],
    ['id' => 'cre_4', 'name' => 'Creative Ocean Vibe', 'category' => 'Creative', 'color' => 'Blue', 'color_code' => '#0369a1', 'pages' => 1, 'ats_score' => 75, 'popularity' => 92, 'date' => '2026-04-28', 'layout' => 'creative', 'badge' => '', 'desc' => 'Vibrant teal/blue highlights designed for marketing campaigns.'],
    ['id' => 'cre_5', 'name' => 'Creative Elegant Rose', 'category' => 'Creative', 'color' => 'Rose', 'color_code' => '#be123c', 'pages' => 1, 'ats_score' => 82, 'popularity' => 91, 'date' => '2026-05-15', 'layout' => 'elegant', 'badge' => 'Warm', 'desc' => 'Stylish warm layout with dynamic typography and elegant touch.'],

    // Student/Fresher (5 templates)
    ['id' => 'stu_1', 'name' => 'Fresher Starter Blue', 'category' => 'Student/Fresher', 'color' => 'Blue', 'color_code' => '#3b82f6', 'pages' => 1, 'ats_score' => 96, 'popularity' => 97, 'date' => '2026-01-25', 'layout' => 'ats', 'badge' => 'Best for Freshers', 'desc' => 'Puts education and project sections first for entry-level candidates.'],
    ['id' => 'stu_2', 'name' => 'Fresher Modern Edge', 'category' => 'Student/Fresher', 'color' => 'Red', 'color_code' => '#ef4444', 'pages' => 1, 'ats_score' => 93, 'popularity' => 91, 'date' => '2026-02-20', 'layout' => 'modern', 'badge' => '', 'desc' => 'Clean design suitable for college graduates and internship applications.'],
    ['id' => 'stu_3', 'name' => 'Fresher Simple Sage', 'category' => 'Student/Fresher', 'color' => 'Green', 'color_code' => '#22c55e', 'pages' => 1, 'ats_score' => 95, 'popularity' => 88, 'date' => '2026-03-15', 'layout' => 'minimal', 'badge' => '', 'desc' => 'A clean single page layout prioritizing coursework and soft skills.'],
    ['id' => 'stu_4', 'name' => 'Fresher Purple Spark', 'category' => 'Student/Fresher', 'color' => 'Purple', 'color_code' => '#a855f7', 'pages' => 1, 'ats_score' => 91, 'popularity' => 89, 'date' => '2026-04-10', 'layout' => 'creative', 'badge' => '', 'desc' => 'Fresh vibrant template suitable for creative college programs.'],
    ['id' => 'stu_5', 'name' => 'Fresher Academic Entry', 'category' => 'Student/Fresher', 'color' => 'Dark', 'color_code' => '#52525b', 'pages' => 1, 'ats_score' => 97, 'popularity' => 92, 'date' => '2026-05-14', 'layout' => 'academic', 'badge' => '', 'desc' => 'Perfect entry level academic layout for research fellowships.'],

    // Software Developer (6 templates)
    ['id' => 'dev_1', 'name' => 'Developer Sleek Console', 'category' => 'Software Developer', 'color' => 'Dark', 'color_code' => '#0f172a', 'pages' => 1, 'ats_score' => 94, 'popularity' => 98, 'date' => '2026-01-30', 'layout' => 'tech_sleek', 'badge' => 'Best for IT', 'desc' => 'Tech layout featuring category groups and monospace style elements.'],
    ['id' => 'dev_2', 'name' => 'Developer Electric Cyan', 'category' => 'Software Developer', 'color' => 'Blue', 'color_code' => '#06b6d4', 'pages' => 1, 'ats_score' => 93, 'popularity' => 94, 'date' => '2026-02-15', 'layout' => 'tech_sleek', 'badge' => 'Trending', 'desc' => 'Vibrant developer style displaying tools and stacks clearly.'],
    ['id' => 'dev_3', 'name' => 'Developer Crimson Monospace', 'category' => 'Software Developer', 'color' => 'Red', 'color_code' => '#e11d48', 'pages' => 1, 'ats_score' => 94, 'popularity' => 92, 'date' => '2026-03-08', 'layout' => 'tech_sleek', 'badge' => '', 'desc' => 'Bold red tech details with specific github and portfolio badges.'],
    ['id' => 'dev_4', 'name' => 'Developer Lime Green Stack', 'category' => 'Software Developer', 'color' => 'Green', 'color_code' => '#10b981', 'pages' => 1, 'ats_score' => 95, 'popularity' => 89, 'date' => '2026-03-27', 'layout' => 'tech_sleek', 'badge' => '', 'desc' => 'Sleek terminal-influenced card tags for engineering skills.'],
    ['id' => 'dev_5', 'name' => 'Developer Purple Code', 'category' => 'Software Developer', 'color' => 'Purple', 'color_code' => '#8b5cf6', 'pages' => 2, 'ats_score' => 91, 'popularity' => 91, 'date' => '2026-04-12', 'layout' => 'tech_sleek', 'badge' => '', 'desc' => 'Double page template optimized for multiple projects and tech logs.'],
    ['id' => 'dev_6', 'name' => 'Developer Clean Git', 'category' => 'Software Developer', 'color' => 'Slate', 'color_code' => '#3f3f46', 'pages' => 1, 'ats_score' => 96, 'popularity' => 95, 'date' => '2026-05-08', 'layout' => 'ats', 'badge' => '', 'desc' => 'High ATS friendly code developer template with clear grid lines.'],

    // Designer (4 templates)
    ['id' => 'dsg_1', 'name' => 'Designer Studio Splash', 'category' => 'Designer', 'color' => 'Purple', 'color_code' => '#c084fc', 'pages' => 1, 'ats_score' => 77, 'popularity' => 95, 'date' => '2026-02-02', 'layout' => 'creative', 'badge' => 'Visual Focus', 'desc' => 'Modern design displaying layout blocks and design tools.'],
    ['id' => 'dsg_2', 'name' => 'Designer Crimson Minimal', 'category' => 'Designer', 'color' => 'Red', 'color_code' => '#f43f5e', 'pages' => 1, 'ats_score' => 83, 'popularity' => 91, 'date' => '2026-02-28', 'layout' => 'minimal', 'badge' => '', 'desc' => 'High contrast elegant minimal layout suitable for UI/UX profiles.'],
    ['id' => 'dsg_3', 'name' => 'Designer Teal Border', 'category' => 'Designer', 'color' => 'Teal', 'color_code' => '#14b8a6', 'pages' => 1, 'ats_score' => 80, 'popularity' => 88, 'date' => '2026-03-18', 'layout' => 'modern', 'badge' => '', 'desc' => 'Sleek margins and colored title borders for branding experts.'],
    ['id' => 'dsg_4', 'name' => 'Designer Royal Rose', 'category' => 'Designer', 'color' => 'Rose', 'color_code' => '#fda4af', 'pages' => 1, 'ats_score' => 76, 'popularity' => 90, 'date' => '2026-05-01', 'layout' => 'elegant', 'badge' => '', 'desc' => 'Warm, artistic design elements tailored for fashion and media designers.'],

    // Business Analyst (5 templates)
    ['id' => 'ana_1', 'name' => 'Analyst Stats Grid', 'category' => 'Business Analyst', 'color' => 'Blue', 'color_code' => '#1d4ed8', 'pages' => 1, 'ats_score' => 95, 'popularity' => 94, 'date' => '2026-02-05', 'layout' => 'professional', 'badge' => 'Highly Rated', 'desc' => 'Clean corporate sidebar highlighting statistical metrics.'],
    ['id' => 'ana_2', 'name' => 'Analyst Modern Business', 'category' => 'Business Analyst', 'color' => 'Green', 'color_code' => '#15803d', 'pages' => 1, 'ats_score' => 93, 'popularity' => 89, 'date' => '2026-03-02', 'layout' => 'modern', 'badge' => '', 'desc' => 'Structured modern layout showing analytical expertise and tools.'],
    ['id' => 'ana_3', 'name' => 'Analyst Orange Pitch', 'category' => 'Business Analyst', 'color' => 'Orange', 'color_code' => '#c2410c', 'pages' => 1, 'ats_score' => 94, 'popularity' => 87, 'date' => '2026-03-24', 'layout' => 'ats', 'badge' => '', 'desc' => 'A clean layout optimized for metrics, audits and KPI reporting.'],
    ['id' => 'ana_4', 'name' => 'Analyst Slate Structured', 'category' => 'Business Analyst', 'color' => 'Slate', 'color_code' => '#334155', 'pages' => 2, 'ats_score' => 92, 'popularity' => 91, 'date' => '2026-04-18', 'layout' => 'professional', 'badge' => '', 'desc' => 'Extended layout prioritizing project management and BI tools.'],
    ['id' => 'ana_5', 'name' => 'Analyst Minimal Scale', 'category' => 'Business Analyst', 'color' => 'Dark', 'color_code' => '#27272a', 'pages' => 1, 'ats_score' => 97, 'popularity' => 92, 'date' => '2026-05-15', 'layout' => 'minimal', 'badge' => '', 'desc' => 'Clean minimalist grid displaying qualifications directly.'],

    // Executive (3 templates)
    ['id' => 'exec_1', 'name' => 'Executive Crown Maroon', 'category' => 'Executive', 'color' => 'Red', 'color_code' => '#7f1d1d', 'pages' => 2, 'ats_score' => 94, 'popularity' => 98, 'date' => '2026-01-12', 'layout' => 'executive', 'badge' => 'Best for Execs', 'desc' => 'Premium dual column design for board officers and senior directors.'],
    ['id' => 'exec_2', 'name' => 'Executive Royal Navy', 'category' => 'Executive', 'color' => 'Blue', 'color_code' => '#0f172a', 'pages' => 2, 'ats_score' => 95, 'popularity' => 95, 'date' => '2026-03-10', 'layout' => 'executive', 'badge' => 'Premium', 'desc' => 'Sleek executive template with elegant headers and structured layouts.'],
    ['id' => 'exec_3', 'name' => 'Executive Elegant Slate', 'category' => 'Executive', 'color' => 'Slate', 'color_code' => '#1e293b', 'pages' => 2, 'ats_score' => 93, 'popularity' => 92, 'date' => '2026-05-04', 'layout' => 'elegant', 'badge' => '', 'desc' => 'Warm serif typography designed to showcase long leadership profiles.']
];
?>

<style>
/* Canberra & Premium Novoresume Dashboard Feel */
.gallery-wrapper {
    display: grid;
    grid-template-columns: 280px 1fr;
    min-height: calc(100vh - 80px);
    background: #fdfafa;
}

.gallery-sidebar {
    background: #ffffff;
    border-right: 1px solid var(--border-color);
    padding: 30px 20px;
    position: sticky;
    top: 80px;
    max-height: calc(100vh - 80px);
    overflow-y: auto;
}

.gallery-content {
    padding: 40px;
    overflow-x: hidden;
}

/* Category Filter Chips */
.filter-header-group {
    background: #ffffff;
    padding: 20px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    margin-bottom: 30px;
    box-shadow: var(--shadow-main);
}

.color-dot {
    display: inline-block;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    margin-right: 8px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: var(--transition);
}
.color-dot.active, .color-dot:hover {
    border-color: var(--primary);
    transform: scale(1.15);
}

.search-input-group {
    position: relative;
}
.search-icon-inside {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}

/* Template Card Styling - Premium Mock Preview */
.tpl-card-outer {
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: var(--transition);
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.015);
}
.tpl-card-outer:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 36px rgba(220, 38, 38, 0.06);
    border-color: rgba(220, 38, 38, 0.2);
}

/* Dynamic CSS Mock Resume Thumbnail */
.tpl-mock-preview {
    height: 260px;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    padding: 18px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    gap: 8px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.tpl-mock-preview .mock-header {
    height: 38px;
    border-radius: 4px;
    width: 100%;
}
.tpl-mock-preview .mock-line {
    background: #e2e8f0;
    height: 6px;
    border-radius: 3px;
    width: 100%;
}
.tpl-mock-preview .mock-line.short { width: 60%; }
.tpl-mock-preview .mock-line.medium { width: 80%; }
.tpl-mock-preview .mock-section {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-top: 5px;
}

/* Hover overlay actions */
.tpl-preview-overlay {
    position: absolute;
    inset: 0;
    background: rgba(31, 17, 17, 0.7);
    backdrop-filter: blur(4px);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 12px;
    opacity: 0;
    transition: var(--transition);
    z-index: 5;
}
.tpl-card-outer:hover .tpl-preview-overlay {
    opacity: 1;
}

.tpl-details {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.tpl-card-tag {
    position: absolute;
    top: 12px;
    left: 12px;
    background: var(--primary);
    color: #ffffff;
    padding: 3px 10px;
    font-size: 10px;
    font-weight: 700;
    border-radius: 999px;
    z-index: 4;
    text-transform: uppercase;
}

.tpl-card-score {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid var(--border-color);
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 800;
    border-radius: 6px;
    z-index: 4;
    color: var(--success);
    display: flex;
    align-items: center;
    gap: 3px;
}

/* Recommendation groups banner */
.recommend-card {
    background: linear-gradient(135deg, #ffffff 0%, #fffbfb 100%);
    border-left: 5px solid var(--primary);
    transition: var(--transition);
}
.recommend-card:hover {
    transform: scale(1.02);
}

/* Zoomable interactive Modal Preview content styling */
#modal-resume-container {
    transition: transform 0.2s ease;
    transform-origin: top center;
}
</style>

<div class="gallery-wrapper">
    <!-- Sidebar Filters -->
    <aside class="gallery-sidebar d-none d-lg-block">
        <h4 class="mb-4" style="font-size:18px;font-weight:800;">Filters</h4>
        
        <div class="mb-4">
            <label class="form-label">Search Templates</label>
            <div class="search-input-group">
                <input type="text" id="searchBar" class="form-control" placeholder="Search template..." onkeyup="filterTemplates()">
                <span class="search-icon-inside">🔍</span>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Sort By</label>
            <select id="sortSelector" class="form-control" onchange="sortTemplates()">
                <option value="popular">Most Popular</option>
                <option value="score">Highest ATS Score</option>
                <option value="latest">Latest Addition</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label">Page Count</label>
            <div class="d-flex gap-2">
                <button class="btn btn-secondary btn-sm flex-fill active" id="btnPageAll" onclick="filterPages('all', this)">All</button>
                <button class="btn btn-secondary btn-sm flex-fill" id="btnPage1" onclick="filterPages(1, this)">1 Page</button>
                <button class="btn btn-secondary btn-sm flex-fill" id="btnPage2" onclick="filterPages(2, this)">2 Pages</button>
            </div>
        </div>

        <div class="mb-4">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="atsSwitch" onchange="filterTemplates()">
                <label class="form-check-label font-weight-600 text-dark" for="atsSwitch">ATS Friendly Only (95+ Score)</label>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Filter Color Accent</label>
            <div class="d-flex flex-wrap gap-1">
                <span class="color-dot active" style="background:#cbd5e1;" onclick="filterColor('all', this)" title="All Colors"></span>
                <span class="color-dot" style="background:#dc2626;" onclick="filterColor('Red', this)" title="Red"></span>
                <span class="color-dot" style="background:#0284c7;" onclick="filterColor('Blue', this)" title="Blue"></span>
                <span class="color-dot" style="background:#16a34a;" onclick="filterColor('Green', this)" title="Green"></span>
                <span class="color-dot" style="background:#1f2937;" onclick="filterColor('Dark', this)" title="Dark"></span>
                <span class="color-dot" style="background:#7c3aed;" onclick="filterColor('Purple', this)" title="Purple"></span>
                <span class="color-dot" style="background:#ea580c;" onclick="filterColor('Orange', this)" title="Orange"></span>
                <span class="color-dot" style="background:#e11d48;" onclick="filterColor('Rose', this)" title="Rose"></span>
                <span class="color-dot" style="background:#475569;" onclick="filterColor('Slate', this)" title="Slate"></span>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Category</label>
            <div class="list-group list-group-flush" style="font-size:14px;">
                <button class="list-group-item list-group-item-action border-0 px-2 py-2 active rounded font-weight-600" onclick="filterCategory('all', this)">All Categories</button>
                <button class="list-group-item list-group-item-action border-0 px-2 py-2" onclick="filterCategory('ATS Friendly', this)">ATS Friendly</button>
                <button class="list-group-item list-group-item-action border-0 px-2 py-2" onclick="filterCategory('Modern', this)">Modern</button>
                <button class="list-group-item list-group-item-action border-0 px-2 py-2" onclick="filterCategory('Professional', this)">Professional</button>
                <button class="list-group-item list-group-item-action border-0 px-2 py-2" onclick="filterCategory('Minimalist', this)">Minimalist</button>
                <button class="list-group-item list-group-item-action border-0 px-2 py-2" onclick="filterCategory('Creative', this)">Creative</button>
                <button class="list-group-item list-group-item-action border-0 px-2 py-2" onclick="filterCategory('Student/Fresher', this)">Student / Fresher</button>
                <button class="list-group-item list-group-item-action border-0 px-2 py-2" onclick="filterCategory('Software Developer', this)">Software Developer</button>
                <button class="list-group-item list-group-item-action border-0 px-2 py-2" onclick="filterCategory('Designer', this)">Designer</button>
                <button class="list-group-item list-group-item-action border-0 px-2 py-2" onclick="filterCategory('Business Analyst', this)">Business Analyst</button>
                <button class="list-group-item list-group-item-action border-0 px-2 py-2" onclick="filterCategory('Executive', this)">Executive</button>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="gallery-content">
        <!-- Top Hero Section / Canva Dashboard feel -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="mb-2" style="font-size: 32px; font-weight: 800; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Canva-Style Resume Template Gallery</h1>
                <p class="text-muted" style="max-width: 700px; margin: 0;">Explore over 50+ hand-crafted, high-performing resume layouts designed to win recruiter attention. Dynamic custom filtering and full ATS validation score reporting.</p>
            </div>
            <div>
                <a href="uploads/sample_resume.pdf" class="btn btn-primary" download>📥 Download Sample PDF</a>
            </div>
        </div>

        <!-- Curated Recommendations Row -->
        <h3 class="mb-3" style="font-size:20px;font-weight:800;display:flex;align-items:center;gap:8px;">💡 Curated Recommendations</h3>
        <div class="row g-3 mb-5">
            <div class="col-md-3">
                <div class="card p-3 recommend-card" style="cursor:pointer;" onclick="quickFilterRecommendation('best_fresher')">
                    <h5 style="font-size:15px;font-weight:700;color:var(--primary);">🎓 Best for Freshers</h5>
                    <p class="text-muted mb-0" style="font-size:12px;">Prioritizes internships, education & projects.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 recommend-card" style="cursor:pointer;" onclick="quickFilterRecommendation('best_it')">
                    <h5 style="font-size:15px;font-weight:700;color:#0369a1;">💻 Best for IT & Dev</h5>
                    <p class="text-muted mb-0" style="font-size:12px;">Highlight categories & programming stacks.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 recommend-card" style="cursor:pointer;" onclick="quickFilterRecommendation('best_exec')">
                    <h5 style="font-size:15px;font-weight:700;color:#1e293b;">👑 Best for Experienced</h5>
                    <p class="text-muted mb-0" style="font-size:12px;">Two-page formats for corporate leaders.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 recommend-card" style="cursor:pointer;" onclick="quickFilterRecommendation('best_ats')">
                    <h5 style="font-size:15px;font-weight:700;color:var(--success);">🤖 Best ATS Scores</h5>
                    <p class="text-muted mb-0" style="font-size:12px;">98%+ Parser friendliness rating guaranteed.</p>
                </div>
            </div>
        </div>

        <!-- Mobile Filter Toggle Button -->
        <div class="d-lg-none mb-4">
            <button class="btn btn-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#mobileFilters" aria-expanded="false">
                ⚙️ Toggle Advanced Filters & Search
            </button>
            <div class="collapse mt-2 p-3 bg-white border rounded" id="mobileFilters">
                <div class="mb-3">
                    <label class="form-label">Search Templates</label>
                    <input type="text" id="searchBarMobile" class="form-control" placeholder="Search template..." onkeyup="syncSearchMobile()">
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select id="categorySelectMobile" class="form-control" onchange="syncCategoryMobile()">
                        <option value="all">All Categories</option>
                        <option value="ATS Friendly">ATS Friendly</option>
                        <option value="Modern">Modern</option>
                        <option value="Professional">Professional</option>
                        <option value="Minimalist">Minimalist</option>
                        <option value="Creative">Creative</option>
                        <option value="Student/Fresher">Student/Fresher</option>
                        <option value="Software Developer">Software Developer</option>
                        <option value="Designer">Designer</option>
                        <option value="Business Analyst">Business Analyst</option>
                        <option value="Executive">Executive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Templates Grid -->
        <div class="row g-4" id="templatesGrid">
            <?php foreach ($templates as $t): ?>
                <div class="col-sm-6 col-md-4 col-xl-3 template-item" 
                     data-id="<?= $t['id'] ?>"
                     data-name="<?= htmlspecialchars($t['name']) ?>"
                     data-category="<?= htmlspecialchars($t['category']) ?>"
                     data-color="<?= $t['color'] ?>"
                     data-pages="<?= $t['pages'] ?>"
                     data-ats="<?= $t['ats_score'] ?>"
                     data-popularity="<?= $t['popularity'] ?>"
                     data-date="<?= $t['date'] ?>"
                     data-layout="<?= $t['layout'] ?>">
                     
                    <div class="tpl-card-outer">
                        <?php if ($t['badge']): ?>
                            <span class="tpl-card-tag" style="background:<?= $t['color_code'] ?>;"><?= $t['badge'] ?></span>
                        <?php endif; ?>
                        <span class="tpl-card-score">🤖 <?= $t['ats_score'] ?>%</span>

                        <!-- Dynamic CSS representation of the template preview card -->
                        <div class="tpl-mock-preview" onclick="openPreviewModal('<?= $t['id'] ?>')">
                            <!-- ATS / Sidebar / Modern / Minimal preview indicators -->
                            <?php if ($t['layout'] === 'professional' || $t['layout'] === 'executive'): ?>
                                <div class="d-flex h-100 w-100 gap-2">
                                    <div class="mock-sidebar" style="background:<?= $t['color_code'] ?>;width:30%;border-radius:4px;opacity:0.85;"></div>
                                    <div class="d-flex flex-column gap-2 flex-fill pt-1">
                                        <div class="mock-line" style="background:<?= $t['color_code'] ?>;width:50%;"></div>
                                        <div class="mock-line short"></div>
                                        <div class="mock-line"></div>
                                        <div class="mock-line medium"></div>
                                    </div>
                                </div>
                            <?php elseif ($t['layout'] === 'modern' || $t['layout'] === 'tech_sleek'): ?>
                                <div class="mock-header" style="background:linear-gradient(135deg, <?= $t['color_code'] ?> 0%, #1e293b 100%);"></div>
                                <div class="mock-line short"></div>
                                <div class="mock-line"></div>
                                <div class="mock-line medium"></div>
                                <div class="mock-line"></div>
                            <?php else: // ats, minimal, creative ?>
                                <div class="mock-line" style="background:<?= $t['color_code'] ?>;width:40%;height:10px;"></div>
                                <div class="mock-line short"></div>
                                <div class="mock-line"></div>
                                <div class="mock-line medium"></div>
                                <div class="mock-line"></div>
                                <div class="mock-line short"></div>
                            <?php endif; ?>

                            <!-- Hover Overlay -->
                            <div class="tpl-preview-overlay">
                                <button type="button" class="btn btn-primary btn-sm px-4" onclick="openPreviewModal('<?= $t['id'] ?>')">👁️ Quick Preview</button>
                                <a href="resume_builder.php?template=<?= $t['layout'] ?>&trial=1" class="btn btn-secondary btn-sm px-4">✏️ Use Template</a>
                            </div>
                        </div>

                        <div class="tpl-details">
                            <div>
                                <h4 class="mb-1" style="font-size:15px;font-weight:700;color:var(--text-dark);"><?= htmlspecialchars($t['name']) ?></h4>
                                <span class="badge badge-info mb-3"><?= htmlspecialchars($t['category']) ?></span>
                                <p class="text-muted mb-0" style="font-size:12px;line-height:1.4;"><?= htmlspecialchars($t['desc']) ?></p>
                            </div>
                            <div class="d-flex gap-2 mt-3 pt-3 border-top">
                                <button class="btn btn-secondary btn-sm flex-fill" onclick="openPreviewModal('<?= $t['id'] ?>')">Preview</button>
                                <a href="resume_builder.php?template=<?= $t['layout'] ?>&trial=1" class="btn btn-primary btn-sm flex-fill">Use</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- Premium Live Zoomable Resume Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: var(--radius-md); overflow: hidden;">
            <div class="modal-header bg-light border-bottom">
                <div>
                    <h5 class="modal-title font-weight-800 text-dark" id="previewModalLabel">Template Live Preview</h5>
                    <span class="badge badge-info" id="modalTemplateCategory">ATS Friendly</span>
                    <span class="text-muted ms-2" id="modalTemplatePages" style="font-size: 13px;">1 Page</span>
                </div>
                <!-- Zoom Controls -->
                <div class="d-flex align-items-center gap-2 ms-auto me-3">
                    <span style="font-size:12px;font-weight:600;color:var(--text-muted);">Zoom:</span>
                    <button class="btn btn-secondary btn-sm py-1 px-2" onclick="adjustZoom(-0.1)">➖</button>
                    <span id="zoomLabel" style="font-size:13px;font-weight:700;min-width:40px;text-align:center;">100%</span>
                    <button class="btn btn-secondary btn-sm py-1 px-2" onclick="adjustZoom(0.1)">➕</button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body bg-light" style="max-height: 70vh; overflow: auto; display: flex; justify-content: center; align-items: flex-start; padding: 30px;">
                <!-- Target Container that zooms -->
                <div id="modal-resume-container" style="width: 100%; max-width: 650px;">
                    <!-- Dynamically populated via javascript depending on layout and colors -->
                </div>
            </div>

            <div class="modal-footer bg-light border-top d-flex justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:14px;color:var(--text-muted);">ATS Compatibility Score:</span>
                    <strong class="text-success" style="font-size:18px;" id="modalTemplateScore">98%</strong>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <a href="resume_builder.php?template=ats&trial=1" id="modalUseBtn" class="btn btn-primary btn-sm">Use This Template</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Filter State Variables
let currentCategory = 'all';
let currentColor = 'all';
let currentPageFilter = 'all';
let currentRecommendation = 'all';
let zoomScale = 1.0;

function filterCategory(cat, el) {
    currentCategory = cat;
    currentRecommendation = 'all'; // reset recommendation group
    if (el) {
        document.querySelectorAll('.gallery-sidebar .list-group-item').forEach(item => item.classList.remove('active', 'font-weight-600'));
        el.classList.add('active', 'font-weight-600');
    }
    filterTemplates();
}

function filterColor(col, el) {
    currentColor = col;
    if (el) {
        document.querySelectorAll('.color-dot').forEach(dot => dot.classList.remove('active'));
        el.classList.add('active');
    }
    filterTemplates();
}

function filterPages(pages, el) {
    currentPageFilter = pages;
    if (el) {
        document.querySelectorAll('#btnPageAll, #btnPage1, #btnPage2').forEach(btn => btn.classList.remove('active'));
        el.classList.add('active');
    }
    filterTemplates();
}

function quickFilterRecommendation(rec) {
    currentRecommendation = rec;
    // reset other filters to prevent overlapping limits
    currentCategory = 'all';
    currentColor = 'all';
    currentPageFilter = 'all';
    document.querySelectorAll('.gallery-sidebar .list-group-item').forEach(item => item.classList.remove('active'));
    document.querySelectorAll('.gallery-sidebar .list-group-item')[0].classList.add('active');
    document.querySelectorAll('.color-dot').forEach(dot => dot.classList.remove('active'));
    document.querySelectorAll('.color-dot')[0].classList.add('active');
    document.querySelectorAll('#btnPageAll, #btnPage1, #btnPage2').forEach(btn => btn.classList.remove('active'));
    document.getElementById('btnPageAll').classList.add('active');
    
    filterTemplates();
}

function filterTemplates() {
    const searchVal = document.getElementById('searchBar').value.toLowerCase();
    const atsOnly = document.getElementById('atsSwitch').checked;
    const items = document.querySelectorAll('.template-item');

    items.forEach(item => {
        const name = item.getAttribute('data-name').toLowerCase();
        const category = item.getAttribute('data-category');
        const color = item.getAttribute('data-color');
        const pages = parseInt(item.getAttribute('data-pages'));
        const atsScore = parseInt(item.getAttribute('data-ats'));
        const id = item.getAttribute('data-id');

        let match = true;

        // Search Match
        if (searchVal && !name.includes(searchVal)) {
            match = false;
        }

        // Category Match
        if (currentCategory !== 'all' && category !== currentCategory) {
            match = false;
        }

        // Color Match
        if (currentColor !== 'all' && color !== currentColor) {
            match = false;
        }

        // Page Count Match
        if (currentPageFilter !== 'all' && pages !== parseInt(currentPageFilter)) {
            match = false;
        }

        // ATS Switch Match
        if (atsOnly && atsScore < 95) {
            match = false;
        }

        // Recommendation matches
        if (currentRecommendation !== 'all') {
            if (currentRecommendation === 'best_fresher' && category !== 'Student/Fresher') {
                match = false;
            }
            if (currentRecommendation === 'best_it' && category !== 'Software Developer') {
                match = false;
            }
            if (currentRecommendation === 'best_exec' && category !== 'Executive' && pages !== 2) {
                match = false;
            }
            if (currentRecommendation === 'best_ats' && atsScore < 97) {
                match = false;
            }
        }

        if (match) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function sortTemplates() {
    const sortVal = document.getElementById('sortSelector').value;
    const grid = document.getElementById('templatesGrid');
    const items = Array.from(grid.querySelectorAll('.template-item'));

    items.sort((a, b) => {
        if (sortVal === 'score') {
            return parseInt(b.getAttribute('data-ats')) - parseInt(a.getAttribute('data-ats'));
        } else if (sortVal === 'latest') {
            return new Date(b.getAttribute('data-date')) - new Date(a.getAttribute('data-date'));
        } else { // popular
            return parseInt(b.getAttribute('data-popularity')) - parseInt(a.getAttribute('data-popularity'));
        }
    });

    items.forEach(item => grid.appendChild(item));
}

function syncSearchMobile() {
    const mobileVal = document.getElementById('searchBarMobile').value;
    document.getElementById('searchBar').value = mobileVal;
    filterTemplates();
}

function syncCategoryMobile() {
    const mobileCat = document.getElementById('categorySelectMobile').value;
    filterCategory(mobileCat);
}

// Live Preview Modal rendering depending on template selected
function openPreviewModal(id) {
    const item = document.querySelector(`.template-item[data-id="${id}"]`);
    if (!item) return;

    const name = item.getAttribute('data-name');
    const category = item.getAttribute('data-category');
    const pages = item.getAttribute('data-pages');
    const atsScore = item.getAttribute('data-ats');
    const layout = item.getAttribute('data-layout');
    const color = item.getAttribute('data-color');

    // Color map for previews
    const colors = {
        'Red': '#dc2626',
        'Blue': '#2563eb',
        'Green': '#16a34a',
        'Dark': '#1f2937',
        'Purple': '#7c3aed',
        'Orange': '#ea580c',
        'Rose': '#e11d48',
        'Slate': '#475569',
        'Indigo': '#4f46e5',
        'Teal': '#0d9488'
    };
    const activeColor = colors[color] || '#dc2626';

    document.getElementById('previewModalLabel').textContent = name;
    document.getElementById('modalTemplateCategory').textContent = category;
    document.getElementById('modalTemplatePages').textContent = `${pages} Page${pages > 1 ? 's' : ''}`;
    document.getElementById('modalTemplateScore').textContent = `${atsScore}%`;
    document.getElementById('modalUseBtn').href = `resume_builder.php?template=${layout}&trial=1`;

    // Dynamic mock content building for preview modal
    const resumeContainer = document.getElementById('modal-resume-container');
    zoomScale = 1.0;
    document.getElementById('zoomLabel').textContent = '100%';
    resumeContainer.style.transform = `scale(${zoomScale})`;

    let mockHtml = '';
    
    if (layout === 'professional' || layout === 'executive') {
        mockHtml = `
            <div style="background:#ffffff; border:1px solid #cbd5e1; box-shadow:0 10px 25px rgba(0,0,0,0.05); font-family:sans-serif; color:#334155; display:grid; grid-template-columns:30% 70%; min-height:550px;">
                <div style="background:${activeColor}; color:#ffffff; padding:20px; display:flex; flex-direction:column; gap:20px;">
                    <div>
                        <h3 style="margin:0; font-size:18px; font-weight:800;">John Doe</h3>
                        <div style="font-size:11px; opacity:0.8; margin-top:4px;">Senior Software Engineer</div>
                    </div>
                    <div style="font-size:10px; display:flex; flex-direction:column; gap:6px; opacity:0.9;">
                        <div>✉ john.doe@email.com</div>
                        <div>📞 +1 (555) 019-2834</div>
                        <div>📍 San Francisco, CA</div>
                    </div>
                    <div>
                        <h4 style="font-size:11px; font-weight:700; text-transform:uppercase; border-bottom:1px solid rgba(255,255,255,0.2); padding-bottom:4px; margin-bottom:8px;">Core Skills</h4>
                        <div style="font-size:10px; display:flex; flex-direction:column; gap:4px;">
                            <div>• React & Next.js</div>
                            <div>• Node.js & APIs</div>
                            <div>• Python & cloud</div>
                        </div>
                    </div>
                </div>
                <div style="padding:25px; display:flex; flex-direction:column; gap:16px;">
                    <div>
                        <h4 style="font-size:12px; font-weight:700; text-transform:uppercase; color:${activeColor}; border-bottom:2px solid ${activeColor}; padding-bottom:4px; margin-bottom:8px;">Profile</h4>
                        <p style="font-size:11px; line-height:1.5; margin:0;">Innovative and results-driven Senior Software Engineer with 8+ years of experience designing, building, and optimizing scalable web applications.</p>
                    </div>
                    <div>
                        <h4 style="font-size:12px; font-weight:700; text-transform:uppercase; color:${activeColor}; border-bottom:2px solid ${activeColor}; padding-bottom:4px; margin-bottom:8px;">Experience</h4>
                        <div style="margin-bottom:8px;">
                            <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700;">
                                <span>Google Inc. - Sr. Engineer</span>
                                <span>2021 - Present</span>
                            </div>
                            <div style="font-size:10px; line-height:1.4; color:#64748b; margin-top:2px;">• Led design of analytical tools reducing database load by 40%.</div>
                        </div>
                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700;">
                                <span>Tech Solutions - Developer</span>
                                <span>2018 - 2021</span>
                            </div>
                            <div style="font-size:10px; line-height:1.4; color:#64748b; margin-top:2px;">• Built responsive interfaces using React & Next.</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else if (layout === 'modern' || layout === 'tech_sleek') {
        mockHtml = `
            <div style="background:#ffffff; border:1px solid #cbd5e1; box-shadow:0 10px 25px rgba(0,0,0,0.05); font-family:sans-serif; color:#334155; min-height:550px;">
                <div style="background:linear-gradient(135deg, ${activeColor} 0%, #1e293b 100%); color:#ffffff; padding:25px;">
                    <h3 style="margin:0; font-size:24px; font-weight:800;">John Doe</h3>
                    <div style="font-size:13px; opacity:0.9; margin-top:4px;">Senior Software Engineer</div>
                    <div style="display:flex; gap:15px; font-size:10px; margin-top:10px; opacity:0.8;">
                        <span>✉ john.doe@email.com</span>
                        <span>📞 +1 (555) 019-2834</span>
                        <span>📍 San Francisco</span>
                    </div>
                </div>
                <div style="padding:25px; display:flex; flex-direction:column; gap:16px;">
                    <div>
                        <h4 style="font-size:12px; font-weight:700; color:${activeColor}; display:flex; align-items:center; gap:6px; margin-bottom:8px;">
                            <span style="width:3px; height:12px; background:${activeColor}; display:inline-block;"></span>Summary
                        </h4>
                        <p style="font-size:11px; line-height:1.5; margin:0;">Passionate software developer focusing on modular system design, rapid prototyping, and automated unit testing procedures.</p>
                    </div>
                    <div>
                        <h4 style="font-size:12px; font-weight:700; color:${activeColor}; display:flex; align-items:center; gap:6px; margin-bottom:8px;">
                            <span style="width:3px; height:12px; background:${activeColor}; display:inline-block;"></span>Experience
                        </h4>
                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700;">
                                <span>Google Inc. - Sr. Engineer</span>
                                <span style="background:#f1f5f9; padding:1px 6px; border-radius:4px; font-size:9px;">Present</span>
                            </div>
                            <p style="font-size:10px; color:#475569; margin:4px 0 0 0;">Led redesign of the analytics dashboard, improving query response times by 40%.</p>
                        </div>
                    </div>
                    <div>
                        <h4 style="font-size:12px; font-weight:700; color:${activeColor}; display:flex; align-items:center; gap:6px; margin-bottom:8px;">
                            <span style="width:3px; height:12px; background:${activeColor}; display:inline-block;"></span>Skills
                        </h4>
                        <div style="display:flex; flex-wrap:wrap; gap:6px;">
                            <span style="background:#f1f5f9; color:${activeColor}; padding:3px 8px; border-radius:12px; font-size:10px; font-weight:600;">React</span>
                            <span style="background:#f1f5f9; color:${activeColor}; padding:3px 8px; border-radius:12px; font-size:10px; font-weight:600;">Node.js</span>
                            <span style="background:#f1f5f9; color:${activeColor}; padding:3px 8px; border-radius:12px; font-size:10px; font-weight:600;">Python</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else { // ats, minimal, elegant, creative
        mockHtml = `
            <div style="background:#ffffff; border:1px solid #cbd5e1; box-shadow:0 10px 25px rgba(0,0,0,0.05); font-family:sans-serif; color:#334155; padding:30px; min-height:550px; display:flex; flex-direction:column; gap:20px;">
                <div style="border-bottom:2px solid ${activeColor}; padding-bottom:12px; text-align:center;">
                    <h3 style="margin:0; font-size:24px; font-weight:800; color:#1e293b;">John Doe</h3>
                    <div style="font-size:12px; color:${activeColor}; font-weight:600; margin-top:2px;">Senior Software Engineer</div>
                    <div style="display:flex; justify-content:center; gap:15px; font-size:10px; margin-top:8px; color:#64748b;">
                        <span>✉ john@example.com</span>
                        <span>📞 (555) 019-2834</span>
                        <span>📍 San Francisco, CA</span>
                    </div>
                </div>
                <div>
                    <h4 style="font-size:11px; font-weight:700; text-transform:uppercase; color:${activeColor}; letter-spacing:0.05em; margin-bottom:6px;">Summary</h4>
                    <p style="font-size:11px; line-height:1.5; margin:0;">Experienced developer specializing in frontend and backend integrations, AWS infrastructure deployment, and developer mentoring.</p>
                </div>
                <div>
                    <h4 style="font-size:11px; font-weight:700; text-transform:uppercase; color:${activeColor}; letter-spacing:0.05em; margin-bottom:6px;">Experience</h4>
                    <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700; margin-bottom:3px;">
                        <span>Senior Developer — Google Inc.</span>
                        <span>2021 – Present</span>
                    </div>
                    <p style="font-size:10px; color:#475569; margin:0; line-height:1.4;">• Led and deployed critical customer analytics dashboard utilizing React, Redux, and Node.js.</p>
                </div>
                <div>
                    <h4 style="font-size:11px; font-weight:700; text-transform:uppercase; color:${activeColor}; letter-spacing:0.05em; margin-bottom:6px;">Education</h4>
                    <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700;">
                        <span>Stanford University — MS Computer Science</span>
                        <span>2018</span>
                    </div>
                </div>
            </div>
        `;
    }

    resumeContainer.innerHTML = mockHtml;
    
    // Open Modal
    const modalEl = document.getElementById('previewModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function adjustZoom(amount) {
    zoomScale = Math.max(0.5, Math.min(1.5, zoomScale + amount));
    const container = document.getElementById('modal-resume-container');
    container.style.transform = `scale(${zoomScale})`;
    document.getElementById('zoomLabel').textContent = `${Math.round(zoomScale * 100)}%`;
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
