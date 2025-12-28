<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Result Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Student Login</h2>

            <?php
            require_once __DIR__ . '/../config/config.php';
            if (!empty($_SESSION['success'])) {
                echo '<p class="text-success">' . $_SESSION['success'] . '</p>';
                unset($_SESSION['success']);
            }
            if (!empty($_SESSION['error'])) {
                echo '<p class="text-error">' . $_SESSION['error'] . '</p>';
                unset($_SESSION['error']);
            }
            ?>

            <form action="process_student_login.php" method="POST">
                <div class="form-group">
                    <label for="roll_number">Roll Number</label>
                    <input type="text" id="roll_number" name="roll_number" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn">Login</button>
            </form>

            <p class="register-link">Don't have an account? <a href="student_register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
