<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Result Management System - Bangladesh</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .home-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            padding: 40px 20px;
            color: white;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .user-options {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 50px;
            flex-wrap: wrap;
        }
        
        .option-card {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            width: 300px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .option-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .option-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #667eea;
        }
        
        .option-card h3 {
            margin-bottom: 20px;
            color: #444;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 5px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #764ba2;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
        }
        
        .btn-outline:hover {
            background: #667eea;
            color: white;
        }
        
        .footer {
            text-align: center;
            padding: 30px;
            color: white;
            margin-top: 50px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        @media (max-width: 768px) {
            .user-options {
                flex-direction: column;
                align-items: center;
            }
            
            .option-card {
                width: 90%;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="home-container">
        <header class="header">
            <h1>College Student Result Management System</h1>
            <p>Bangladesh Educational Institution Portal</p>
            <p>Efficient, Secure, and User-Friendly Result Management</p>
        </header>
        
        <div class="user-options">
            <!-- Student Section -->
            <div class="option-card">
                <div class="option-icon">🎓</div>
                <h3>Student Portal</h3>
                <p>View your results, check attendance, and manage your academic profile.</p>
                <div class="option-buttons">
                    <a href="student/student_login.php" class="btn">Student Login</a>
                    <a href="student/student_register.php" class="btn btn-outline">Register</a>
                </div>
            </div>
            
            <!-- Teacher Section -->
            <div class="option-card">
                <div class="option-icon">👨‍🏫</div>
                <h3>Teacher Portal</h3>
                <p>Add marks, update results, manage attendance and assignments.</p>
                <div class="option-buttons">
                    <a href="teacher/teacher_login.php" class="btn">Teacher Login</a>
                    <a href="teacher/teacher_register.php" class="btn btn-outline">Register</a>
                </div>
            </div>
            
            <!-- Admin Section -->
            <div class="option-card">
                <div class="option-icon">👨‍💼</div>
                <h3>Admin Panel</h3>
                <p>Manage users, courses, semesters, and overall system administration.</p>
                <div class="option-buttons">
                    <a href="admin/admin_login.php" class="btn">Admin Login</a>
                </div>
            </div>
        </div>
        
        <div class="system-features">
            <h2 style="text-align: center; color: white; margin: 40px 0 20px 0;">System Features</h2>
            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                <div style="background: rgba(255,255,255,0.9); padding: 20px; border-radius: 10px; width: 250px;">
                    <h4>📊 Real-time Results</h4>
                    <p>Instant result publication and viewing</p>
                </div>
                <div style="background: rgba(255,255,255,0.9); padding: 20px; border-radius: 10px; width: 250px;">
                    <h4>🔐 Secure Access</h4>
                    <p>Role-based authentication system</p>
                </div>
                <div style="background: rgba(255,255,255,0.9); padding: 20px; border-radius: 10px; width: 250px;">
                    <h4>🖨️ Print Results</h4>
                    <p>Generate printable result sheets</p>
                </div>
                <div style="background: rgba(255,255,255,0.9); padding: 20px; border-radius: 10px; width: 250px;">
                    <h4>📱 Responsive Design</h4>
                    <p>Accessible on all devices</p>
                </div>
            </div>
        </div>
        
        <footer class="footer">
            <p>© 2024 College Result Management System | Developed for Bangladeshi Educational Institutions</p>
            <p style="margin-top: 10px; opacity: 0.8;">Version 1.0 | All Rights Reserved</p>
        </footer>
    </div>
</body>
</html>