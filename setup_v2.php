<?php
// setup_v2.php - Run once to set up the full upgraded database
$host = 'localhost';
$user = 'root';
$pass = 'pass';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Setup v2 - AI Resume Analyzer</title>
    <link href='https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap' rel='stylesheet'>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0d0e15; color: #e4e6eb; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .container { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 40px; max-width: 700px; width: 100%; box-shadow: 0 8px 32px 0 rgba(0,0,0,0.37); }
        h1 { color: #a855f7; font-size: 28px; margin-bottom: 20px; font-weight: 800; text-align: center; }
        .log { background-color: #07080d; border-radius: 8px; padding: 20px; font-family: monospace; font-size: 14px; line-height: 1.6; max-height: 450px; overflow-y: auto; border: 1px solid rgba(168,85,247,0.2); color: #38bdf8; }
        .success { color: #4ade80; } .error { color: #f87171; } .info { color: #fbbf24; }
        .btn { display: block; width: 100%; padding: 12px; background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); border: none; border-radius: 8px; color: white; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; margin-top: 25px; }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🚀 AI Resume Analyzer v2 Setup</h1>
    <div class='log'>";

$conn = @new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    echo "<span class='error'>[ERROR] MySQL Connection Failed: " . $conn->connect_error . "</span><br>";
    echo "</div></div></body></html>";
    exit;
}
echo "<span class='success'>[OK] Connected to MySQL Server.</span><br>";

$dbName = 'resume_analyzer';
if ($conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    echo "<span class='success'>[OK] Database '$dbName' ready.</span><br>";
} else {
    echo "<span class='error'>[ERROR] " . $conn->error . "</span><br>"; exit;
}
$conn->select_db($dbName);

$tables = [
    "users" => "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(30) DEFAULT NULL,
        `profile_photo` VARCHAR(512) DEFAULT NULL,
        `bio` TEXT DEFAULT NULL,
        `linkedin_url` VARCHAR(512) DEFAULT NULL,
        `github_url` VARCHAR(512) DEFAULT NULL,
        `portfolio_url` VARCHAR(512) DEFAULT NULL,
        `city` VARCHAR(100) DEFAULT NULL,
        `country` VARCHAR(100) DEFAULT NULL,
        `is_verified` TINYINT(1) DEFAULT 0,
        `verification_token` VARCHAR(255) DEFAULT NULL,
        `reset_token` VARCHAR(255) DEFAULT NULL,
        `reset_token_expires` DATETIME DEFAULT NULL,
        `theme_preference` ENUM('dark','light') DEFAULT 'dark',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "admins" => "CREATE TABLE IF NOT EXISTS `admins` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "resume_profiles" => "CREATE TABLE IF NOT EXISTS `resume_profiles` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `title` VARCHAR(255) NOT NULL DEFAULT 'My Resume',
        `template` ENUM('ats','professional','modern','creative') DEFAULT 'ats',
        `full_name` VARCHAR(255) DEFAULT NULL,
        `job_title` VARCHAR(255) DEFAULT NULL,
        `email` VARCHAR(255) DEFAULT NULL,
        `phone` VARCHAR(50) DEFAULT NULL,
        `address` TEXT DEFAULT NULL,
        `linkedin` VARCHAR(512) DEFAULT NULL,
        `github` VARCHAR(512) DEFAULT NULL,
        `portfolio` VARCHAR(512) DEFAULT NULL,
        `summary` TEXT DEFAULT NULL,
        `languages` JSON DEFAULT NULL,
        `references_data` JSON DEFAULT NULL,
        `share_token` VARCHAR(100) DEFAULT NULL,
        `is_public` TINYINT(1) DEFAULT 0,
        `version` INT DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "education" => "CREATE TABLE IF NOT EXISTS `education` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `resume_id` INT NOT NULL,
        `institution` VARCHAR(255) NOT NULL,
        `degree` VARCHAR(255) NOT NULL,
        `field_of_study` VARCHAR(255) DEFAULT NULL,
        `start_year` VARCHAR(10) DEFAULT NULL,
        `end_year` VARCHAR(10) DEFAULT NULL,
        `gpa` VARCHAR(20) DEFAULT NULL,
        `description` TEXT DEFAULT NULL,
        `sort_order` INT DEFAULT 0,
        FOREIGN KEY (`resume_id`) REFERENCES `resume_profiles`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "resume_skills" => "CREATE TABLE IF NOT EXISTS `resume_skills` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `resume_id` INT NOT NULL,
        `skill_name` VARCHAR(100) NOT NULL,
        `level` ENUM('beginner','intermediate','advanced','expert') DEFAULT 'intermediate',
        `category` VARCHAR(100) DEFAULT NULL,
        `sort_order` INT DEFAULT 0,
        FOREIGN KEY (`resume_id`) REFERENCES `resume_profiles`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "experience" => "CREATE TABLE IF NOT EXISTS `experience` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `resume_id` INT NOT NULL,
        `company` VARCHAR(255) NOT NULL,
        `position` VARCHAR(255) NOT NULL,
        `location` VARCHAR(255) DEFAULT NULL,
        `start_date` VARCHAR(20) DEFAULT NULL,
        `end_date` VARCHAR(20) DEFAULT NULL,
        `is_current` TINYINT(1) DEFAULT 0,
        `description` TEXT DEFAULT NULL,
        `sort_order` INT DEFAULT 0,
        FOREIGN KEY (`resume_id`) REFERENCES `resume_profiles`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "projects" => "CREATE TABLE IF NOT EXISTS `projects` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `resume_id` INT NOT NULL,
        `project_name` VARCHAR(255) NOT NULL,
        `role` VARCHAR(255) DEFAULT NULL,
        `technologies` VARCHAR(512) DEFAULT NULL,
        `description` TEXT DEFAULT NULL,
        `url` VARCHAR(512) DEFAULT NULL,
        `github_url` VARCHAR(512) DEFAULT NULL,
        `start_date` VARCHAR(20) DEFAULT NULL,
        `end_date` VARCHAR(20) DEFAULT NULL,
        `sort_order` INT DEFAULT 0,
        FOREIGN KEY (`resume_id`) REFERENCES `resume_profiles`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "certifications" => "CREATE TABLE IF NOT EXISTS `certifications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `resume_id` INT NOT NULL,
        `cert_name` VARCHAR(255) NOT NULL,
        `issuer` VARCHAR(255) DEFAULT NULL,
        `issue_date` VARCHAR(20) DEFAULT NULL,
        `expiry_date` VARCHAR(20) DEFAULT NULL,
        `credential_id` VARCHAR(255) DEFAULT NULL,
        `cert_url` VARCHAR(512) DEFAULT NULL,
        `sort_order` INT DEFAULT 0,
        FOREIGN KEY (`resume_id`) REFERENCES `resume_profiles`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "achievements" => "CREATE TABLE IF NOT EXISTS `achievements` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `resume_id` INT NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `date` VARCHAR(20) DEFAULT NULL,
        `sort_order` INT DEFAULT 0,
        FOREIGN KEY (`resume_id`) REFERENCES `resume_profiles`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "resumes" => "CREATE TABLE IF NOT EXISTS `resumes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `file_name` VARCHAR(255) NOT NULL,
        `file_path` VARCHAR(512) NOT NULL,
        `target_role` VARCHAR(255) DEFAULT NULL,
        `status` VARCHAR(50) DEFAULT 'Review',
        `text_content` LONGTEXT DEFAULT NULL,
        `ats_score` INT DEFAULT NULL,
        `analysis_result` LONGTEXT DEFAULT NULL,
        `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "skills" => "CREATE TABLE IF NOT EXISTS `skills` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `resume_id` INT NOT NULL,
        `skill_name` VARCHAR(100) NOT NULL,
        `match_status` VARCHAR(50) NOT NULL,
        FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "interview_questions" => "CREATE TABLE IF NOT EXISTS `interview_questions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `resume_id` INT NOT NULL,
        `question` TEXT NOT NULL,
        `answer` TEXT DEFAULT NULL,
        `category` ENUM('technical','hr','project','behavioral') DEFAULT 'technical',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "interview_answers" => "CREATE TABLE IF NOT EXISTS `interview_answers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `question_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `user_answer` TEXT NOT NULL,
        `score` INT NOT NULL DEFAULT 0,
        `feedback` TEXT DEFAULT NULL,
        `answered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`question_id`) REFERENCES `interview_questions`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "activity_log" => "CREATE TABLE IF NOT EXISTS `activity_log` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `action` VARCHAR(255) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "settings" => "CREATE TABLE IF NOT EXISTS `settings` (
        `setting_key` VARCHAR(100) PRIMARY KEY,
        `setting_value` TEXT DEFAULT NULL
    ) ENGINE=InnoDB"
];

foreach ($tables as $name => $sql) {
    if ($conn->query($sql)) {
        echo "<span class='success'>[OK] Table '$name' verified.</span><br>";
    } else {
        echo "<span class='error'>[ERROR] Table '$name': " . $conn->error . "</span><br>";
    }
}

// Add category column to interview_questions if missing
$conn->query("ALTER TABLE `interview_questions` ADD COLUMN IF NOT EXISTS `category` ENUM('technical','hr','project','behavioral') DEFAULT 'technical'");
echo "<span class='info'>[INFO] interview_questions.category column ensured.</span><br>";

// Seed settings
$conn->query("INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES ('gemini_api_key', '')");
$conn->query("INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES ('demo_mode', '1')");
$conn->query("INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES ('app_name', 'AI Resume Builder & Analyzer')");
echo "<span class='success'>[OK] Settings seeded.</span><br>";

// Seed admin
$adminEmail = 'admin@resume.com';
$adminPass = password_hash('adminpassword', PASSWORD_DEFAULT);
$res = $conn->query("SELECT id FROM admins WHERE email='$adminEmail'");
if ($res && $res->num_rows == 0) {
    $conn->query("INSERT INTO admins (name, email, password) VALUES ('HR Manager', '$adminEmail', '$adminPass')");
    echo "<span class='success'>[OK] Admin seeded: admin@resume.com / adminpassword</span><br>";
} else {
    echo "<span class='info'>[INFO] Admin already exists.</span><br>";
}

echo "<span class='success'><br>✅ Setup Complete! All tables are ready.</span><br>";
echo "</div>
    <a href='index.php' class='btn'>🚀 Launch Application</a>
</div></body></html>";

$conn->close();
?>
