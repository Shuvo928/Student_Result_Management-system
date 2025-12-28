<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Registration - College Result Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="registration-form">
            <h2><i class="fas fa-chalkboard-teacher"></i> Teacher Registration</h2>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> 
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active" data-step="1">
                    <span>1</span>
                    <div class="step-label">Personal</div>
                </div>
                <div class="step" data-step="2">
                    <span>2</span>
                    <div class="step-label">Professional</div>
                </div>
                <div class="step" data-step="3">
                    <span>3</span>
                    <div class="step-label">Account</div>
                </div>
                <div class="step" data-step="4">
                    <span>4</span>
                    <div class="step-label">Review</div>
                </div>
            </div>
            
            <form id="teacherRegisterForm" action="process_teacher_register.php" method="POST" data-validate onsubmit="return validateFinalForm()">
                
                <!-- Step 1: Personal Information -->
                <div class="form-step active" id="step1">
                    <h3><i class="fas fa-user"></i> Personal Information</h3>
                    
                    <div class="form-group">
                        <label for="name"><i class="fas fa-signature"></i> Full Name *</label>
                        <input type="text" id="name" name="name" required placeholder="Enter your full name">
                        <div class="form-feedback" id="nameFeedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email Address *</label>
                        <input type="email" id="email" name="email" required placeholder="example@college.edu">
                        <div class="form-feedback" id="emailFeedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required placeholder="01XXXXXXXXX">
                        <div class="form-feedback" id="phoneFeedback"></div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn btn-prev" disabled><i class="fas fa-arrow-left"></i> Previous</button>
                        <button type="button" class="btn btn-next" onclick="nextStep(2)">Next <i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
                
                <!-- Step 2: Professional Information -->
                <div class="form-step" id="step2">
                    <h3><i class="fas fa-briefcase"></i> Professional Information</h3>
                    
                    <div class="form-group">
                        <label for="department"><i class="fas fa-building"></i> Department *</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="CSE">Computer Science & Engineering</option>
                            <option value="EEE">Electrical & Electronic Engineering</option>
                            <option value="CE">Civil Engineering</option>
                            <option value="ME">Mechanical Engineering</option>
                            <option value="BBA">Business Administration</option>
                            <option value="English">English</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Physics">Physics</option>
                            <option value="Chemistry">Chemistry</option>
                        </select>
                        <div class="form-feedback" id="departmentFeedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="designation"><i class="fas fa-user-tie"></i> Designation</label>
                        <select id="designation" name="designation">
                            <option value="">Select Designation</option>
                            <option value="Professor">Professor</option>
                            <option value="Associate Professor">Associate Professor</option>
                            <option value="Assistant Professor">Assistant Professor</option>
                            <option value="Lecturer">Lecturer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="employee_id"><i class="fas fa-id-card"></i> Employee ID (Optional)</label>
                        <input type="text" id="employee_id" name="employee_id" placeholder="EMP-XXXXX">
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn btn-prev" onclick="prevStep(1)"><i class="fas fa-arrow-left"></i> Previous</button>
                        <button type="button" class="btn btn-next" onclick="nextStep(3)">Next <i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
                
                <!-- Step 3: Account Credentials -->
                <div class="form-step" id="step3">
                    <h3><i class="fas fa-key"></i> Account Credentials</h3>
                    
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user-circle"></i> Username *</label>
                        <input type="text" id="username" name="username" required placeholder="Choose a unique username">
                        <div class="form-feedback" id="usernameFeedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password *</label>
                        <input type="password" id="password" name="password" required placeholder="Minimum 8 characters">
                        <div class="password-strength">
                            <div class="password-strength-bar" id="passwordStrength"></div>
                        </div>
                        <div class="form-feedback" id="passwordFeedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter your password">
                        <div class="form-feedback" id="confirmPasswordFeedback"></div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn btn-prev" onclick="prevStep(2)"><i class="fas fa-arrow-left"></i> Previous</button>
                        <button type="button" class="btn btn-next" onclick="nextStep(4)">Next <i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
                
                <!-- Step 4: Review & Submit -->
                <div class="form-step" id="step4">
                    <h3><i class="fas fa-clipboard-check"></i> Review & Submit</h3>
                    
                    <!-- Preview Card -->
                    <div class="preview-card" id="previewCard">
                        <h4 class="preview-title"><i class="fas fa-eye"></i> Registration Preview</h4>
                        <div class="preview-grid">
                            <div class="preview-item">
                                <div class="preview-label">Full Name:</div>
                                <div class="preview-value" id="previewName">-</div>
                            </div>
                            <div class="preview-item">
                                <div class="preview-label">Email:</div>
                                <div class="preview-value" id="previewEmail">-</div>
                            </div>
                            <div class="preview-item">
                                <div class="preview-label">Phone:</div>
                                <div class="preview-value" id="previewPhone">-</div>
                            </div>
                            <div class="preview-item">
                                <div class="preview-label">Department:</div>
                                <div class="preview-value" id="previewDepartment">-</div>
                            </div>
                            <div class="preview-item">
                                <div class="preview-label">Designation:</div>
                                <div class="preview-value" id="previewDesignation">-</div>
                            </div>
                            <div class="preview-item">
                                <div class="preview-label">Username:</div>
                                <div class="preview-value" id="previewUsername">-</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="terms-group">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            I agree to the <a href="#" onclick="showTerms()">Terms & Conditions</a> 
                            and <a href="#" onclick="showPrivacy()">Privacy Policy</a>
                        </label>
                    </div>
                    <div class="form-feedback" id="termsFeedback"></div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn btn-prev" onclick="prevStep(3)"><i class="fas fa-arrow-left"></i> Previous</button>
                        <button type="submit" class="btn btn-submit" id="submitBtn">
                            <i class="fas fa-paper-plane"></i> Submit Registration
                        </button>
                    </div>
                </div>
            </form>
            
            <div class="form-footer">
                Already have an account? <a href="teacher_login.php"><i class="fas fa-sign-in-alt"></i> Login here</a>
            </div>
        </div>
    </div>
    
    <script src="../script.js"></script>
</body>
</html>