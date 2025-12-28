<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('student_login.php');
}

$roll = isset($_POST['roll_number']) ? trim($_POST['roll_number']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($roll) || empty($password)) {
    $_SESSION['error'] = 'Please provide roll number and password.';
    redirect('student_login.php');
}

try {
    $stmt = $conn->prepare('SELECT student_id, password FROM students WHERE roll_number = :roll LIMIT 1');
    $stmt->bindParam(':roll', $roll);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = 'Invalid roll number or password.';
        redirect('student_login.php');
    }

    if (!isset($user['password']) || !password_verify($password, $user['password'])) {
        $_SESSION['error'] = 'Invalid roll number or password.';
        redirect('student_login.php');
    }

    // Login success
    $_SESSION['student_id'] = $user['student_id'];
    redirect('student_dashboard.php');

} catch (PDOException $e) {
    $_SESSION['error'] = 'Login failed: ' . $e->getMessage();
    redirect('student_login.php');
}

?>
