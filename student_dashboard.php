<?php
require_once '../config/config.php';

if (!isStudentLoggedIn()) {
    redirect("student_login.php");
}

$student_id = $_SESSION['student_id'];

// Get student details
$query = "SELECT * FROM students WHERE student_id = :student_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':student_id', $student_id);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$result_query = "SELECT r.*, c.course_code, c.course_name, c.credit 
                 FROM results r 
                 JOIN courses c ON r.course_id = c.course_id 
                 WHERE r.student_id = :student_id 
                 ORDER BY r.semester DESC";
$result_stmt = $conn->prepare($result_query);
$result_stmt->bindParam(':student_id', $student_id);
$result_stmt->execute();
$results = $result_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate semester GPA (use current student semester)
$gpa = 0;
$current_sem = $student['semester'];
// If student semester not set, try to derive from latest result
if (empty($current_sem) && count($results) > 0) {
    $current_sem = $results[0]['semester'];
}

// Determine semester to show (can be overridden via ?semester=)
$selected_semester = isset($_GET['semester']) && $_GET['semester'] !== '' ? $_GET['semester'] : $current_sem;

// build list of semesters present in results for selector
$semesters = array_values(array_unique(array_map(function($r){ return $r['semester']; }, $results)));

$sem_grade_points = 0;
$sem_credits = 0;
foreach ($results as $result) {
    if ((string)$result['semester'] === (string)$selected_semester) {
        $sem_grade_points += ($result['grade_point'] * $result['credit']);
        $sem_credits += $result['credit'];
    }
}

$gpa = ($sem_credits > 0) ? round($sem_grade_points / $sem_credits, 2) : 0;

// Show all courses results
$filtered_results = $results;

// Compute overall totals and GPA (weighted by credit)
$total_marks_sum = 0;
$overall_grade_points = 0;
$overall_credits = 0;
foreach ($filtered_results as $res) {
    $total_marks_sum += intval($res['total_marks']);
    $overall_grade_points += ($res['grade_point'] * $res['credit']);
    $overall_credits += $res['credit'];
}
$gpa = ($overall_credits > 0) ? round($overall_grade_points / $overall_credits, 2) : 0;
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
      .sidebar { min-height:100vh; background:#f8f9fa; }
      .stat-card { border-radius:8px; padding:16px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
      .student-illustration { width:100%; max-width:120px; }
    </style>
  </head>
  <body class="bg-light">
        <div class="container-fluid">
            <div class="row">
                <aside class="col-lg-3 col-md-4 p-4 sidebar">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img class="student-illustration" src="data:image/svg+xml;utf8,<?php echo rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120"><rect width="120" height="120" rx="12" fill="#e9f5ff"/><circle cx="60" cy="36" r="20" fill="#ffd59e"/><path d="M20 100c0-20 22-36 40-36s40 16 40 36v4H20v-4z" fill="#bfe6c7"/><circle cx="50" cy="34" r="3" fill="#333"/><circle cx="70" cy="34" r="3" fill="#333"/></svg>'); ?>" alt="student" />
                        <div>
                            <h5 class="mb-0"><?php echo htmlspecialchars($student['name']); ?></h5>
                            <div class="text-muted">Roll: <?php echo htmlspecialchars($student['roll_number']); ?></div>
                        </div>
                    </div>

                    <nav class="nav flex-column">
                        <a class="nav-link active" href="student_dashboard.php">Dashboard</a>
                        <a class="nav-link" href="view_results.php">View Results</a>
                        <a class="nav-link" href="print_result.php">Print Result</a>
                        <a class="nav-link" href="profile.php">Profile</a>
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </nav>
                </aside>

                <main class="col-lg-9 col-md-8 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-0">Welcome, <?php echo htmlspecialchars($student['name']); ?></h2>
                            <div class="text-muted">Department: <?php echo htmlspecialchars($student['department']); ?></div>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-primary fs-6">GPA: <strong><?php echo $gpa; ?></strong></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <small class="text-muted">Courses (by department)</small>
                                <div class="mt-2">
                                    <?php
                                        $dept = isset($student['department']) ? $student['department'] : '';
                                        $subjects_map = [
                                            'Science' => ['Bangla','English','General math','Higher math','Physics','Chemistry','Biology','Religion'],
                                            'Arts' => ['Bangla','English','General math','Social science','Economics','Religion'],
                                            'Commerce' => ['Bangla','English','General math','Finance','Accounting','Religion'],
                                        ];
                                        $subjectOptions = isset($subjects_map[$dept]) ? $subjects_map[$dept] : ['Bangla','English','General math','Religion'];
                                        foreach($subjectOptions as $sub) {
                                            echo '<span class="badge bg-secondary me-1 mb-1">'.htmlspecialchars($sub).'</span>';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card text-center">
                                <small class="text-muted">Total Marks</small>
                                <div class="h5 mb-0"><?php echo $total_marks_sum; ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card text-center">
                                <small class="text-muted">GPA (overall)</small>
                                <div class="h5 mb-0"><?php echo $gpa; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- subject selector removed: showing all courses -->

                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Course</th>
                                            <th>Credit</th>
                                            <th>Exam</th>
                                            <th>Class Test</th>
                                            <th>Attendance</th>
                                            <th>Assignment</th>
                                            <th>Total</th>
                                            <th>Grade</th>
                                            <th>GPA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                                <?php if(count($filtered_results) > 0): ?>
                                                    <?php foreach($filtered_results as $result): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="fw-bold"><?php echo htmlspecialchars($result['course_code']); ?></div>
                                                                <div class="text-muted small"><?php echo htmlspecialchars($result['course_name']); ?></div>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($result['credit']); ?></td>
                                                            <td><?php echo htmlspecialchars($result['exam_marks']); ?></td>
                                                            <td><?php echo htmlspecialchars($result['class_test']); ?></td>
                                                            <td><?php echo htmlspecialchars($result['attendance']); ?></td>
                                                            <td><?php echo htmlspecialchars($result['assignment']); ?></td>
                                                            <td><?php echo htmlspecialchars($result['total_marks']); ?></td>
                                                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($result['grade']); ?></span></td>
                                                            <td><?php echo htmlspecialchars($result['grade_point']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center py-4">No results found</td>
                                                    </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </main>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>