<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Result Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="registration-form">
            <h2>Student Registration</h2>
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

            <form id="studentRegisterForm" action="process_student_register.php" method="POST">
                <div class="form-group">
                    <label for="roll_number">Roll Number *</label>
                    <input type="text" id="roll_number" name="roll_number" required>
                </div>
                
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="department">Department *</label>
                    <select id="department" name="department" required>
                        <option value="">Select Department</option>
                        <option value="Science">Science</option>
                        <option value="Arts">Arts</option>
                        <option value="Commerce">Commerce</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="semester">Semester (optional)</label>
                    <select id="semester" name="semester">
                        <option value="">Select Semester</option>
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                        <option value="3">3rd Semester</option>
                        <option value="4">4th Semester</option>
                        <option value="5">5th Semester</option>
                        <option value="6">6th Semester</option>
                        <option value="7">7th Semester</option>
                        <option value="8">8th Semester</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="batch_year">Batch Year (optional)</label>
                    <input type="number" id="batch_year" name="batch_year" min="2000" max="2030">
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn">Register</button>
                
                <p class="login-link">
                    Already have an account? <a href="student_login.php">Login here</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('studentRegisterForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
            
            // Validate roll number format
            const rollNumber = document.getElementById('roll_number').value;
            if (!/^[A-Za-z0-9\-]+$/.test(rollNumber)) {
                e.preventDefault();
                alert('Roll number can only contain letters, numbers, and hyphens!');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>