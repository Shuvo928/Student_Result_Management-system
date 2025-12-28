<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Result Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional local stylesheet (overrides) -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background: linear-gradient(135deg,#6dd5ed 0%,#2193b0 100%);
            background-attachment: fixed;
        }
        .login-card { max-width: 420px; width: 100%; margin-top: 40px; background: rgba(255,255,255,0.98); border: none; }
        .brand { font-weight: 600; letter-spacing: .4px; }
        .avatar-wrapper{ width:92px; height:92px; margin:-60px auto 10px; border-radius:50%; background:#ffffff; display:flex; align-items:center; justify-content:center; box-shadow:0 6px 18px rgba(0,0,0,0.12); }
        .avatar-wrapper svg{ width:56px; height:56px; }
    </style>
</head>
<body class="bg-light">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="login-card card shadow-sm p-3">
            <div class="card-body text-center">
                <div class="avatar-wrapper" aria-hidden="true">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="admin avatar">
                        <circle cx="32" cy="16" r="12" fill="#2b6cb0" />
                        <path d="M12 56c0-11 9-20 20-20s20 9 20 20" fill="#234e6b" />
                        <path d="M32 28c-6 0-10 4-10 4v6c0 6 4 10 10 10s10-4 10-10v-6s-4-4-10-4z" fill="#f6d365" />
                        <path d="M28 36l4 6 4-6-4 2-4-2z" fill="#ff7043" />
                    </svg>
                </div>
                <div class="mb-3">
                    <h3 class="brand">Result Management</h3>
                    <p class="text-muted small mb-0">Admin Sign In</p>
                </div>

                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-sm" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-sm" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form action="process_admin_login.php" method="POST" novalidate>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" placeholder="username" required autofocus>
                        <label for="username">Username</label>
                    </div>

                    <div class="form-floating mb-2">
                        <input type="password" class="form-control" id="password" name="password" placeholder="password" required>
                        <label for="password">Password</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="togglePassword" aria-label="Show password">
                        <label class="form-check-label" for="togglePassword">Show password</label>
                    </div>

                    <div class="d-grid mb-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>

                    <div class="text-center">
                        <a href="../index.php" class="link-secondary">Back to Home</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function(){
            const pw = document.getElementById('password');
            const toggle = document.getElementById('togglePassword');
            if (toggle && pw) {
                toggle.addEventListener('change', function(){
                    pw.type = this.checked ? 'text' : 'password';
                });
            }
        })();
    </script>
</body>
</html>