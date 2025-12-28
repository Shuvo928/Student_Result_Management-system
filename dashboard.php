<?php
require_once __DIR__ . '/../config/config.php';

if (!isAdminLoggedIn()) {
  redirect(BASE_URL . 'admin/admin_login.php');
}

// Server-side pagination + search for students
$students = [];
$students_per_page = 25;
$students_page = isset($_GET['students_page']) ? max(1, intval($_GET['students_page'])) : 1;
$students_offset = ($students_page - 1) * $students_per_page;
$students_q = isset($_GET['students_q']) ? sanitize($_GET['students_q']) : '';
// additional student filters/sorting
$students_department = isset($_GET['students_department']) ? sanitize($_GET['students_department']) : '';
$students_semester = isset($_GET['students_semester']) ? sanitize($_GET['students_semester']) : '';
$students_sort = isset($_GET['students_sort']) ? sanitize($_GET['students_sort']) : 'student_id';
$students_order = (isset($_GET['students_order']) && strtolower($_GET['students_order']) === 'asc') ? 'ASC' : 'DESC';
try {
  $whereParts = [];
  $params = [];
  if ($students_q !== '') {
    $whereParts[] = '(name LIKE :q OR roll_number LIKE :q OR department LIKE :q OR email LIKE :q)';
    $params[':q'] = '%' . $students_q . '%';
  }
  if ($students_department !== '') {
    $whereParts[] = 'department = :dept';
    $params[':dept'] = $students_department;
  }
  if ($students_semester !== '') {
    $whereParts[] = 'semester = :sem';
    $params[':sem'] = $students_semester;
  }
  $where = '';
  if (count($whereParts) > 0) { $where = 'WHERE ' . implode(' AND ', $whereParts); }

  // validate sort field against allowed columns to prevent injection
  $allowedStudentSort = ['student_id','name','roll_number','department','semester','registration_date','created_at','email'];
  if (!in_array($students_sort, $allowedStudentSort)) { $students_sort = 'student_id'; }

  // total count with optional filter
  $countSql = 'SELECT COUNT(*) as cnt FROM students ' . $where;
  $countStmt = $conn->prepare($countSql);
  foreach ($params as $k => $v) { $countStmt->bindValue($k, $v); }
  $countStmt->execute();
  $students_total = (int)$countStmt->fetchColumn();

  $sSql = 'SELECT * FROM students ' . $where . ' ORDER BY ' . $students_sort . ' ' . $students_order . ' LIMIT :limit OFFSET :offset';
  $sstmt = $conn->prepare($sSql);
  foreach ($params as $k => $v) { $sstmt->bindValue($k, $v); }
  $sstmt->bindValue(':limit', $students_per_page, PDO::PARAM_INT);
  $sstmt->bindValue(':offset', $students_offset, PDO::PARAM_INT);
  $sstmt->execute();
  $students = $sstmt->fetchAll(PDO::FETCH_ASSOC);
  $students_total_pages = (int)ceil($students_total / $students_per_page);
} catch (Exception $e) {
  $students = [];
  $students_total = 0;
  $students_total_pages = 0;
}

// Server-side pagination + search for teachers
$teachers = [];
$teachers_per_page = 25;
$teachers_page = isset($_GET['teachers_page']) ? max(1, intval($_GET['teachers_page'])) : 1;
$teachers_offset = ($teachers_page - 1) * $teachers_per_page;
$teachers_q = isset($_GET['teachers_q']) ? sanitize($_GET['teachers_q']) : '';
// additional teacher filters/sorting
$teachers_department = isset($_GET['teachers_department']) ? sanitize($_GET['teachers_department']) : '';
$teachers_sort = isset($_GET['teachers_sort']) ? sanitize($_GET['teachers_sort']) : 'teacher_id';
$teachers_order = (isset($_GET['teachers_order']) && strtolower($_GET['teachers_order']) === 'asc') ? 'ASC' : 'DESC';
try {
  $twhereParts = [];
  $tparams = [];
  if ($teachers_q !== '') {
    $twhereParts[] = '(name LIKE :q OR department LIKE :q OR email LIKE :q)';
    $tparams[':q'] = '%' . $teachers_q . '%';
  }
  if ($teachers_department !== '') {
    $twhereParts[] = 'department = :tdept';
    $tparams[':tdept'] = $teachers_department;
  }
  $twhere = '';
  if (count($twhereParts) > 0) { $twhere = 'WHERE ' . implode(' AND ', $twhereParts); }

  $allowedTeacherSort = ['teacher_id','name','email','department','created_at'];
  if (!in_array($teachers_sort, $allowedTeacherSort)) { $teachers_sort = 'teacher_id'; }

  $tcount = $conn->prepare('SELECT COUNT(*) as cnt FROM teachers ' . $twhere);
  foreach ($tparams as $k => $v) { $tcount->bindValue($k, $v); }
  $tcount->execute();
  $teachers_total = (int)$tcount->fetchColumn();

  $tSql = 'SELECT * FROM teachers ' . $twhere . ' ORDER BY ' . $teachers_sort . ' ' . $teachers_order . ' LIMIT :limit OFFSET :offset';
  $tstmt = $conn->prepare($tSql);
  foreach ($tparams as $k => $v) { $tstmt->bindValue($k, $v); }
  $tstmt->bindValue(':limit', $teachers_per_page, PDO::PARAM_INT);
  $tstmt->bindValue(':offset', $teachers_offset, PDO::PARAM_INT);
  $tstmt->execute();
  $teachers = $tstmt->fetchAll(PDO::FETCH_ASSOC);
  $teachers_total_pages = (int)ceil($teachers_total / $teachers_per_page);
} catch (Exception $e) {
  $teachers = [];
  $teachers_total = 0;
  $teachers_total_pages = 0;
}

// Fetch distinct departments and semesters for filters
$studentDepartments = [];
$studentSemesters = [];
$teacherDepartments = [];
try {
  $d = $conn->query("SELECT DISTINCT department FROM students WHERE department IS NOT NULL AND department <> '' ORDER BY department");
  $studentDepartments = $d->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) { $studentDepartments = []; }
try {
  $s = $conn->query("SELECT DISTINCT semester FROM students WHERE semester IS NOT NULL AND semester <> '' ORDER BY semester");
  $studentSemesters = $s->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) { $studentSemesters = []; }
try {
  $td = $conn->query("SELECT DISTINCT department FROM teachers WHERE department IS NOT NULL AND department <> '' ORDER BY department");
  $teacherDepartments = $td->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) { $teacherDepartments = []; }

// Fetch aggregated results (average GPA per student)
$results_summary = [];
try {
    $rstmt = $conn->prepare('SELECT r.student_id, s.name, COUNT(*) as course_count, ROUND(AVG(r.grade_point),2) as avg_gpa FROM results r JOIN students s ON r.student_id = s.student_id GROUP BY r.student_id ORDER BY avg_gpa DESC');
    $rstmt->execute();
    $results_summary = $rstmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $results_summary = [];
}

// results totals and latest timestamp for change-detection
$results_total = 0;
$results_max_created = null;
try {
  $rt = $conn->query('SELECT COUNT(*) FROM results'); $results_total = (int)$rt->fetchColumn();
  $rm = $conn->query('SELECT MAX(created_at) FROM results'); $results_max_created = $rm->fetchColumn();
} catch (Exception $e) { $results_total = 0; $results_max_created = null; }

// Recent registrations (latest 6 students and teachers)
$recent_students = [];
$recent_teachers = [];
try {
  // Some schemas don't have `created_at`; use `student_id` as fallback ordering
  // Prefer ordering by `registration_date` if present, otherwise fall back to `student_id`.
  try {
    $rs = $conn->query("SELECT student_id, roll_number, name, email, phone, department, semester, batch_year, registration_date FROM students ORDER BY registration_date DESC LIMIT 6");
  } catch (Exception $e) {
    $rs = $conn->query("SELECT student_id, roll_number, name, email, phone, department, semester, batch_year FROM students ORDER BY student_id DESC LIMIT 6");
  }
  $recent_students = $rs->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $recent_students = []; }
try {
  // Teachers table may also lack created_at; order by teacher_id instead
  $rt = $conn->query("SELECT teacher_id, name, email, department FROM teachers ORDER BY teacher_id DESC LIMIT 6");
  $recent_teachers = $rt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $recent_teachers = []; }

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
      .sidebar { min-height:100vh; background:linear-gradient(180deg,#ffffff,#f7fbff); border-right:1px solid rgba(0,0,0,0.04); }
      .stat-card { border-radius:12px; padding:18px; background:linear-gradient(180deg,#ffffff,#fbfdff); box-shadow:0 6px 24px rgba(13,38,59,0.04); }
      .table-fixed { font-size:0.95rem; }
      .avatar-sm { width:42px; height:42px; border-radius:50%; background:#e9f5ff; display:inline-flex; align-items:center; justify-content:center; }
      .brand-logo{ font-weight:700; color:#0d6efd; letter-spacing:0.2px }
      .nav-link.active{ background:rgba(13,110,253,0.06); border-radius:6px }
      .stat-icon { font-size:1.6rem; color: #0d6efd; opacity:0.95 }
      .card .card-header{ background:transparent; border-bottom:0 }
    </style>
  </head>
  <body class="bg-light">
    <div class="container-fluid">
      <div class="row">
        <aside class="col-lg-3 col-md-4 p-4 sidebar">
          <div class="d-flex align-items-center mb-3">
            <div class="avatar-sm me-2"><i class="bi bi-shield-lock-fill text-primary"></i></div>
            <div>
              <div class="brand-logo">Result Management</div>
              <div class="small text-muted">Admin Panel</div>
            </div>
          </div>

          <div class="mb-3 p-2 bg-white rounded-3">
            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></div>
            <div class="text-muted small">Manage students, teachers & results</div>
          </div>

          <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Overview</a>
            <a class="nav-link" href="#students"><i class="bi bi-people-fill me-2"></i>Students</a>
            <a class="nav-link" href="#teachers"><i class="bi bi-person-badge-fill me-2"></i>Teachers</a>
            <a class="nav-link" href="#results"><i class="bi bi-bar-chart-line-fill me-2"></i>Results</a>
            <a class="nav-link text-danger mt-2" href="../Student/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
          </nav>
        </aside>

        <main class="col-lg-9 col-md-8 p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h2 class="mb-0">Dashboard</h2>
              <div class="text-muted small">Overview & quick actions</div>
            </div>
            <div>
              <a href="export.php?type=all" class="btn btn-outline-secondary btn-sm me-2"><i class="bi bi-download"></i> Export</a>
              <a href="#students" class="btn btn-primary btn-sm">Add Student</a>
            </div>
          </div>

          <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
            <div class="alert alert-info">
              <strong>Debug info:</strong>
              <?php
                try {
                  $c = $conn->query('SELECT COUNT(*) AS c FROM students');
                  $ct = (int)$c->fetchColumn();
                } catch (Exception $e) { $ct = 'ERROR: '.$e->getMessage(); }
                echo ' students_total=' . htmlspecialchars((string)$ct);
              ?>
              — <a href="inspect_students.php" class="alert-link">Open students inspector</a>
            </div>
          <?php endif; ?>

          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <div>
                  <small class="text-muted">Total Students</small>
                  <div class="h4 mt-1 mb-0"><?php echo (int)($students_total ?? count($students)); ?></div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
                <div>
                  <small class="text-muted">Total Teachers</small>
                  <div class="h4 mt-1 mb-0"><?php echo (int)($teachers_total ?? count($teachers)); ?></div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
                <div>
                  <small class="text-muted">Results Records</small>
                  <div class="h4 mt-1 mb-0"><?php echo (int)($results_total ?? count($results_summary)); ?></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent registrations -->
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <strong>Recent Students</strong>
                  <a href="#students" class="small">View all</a>
                </div>
                <div class="card-body p-0">
                  <div class="list-group list-group-flush">
                    <?php if(count($recent_students) > 0): foreach($recent_students as $rs): ?>
                      <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                          <div class="fw-bold"><?php echo htmlspecialchars($rs['name'] ?? $rs['roll_number']); ?></div>
                          <div class="small text-muted"><?php echo htmlspecialchars($rs['email'] ?? ''); ?> • <?php echo htmlspecialchars($rs['department'] ?? ''); ?></div>
                        </div>
                        <div class="text-end small text-muted"><?php echo htmlspecialchars(isset($rs['created_at']) ? $rs['created_at'] : ('ID: ' . ($rs['student_id'] ?? ''))); ?></div>
                      </div>
                    <?php endforeach; else: ?>
                      <div class="list-group-item text-center text-muted">No recent students</div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <strong>Recent Teachers</strong>
                  <a href="#teachers" class="small">View all</a>
                </div>
                <div class="card-body p-0">
                  <div class="list-group list-group-flush">
                    <?php if(count($recent_teachers) > 0): foreach($recent_teachers as $rt): ?>
                      <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                          <div class="fw-bold"><?php echo htmlspecialchars($rt['name']); ?></div>
                          <div class="small text-muted"><?php echo htmlspecialchars($rt['email'] ?? ''); ?> • <?php echo htmlspecialchars($rt['department'] ?? ''); ?></div>
                        </div>
                        <div class="text-end small text-muted"><?php echo htmlspecialchars(isset($rt['created_at']) ? $rt['created_at'] : ('ID: ' . ($rt['teacher_id'] ?? ''))); ?></div>
                      </div>
                    <?php endforeach; else: ?>
                      <div class="list-group-item text-center text-muted">No recent teachers</div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Students section: list and quick actions -->
          <section id="students" class="mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Students</strong>
                <div>
                  <a href="#" class="btn btn-sm btn-primary">Add Student</a>
                  <?php
                    $studentsExportUrl = 'export.php?type=students';
                    $studentsExportUrl .= $students_q!=='' ? '&students_q='.urlencode($students_q) : '';
                    $studentsExportUrl .= $students_department!=='' ? '&students_department='.urlencode($students_department) : '';
                    $studentsExportUrl .= $students_semester!=='' ? '&students_semester='.urlencode($students_semester) : '';
                    $studentsExportUrl .= '&students_sort='.urlencode($students_sort).'&students_order='.urlencode(strtolower($students_order));
                  ?>
                  <a href="<?php echo $studentsExportUrl; ?>" class="btn btn-sm btn-outline-secondary ms-2">Export CSV</a>
                </div>
              </div>
              <div class="card-body p-0">
                <div class="p-3">
                  <form id="students-search-form" class="row g-2" method="get" action="dashboard.php#students">
                    <div class="col-md-4">
                      <input name="students_q" value="<?php echo htmlspecialchars($students_q ?? ''); ?>" class="form-control form-control-sm" placeholder="Search by name, roll, email">
                    </div>
                    <div class="col-md-2">
                      <select name="students_department" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        <?php foreach($studentDepartments as $dpt): ?>
                          <option value="<?php echo htmlspecialchars($dpt); ?>" <?php echo ($students_department===$dpt)?'selected':''; ?>><?php echo htmlspecialchars($dpt); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-1">
                      <select name="students_semester" class="form-select form-select-sm">
                        <option value="">Sem</option>
                        <?php foreach($studentSemesters as $sem): ?>
                          <option value="<?php echo htmlspecialchars($sem); ?>" <?php echo ($students_semester===$sem)?'selected':''; ?>><?php echo htmlspecialchars($sem); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <select name="students_sort" class="form-select form-select-sm">
                        <option value="student_id" <?php echo ($students_sort==='student_id')?'selected':''; ?>>Sort: ID</option>
                        <option value="name" <?php echo ($students_sort==='name')?'selected':''; ?>>Name</option>
                        <option value="roll_number" <?php echo ($students_sort==='roll_number')?'selected':''; ?>>Roll</option>
                        <option value="department" <?php echo ($students_sort==='department')?'selected':''; ?>>Department</option>
                        <option value="semester" <?php echo ($students_sort==='semester')?'selected':''; ?>>Semester</option>
                      </select>
                    </div>
                    <div class="col-md-1">
                      <select name="students_order" class="form-select form-select-sm">
                        <option value="desc" <?php echo ($students_order==='DESC')?'selected':''; ?>>Desc</option>
                        <option value="asc" <?php echo ($students_order==='ASC')?'selected':''; ?>>Asc</option>
                      </select>
                    </div>
                    <div class="col-md-2 text-end">
                      <button class="btn btn-sm btn-primary" type="submit">Apply</button>
                      <a href="dashboard.php#students" class="btn btn-sm btn-outline-secondary ms-2">Clear</a>
                    </div>
                    <div class="col-12 text-end mt-1">
                      <small class="text-muted">Total: <?php echo $students_total ?? 0; ?></small>
                    </div>
                  </form>
                </div>
                <div class="table-responsive">
                  <table class="table table-striped table-hover table-fixed mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Roll</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($students) > 0): foreach($students as $st): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($st['student_id']); ?></td>
                          <td><?php echo htmlspecialchars($st['name']); ?></td>
                          <td><?php echo htmlspecialchars($st['roll_number'] ?? ''); ?></td>
                          <td><?php echo htmlspecialchars($st['department'] ?? ''); ?></td>
                          <td><?php echo htmlspecialchars($st['semester'] ?? ''); ?></td>
                          <td><?php echo htmlspecialchars($st['email'] ?? ''); ?></td>
                          <td><?php echo htmlspecialchars($st['phone'] ?? ''); ?></td>
                          <td><?php echo htmlspecialchars(isset($st['created_at']) ? $st['created_at'] : ('ID: ' . ($st['student_id'] ?? ''))); ?></td>
                          <td>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-view-student"
                              data-profile="<?php echo htmlspecialchars(base64_encode(json_encode($st))); ?>">
                              View
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center py-4">No students found</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="card-footer bg-white">
                <nav aria-label="students pagination">
                  <ul class="pagination pagination-sm mb-0">
                    <?php if($students_page > 1): ?>
                      <li class="page-item"><a class="page-link" href="dashboard.php?students_page=<?php echo $students_page-1; ?>#students">Prev</a></li>
                    <?php else: ?>
                      <li class="page-item disabled"><span class="page-link">Prev</span></li>
                    <?php endif; ?>

                    <?php for($p = 1; $p <= $students_total_pages; $p++): if ($p > 1 && $p < $students_total_pages && abs($p - $students_page) > 3) { if ($p < $students_page) { continue; } } ?>
                      <li class="page-item <?php echo $p === $students_page ? 'active':'';?>">
                        <a class="page-link" href="dashboard.php?students_page=<?php echo $p; ?><?php echo $students_q!==''? '&students_q='.urlencode($students_q):''; ?>#students"><?php echo $p; ?></a>
                      </li>
                    <?php endfor; ?>

                    <?php if($students_page < $students_total_pages): ?>
                      <li class="page-item"><a class="page-link" href="dashboard.php?students_page=<?php echo $students_page+1; ?>#students">Next</a></li>
                    <?php else: ?>
                      <li class="page-item disabled"><span class="page-link">Next</span></li>
                    <?php endif; ?>
                  </ul>
                </nav>
              </div>
            </div>
          </section>

          <!-- Teachers section: list and quick actions -->
          <section id="teachers" class="mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Teachers</strong>
                <div>
                  <a href="#" class="btn btn-sm btn-primary">Add Teacher</a>
                  <?php
                    $teachersExportUrl = 'export.php?type=teachers';
                    $teachersExportUrl .= $teachers_q!=='' ? '&teachers_q='.urlencode($teachers_q) : '';
                    $teachersExportUrl .= $teachers_department!=='' ? '&teachers_department='.urlencode($teachers_department) : '';
                    $teachersExportUrl .= '&teachers_sort='.urlencode($teachers_sort).'&teachers_order='.urlencode(strtolower($teachers_order));
                  ?>
                  <a href="<?php echo $teachersExportUrl; ?>" class="btn btn-sm btn-outline-secondary ms-2">Export CSV</a>
                </div>
              </div>
              <div class="card-body p-0">
                <div class="p-3">
                  <form id="teachers-search-form" class="row g-2" method="get" action="dashboard.php#teachers">
                    <div class="col-md-4">
                      <input name="teachers_q" value="<?php echo htmlspecialchars($teachers_q ?? ''); ?>" class="form-control form-control-sm" placeholder="Search by name or email">
                    </div>
                    <div class="col-md-3">
                      <select name="teachers_department" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        <?php foreach($teacherDepartments as $dpt): ?>
                          <option value="<?php echo htmlspecialchars($dpt); ?>" <?php echo ($teachers_department===$dpt)?'selected':''; ?>><?php echo htmlspecialchars($dpt); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <select name="teachers_sort" class="form-select form-select-sm">
                        <option value="teacher_id" <?php echo ($teachers_sort==='teacher_id')?'selected':''; ?>>Sort: ID</option>
                        <option value="name" <?php echo ($teachers_sort==='name')?'selected':''; ?>>Name</option>
                        <option value="email" <?php echo ($teachers_sort==='email')?'selected':''; ?>>Email</option>
                        <option value="department" <?php echo ($teachers_sort==='department')?'selected':''; ?>>Department</option>
                      </select>
                    </div>
                    <div class="col-md-1">
                      <select name="teachers_order" class="form-select form-select-sm">
                        <option value="desc" <?php echo ($teachers_order==='DESC')?'selected':''; ?>>Desc</option>
                        <option value="asc" <?php echo ($teachers_order==='ASC')?'selected':''; ?>>Asc</option>
                      </select>
                    </div>
                    <div class="col-md-2 text-end">
                      <button class="btn btn-sm btn-primary" type="submit">Apply</button>
                      <a href="dashboard.php#teachers" class="btn btn-sm btn-outline-secondary ms-2">Clear</a>
                    </div>
                    <div class="col-12 text-end mt-1">
                      <small class="text-muted">Total: <?php echo $teachers_total ?? 0; ?></small>
                    </div>
                  </form>
                </div>
                <div class="table-responsive">
                  <table class="table table-striped table-hover table-fixed mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Phone</th>
                        <th>Registered</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($teachers) > 0): foreach($teachers as $t): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($t['teacher_id']); ?></td>
                          <td><?php echo htmlspecialchars($t['name']); ?></td>
                          <td><?php echo htmlspecialchars($t['email'] ?? ''); ?></td>
                          <td><?php echo htmlspecialchars($t['department'] ?? ''); ?></td>
                          <td><?php echo htmlspecialchars($t['phone'] ?? ''); ?></td>
                          <td><?php echo htmlspecialchars(isset($t['created_at']) ? $t['created_at'] : ('ID: ' . ($t['teacher_id'] ?? ''))); ?></td>
                          <td>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-view-teacher"
                              data-profile="<?php echo htmlspecialchars(base64_encode(json_encode($t))); ?>">
                              View
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center py-4">No teachers found</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="card-footer bg-white">
                <nav aria-label="teachers pagination">
                  <ul class="pagination pagination-sm mb-0">
                    <?php if($teachers_page > 1): ?>
                      <li class="page-item"><a class="page-link" href="dashboard.php?teachers_page=<?php echo $teachers_page-1; ?>#teachers">Prev</a></li>
                    <?php else: ?>
                      <li class="page-item disabled"><span class="page-link">Prev</span></li>
                    <?php endif; ?>

                    <?php for($p = 1; $p <= $teachers_total_pages; $p++): if ($p > 1 && $p < $teachers_total_pages && abs($p - $teachers_page) > 3) { if ($p < $teachers_page) { continue; } } ?>
                      <li class="page-item <?php echo $p === $teachers_page ? 'active':'';?>">
                        <a class="page-link" href="dashboard.php?teachers_page=<?php echo $p; ?><?php echo $teachers_q!==''? '&teachers_q='.urlencode($teachers_q):''; ?>#teachers"><?php echo $p; ?></a>
                      </li>
                    <?php endfor; ?>

                    <?php if($teachers_page < $teachers_total_pages): ?>
                      <li class="page-item"><a class="page-link" href="dashboard.php?teachers_page=<?php echo $teachers_page+1; ?>#teachers">Next</a></li>
                    <?php else: ?>
                      <li class="page-item disabled"><span class="page-link">Next</span></li>
                    <?php endif; ?>
                  </ul>
                </nav>
              </div>
            </div>
          </section>

          <section id="results" class="mb-4">
            <div class="card">
              <div class="card-header"><strong>Results Summary</strong></div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Student</th>
                        <th>Courses</th>
                        <th>Avg GPA</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($results_summary) > 0): foreach($results_summary as $rs): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($rs['name']); ?></td>
                          <td><?php echo htmlspecialchars($rs['course_count']); ?></td>
                          <td><?php echo htmlspecialchars($rs['avg_gpa']); ?></td>
                          <td><a href="?student_results=<?php echo urlencode($rs['student_id']); ?>" class="btn btn-sm btn-outline-secondary">View Results</a></td>
                        </tr>
                      <?php endforeach; else: ?>
                        <tr><td colspan="4" class="text-center py-4">No results found</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </section>

          <?php
          // Optional: view student profile
          if (isset($_GET['view_student'])) {
              $sid = intval($_GET['view_student']);
              $pstmt = $conn->prepare('SELECT * FROM students WHERE student_id = :id LIMIT 1');
              $pstmt->bindParam(':id', $sid, PDO::PARAM_INT);
              $pstmt->execute();
              $profile = $pstmt->fetch(PDO::FETCH_ASSOC);
              if ($profile) {
                  echo '<div class="card mb-4"><div class="card-header"><strong>Student Profile</strong></div><div class="card-body">';
                  echo '<dl class="row">';
                  foreach ($profile as $k=>$v) {
                      echo '<dt class="col-sm-3">'.htmlspecialchars($k).'</dt><dd class="col-sm-9">'.htmlspecialchars($v).'</dd>';
                  }
                  echo '</dl>';
                  echo '</div></div>';
              }
          }

          // Optional: view student results
          if (isset($_GET['student_results'])) {
              $sid = intval($_GET['student_results']);
              $q = $conn->prepare('SELECT r.*, c.course_name, c.course_code FROM results r LEFT JOIN courses c ON r.course_id = c.course_id WHERE r.student_id = :id');
              $q->bindParam(':id', $sid, PDO::PARAM_INT);
              $q->execute();
              $student_results = $q->fetchAll(PDO::FETCH_ASSOC);
              echo '<div class="card mb-4"><div class="card-header"><strong>Student Results</strong></div><div class="card-body p-0">';
              if (count($student_results) > 0) {
                  echo '<div class="table-responsive"><table class="table mb-0"><thead><tr><th>Course</th><th>Total</th><th>Grade</th><th>GPA</th></tr></thead><tbody>';
                  foreach ($student_results as $sr) {
                      echo '<tr><td>'.htmlspecialchars($sr['course_code'].' - '.$sr['course_name']).'</td><td>'.htmlspecialchars($sr['total_marks']).'</td><td>'.htmlspecialchars($sr['grade']).'</td><td>'.htmlspecialchars($sr['grade_point']).'</td></tr>';
                  }
                  echo '</tbody></table></div>';
              } else {
                  echo '<div class="p-3">No results found for this student.</div>';
              }
              echo '</div></div>';
          }
          ?>

        </main>
      </div>
    </div>
    <!-- Modal used to show full registration/profile details for students/teachers -->
    <div class="modal fade" id="entityModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <dl class="row" id="entityDetails"></dl>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      // Helper: decode base64 JSON profile and return object
      function decodeProfile(b64){
        try{
          return JSON.parse(decodeURIComponent(escape(window.atob(b64))));
        }catch(e){
          try{ return JSON.parse(window.atob(b64)); }catch(e2){ return null; }
        }
      }

      // Populate modal with a profile object
      function showProfile(title, obj){
        const dl = document.getElementById('entityDetails');
        dl.innerHTML = '';
        for(const k in obj){
          const dt = document.createElement('dt'); dt.className = 'col-sm-3'; dt.textContent = k;
          const dd = document.createElement('dd'); dd.className = 'col-sm-9'; dd.textContent = obj[k] === null ? '' : obj[k];
          dl.appendChild(dt); dl.appendChild(dd);
        }
        document.querySelector('#entityModal .modal-title').textContent = title;
        var modal = new bootstrap.Modal(document.getElementById('entityModal'));
        modal.show();
      }

      // Wire up View buttons (students & teachers) using event delegation
      document.addEventListener('click', function(e){
        if(e.target.closest('.btn-view-student')){
          const btn = e.target.closest('.btn-view-student');
          const profile = decodeProfile(btn.getAttribute('data-profile'));
          showProfile('Student Profile', profile || {});
        }
        if(e.target.closest('.btn-view-teacher')){
          const btn = e.target.closest('.btn-view-teacher');
          const profile = decodeProfile(btn.getAttribute('data-profile'));
          showProfile('Teacher Profile', profile || {});
        }
      });

      // Debounce helper
      function debounce(fn, wait){
        let t;
        return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), wait); };
      }

      // Fetch dashboard page and replace a section (students or teachers) by parsing returned HTML
      async function fetchAndReplace(params, sectionId){
        const url = 'dashboard.php?' + params + '#' + sectionId;
        try{
          const res = await fetch(url, { credentials: 'same-origin' });
          const text = await res.text();
          const parser = new DOMParser();
          const doc = parser.parseFromString(text, 'text/html');
          const newSection = doc.querySelector('#' + sectionId);
          const curSection = document.querySelector('#' + sectionId);
          if(newSection && curSection){ curSection.innerHTML = newSection.innerHTML; }
        }catch(err){ console.error('Fetch error', err); }
      }

      // Handle students form via AJAX (debounced on input)
      const studentsForm = document.getElementById('students-search-form');
      if(studentsForm){
        const submitStudents = debounce(function(){
          const fd = new FormData(studentsForm);
          const qs = new URLSearchParams(fd).toString();
          fetchAndReplace(qs + '&students_page=1', 'students');
        }, 450);
        studentsForm.addEventListener('input', submitStudents);
        studentsForm.addEventListener('submit', function(e){ e.preventDefault(); submitStudents(); });
      }

      // Handle teachers form via AJAX
      const teachersForm = document.getElementById('teachers-search-form');
      if(teachersForm){
        const submitTeachers = debounce(function(){
          const fd = new FormData(teachersForm);
          const qs = new URLSearchParams(fd).toString();
          fetchAndReplace(qs + '&teachers_page=1', 'teachers');
        }, 450);
        teachersForm.addEventListener('input', submitTeachers);
        teachersForm.addEventListener('submit', function(e){ e.preventDefault(); submitTeachers(); });
      }

      // Intercept pagination links inside students/teachers sections and load via AJAX
      document.addEventListener('click', function(e){
        const a = e.target.closest('a.page-link');
        if(!a) return;
        const href = a.getAttribute('href') || '';
        if(href.indexOf('dashboard.php') !== -1){
          // parse which section
          if(href.indexOf('#students') !== -1){
            e.preventDefault();
            // preserve current students form values
            if(studentsForm){ const fd = new FormData(studentsForm); const qs = new URLSearchParams(fd).toString(); const m = href.match(/students_page=(\d+)/); const page = m ? m[1] : 1; fetchAndReplace(qs + '&students_page=' + page, 'students'); }
          }
          if(href.indexOf('#teachers') !== -1){
            e.preventDefault();
            if(teachersForm){ const fd = new FormData(teachersForm); const qs = new URLSearchParams(fd).toString(); const m = href.match(/teachers_page=(\d+)/); const page = m ? m[1] : 1; fetchAndReplace(qs + '&teachers_page=' + page, 'teachers'); }
          }
        }
      });

      // Polling to detect new registrations or results (every 10s)
      let pollInterval = 10000;
      let state = {
        students: <?php echo (int)$students_total; ?>,
        teachers: <?php echo (int)$teachers_total; ?>,
        results_count: <?php echo (int)$results_total; ?>,
        results_max: '<?php echo htmlspecialchars($results_max_created ?? ''); ?>'
      };

      async function pollChanges(){
        try{
          const res = await fetch('changes.php', { credentials: 'same-origin' });
          if(!res.ok) return;
          const data = await res.json();
          if (data.students_count !== undefined && data.students_count != state.students) {
            state.students = data.students_count;
            // refresh students section
            if (studentsForm) { const fd = new FormData(studentsForm); const qs = new URLSearchParams(fd).toString(); fetchAndReplace(qs + '&students_page=1', 'students'); }
          }
          if (data.teachers_count !== undefined && data.teachers_count != state.teachers) {
            state.teachers = data.teachers_count;
            if (teachersForm) { const fd = new FormData(teachersForm); const qs = new URLSearchParams(fd).toString(); fetchAndReplace(qs + '&teachers_page=1', 'teachers'); }
          }
          if (data.results_count !== undefined && data.results_count != state.results_count) {
            state.results_count = data.results_count;
            fetchAndReplace('results_page=1', 'results');
          } else if (data.results_max !== undefined && data.results_max !== state.results_max) {
            state.results_max = data.results_max;
            fetchAndReplace('results_page=1', 'results');
          }
        } catch(err){ /* ignore errors */ }
      }
      setInterval(pollChanges, pollInterval);
    </script>
  </body>
</html>
