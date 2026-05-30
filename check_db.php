<?php
require_once __DIR__ . '/db.php';
$db = getDB();
$table = 'interview_answers';
$stmt = $db->query("DESCRIBE $table");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Columns in $table:\n";
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
?>
