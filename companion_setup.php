<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/db.php';
try {
    $db = getDB();
    $sql = file_get_contents(__DIR__ . '/sql/companion_extension.sql');
    // split by semicolon to run individual queries
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($queries as $q) {
        if (!empty($q)) {
            $db->exec($q);
        }
    }
    echo "Extension tables created successfully via PHP PDO!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
