<?php
// config.php – Database credentials and base settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'resume_analyzer');
define('DB_USER', 'root');
define('DB_PASS', 'pass'); // Set your MySQL password
define('BASE_URL', '/resume/');
?>
