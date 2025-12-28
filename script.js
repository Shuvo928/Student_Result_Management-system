// Form Step Management
let currentStep = 1;
const totalSteps = 4;

// Form Data Storage
let formData = {
    name: '',
    email: '',
    phone: '',
    department: '',
    designation: '',
    employee_id: '',
    username: '',
    password: ''
};

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    // Set up event listeners for real-time validation
    setupValidationListeners();
    
    // Initialize the first step
    showStep(1);
});

// Show specific step
function showStep(stepNumber) {
    // Hide all steps
    document.querySelectorAll('.form-step').forEach(step => {
        step.classList.remove('active');
    });
    
    // Remove active class from all steps in indicator
    document.querySelectorAll('.step').forEach(step => {
        step.classList.remove('active');
    });
    
    // Show current step
    document.getElementById(`step${stepNumber}`).classList.add('active');
    document.querySelector(`.step[data-step="${stepNumber}"]`).classList.add('active');
    
    // Mark previous steps as completed
    for (let i = 1; i < stepNumber; i++) {
        document.querySelector(`.step[data-step="${i}"]`).classList.add('completed');
    }
    
    // Update current step
    currentStep = stepNumber;
    
    // If on last step, update preview
    if (currentStep === 4) {
        updatePreview();
        document.getElementById('previewCard').classList.add('show');
    } else {
        document.getElementById('previewCard').classList.remove('show');
    }
}

// Next Step Function
function nextStep(step) {
    if (validateCurrentStep(currentStep)) {
        saveStepData(currentStep);
        showStep(step);
    }
}

// Previous Step Function
function prevStep(step) {
    showStep(step);
}

// Save data from current step
function saveStepData(step) {
    switch(step) {
        case 1:
            formData.name = document.getElementById('name').value.trim();
            formData.email = document.getElementById('email').value.trim();
            formData.phone = document.getElementById('phone').value.trim();
            break;
        case 2:
            formData.department = document.getElementById('department').value;
            formData.designation = document.getElementById('designation').value;
            formData.employee_id = document.getElementById('employee_id').value.trim();
            break;
        case 3:
            formData.username = document.getElementById('username').value.trim();
            formData.password = document.getElementById('password').value;
            break;
    }
}

// Update preview with form data
function updatePreview() {
    const departmentNames = {
        'CSE': 'Computer Science & Engineering',
        'EEE': 'Electrical & Electronic Engineering',
        'CE': 'Civil Engineering',
        'ME': 'Mechanical Engineering',
        'BBA': 'Business Administration',
        'English': 'English',
        'Mathematics': 'Mathematics',
        'Physics': 'Physics',
        'Chemistry': 'Chemistry',
        'Economics': 'Economics',
        'Law': 'Law'
    };
    
    document.getElementById('previewName').textContent = formData.name || '-';
    document.getElementById('previewEmail').textContent = formData.email || '-';
    document.getElementById('previewPhone').textContent = formData.phone || '-';
    document.getElementById('previewDepartment').textContent = departmentNames[formData.department] || '-';
    document.getElementById('previewDesignation').textContent = formData.designation || 'Not specified';
    document.getElementById('previewUsername').textContent = formData.username || '-';
}

// Validation Functions
function validateCurrentStep(step) {
    let isValid = true;
    
    switch(step) {
        case 1:
            isValid = validateName() && validateEmail() && validatePhone();
            break;
        case 2:
            isValid = validateDepartment();
            break;
        case 3:
            isValid = validateUsername() && validatePassword() && validateConfirmPassword();
            break;
    }
    
    return isValid;
}

function validateName() {
    const name = document.getElementById('name').value.trim();
    const feedback = document.getElementById('nameFeedback');
    
    if (!name) {
        showFeedback(feedback, 'Name is required', false);
        return false;
    }
    
    if (name.length < 3) {
        showFeedback(feedback, 'Name must be at least 3 characters', false);
        return false;
    }
    
    showFeedback(feedback, '✓ Valid name', true);
    return true;
}

function validateEmail() {
    const email = document.getElementById('email').value.trim();
    const feedback = document.getElementById('emailFeedback');
    
    if (!email) {
        showFeedback(feedback, 'Email is required', false);
        return false;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showFeedback(feedback, 'Please enter a valid email address', false);
        return false;
    }
    
    showFeedback(feedback, '✓ Valid email', true);
    return true;
}

function validatePhone() {
    const phone = document.getElementById('phone').value.trim();
    const feedback = document.getElementById('phoneFeedback');
    
    if (!phone) {
        showFeedback(feedback, 'Phone number is required', false);
        return false;
    }
    
    const phoneRegex = /^01[3-9]\d{8}$/;
    if (!phoneRegex.test(phone)) {
        showFeedback(feedback, 'Please enter a valid Bangladeshi mobile number (01XXXXXXXXX)', false);
        return false;
    }
    
    showFeedback(feedback, '✓ Valid phone number', true);
    return true;
}

function validateDepartment() {
    const department = document.getElementById('department').value;
    const feedback = document.getElementById('departmentFeedback');
    
    if (!department) {
        showFeedback(feedback, 'Please select a department', false);
        return false;
    }
    
    showFeedback(feedback, '✓ Department selected', true);
    return true;
}

function validateUsername() {
    const username = document.getElementById('username').value.trim();
    const feedback = document.getElementById('usernameFeedback');
    
    if (!username) {
        showFeedback(feedback, 'Username is required', false);
        return false;
    }
    
    if (username.length < 4) {
        showFeedback(feedback, 'Username must be at least 4 characters', false);
        return false;
    }
    
    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        showFeedback(feedback, 'Username can only contain letters, numbers, and underscores', false);
        return false;
    }
    
    showFeedback(feedback, '✓ Username available', true);
    return true;
}

function validatePassword() {
    const password = document.getElementById('password').value;
    const feedback = document.getElementById('passwordFeedback');
    const strengthBar = document.getElementById('passwordStrength');
    
    if (!password) {
        showFeedback(feedback, 'Password is required', false);
        strengthBar.className = 'password-strength-bar';
        return false;
    }
    
    let strength = 0;
    
    // Length check
    if (password.length >= 8) strength++;
    
    // Lowercase check
    if (/[a-z]/.test(password)) strength++;
    
    // Uppercase check
    if (/[A-Z]/.test(password)) strength++;
    
    // Number check
    if (/[0-9]/.test(password)) strength++;
    
    // Special character check
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    // Update strength bar
    strengthBar.className = 'password-strength-bar';
    if (strength <= 2) {
        strengthBar.classList.add('strength-weak');
        showFeedback(feedback, 'Weak password', false);
        return false;
    } else if (strength <= 3) {
        strengthBar.classList.add('strength-fair');
        showFeedback(feedback, 'Fair password', true);
    } else if (strength <= 4) {
        strengthBar.classList.add('strength-good');
        showFeedback(feedback, 'Good password', true);
    } else {
        strengthBar.classList.add('strength-strong');
        showFeedback(feedback, 'Strong password!', true);
    }
    
    return true;
}

function validateConfirmPassword() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const feedback = document.getElementById('confirmPasswordFeedback');
    
    if (!confirmPassword) {
        showFeedback(feedback, 'Please confirm your password', false);
        return false;
    }
    
    if (password !== confirmPassword) {
        showFeedback(feedback, 'Passwords do not match', false);
        return false;
    }
    
    showFeedback(feedback, '✓ Passwords match', true);
    return true;
}

function showFeedback(element, message, isValid) {
    if (!element) return;
    
    element.textContent = message;
    element.className = 'form-feedback show';
    element.classList.add(isValid ? 'valid' : 'invalid');
}

// Setup validation listeners
function setupValidationListeners() {
    // Name input formatting
    const nameInput = document.getElementById('name');
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^A-Za-z\s]/g, '');
            validateName();
        });
    }
    
    // Phone input formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^\d]/g, '').substring(0, 11);
            validatePhone();
        });
    }
    
    // Username input formatting
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z0-9_]/g, '');
            validateUsername();
        });
    }
    
    // Password validation on input
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', validatePassword);
    }
    
    // Confirm password validation
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', validateConfirmPassword);
    }
    
    // Department validation
    const departmentSelect = document.getElementById('department');
    if (departmentSelect) {
        departmentSelect.addEventListener('change', validateDepartment);
    }
    
    // Email validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('input', validateEmail);
    }
}

// Final form validation
function validateFinalForm() {
    // Check terms agreement
    const termsCheckbox = document.getElementById('terms');
    const termsFeedback = document.getElementById('termsFeedback');
    
    if (!termsCheckbox.checked) {
        termsFeedback.textContent = 'You must agree to the terms and conditions';
        termsFeedback.className = 'form-feedback show invalid';
        return false;
    }
    
    // Validate all steps
    for (let i = 1; i <= totalSteps; i++) {
        if (!validateCurrentStep(i)) {
            alert('Please complete all required fields correctly before submitting.');
            showStep(i);
            return false;
        }
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.innerHTML = 'Registering... <span class="loading"></span>';
        submitBtn.disabled = true;
    }
    
    return true;
}

// Show terms modal
function showTerms() {
    alert('Terms and Conditions:\n\n1. You agree to use this system responsibly.\n2. All data entered must be accurate.\n3. Keep your credentials secure.\n4. The institution reserves the right to suspend accounts.\n\nClick OK to continue.');
}

// Show privacy policy
function showPrivacy() {
    alert('Privacy Policy:\n\n1. We collect necessary information for system functionality.\n2. Your data is stored securely.\n3. We do not share your information with third parties.\n4. You can request data deletion.\n\nClick OK to continue.');
}