<?php
require_once __DIR__ . '/../config/config.php';

if (!isAdminLoggedIn()) {
    redirect('admin/admin_login.php');
}

// sanitize type
$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
if ($type !== 'students' && $type !== 'teachers') {
    echo 'Invalid export type.';
    exit;
}

try {
    if ($type === 'students') {
        $students_q = isset($_GET['students_q']) ? sanitize($_GET['students_q']) : '';
        $students_department = isset($_GET['students_department']) ? sanitize($_GET['students_department']) : '';
        $students_semester = isset($_GET['students_semester']) ? sanitize($_GET['students_semester']) : '';
        $students_sort = isset($_GET['students_sort']) ? sanitize($_GET['students_sort']) : 'student_id';
        $students_order = (isset($_GET['students_order']) && strtolower($_GET['students_order']) === 'asc') ? 'ASC' : 'DESC';

        $whereParts = [];
        $params = [];
        if ($students_q !== '') { $whereParts[] = '(name LIKE :q OR roll_number LIKE :q OR department LIKE :q OR email LIKE :q)'; $params[':q']='%'.$students_q.'%'; }
        if ($students_department !== '') { $whereParts[] = 'department = :dept'; $params[':dept'] = $students_department; }
        if ($students_semester !== '') { $whereParts[] = 'semester = :sem'; $params[':sem'] = $students_semester; }
        $where = count($whereParts)>0 ? 'WHERE '.implode(' AND ', $whereParts) : '';

        $allowedStudentSort = ['student_id','name','roll_number','department','semester','created_at','email'];
        if (!in_array($students_sort, $allowedStudentSort)) { $students_sort = 'student_id'; }

        $sql = 'SELECT student_id,name,roll_number,email,phone,department,semester,created_at FROM students ' . $where . ' ORDER BY ' . $students_sort . ' ' . $students_order;
        $stmt = $conn->prepare($sql);
        foreach ($params as $k=>$v) { $stmt->bindValue($k,$v); }
        $filename = 'students_' . date('Ymd_His') . '.csv';
    } else {
        $teachers_q = isset($_GET['teachers_q']) ? sanitize($_GET['teachers_q']) : '';
        $teachers_department = isset($_GET['teachers_department']) ? sanitize($_GET['teachers_department']) : '';
        $teachers_sort = isset($_GET['teachers_sort']) ? sanitize($_GET['teachers_sort']) : 'teacher_id';
        $teachers_order = (isset($_GET['teachers_order']) && strtolower($_GET['teachers_order']) === 'asc') ? 'ASC' : 'DESC';

        $twhereParts = [];
        $tparams = [];
        if ($teachers_q !== '') { $twhereParts[] = '(name LIKE :q OR department LIKE :q OR email LIKE :q)'; $tparams[':q']='%'.$teachers_q.'%'; }
        if ($teachers_department !== '') { $twhereParts[] = 'department = :dept'; $tparams[':dept'] = $teachers_department; }
        $twhere = count($twhereParts)>0 ? 'WHERE '.implode(' AND ', $twhereParts) : '';

        $allowedTeacherSort = ['teacher_id','name','email','department','created_at'];
        if (!in_array($teachers_sort, $allowedTeacherSort)) { $teachers_sort = 'teacher_id'; }

        $sql = 'SELECT teacher_id,name,email,phone,department,created_at FROM teachers ' . $twhere . ' ORDER BY ' . $teachers_sort . ' ' . $teachers_order;
        $stmt = $conn->prepare($sql);
        foreach ($tparams as $k=>$v) { $stmt->bindValue($k,$v); }
        $filename = 'teachers_' . date('Ymd_His') . '.csv';
    }

    // execute prepared statement
    $stmt->execute();

} catch (Exception $e) {
    http_response_code(500);
    echo 'Query error.';
    exit;
}

// Send CSV headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
// prevent timeouts for large exports
set_time_limit(0);

$out = fopen('php://output', 'w');
if (!$out) { exit; }

// Write BOM for Excel compatibility
fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Stream rows one by one to avoid high memory usage
$first = true;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($first) {
        fputcsv($out, array_keys($row));
        $first = false;
    }
    fputcsv($out, $row);
    // flush output buffers so the client receives data progressively
    if (function_exists('ob_flush')) { @ob_flush(); }
    flush();
}

// If no rows were emitted, output an empty header row
if ($first) {
    if ($type === 'students') {
        fputcsv($out, ['student_id','name','roll_number','email','phone','department','semester','created_at']);
    } else {
        fputcsv($out, ['teacher_id','name','email','phone','department','created_at']);
    }
}

fclose($out);
exit;
