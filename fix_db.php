<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
$db = getDB();

try {
    // Check if user_id column exists in interview_answers
    $stmt = $db->query("SHOW COLUMNS FROM `interview_answers` LIKE 'user_id'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        $db->exec("ALTER TABLE `interview_answers` ADD `user_id` INT NOT NULL AFTER `question_id` ");
        echo "SUCCESS: user_id column added to interview_answers.<br>";
    } else {
        echo "INFO: user_id column already exists in interview_answers.<br>";
    }
    
    // Also ensure the foreign key is set up
    // First check if fk exists
    $stmt = $db->prepare("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                          WHERE TABLE_NAME = 'interview_answers' 
                          AND CONSTRAINT_NAME = 'fk_answers_user' 
                          AND TABLE_SCHEMA = DATABASE()");
    $stmt->execute();
    if (!$stmt->fetch()) {
        try {
            $db->exec("ALTER TABLE `interview_answers` ADD CONSTRAINT `fk_answers_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE");
            echo "SUCCESS: Foreign key fk_answers_user created.<br>";
        } catch (Exception $e) {
            echo "INFO: Could not create foreign key (maybe column data exists or index needs to be manually set).<br>";
        }
    } else {
        echo "INFO: Foreign key fk_answers_user already exists.<br>";
    }
    
    echo "DONE.";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
