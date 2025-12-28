<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    // Check if input is email or username
    $query = "SELECT * FROM teachers WHERE (username = :username OR email = :username) AND status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $teacher['password'])) {
            $_SESSION['teacher_id'] = $teacher['teacher_id'];
            $_SESSION['teacher_name'] = $teacher['name'];
            $_SESSION['teacher_department'] = $teacher['department'];
            $_SESSION['teacher_username'] = $teacher['username'];
            
            $_SESSION['success'] = "Login successful!";
            // Dashboard is located in the "Teacher Dashboard" folder
            // Use absolute URL built from BASE_URL to avoid relative-path/encoding issues
            redirect(BASE_URL . 'Teacher%20Dashboard/teacher_dashboard.php');
        } else {
            $_SESSION['error'] = "Invalid password!";
            redirect("teacher_login.php");
        }
    } else {
        $_SESSION['error'] = "Teacher not found or account is inactive!";
        redirect("teacher_login.php");
    }
}
?>