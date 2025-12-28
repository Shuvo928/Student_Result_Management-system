<?php
require_once '../config/config.php';

if (!isTeacherLoggedIn()) {
    redirect('../teacher_login.php');
}

$teacher_id = $_SESSION['teacher_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('add_marks.php');
}

$course_id = $_POST['course_id'] ?? '';
$semester = $_POST['semester'] ?? '';
$student_ids = $_POST['student_id'] ?? [];
$half_yearlys = $_POST['half_yearly'] ?? [];
$exam_marks = $_POST['exam_marks'] ?? [];
$class_tests = $_POST['class_test'] ?? [];
$assignments = $_POST['assignment'] ?? [];
$attendances = $_POST['attendance'] ?? [];
$totals = $_POST['total_marks'] ?? [];
$grades = $_POST['grade'] ?? [];
$grade_points = $_POST['grade_point'] ?? [];

// Prepare insert statement (match existing results table columns)
$insert_sql = "INSERT INTO results (student_id, course_id, teacher_id, exam_marks, half_yearly, class_test, attendance, assignment, total_marks, grade, grade_point, semester, published_date)
               VALUES (:student_id, :course_id, :teacher_id, :exam_marks, :half_yearly, :class_test, :attendance, :assignment, :total_marks, :grade, :grade_point, :semester, :published_date)";

// Validate course exists to avoid foreign key violation
if (empty($course_id) || !is_numeric($course_id)) {
    $_SESSION['error'] = 'Invalid course selected.';
    redirect('add_marks.php');
}
try {
    $cc = $conn->prepare('SELECT course_id FROM courses WHERE course_id = :cid LIMIT 1');
    $cc->execute([':cid' => $course_id]);
    if ($cc->rowCount() === 0) {
        $_SESSION['error'] = 'Selected course does not exist.';
        redirect('add_marks.php');
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error checking course: ' . $e->getMessage();
    redirect('add_marks.php');
}

try {
    $stmt = $conn->prepare($insert_sql);

    // If no students were loaded/submitted, fail early with message
    if (!is_array($student_ids) || count($student_ids) === 0) {
        $_SESSION['error'] = 'No students loaded. Please select a course/subject that has students.';
        // log for debugging
        @file_put_contents(__DIR__ . '/../process_add_marks_debug.log', date('c') . " - no students - POST: " . json_encode($_POST) . "\n", FILE_APPEND | LOCK_EX);
        redirect('add_marks.php');
    }

    $skipped = 0;
    for ($i = 0; $i < count($student_ids); $i++) {
        $sid = $student_ids[$i];
        // Resolve student id: if provided value is not a valid student_id, try treat as roll_number
        $resolved_sid = null;
        if (empty($sid)) {
            @file_put_contents(__DIR__ . '/../process_add_marks_debug.log', date('c') . " - empty student id at index {$i}\n", FILE_APPEND | LOCK_EX);
            $skipped++;
            continue;
        }
        // Check if student exists by id
        $chk = $conn->prepare('SELECT student_id FROM students WHERE student_id = :id LIMIT 1');
        $chk->execute([':id' => $sid]);
        $found = $chk->fetch(PDO::FETCH_ASSOC);
        if ($found && isset($found['student_id'])) {
            $resolved_sid = $found['student_id'];
        } else {
            // try by roll_number
            $chk2 = $conn->prepare('SELECT student_id FROM students WHERE roll_number = :roll LIMIT 1');
            $chk2->execute([':roll' => $sid]);
            $found2 = $chk2->fetch(PDO::FETCH_ASSOC);
            if ($found2 && isset($found2['student_id'])) {
                $resolved_sid = $found2['student_id'];
            } else {
                @file_put_contents(__DIR__ . '/../process_add_marks_debug.log', date('c') . " - student not found (given:'{$sid}') at index {$i}\n", FILE_APPEND | LOCK_EX);
                $skipped++;
                continue;
            }
        }
        // use resolved id
        $sid = $resolved_sid;
        $half = floatval($half_yearlys[$i] ?? 0);
        $exam = floatval($exam_marks[$i] ?? 0);
        $ct = floatval($class_tests[$i] ?? 0);
        $assign = floatval($assignments[$i] ?? 0);
        $att = floatval($attendances[$i] ?? 0);

        // If total was provided by JS, trust it; otherwise compute server-side
        if (isset($totals[$i]) && $totals[$i] !== '') {
            $total = floatval($totals[$i]);
        } else {
            // server-side calculation using same max/weights
            $max = ['half'=>100,'exam'=>70,'classTest'=>15,'assignment'=>10,'attendance'=>5];
            $weights = ['half'=>0.25,'exam'=>0.50,'classTest'=>0.10,'assignment'=>0.10,'attendance'=>0.05];
            $total = ($half / $max['half']) * 100 * $weights['half']
                   + ($exam / $max['exam']) * 100 * $weights['exam']
                   + ($ct / $max['classTest']) * 100 * $weights['classTest']
                   + ($assign / $max['assignment']) * 100 * $weights['assignment']
                   + ($att / $max['attendance']) * 100 * $weights['attendance'];
            $total = round($total, 2);
        }

        // Determine grade and grade_point
        if (isset($grades[$i]) && $grades[$i] !== '') {
            $grade = $grades[$i];
        } else {
            $grade = getGrade($total);
        }

        if (isset($grade_points[$i]) && $grade_points[$i] !== '') {
            $gp = floatval($grade_points[$i]);
        } else {
            $gp = getGradePoint($grade);
        }

        $published = date('Y-m-d H:i:s');

        $stmt->bindParam(':student_id', $sid);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->bindParam(':exam_marks', $exam);
        $stmt->bindParam(':half_yearly', $half);
        $stmt->bindParam(':class_test', $ct);
        $stmt->bindParam(':attendance', $att);
        $stmt->bindParam(':assignment', $assign);
        $stmt->bindParam(':total_marks', $total);
        $stmt->bindParam(':grade', $grade);
        $stmt->bindParam(':grade_point', $gp);
        $stmt->bindParam(':semester', $semester);
        $stmt->bindParam(':published_date', $published);

        $stmt->execute();

        // After successful insert, attempt to notify student via SMS (placeholder)
        try {
            // get student phone and name (students table uses 'name' and 'roll_number')
            $sstmt = $conn->prepare('SELECT name, roll_number, phone FROM students WHERE student_id = :sid LIMIT 1');
            $sstmt->execute([':sid' => $sid]);
            $srow = $sstmt->fetch(PDO::FETCH_ASSOC);
            $student_name = $srow ? trim($srow['name'] ?? '') : '';
            $student_phone = $srow['phone'] ?? '';
            $student_roll = $srow['roll_number'] ?? '';

            // get course name
            $cstmt = $conn->prepare('SELECT course_name, course_code FROM courses WHERE course_id = :cid LIMIT 1');
            $cstmt->execute([':cid' => $course_id]);
            $crow = $cstmt->fetch(PDO::FETCH_ASSOC);
            $course_name = $crow['course_name'] ?? '';
            $course_code = $crow['course_code'] ?? '';

            if (!empty($student_phone)) {
                $msg = "Dear {$student_name} (Roll: {$student_roll}), your result for {$course_code} {$course_name} has been published. Total: {$total}, Grade: {$grade}.";
                sendSMS($student_phone, $msg);
            }
        } catch (Exception $e) {
            // don't break the flow on SMS errors
        }
    }

    $_SESSION['success'] = 'Marks submitted successfully.';
    redirect('add_marks.php');

} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    redirect('add_marks.php');
}

// Helper functions
function getGrade($total) {
    if ($total >= 80) return 'A+';
    if ($total >= 75) return 'A';
    if ($total >= 70) return 'A-';
    if ($total >= 65) return 'B+';
    if ($total >= 60) return 'B';
    if ($total >= 55) return 'B-';
    if ($total >= 50) return 'C+';
    if ($total >= 45) return 'C';
    if ($total >= 40) return 'D';
    return 'F';
}

function getGradePoint($grade) {
    $map = [
        'A+'=>4.00,'A'=>3.75,'A-'=>3.50,
        'B+'=>3.25,'B'=>3.00,'B-'=>2.75,
        'C+'=>2.50,'C'=>2.25,'D'=>2.00,'F'=>0.00
    ];
    return $map[$grade] ?? 0.00;

}

// Placeholder SMS sender: append messages to a local log file for delivery by external system
function sendSMS($phone, $message) {
    $logfile = __DIR__ . '/../sms_log.txt';
    $time = date('Y-m-d H:i:s');
    $entry = "[{$time}] To: {$phone} | Message: {$message}\n";
    @file_put_contents($logfile, $entry, FILE_APPEND | LOCK_EX);
}

?>
