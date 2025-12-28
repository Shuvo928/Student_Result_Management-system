<?php
require_once '../config/config.php';

if (!isTeacherLoggedIn()) {
    redirect("teacher_login.php");
}

$teacher_id = $_SESSION['teacher_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Result Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root{--sidebar-bg:linear-gradient(180deg,#0d9488 0%, #0b1220 100%);--accent:#0d9488;--card-bg:#ffffff;--muted:#6c757d}
        body{background:#f7fbfa}
        .sidebar { min-height: 100vh; background:var(--sidebar-bg); color:#e6eef0 }
        .sidebar .nav-link{ color:#cfe8e4 }
        .sidebar .nav-link.active{ background:rgba(255,255,255,0.04); border-radius:6px }
        .stat-card { border-radius: 8px; padding: 18px; background: var(--card-bg); box-shadow: 0 4px 10px rgba(13,20,32,0.06); }
        .activity-item { border-bottom: 1px solid rgba(13,20,32,0.04); padding: 10px 0; }
        .header-info span { display:inline-block; margin-right:16px; color:var(--muted); }
        .btn-accent{ background:var(--accent); color:#fff; border:none }
        .card-accent{ border-left:6px solid var(--accent) }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <aside class="col-md-3 col-lg-2 sidebar p-3">
                <div class="d-flex flex-column align-items-start mb-4">
                    <h4 class="mb-1">Teacher Panel</h4>
                    <div class="text-muted small"><?php echo $_SESSION['teacher_name']; ?></div>
                    <div class="text-muted small"><?php echo $_SESSION['teacher_department']; ?></div>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="teacher_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a class="nav-link" href="add_marks.php"><i class="fas fa-plus-circle me-2"></i>Add Marks</a>
                    <a class="nav-link" href="update_marks.php"><i class="fas fa-edit me-2"></i>Update Marks</a>
                    <a class="nav-link" href="view_students.php"><i class="fas fa-users me-2"></i>View Students</a>
                    <a class="nav-link" href="attendance.php"><i class="fas fa-user-check me-2"></i>Attendance</a>
                    <a class="nav-link" href="assignments.php"><i class="fas fa-tasks me-2"></i>Assignments</a>
                    <a class="nav-link" href="teacher_profile.php"><i class="fas fa-user-cog me-2"></i>Profile</a>
                    <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0">Teacher Dashboard</h2>
                        <div class="header-info mt-1">
                            <span>Welcome, <?php echo $_SESSION['teacher_name']; ?></span>
                            <span>Department: <?php echo $_SESSION['teacher_department']; ?></span>
                        </div>
                    </div>
                    <div>
                        <a href="add_marks.php" class="btn btn-outline-primary me-2"><i class="fas fa-plus"></i> Add Marks</a>
                        <a href="teacher_profile.php" class="btn btn-outline-secondary"><i class="fas fa-user"></i> Profile</a>
                    </div>
                </div>
            
                <!-- Dashboard - Add Marks + Stats -->
                <div class="row g-3 mb-4" id="dashboardStats">
                    <div class="col-md-4">
                        <div class="card stat-card card-accent">
                            <div class="card-body">
                                <h5 class="card-title">Add Marks</h5>
                                <form method="post" action="process_add_marks.php">
                                    <div class="mb-2">
                                        <label class="form-label small">Roll Number</label>
                                        <input name="student_id[]" class="form-control form-control-sm" required />
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Course ID</label>
                                        <input name="course_id" class="form-control form-control-sm" required />
                                    </div>
                                    <input type="hidden" name="semester" value="1" />
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small">Half-Yearly (0-100)</label>
                                            <input type="number" step="1" min="0" max="100" name="half_yearly[]" class="form-control form-control-sm" />
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Final Exam (0-70)</label>
                                            <input type="number" step="1" min="0" max="70" name="exam_marks[]" class="form-control form-control-sm" />
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Class Test (0-15)</label>
                                            <input type="number" step="1" min="0" max="15" name="class_test[]" class="form-control form-control-sm" />
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Assignment (0-10)</label>
                                            <input type="number" step="1" min="0" max="10" name="assignment[]" class="form-control form-control-sm" />
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Attendance (0-5)</label>
                                            <input type="number" step="1" min="0" max="5" name="attendance[]" class="form-control form-control-sm" />
                                        </div>
                                    </div>
                                    <div class="mt-3 d-flex justify-content-end">
                                        <button class="btn btn-sm btn-accent" type="submit">Submit Marks</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card text-center">
                            <div class="text-muted">Courses Assigned</div>
                            <div class="h3 mt-2" id="statCoursesAssigned">0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card text-center">
                            <div class="text-muted">Results Published</div>
                            <div class="h3 mt-2" id="statResultsPublished">0</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities (dynamic) -->
                <div class="card mb-4">
                    <div class="card-header">Recent Activities</div>
                    <div class="card-body p-3">
                        <div class="list-unstyled" id="activityList">
                            <p class="text-muted">Loading activities…</p>
                        </div>
                    </div>
                </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    fetch('dashboard_api.php')
                        .then(r => r.json())
                        .then(res => {
                            if (!res.success) return;
                            const d = res.data;
                            // simple number animation
                            animateNumber('statCoursesAssigned', d.courses_assigned);
                            animateNumber('statResultsPublished', d.results_published);

                            const list = document.getElementById('activityList');
                            list.innerHTML = '';
                            if (d.activities && d.activities.length) {
                                d.activities.forEach(a => {
                                    const item = document.createElement('div');
                                    item.className = 'activity-item d-flex justify-content-between align-items-start';
                                    item.innerHTML = `
                                        <div>
                                            <div class="fw-semibold">${escapeHtml(a.student_name||'Unknown')}</div>
                                            <div class="small text-muted">${escapeHtml(a.course_code||a.course_name||'Course')} — Grade: <strong>${a.grade}</strong></div>
                                        </div>
                                        <div class="text-end small text-muted">${formatDate(a.published_date)}</div>
                                    `;
                                    list.appendChild(item);
                                });
                            } else {
                                list.innerHTML = '<p class="text-muted">No recent activities</p>';
                            }
                        })
                        .catch(err => {
                            document.getElementById('activityList').innerHTML = '<p class="muted">Unable to load activities</p>';
                            console.error(err);
                        });
                });

                function animateNumber(id, target) {
                    const el = document.getElementById(id);
                    if (!el) return;
                    let start = 0;
                    target = parseInt(target,10) || 0;
                    const step = Math.max(1, Math.round(target / 30));
                    const timer = setInterval(() => {
                        start += step;
                        if (start >= target) {
                            el.textContent = target;
                            clearInterval(timer);
                        } else {
                            el.textContent = start;
                        }
                    }, 12);
                }

                function formatDate(iso) {
                    try {
                        const d = new Date(iso);
                        return d.toLocaleString();
                    } catch(e) { return iso; }
                }

                function escapeHtml(str) {
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                }
            </script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        </div>
    </div>
</body>
</html>