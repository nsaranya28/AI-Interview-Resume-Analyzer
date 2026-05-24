<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$files = [
    'register.php',
    'auth.php',
    'admin_login.php',
    'admin_dashboard.php',
    'dashboard.php',
    'upload_resume.php',
    'logout.php',
    'setup.php',
];

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/resume/';

foreach ($files as $f) {
    echo "\n=== CHECKING: $f ===\n";
    
    // Check for common issues by reading file content
    $content = file_get_contents(__DIR__ . '/' . $f);
    
    // Check what it includes
    preg_match_all("/require_once?\s+['\"]([^'\"]+)['\"]/", $content, $m1);
    preg_match_all("/require_once?\s+__DIR__\s*\.\s*['\"]([^'\"]+)['\"]/", $content, $m2);
    preg_match_all("/include_once?\s+__DIR__\s*\.\s*['\"]([^'\"]+)['\"]/", $content, $m3);
    preg_match_all("/include_once?\s+['\"]([^'\"]+)['\"]/", $content, $m4);
    
    $all = array_merge($m1[1], $m2[1], $m3[1], $m4[1]);
    $all = array_unique($all);
    echo "  Includes: " . (empty($all) ? 'none' : implode(', ', $all)) . "\n";
    
    // Check for session_start
    if (preg_match('/session_start\(\)/', $content)) {
        echo "  Has session_start(): YES\n";
    }
    
    // Check for BASE_URL usage
    if (strpos($content, 'BASE_URL') !== false) {
        echo "  Uses BASE_URL: YES\n";
    }
    
    // Check for undefined constant/variable issues
    if (strpos($content, 'getDB()') !== false) {
        echo "  Uses getDB(): YES\n";
    }
    if (strpos($content, '$pdo') !== false) {
        echo "  Uses \$pdo: YES\n";
    }
    
    // Check if file references header/footer
    if (strpos($content, 'header.php') !== false) {
        echo "  Includes header.php: YES\n";
    }
    if (strpos($content, 'footer.php') !== false) {
        echo "  Includes footer.php: YES\n";
    }
    
    // Check for problematic patterns
    if (preg_match("/require_once\s+'config\.php'/", $content)) {
        echo "  WARNING: requires 'config.php' without path!\n";
    }
    if (preg_match("/require_once\s+'db\.php'/", $content)) {
        echo "  WARNING: requires 'db.php' without path!\n";
    }
}
