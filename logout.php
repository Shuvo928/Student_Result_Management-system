<?php
require_once __DIR__ . '/../config/config.php';

// Logout student (and any other user flags)
unset($_SESSION['student_id']);
unset($_SESSION['teacher_id']);
unset($_SESSION['admin_id']);
unset($_SESSION['user_id']);
session_destroy();
redirect('../index.php');
    
?>
