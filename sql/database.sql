-- Database schema for AI Resume Analyzer
CREATE DATABASE IF NOT EXISTS resume_analyzer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE resume_analyzer;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_admin TINYINT(1) DEFAULT 0
);

CREATE TABLE resumes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resume_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(512) NOT NULL,
    ats_score DECIMAL(5,2) NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE analysis_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    skills JSON NULL,
    missing_keywords JSON NULL,
    suggestions TEXT NULL,
    interview_questions JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
);
