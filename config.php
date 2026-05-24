<?php
// config.php
// Central config and settings loader

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'pass');
if (!defined('DB_NAME')) define('DB_NAME', 'resume_analyzer');

// Auto-load config settings from database
require_once __DIR__ . '/db.php';

// ---- INSERT YOUR GEMINI API KEY BELOW ----
$gemini_api_key = 'YOUR_GEMINI_API_KEY_HERE'; // Replace with your actual Gemini API key
$demo_mode = false; // Set to false to use real AI

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
    // Optional: you can set a placeholder demo key
    $gemini_api_key = '';
}

$GLOBALS['gemini_api_key'] = $gemini_api_key;
$GLOBALS['demo_mode'] = $demo_mode;
