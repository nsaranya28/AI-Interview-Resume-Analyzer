<?php
// config.php
// Central config and settings loader

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'pass');
define('DB_NAME', 'resume_analyzer');

// Auto-load config settings from database
require_once __DIR__ . '/db.php';

$gemini_api_key = '';
$demo_mode = true;

try {
    $db = getDB();
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['setting_key'] === 'gemini_api_key') {
            $gemini_api_key = $row['setting_value'];
        }
        if ($row['setting_key'] === 'demo_mode') {
            $demo_mode = ($row['setting_value'] === '1' || $row['setting_value'] === 'true');
        }
    }
} catch (Exception $e) {
    // Database or table might not exist yet during setup
}

// Fallback to demo mode if API key is blank
if (empty($gemini_api_key)) {
    $demo_mode = true;
}

$GLOBALS['gemini_api_key'] = $gemini_api_key;
$GLOBALS['demo_mode'] = $demo_mode;
