<?php
require_once __DIR__ . '/db.php';

function test_db($password) {
    $host = 'localhost';
    $db_name = 'resume_analyzer';
    $user = 'root';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, $user, $password, $options);
    } catch (\PDOException $e) {
        return null;
    }
}

$db = test_db('');
if (!$db) $db = test_db('pass');

if (!$db) {
    die("Could not connect to database with empty or 'pass' password.\n");
}

echo "--- Users Table ---\n";
try {
    $users = $db->query("SELECT id, name, email FROM users")->fetchAll();
    foreach ($users as $u) {
        echo "ID: {$u['id']} | Name: {$u['name']} | Email: {$u['email']}\n";
    }
} catch (Exception $e) { echo "Users table error: " . $e->getMessage() . "\n"; }

echo "\n--- Admins Table ---\n";
try {
    $admins = $db->query("SELECT id, name, email FROM admins")->fetchAll();
    foreach ($admins as $a) {
        echo "ID: {$a['id']} | Name: {$a['name']} | Email: {$a['email']}\n";
    }
} catch (Exception $e) { echo "Admins table error: " . $e->getMessage() . "\n"; }

