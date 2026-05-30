-- ============================================================
-- AI Resume Builder & Analyzer - Complete Database Schema
-- ============================================================
CREATE DATABASE IF NOT EXISTS `resume_analyzer` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `resume_analyzer`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
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
) ENGINE=InnoDB;

-- Admins table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Resume Builder Records
CREATE TABLE IF NOT EXISTS `resume_profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT 'My Resume',
    `template` ENUM('ats','professional','modern','creative') DEFAULT 'ats',
    -- Personal Info
    `full_name` VARCHAR(255) DEFAULT NULL,
    `job_title` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `linkedin` VARCHAR(512) DEFAULT NULL,
    `github` VARCHAR(512) DEFAULT NULL,
    `portfolio` VARCHAR(512) DEFAULT NULL,
    -- Career Objective
    `summary` TEXT DEFAULT NULL,
    -- Languages
    `languages` JSON DEFAULT NULL,
    -- References
    `references_data` JSON DEFAULT NULL,
    -- Share token
    `share_token` VARCHAR(100) DEFAULT NULL,
    `is_public` TINYINT(1) DEFAULT 0,
    `version` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Education
CREATE TABLE IF NOT EXISTS `education` (
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
) ENGINE=InnoDB;

-- Skills
CREATE TABLE IF NOT EXISTS `resume_skills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `resume_id` INT NOT NULL,
    `skill_name` VARCHAR(100) NOT NULL,
    `level` ENUM('beginner','intermediate','advanced','expert') DEFAULT 'intermediate',
    `category` VARCHAR(100) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`resume_id`) REFERENCES `resume_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Work Experience
CREATE TABLE IF NOT EXISTS `experience` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `resume_id` INT NOT NULL,
    `company` VARCHAR(255) NOT NULL,
    `position` VARCHAR(255) NOT NULL,
    `location` VARCHAR(255) DEFAULT NULL,
    `start_date` VARCHAR(20) DEFAULT NULL,
    `end_date` VARCHAR(20) DEFAULT NULL,
    `is_current` TINYINT(1) DEFAULT 0,
    `description` TEXT DEFAULT NULL,
    `achievements` JSON DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`resume_id`) REFERENCES `resume_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Projects
CREATE TABLE IF NOT EXISTS `projects` (
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
) ENGINE=InnoDB;

-- Certifications
CREATE TABLE IF NOT EXISTS `certifications` (
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
) ENGINE=InnoDB;

-- Achievements / Awards
CREATE TABLE IF NOT EXISTS `achievements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `resume_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `date` VARCHAR(20) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`resume_id`) REFERENCES `resume_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Uploaded Resumes (Analyzer)
CREATE TABLE IF NOT EXISTS `resumes` (
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
) ENGINE=InnoDB;

-- Analysis Skills (for uploaded resumes)
CREATE TABLE IF NOT EXISTS `skills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `resume_id` INT NOT NULL,
    `skill_name` VARCHAR(100) NOT NULL,
    `match_status` VARCHAR(50) NOT NULL,
    FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Interview Questions for uploaded resumes
CREATE TABLE IF NOT EXISTS `interview_questions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `resume_id` INT NOT NULL,
    `question` TEXT NOT NULL,
    `answer` TEXT DEFAULT NULL,
    `category` ENUM('technical','hr','project','behavioral') DEFAULT 'technical',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`resume_id`) REFERENCES `resumes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Interview Answers (Mock Interview practice)
CREATE TABLE IF NOT EXISTS `interview_answers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `question_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `user_answer` TEXT NOT NULL,
    `score` INT NOT NULL DEFAULT 0,
    `feedback` TEXT DEFAULT NULL,
    `answered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`question_id`) REFERENCES `interview_questions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Activity Log
CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `action` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Settings
CREATE TABLE IF NOT EXISTS `settings` (
    `setting_key` VARCHAR(100) PRIMARY KEY,
    `setting_value` TEXT DEFAULT NULL
) ENGINE=InnoDB;

-- Default settings
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
    ('gemini_api_key', ''),
    ('demo_mode', '1'),
    ('app_name', 'AI Resume Builder & Analyzer'),
    ('app_version', '2.0');

-- Default Admin
INSERT IGNORE INTO `admins` (`name`, `email`, `password`) VALUES
    ('HR Manager', 'admin@resume.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Default admin password is: password
