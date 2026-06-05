-- companion_extension.sql - AI Career Companion Platform Extensions

USE `resume_analyzer`;

-- Cover Letters Table
CREATE TABLE IF NOT EXISTS `cover_letters` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `resume_id` INT DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT 'My Cover Letter',
    `company_name` VARCHAR(255) DEFAULT NULL,
    `job_title` VARCHAR(255) DEFAULT NULL,
    `content` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- LinkedIn Profile Optimizations Table
CREATE TABLE IF NOT EXISTS `linkedin_profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `resume_id` INT DEFAULT NULL,
    `headline` VARCHAR(512) DEFAULT NULL,
    `summary` TEXT DEFAULT NULL,
    `suggestions` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Personal Portfolios Table
CREATE TABLE IF NOT EXISTS `portfolios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `resume_id` INT DEFAULT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `theme` VARCHAR(50) DEFAULT 'modern',
    `custom_domain` VARCHAR(255) DEFAULT NULL,
    `views` INT DEFAULT 0,
    `about_bio` TEXT DEFAULT NULL,
    `social_links` JSON DEFAULT NULL,
    `contact_email` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Career Roadmaps Table
CREATE TABLE IF NOT EXISTS `career_roadmaps` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `target_role` VARCHAR(255) NOT NULL,
    `roadmap_json` LONGTEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
