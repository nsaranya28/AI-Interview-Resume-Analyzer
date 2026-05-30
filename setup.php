<?php
// setup.php
// Run this file in your browser (http://localhost/resume/setup.php) or via CLI to setup the database.

$host = 'localhost';
$user = 'root';
$pass = 'pass';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Setup - AI Interview & Resume Analyzer</title>
    <link href='https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap' rel='stylesheet'>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #1e223aff;
            color: #e4e6eb;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            backdrop-filter: blur(8px);
        }
        h1 {
            color: #a855f7;
            font-size: 28px;
            margin-bottom: 20px;
            font-weight: 800;
            text-align: center;
        }
        .log {
            background-color: #07080d;
            border-radius: 8px;
            padding: 20px;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.6;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid rgba(168, 85, 247, 0.2);
            color: #38bdf8;
        }
        .success { color: #4ade80; }
        .error { color: #f87171; }
        .info { color: #fbbf24; }
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            margin-top: 25px;
            transition: opacity 0.2s;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
<div class='container'>
    <h1>AI Resume Analyzer Setup</h1>
    <div class='log'>";

// 1. Connect to MySQL Server
$conn = @new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    echo "<span class='error'>[ERROR] MySQL Connection Failed: " . $conn->connect_error . "</span><br>";
    echo "<span class='info'>Please make sure XAMPP MySQL is running and password is empty.</span><br>";
    echo "</div></div></body></html>";
    exit;
}
echo "<span class='success'>[SUCCESS] Connected to MySQL Server.</span><br>";

// 2. Create Database
$dbName = 'resume_analyzer';
if ($conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    echo "<span class='success'>[SUCCESS] Database '$dbName' verified/created.</span><br>";
} else {
    echo "<span class='error'>[ERROR] Creating Database: " . $conn->error . "</span><br>";
    exit;
}

// 3. Connect to Database
$conn->select_db($dbName);

// 4. Create Tables
$tables = [
    "users" => "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "resumes" => "CREATE TABLE IF NOT EXISTS `resumes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `file_name` VARCHAR(255) NOT NULL,
        `file_path` VARCHAR(255) NOT NULL,
        `target_role` VARCHAR(255) DEFAULT NULL,
        `status` VARCHAR(50) DEFAULT 'Applied',
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

    "settings" => "CREATE TABLE IF NOT EXISTS `settings` (
        `setting_key` VARCHAR(100) PRIMARY KEY,
        `setting_value` TEXT NULL
    ) ENGINE=InnoDB",

    "interview_questions" => "CREATE TABLE IF NOT EXISTS `interview_questions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `resume_id` INT NOT NULL,
        `question` TEXT NOT NULL,
        `answer` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "interview_answers" => "CREATE TABLE IF NOT EXISTS `interview_answers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `question_id` INT NOT NULL,
        `user_answer` TEXT NOT NULL,
        `score` INT NOT NULL,
        `feedback` TEXT,
        FOREIGN KEY (`question_id`) REFERENCES `interview_questions`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "admins" => "CREATE TABLE IF NOT EXISTS `admins` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB"
];

foreach ($tables as $name => $sql) {
    if ($conn->query($sql)) {
        echo "<span class='success'>[SUCCESS] Table '$name' verified/created.</span><br>";
    } else {
        echo "<span class='error'>[ERROR] Creating table '$name': " . $conn->error . "</span><br>";
    }
}

// 5. Create default Admin if not exists
$adminEmail = 'admin@resume.com';
$adminPass = 'adminpassword';
$res = $conn->query("SELECT id FROM admins WHERE email = '$adminEmail'");
if ($res && $res->num_rows == 0) {
    $hashedPass = password_hash($adminPass, PASSWORD_DEFAULT);
    $adminName = 'HR Manager';
    if ($conn->query("INSERT INTO admins (name, email, password) VALUES ('$adminName', '$adminEmail', '$hashedPass')")) {
        echo "<span class='success'>[SUCCESS] Seeded default Admin user ($adminEmail / $adminPass).</span><br>";
    } else {
        echo "<span class='error'>[ERROR] Seeding Admin: " . $conn->error . "</span><br>";
    }
} else {
    echo "<span class='info'>[INFO] Default Admin user already exists.</span><br>";
}

// 6. Seed Default Settings if not exists
$conn->query("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('gemini_api_key', '')");
$conn->query("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('demo_mode', '1')");
echo "<span class='success'>[SUCCESS] Seeded default system settings.</span><br>";

echo "</div>
    <a href='index.php' class='btn'>Go to Landing Page</a>
</div>
</body>
</html>";

$conn->close();
?>
