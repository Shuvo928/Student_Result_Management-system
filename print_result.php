<?php
require_once __DIR__ . '/../config/config.php';

if (!isStudentLoggedIn()) {
    redirect('student_login.php');
}

$student_id = $_SESSION['student_id'];

// Fetch student
$stmt = $conn->prepare('SELECT * FROM students WHERE student_id = :student_id LIMIT 1');
$stmt->bindParam(':student_id', $student_id);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch results joined with courses
$result_query = "SELECT r.*, c.course_code, c.course_name, c.credit
                 FROM results r
                 LEFT JOIN courses c ON r.course_id = c.course_id
                 WHERE r.student_id = :student_id
                 ORDER BY c.course_name";
$rstmt = $conn->prepare($result_query);
$rstmt->bindParam(':student_id', $student_id);
$rstmt->execute();
$results = $rstmt->fetchAll(PDO::FETCH_ASSOC);

$courses_data = [];
$total_marks = 0;
$total_max_marks = 0; // assume 100 per course if not present
foreach ($results as $row) {
    $marks = isset($row['total_marks']) ? floatval($row['total_marks']) : 0;
    $max_marks = 100; // fallback
    $percentage = $max_marks > 0 ? ($marks / $max_marks) * 100 : 0;

    // grade mapping (consistent with other files)
    if ($percentage >= 80) {
        $grade = 'A'; $remarks = 'Excellent';
    } elseif ($percentage >= 70) {
        $grade = 'B'; $remarks = 'Good';
    } elseif ($percentage >= 60) {
        $grade = 'C'; $remarks = 'Best Try';
    } elseif ($percentage >= 40) {
        $grade = 'D'; $remarks = 'Try Our Best';
    } else {
        $grade = 'F'; $remarks = 'Failed';
    }

    $courses_data[] = [
        'course_code' => $row['course_code'] ?? '-',
        'course_name' => $row['course_name'] ?? '-',
        'credit' => isset($row['credit']) ? $row['credit'] : 0,
        'marks_obtained' => $marks,
        'total_marks' => $max_marks,
        'percentage' => round($percentage, 2),
        'grade' => $grade,
        'remarks' => $remarks,
    ];

    $total_marks += $marks;
    $total_max_marks += $max_marks;
}

$overall_percentage = $total_max_marks > 0 ? round(($total_marks / $total_max_marks) * 100, 2) : 0;

// overall result class
if ($overall_percentage >= 80) { $overall_result = 'Excellent'; $result_class = 'excellent'; }
elseif ($overall_percentage >= 70) { $overall_result = 'Good'; $result_class = 'good'; }
elseif ($overall_percentage >= 60) { $overall_result = 'Best Try'; $result_class = 'best-try'; }
elseif ($overall_percentage >= 40) { $overall_result = 'Try Our Best'; $result_class = 'try-best'; }
else { $overall_result = 'Failed'; $result_class = 'failed'; }

$feedback_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

    $fstmt = $conn->prepare('INSERT INTO feedback (student_id, feedback_text, rating) VALUES (:sid, :text, :rating)');
    $fstmt->bindParam(':sid', $student_id);
    $fstmt->bindParam(':text', $feedback);
    $fstmt->bindParam(':rating', $rating);
    if ($fstmt->execute()) {
        $feedback_message = '<div class="alert alert-success">Thank you for your feedback!</div>';
    } else {
        $feedback_message = '<div class="alert alert-danger">Failed to submit feedback. Please try again.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Marksheet - <?php echo htmlspecialchars($student['name'] ?? 'Student'); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* minimal inline styles kept from original for print view */
        body { font-family: Arial, sans-serif; background:#f5f5f5; padding:20px; }
        .marksheet-container { max-width:1000px; margin:0 auto; background:#fff; padding:0; border-radius:10px; overflow:hidden; }
        .header { background:#34495e; color:#fff; padding:30px; text-align:center; }
        .student-info { padding:20px; background:#f8f9fa; }
        .marks-table { width:100%; border-collapse:collapse; }
        .marks-table th, .marks-table td { padding:12px; border:1px solid #ddd; }
        .result-summary { padding:20px; }
        .btn { display:inline-block; padding:10px 16px; background:#3498db; color:#fff; border-radius:6px; text-decoration:none; }
        @media print { .btn, .feedback-section { display:none; } }
    </style>
</head>
<body>
    <div class="marksheet-container">
        <div class="header">
            <h2>COLLEGE OF EXCELLENCE</h2>
            <h3>ACADEMIC TRANSCRIPT</h3>
        </div>
        <div class="student-info">
            <strong>Name:</strong> <?php echo htmlspecialchars($student['name'] ?? '-'); ?> &nbsp; | &nbsp;
            <strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id'] ?? $student_id); ?>
        </div>

        <div style="padding:20px;">
            <table class="marks-table">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Credit</th>
                        <th>Marks Obtained</th>
                        <th>Total Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($courses_data) > 0): ?>
                        <?php foreach ($courses_data as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($c['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($c['credit']); ?></td>
                                <td><?php echo htmlspecialchars($c['marks_obtained']); ?></td>
                                <td><?php echo htmlspecialchars($c['total_marks']); ?></td>
                                <td><?php echo htmlspecialchars($c['percentage']); ?>%</td>
                                <td><?php echo htmlspecialchars($c['grade']); ?></td>
                                <td><?php echo htmlspecialchars($c['remarks']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align:center;">No results available</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="result-summary">
                <div><strong>Total Courses:</strong> <?php echo count($courses_data); ?></div>
                <div><strong>Total Marks:</strong> <?php echo $total_marks; ?> / <?php echo $total_max_marks; ?></div>
                <div><strong>Overall Percentage:</strong> <?php echo $overall_percentage; ?>%</div>
                <div><strong>Overall Result:</strong> <span class="<?php echo $result_class; ?>"><?php echo $overall_result; ?></span></div>
            </div>

            <div style="margin:20px 0;">
                <button onclick="window.print()" class="btn">Print Marksheet</button>
            </div>

            <div class="feedback-section">
                <?php echo $feedback_message; ?>
                <form method="POST">
                    <div>
                        <label>Your Feedback</label><br>
                        <textarea name="feedback" rows="4" style="width:100%;" required></textarea>
                    </div>
                    <div style="margin-top:8px;">
                        <label>Rating</label>
                        <select name="rating" required>
                            <option value="5">5</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                    <div style="margin-top:12px;">
                        <button type="submit" name="submit_feedback" class="btn">Submit Feedback</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php
session_start();
require_once 'db_connection.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student information
$student_query = "SELECT s.*, c.course_name 
                  FROM students s 
                  LEFT JOIN courses c ON s.course_id = c.id 
                  WHERE s.id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();

// Get student's course-wise marks
$marks_query = "SELECT m.*, c.course_name, c.course_code, c.credits 
                FROM marks m 
                JOIN courses c ON m.course_id = c.id 
                WHERE m.student_id = ? 
                ORDER BY c.course_name";
$stmt = $conn->prepare($marks_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$marks_result = $stmt->get_result();

// Calculate total marks and percentage
$total_marks = 0;
$total_max_marks = 0;
$courses_data = [];

while ($row = $marks_result->fetch_assoc()) {
    $total_marks += $row['marks_obtained'];
    $total_max_marks += $row['total_marks'];
    $percentage = ($row['marks_obtained'] / $row['total_marks']) * 100;
    
    // Determine grade based on percentage
    if ($percentage >= 80) {
        $grade = 'A';
        $remarks = 'Excellent';
    } elseif ($percentage >= 70) {
        $grade = 'B';
        $remarks = 'Good';
    } elseif ($percentage >= 60) {
        $grade = 'C';
        $remarks = 'Best Try';
    } elseif ($percentage >= 40) {
        $grade = 'D';
        $remarks = 'Try Our Best';
    } else {
        $grade = 'F';
        $remarks = 'Failed';
    }
    
    $courses_data[] = [
        'course_code' => $row['course_code'],
        'course_name' => $row['course_name'],
        'credits' => $row['credits'],
        'marks_obtained' => $row['marks_obtained'],
        'total_marks' => $row['total_marks'],
        'percentage' => round($percentage, 2),
        'grade' => $grade,
        'remarks' => $remarks
    ];
}

// Calculate overall percentage and final result
$overall_percentage = $total_max_marks > 0 ? ($total_marks / $total_max_marks) * 100 : 0;
$overall_percentage = round($overall_percentage, 2);

// Determine overall result
if ($overall_percentage >= 80) {
    $overall_result = 'Excellent';
    $result_class = 'excellent';
} elseif ($overall_percentage >= 70) {
    $overall_result = 'Good';
    $result_class = 'good';
} elseif ($overall_percentage >= 60) {
    $overall_result = 'Best Try';
    $result_class = 'best-try';
} elseif ($overall_percentage >= 40) {
    $overall_result = 'Try Our Best';
    $result_class = 'try-best';
} else {
    $overall_result = 'Failed';
    $result_class = 'failed';
}

// Handle feedback submission
$feedback_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $feedback = $_POST['feedback'];
    $rating = $_POST['rating'];
    
    $feedback_query = "INSERT INTO feedback (student_id, feedback_text, rating) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($feedback_query);
    $stmt->bind_param("isi", $student_id, $feedback, $rating);
    
    if ($stmt->execute()) {
        $feedback_message = '<div class="alert alert-success">Thank you for your feedback!</div>';
    } else {
        $feedback_message = '<div class="alert alert-danger">Failed to submit feedback. Please try again.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Marksheet - <?php echo $student['full_name']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .marksheet-container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 2px solid #2c3e50;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #4a6491);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .header:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 5%;
            width: 90%;
            height: 20px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0,0 Q50,10 100,0" fill="white"/></svg>');
        }
        
        .university-name {
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        
        .marksheet-title {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .academic-year {
            font-size: 18px;
            opacity: 0.9;
        }
        
        .student-info {
            padding: 30px;
            background-color: #f8f9fa;
            border-bottom: 2px dashed #dee2e6;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: 600;
            color: #2c3e50;
            display: inline-block;
            width: 180px;
        }
        
        .info-value {
            color: #495057;
        }
        
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        .marks-table th {
            background-color: #34495e;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .marks-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .marks-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .marks-table tr:hover {
            background-color: #e9ecef;
        }
        
        .result-summary {
            background-color: #f8f9fa;
            padding: 25px;
            margin: 20px 30px;
            border-radius: 10px;
            border-left: 5px solid #3498db;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            text-align: center;
        }
        
        .summary-item {
            padding: 15px;
        }
        
        .summary-value {
            font-size: 28px;
            font-weight: 800;
            color: #2c3e50;
        }
        
        .summary-label {
            font-size: 14px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }
        
        .overall-result {
            text-align: center;
            padding: 20px;
            margin: 0 30px 30px;
            border-radius: 10px;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .excellent { background-color: #d4edda; color: #155724; border: 3px solid #c3e6cb; }
        .good { background-color: #d1ecf1; color: #0c5460; border: 3px solid #bee5eb; }
        .best-try { background-color: #fff3cd; color: #856404; border: 3px solid #ffeaa7; }
        .try-best { background-color: #f8d7da; color: #721c24; border: 3px solid #f5c6cb; }
        .failed { background-color: #f5c6cb; color: #721c24; border: 3px solid #f1b0b7; }
        
        .feedback-section {
            padding: 30px;
            background-color: #f8f9fa;
            border-top: 2px dashed #dee2e6;
        }
        
        .feedback-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
            font-size: 24px;
        }
        
        .feedback-form {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #3498db;
            outline: none;
        }
        
        .rating-stars {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .star {
            font-size: 30px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .star:hover,
        .star.active {
            color: #ffc107;
        }
        
        .btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            display: block;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .print-btn {
            background: linear-gradient(135deg, #27ae60, #219653);
            margin-top: 20px;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #dee2e6;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            padding: 30px;
            margin-top: 20px;
        }
        
        .signature {
            text-align: center;
        }
        
        .signature-line {
            width: 200px;
            height: 2px;
            background-color: #2c3e50;
            margin: 40px auto 10px;
        }
        
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            
            .marksheet-container {
                box-shadow: none;
                border: 2px solid #000;
            }
            
            .btn, .feedback-section {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="marksheet-container">
        <!-- Header Section -->
        <div class="header">
            <div class="university-name">UNIVERSITY OF EXCELLENCE</div>
            <div class="marksheet-title">ACADEMIC TRANSCRIPT</div>
            <div class="academic-year">Academic Year 2023-2024</div>
        </div>
        
        <!-- Student Information -->
        <div class="student-info">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Student Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['full_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Student ID:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['student_id']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date of Birth:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['date_of_birth']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Course:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['course_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['phone']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Marks Table -->
        <div style="padding: 0 30px;">
            <h3 style="color: #2c3e50; margin: 20px 0; text-align: center;">COURSE-WISE PERFORMANCE</h3>
            <table class="marks-table">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Credits</th>
                        <th>Marks Obtained</th>
                        <th>Total Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses_data as $course): ?>
                    <tr>
                        <td><?php echo $course['course_code']; ?></td>
                        <td><?php echo $course['course_name']; ?></td>
                        <td><?php echo $course['credits']; ?></td>
                        <td><?php echo $course['marks_obtained']; ?></td>
                        <td><?php echo $course['total_marks']; ?></td>
                        <td><?php echo $course['percentage']; ?>%</td>
                        <td><?php echo $course['grade']; ?></td>
                        <td>
                            <span style="font-weight: bold; 
                                color: <?php 
                                    if($course['remarks'] == 'Excellent') echo '#155724';
                                    elseif($course['remarks'] == 'Good') echo '#0c5460';
                                    elseif($course['remarks'] == 'Best Try') echo '#856404';
                                    elseif($course['remarks'] == 'Try Our Best') echo '#721c24';
                                    else echo '#721c24';
                                ?>;">
                                <?php echo $course['remarks']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Result Summary -->
        <div class="result-summary">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value"><?php echo count($courses_data); ?></div>
                    <div class="summary-label">Total Courses</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value"><?php echo $total_marks; ?>/<?php echo $total_max_marks; ?></div>
                    <div class="summary-label">Total Marks</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value"><?php echo $overall_percentage; ?>%</div>
                    <div class="summary-label">Percentage</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">
                        <?php 
                            $total_credits = array_sum(array_column($courses_data, 'credits'));
                            echo $total_credits;
                        ?>
                    </div>
                    <div class="summary-label">Total Credits</div>
                </div>
            </div>
        </div>
        
        <!-- Overall Result -->
        <div class="overall-result <?php echo $result_class; ?>">
            Overall Result: <?php echo $overall_result; ?>
        </div>
        
        <!-- Signatures -->
        <div class="signature-section">
            <div class="signature">
                <div class="signature-line"></div>
                <div>Registrar</div>
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                <div>Controller of Examinations</div>
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                <div>Dean of Faculty</div>
            </div>
        </div>
        
        <!-- Feedback Section -->
        <div class="feedback-section">
            <h3>STUDENT FEEDBACK</h3>
            <?php echo $feedback_message; ?>
            <form method="POST" class="feedback-form">
                <div class="form-group">
                    <label for="feedback">Your Feedback:</label>
                    <textarea name="feedback" id="feedback" rows="4" class="form-control" 
                              placeholder="Please share your feedback about the examination process, course content, or any suggestions for improvement..." 
                              required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Overall Rating:</label>
                    <div class="rating-stars">
                        <span class="star" data-rating="1">★</span>
                        <span class="star" data-rating="2">★</span>
                        <span class="star" data-rating="3">★</span>
                        <span class="star" data-rating="4">★</span>
                        <span class="star" data-rating="5">★</span>
                    </div>
                    <input type="hidden" name="rating" id="rating" value="0" required>
                </div>
                
                <button type="submit" name="submit_feedback" class="btn">Submit Feedback</button>
            </form>
        </div>
        
        <!-- Print Button -->
        <div style="padding: 20px 30px;">
            <button onclick="window.print()" class="btn print-btn">Print Marksheet</button>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>This is a computer-generated document. No signature is required.</p>
            <p>© 2024 University of Excellence. All rights reserved.</p>
        </div>
    </div>
    
    <script>
        // Star rating functionality
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('rating');
            
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-rating');
                    ratingInput.value = rating;
                    
                    // Update star display
                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
                
                // Hover effect
                star.addEventListener('mouseover', function() {
                    const rating = this.getAttribute('data-rating');
                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.style.color = '#ffc107';
                        }
                    });
                });
                
                star.addEventListener('mouseout', function() {
                    const currentRating = ratingInput.value;
                    stars.forEach((s, index) => {
                        if (index >= currentRating) {
                            s.style.color = '#ddd';
                        }
                    });
                });
            });
            
            // Initialize with 0 rating
            ratingInput.value = 0;
        });
    </script>
</body>
</html>