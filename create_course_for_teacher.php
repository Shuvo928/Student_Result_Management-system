<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isTeacherLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'unauthorized']);
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

$subject = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
}

if ($subject === '') {
    echo json_encode(['success' => false, 'message' => 'subject required']);
    exit;
}

// Build a course name like "Bangla 101" and a code like "BANG101"
$course_name = $subject . ' 101';
$code_prefix = strtoupper(preg_replace('/[^A-Z]/i', '', $subject));
if ($code_prefix === '') $code_prefix = 'SUB';
$course_code = substr($code_prefix,0,4) . '101';

try {
    // use the PDO connection provided by config.php
    $db = $conn;
    // determine department from session if available
    $department = $_SESSION['teacher_department'] ?? 'General';
    // Try inserting department-aware course. Use columns likely present in schema.
    $stmt = $db->prepare('INSERT INTO courses (course_code, course_name, department) VALUES (:code, :name, :dept)');
    $stmt->execute([':code'=>$course_code, ':name'=>$course_name, ':dept'=>$department]);
    $id = $db->lastInsertId();
    echo json_encode(['success'=>true,'course_id'=>$id,'course_code'=>$course_code,'course_name'=>$course_name,'department'=>$department]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'db_error','detail'=>$e->getMessage()]);
}

?>