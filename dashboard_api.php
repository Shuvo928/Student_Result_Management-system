<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isTeacherLoggedIn()) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

try {
    // Total students
    $q1 = "SELECT COUNT(*) as total_students FROM students";
    $totalStudents = (int)$conn->query($q1)->fetch()['total_students'];

    // Courses assigned
    $q2 = "SELECT COUNT(DISTINCT course_id) as courses_assigned FROM results WHERE teacher_id = :teacher_id";
    $stmt = $conn->prepare($q2);
    $stmt->bindParam(':teacher_id', $teacher_id);
    $stmt->execute();
    $coursesAssigned = (int)$stmt->fetch()['courses_assigned'];

    // Results published
    $q3 = "SELECT COUNT(*) as results_published FROM results WHERE teacher_id = :teacher_id";
    $stmt = $conn->prepare($q3);
    $stmt->bindParam(':teacher_id', $teacher_id);
    $stmt->execute();
    $resultsPublished = (int)$stmt->fetch()['results_published'];

    // Recent activities (latest results)
    $q4 = "SELECT r.published_date, s.name as student_name, c.course_code, c.course_name, r.total_marks, r.grade
           FROM results r
           LEFT JOIN students s ON r.student_id = s.student_id
           LEFT JOIN courses c ON r.course_id = c.course_id
           WHERE r.teacher_id = :teacher_id
           ORDER BY r.published_date DESC
           LIMIT 10";
    $stmt = $conn->prepare($q4);
    $stmt->bindParam(':teacher_id', $teacher_id);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'total_students' => $totalStudents,
            'courses_assigned' => $coursesAssigned,
            'results_published' => $resultsPublished,
            'activities' => $activities
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'db_error', 'message' => $e->getMessage()]);
}

?>
