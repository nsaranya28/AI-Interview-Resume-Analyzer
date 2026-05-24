<?php
// config.php – Database credentials and base settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'resume_analyzer');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'pass'); // Set your MySQL password
if (!defined('BASE_URL')) define('BASE_URL', '/resume/');
?>
