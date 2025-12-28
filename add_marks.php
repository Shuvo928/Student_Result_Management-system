<?php
// include shared config (starts session and creates $conn)
require_once __DIR__ . '/../config/config.php';

// Use the PDO instance available as $conn in config.php
$pdo = $conn;

if (!isset($pdo) || !$pdo) {
    die("Database connection error.");
}

// Only show submitted marks here — dashboard contains the add form
if (!isTeacherLoggedIn()) {
    redirect('teacher_login.php');
}

$teacher_id = $_SESSION['teacher_id'] ?? null;
$fetchError = null;
try {
    if ($teacher_id) {
        $stmt = $pdo->prepare(
            "SELECT r.result_id, r.student_id, s.name AS student_name, s.roll_number, r.course_id, c.course_name, c.course_code, r.semester, r.total_marks, r.grade, r.published_date
             FROM results r
             LEFT JOIN students s ON r.student_id = s.student_id
             LEFT JOIN courses c ON r.course_id = c.course_id
             WHERE r.teacher_id = :tid
             ORDER BY r.published_date DESC
             LIMIT 200"
        );
        $stmt->execute([':tid' => $teacher_id]);
    } else {
        $stmt = $pdo->query(
            "SELECT r.result_id, r.student_id, s.name AS student_name, s.roll_number, r.course_id, c.course_name, c.course_code, r.semester, r.total_marks, r.grade, r.published_date
             FROM results r
             LEFT JOIN students s ON r.student_id = s.student_id
             LEFT JOIN courses c ON r.course_id = c.course_id
             ORDER BY r.published_date DESC
             LIMIT 200"
        );
    }
    $submittedMarks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $submittedMarks = [];
    $fetchError = $e->getMessage();
}

$success_message = $_SESSION['success'] ?? null;
if (isset($_SESSION['success'])) unset($_SESSION['success']);
$error_message = $_SESSION['error'] ?? null;
if (isset($_SESSION['error'])) unset($_SESSION['error']);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Submitted Marks</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background:#f7f9fc }
        .wrap { max-width: 1000px; margin: 0 auto; background: #fff; padding: 18px; border-radius:6px; box-shadow:0 6px 20px rgba(13,20,32,0.06) }
        h2 { margin:0 0 12px 0; color:#0b1220 }
        .muted { color:#6c757d }
        table { width:100%; border-collapse:collapse; margin-top:12px }
        th,td { padding:8px 10px; border-bottom:1px solid #eef3f7; text-align:left }
        th { background:#f1f7fb; color:#0b1220 }
        a.btn { display:inline-block; padding:6px 10px; background:#0d9488; color:#fff; border-radius:4px; text-decoration:none }
        .success { background:#e8f5e9; color:#2e7d32; padding:8px; border-radius:4px; margin-bottom:10px }
        .error { background:#ffebee; color:#c62828; padding:8px; border-radius:4px; margin-bottom:10px }
    </style>
</head>
<body>
    <div class="wrap">
        <h2>Submitted Marks</h2>
        <div class="muted">This page lists marks you've submitted. Use Edit to update an entry.</div>

        <?php if (!empty($success_message)): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($fetchError)): ?>
            <div class="error">Error loading submitted marks: <?php echo htmlspecialchars($fetchError); ?></div>
        <?php endif; ?>

        <?php if (empty($submittedMarks)): ?>
            <p class="muted">No submitted marks found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Roll</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Total</th>
                        <th>Grade</th>
                        <th>Published</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submittedMarks as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['result_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['student_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($row['roll_number'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars(($row['course_code'] ? $row['course_code'] . ' - ' : '') . ($row['course_name'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($row['semester'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['total_marks'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['grade'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['published_date'] ?? ''); ?></td>
                            <td>
                                <a class="btn" href="update_marks.php?result_id=<?php echo urlencode($row['result_id']); ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div style="margin-top:14px"><a href="teacher_dashboard.php">Back to Dashboard</a></div>
    </div>
</body>
</html>
