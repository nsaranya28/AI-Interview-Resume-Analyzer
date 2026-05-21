<?php
require 'c:/xampp/htdocs/resume/AiHelper.php';
$resumeText = 'John Doe experienced in PHP, JavaScript, MySQL.';
$targetRole = 'Software Engineer';
$qa = generateInterviewQuestions($resumeText, $targetRole);
header('Content-Type: application/json');
echo json_encode($qa, JSON_PRETTY_PRINT);
?>
