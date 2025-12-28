<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $department = sanitize($_POST['department']);
    $designation = sanitize($_POST['designation']) ?? '';
    $employee_id = sanitize($_POST['employee_id']) ?? '';
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match!";
    }
    
    // Check password strength
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long!";
    }
    
    // Check if username already exists
    $check_username = "SELECT * FROM teachers WHERE username = :username";
    $stmt = $conn->prepare($check_username);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "Username already exists! Please choose another.";
    }
    
    // Check if email already exists
    $check_email = "SELECT * FROM teachers WHERE email = :email";
    $stmt = $conn->prepare($check_email);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email already registered! Please use another email.";
    }
    
    // Validate phone number format (Bangladeshi)
    if (!preg_match('/^01[3-9]\d{8}$/', $phone)) {
        $errors[] = "Invalid phone number format! Must be a valid Bangladeshi number (01XXXXXXXXX).";
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format!";
    }
    
    // If there are errors, show them
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        redirect("teacher_register.php");
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert into database (match actual table columns)
    try {
        $insert_query = "INSERT INTO teachers (name, email, phone, department, username, password) 
                         VALUES (:name, :email, :phone, :department, :username, :password)";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        
        if ($stmt->execute()) {
            // Get the inserted teacher ID
            $teacher_id = $conn->lastInsertId();
            
            // Send success email (optional)
            sendRegistrationEmail($email, $name, $username);
            
            $_SESSION['success'] = "Registration successful! You can now login.";
            redirect("teacher_login.php");
        } else {
            $_SESSION['error'] = "Registration failed. Please try again.";
            redirect("teacher_register.php");
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        redirect("teacher_register.php");
    }
}

// Function to send registration email (simplified)
function sendRegistrationEmail($email, $name, $username) {
    $to = $email;
    $subject = "Registration Successful - College Result Management System";
    $message = "
    <html>
    <head>
        <title>Registration Successful</title>
    </head>
    <body>
        <h2>Welcome to College Result Management System!</h2>
        <p>Dear $name,</p>
        <p>Your registration as a teacher has been successfully completed.</p>
        <p><strong>Username:</strong> $username</p>
        <p>You can now login to the system using your credentials.</p>
        <p>Thank you for registering!</p>
        <br>
        <p>Best regards,<br>
        College Administration<br>
        Result Management System</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: noreply@college.edu' . "\r\n";
    
    // In production, uncomment this line:
    // mail($to, $subject, $message, $headers);
}
?>