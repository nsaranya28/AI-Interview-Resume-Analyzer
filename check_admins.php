<?php
require_once 'db.php';
try {
    $db = getDB();
    $stmt = $db->query("SELECT email FROM admins");
    $admins = $stmt->fetchAll();
    echo "Found " . count($admins) . " admins:\n";
    foreach ($admins as $admin) {
        echo "- " . $admin['email'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
