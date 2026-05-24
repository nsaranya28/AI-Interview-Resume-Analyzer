<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

echo "=== DATABASE TABLES ===\n";
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "Tables found: " . count($tables) . "\n";

foreach ($tables as $t) {
    echo "\n--- $t ---\n";
    $cols = $pdo->query("DESCRIBE `$t`")->fetchAll();
    foreach ($cols as $c) {
        echo "  {$c['Field']} ({$c['Type']}) " . ($c['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . " {$c['Key']}\n";
    }
}

echo "\n=== USERS COUNT ===\n";
try {
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "Users: $count\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== SETTINGS ===\n";
try {
    $settings = $pdo->query("SELECT * FROM settings")->fetchAll();
    foreach ($settings as $s) {
        echo "  {$s['setting_key']} = {$s['setting_value']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== PHP EXTENSIONS ===\n";
$needed = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'fileinfo', 'curl'];
foreach ($needed as $ext) {
    echo "  $ext: " . (extension_loaded($ext) ? 'OK' : 'MISSING') . "\n";
}
