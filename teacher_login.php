<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login - Result Management System</title>
    <!-- Bootstrap for elegant form layout -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .login-card { max-width: 480px; width: 100%; }
        .brand { font-weight:700; letter-spacing:0.4px; }
        .small-muted { color: #6c757d; font-size: .9rem; }
        .activity-item { border-bottom: 1px solid #eef0f3; padding: 10px 0; }
    </style>
</head>
<body class="bg-light">
    <div class="d-flex align-items-center justify-content-center min-vh-100">
        <div class="card shadow-sm p-4 login-card" style="max-width:480px; width:100%;">
            <div class="text-center mb-3">
                <!-- Teacher cartoon SVG (top of card) -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 260 160" width="180" height="110" role="img" aria-hidden="true">
                    <rect width="100%" height="100%" rx="12" fill="#f7fbfa" />
                    <g transform="translate(30,10)">
                        <circle cx="60" cy="30" r="24" fill="#ffd9b3" />
                        <circle cx="50" cy="26" r="3" fill="#2b2b2b" />
                        <circle cx="70" cy="26" r="3" fill="#2b2b2b" />
                        <path d="M54 38 Q60 44 66 38" stroke="#2b2b2b" stroke-width="1.8" fill="none" stroke-linecap="round" />
                        <rect x="46" y="56" width="28" height="28" rx="6" fill="#0d9488" />
                        <path d="M10 100 C30 78, 90 78, 110 100" stroke="#0d9488" stroke-width="6" fill="none" stroke-linecap="round" />
                    </g>
                </svg>
            </div>

            <div class="text-center mb-3">
                <div class="brand h4 mb-1">College Result Management</div>
                <div class="small-muted">Teacher Portal</div>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-sm">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-sm">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form action="process_teacher_login.php" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label">Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" id="username" name="username" class="form-control" required placeholder="username or email">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control" required placeholder="Your password">
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">Login</button>
                </div>

                <div class="text-center small-muted mb-2">Don't have an account? <a href="teacher_register.php">Register</a></div>
                <div class="text-center"><a href="../index.php">Back to Home</a></div>
            </form>
        </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Simple client-side validation bootstrap style
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()
    </script>
</body>
</html>