<?php
require_once '../config/config.php';

if (!isTeacherLoggedIn()) {
    exit();
}

$course_id = $_GET['course_id'] ?? '';
$semester = $_GET['semester'] ?? '';

if ($course_id && $semester) {
    // Determine department for the course (if any)
    $deptStmt = $conn->prepare('SELECT department FROM courses WHERE course_id = :course_id LIMIT 1');
    $deptStmt->execute([':course_id' => $course_id]);
    $courseRow = $deptStmt->fetch(PDO::FETCH_ASSOC);
    $courseDept = $courseRow['department'] ?? null;

    if ($courseDept && strtolower($courseDept) !== 'general') {
        // Filter by department when course has a specific department
        $query = "SELECT s.student_id, s.roll_number, s.name 
                  FROM students s 
                  WHERE s.department = :dept
                  AND s.semester = :semester
                  ORDER BY s.roll_number";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':dept', $courseDept);
        $stmt->bindParam(':semester', $semester);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // No department filtering; return students by semester
        $query = "SELECT s.student_id, s.roll_number, s.name 
                  FROM students s 
                  WHERE s.semester = :semester
                  ORDER BY s.roll_number";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':semester', $semester);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if (count($students) > 0) {
        foreach ($students as $student) {
            echo '<div class="student-marks-row">';
            echo '<h4>' . $student['name'] . ' (Roll: ' . $student['roll_number'] . ')</h4>';
            echo '<div class="marks-inputs">';
            echo '<input type="hidden" name="student_id[]" value="' . $student['student_id'] . '">';
            // Half-yearly (out of 100)
            echo '<input class="marks-input" type="number" name="half_yearly[]" placeholder="Half-yearly (0-100)" min="0" max="100" step="0.01">';
            // Final exam (out of 70)
            echo '<input class="marks-input" type="number" name="exam_marks[]" placeholder="Final Exam (0-70)" min="0" max="70" step="0.01">';
            echo '<input class="marks-input" type="number" name="class_test[]" placeholder="Class Test (0-15)" min="0" max="15" step="0.01">';
            echo '<input class="marks-input" type="number" name="assignment[]" placeholder="Assignment (0-10)" min="0" max="10" step="0.01">';
            echo '<input class="marks-input" type="number" name="attendance[]" placeholder="Attendance (0-5)" min="0" max="5" step="0.01">';
            // Display calculated total and hidden fields for server
            echo '<div class="calculated">Total: <span class="total-display">-</span></div>';
            echo '<input type="hidden" name="total_marks[]" class="total-input">';
            echo '<input type="hidden" name="grade[]" class="grade-input">';
            echo '<input type="hidden" name="grade_point[]" class="grade-point-input">';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>No students found for this course and semester.</p>';
    }
}
?>