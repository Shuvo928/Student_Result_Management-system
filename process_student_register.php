<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('student_register.php');
}

$roll = isset($_POST['roll_number']) ? trim($_POST['roll_number']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$department = isset($_POST['department']) ? trim($_POST['department']) : '';
$semester = isset($_POST['semester']) ? trim($_POST['semester']) : null;
$batch = isset($_POST['batch_year']) ? trim($_POST['batch_year']) : null;
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Basic validation
if ($password !== $confirm) {
    $_SESSION['error'] = 'Passwords do not match.';
    redirect('student_register.php');
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'Password must be at least 6 characters.';
    redirect('student_register.php');
}

if (empty($roll) || empty($name) || empty($email) || empty($department)) {
    $_SESSION['error'] = 'Please fill all required fields.';
    redirect('student_register.php');
}

try {
    // Check unique roll number
    $chk = $conn->prepare('SELECT student_id FROM students WHERE roll_number = :roll LIMIT 1');
    $chk->bindParam(':roll', $roll);
    $chk->execute();
    if ($chk->fetch()) {
        $_SESSION['error'] = 'Roll number already registered.';
        redirect('student_register.php');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $ins = $conn->prepare('INSERT INTO students (roll_number, name, email, phone, department, semester, batch_year, username, password) VALUES (:roll, :name, :email, :phone, :dept, :sem, :batch, :uname, :pwd)');
    $ins->bindParam(':roll', $roll);
    $ins->bindParam(':name', $name);
    $ins->bindParam(':email', $email);
    $ins->bindParam(':phone', $phone);
    $ins->bindParam(':dept', $department);
    // allow null for optional fields
    if ($semester === '') { $semester = null; }
    if ($batch === '') { $batch = null; }
    $ins->bindParam(':sem', $semester);
    $ins->bindParam(':batch', $batch);
    $ins->bindParam(':uname', $username);
    $ins->bindParam(':pwd', $hash);
    $ins->execute();

    $_SESSION['success'] = 'Registration successful. Please login with your roll number and password.';
    redirect('student_login.php');

} catch (PDOException $e) {
    $_SESSION['error'] = 'Registration failed: ' . $e->getMessage();
    redirect('student_register.php');
}

?>
