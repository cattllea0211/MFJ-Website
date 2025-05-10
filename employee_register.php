<?php

$servername = "localhost";
$username = "mfj_user";
$password = "StrongPassword123!";
$dbname = "mfjdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        echo '<div class="alert error">Password must meet all security requirements.</div>';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO employees (name, email, username, password, role, status) VALUES (?, ?, ?, ?, 'employee', 'pending')");
        
        if ($stmt === false) {
            echo '<div class="alert error">Error in SQL preparation: ' . $conn->error . '</div>';
        } else {
            $stmt->bind_param("ssss", $name, $email, $username, $hashedPassword);

            if ($stmt->execute()) {
                $registrationMessage = '<div class="alert success">Registration successful! Your account is pending approval.</div>';
            } else {
                $registrationMessage = '<div class="alert error">Registration Failed: ' . $stmt->error . '</div>';
            }
            
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFJ - Create an Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        :root {
            --primary: #3a86ff;
            --primary-hover: #2667cc;
            --primary-light: #ebf2ff;
            --secondary: #8ecae6;
            --error: #ef476f;
            --success: #06d6a0;
            --dark: #2b2d42;
            --light: #f8f9fa;
            --gray: #e9ecef;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --card-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
        }

        .page-container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            min-height: 600px;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }

        .image-section {
            flex: 1;
            background: linear-gradient(145deg, var(--primary) 0%, #4361ee 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
            display: none; /* Hidden on mobile */
        }

        .image-section::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 25%, transparent 50%);
            transform: rotate(35deg);
        }

        .logo {
            width: 80px;
            margin-bottom: 20px;
            position: relative;
            z-index: 5;
            filter: brightness(0) invert(1);
        }

        .welcome-text {
            color: white;
            text-align: center;
            z-index: 5;
            margin-bottom: 30px;
        }

        .welcome-text h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }

        .welcome-text p {
            font-size: 16px;
            opacity: 0.9;
            max-width: 350px;
            line-height: 1.7;
        }

        .features {
            color: white;
            z-index: 5;
            width: 100%;
            max-width: 350px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .feature-icon {
            background: rgba(255, 255, 255, 0.15);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .feature-icon i {
            color: white;
            font-size: 16px;
        }

        .feature-text h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .feature-text p {
            font-size: 14px;
            opacity: 0.8;
        }

        .form-section {
            flex: 1;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .back-button {
            position: absolute;
            top: 30px;
            left: 40px;
            display: flex;
            align-items: center;
            color: var(--dark);
            font-weight: 500;
            font-size: 14px;
            text-decoration: none;
            transition: var(--transition);
        }

        .back-button i {
            margin-right: 6px;
            transition: var(--transition);
        }

        .back-button:hover {
            color: var(--primary);
        }

        .back-button:hover i {
            transform: translateX(-3px);
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .form-header p {
            color: #6c757d;
            font-size: 16px;
        }

        .form-container {
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray);
            border-radius: 12px;
            font-size: 15px;
            transition: var(--transition);
            background-color: var(--light);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(58, 134, 255, 0.15);
            background-color: white;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            cursor: pointer;
            font-size: 16px;
            z-index: 10;
            background: transparent;
            border: none;
            padding: 5px;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        .password-strength {
            margin-top: 20px;
        }

        .strength-meter {
            height: 5px;
            border-radius: 3px;
            background-color: var(--gray);
            margin-bottom: 15px;
            overflow: hidden;
        }

        .strength-meter-fill {
            height: 100%;
            width: 0;
            transition: width 0.5s ease;
            border-radius: 3px;
        }

        .strength-text {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }

        .strength-requirements {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .strength-item {
            display: flex;
            align-items: center;
            font-size: 13px;
            color: #6c757d;
        }

        .strength-item i {
            margin-right: 8px;
            font-size: 14px;
            width: 20px;
            text-align: center;
        }

        .strength-item.valid {
            color: var(--success);
        }

        .strength-item.invalid {
            color: #adb5bd;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            background-color: var(--primary);
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 20px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background-color: var(--primary-hover);
            transition: width 0.3s ease;
            z-index: -1;
        }

        .btn:hover::before {
            width: 100%;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .form-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 15px;
            color: #6c757d;
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .form-footer a:hover {
            text-decoration: underline;
            color: var(--primary-hover);
        }

        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .alert i {
            margin-right: 12px;
            font-size: 18px;
        }

        .error {
            background-color: rgba(239, 71, 111, 0.1);
            color: var(--error);
            border: 1px solid rgba(239, 71, 111, 0.3);
        }

        .success {
            background-color: rgba(6, 214, 160, 0.1);
            color: var(--success);
            border: 1px solid rgba(6, 214, 160, 0.3);
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group, .form-header, .form-footer, .back-button {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .back-button { animation-delay: 0.05s; }
        .form-header { animation-delay: 0.1s; }
        .form-group:nth-child(1) { animation-delay: 0.2s; }
        .form-group:nth-child(2) { animation-delay: 0.3s; }
        .form-group:nth-child(3) { animation-delay: 0.4s; }
        .form-group:nth-child(4) { animation-delay: 0.5s; }
        .btn { animation-delay: 0.6s; }
        .form-footer { animation-delay: 0.7s; }

        /* Responsive */
        @media (min-width: 992px) {
            .image-section {
                display: flex;
            }
        }

        @media (max-width: 991px) {
            .page-container {
                flex-direction: column;
                max-width: 550px;
            }
            
            .form-section {
                padding: 40px 30px;
            }
            
            .back-button {
                top: 20px;
                left: 20px;
            }
        }

        @media (max-width: 480px) {
            .form-section {
                padding: 30px 20px;
            }
            
            .form-header h2 {
                font-size: 24px;
            }
            
            .strength-requirements {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="page-container">
    <div class="image-section">
        <img src="logo_clear.png" alt="MFJ Logo" class="logo">
        <div class="welcome-text">
            <h2>Welcome to MFJ</h2>
            <p>Join our platform to access exclusive tools, resources, and services designed for your success.</p>
        </div>
        
        <div class="features">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="feature-text">
                    <h4>Secure Platform</h4>
                    <p>Your data is encrypted and protected</p>
                </div>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <div class="feature-text">
                    <h4>Fast Access</h4>
                    <p>Quick access to all our services</p>
                </div>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="feature-text">
                    <h4>24/7 Support</h4>
                    <p>Our team is always ready to help</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="form-section">
        <a href="employee_login.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>
        
        <div class="form-container">
            <div class="form-header">
                <h2>Create Your Account</h2>
                <p>Enter your information to get started</p>
            </div>
            
            <div id="registrationMessage"></div>
            
            <form id="registrationForm" method="POST" action="employee_register.php" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required minlength="5" pattern="[a-zA-Z0-9_-]+" title="Usernames can only contain letters, numbers, underscores and hyphens">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control" required onkeyup="checkPasswordStrength()">
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    
                    <div class="password-strength">
                        <div class="strength-text">
                            <span id="strengthText">Password Strength</span>
                            <span id="strengthLevel">Weak</span>
                        </div>
                        <div class="strength-meter">
                            <div class="strength-meter-fill" id="strengthMeter" style="width: 0%; background-color: #ef476f;"></div>
                        </div>
                        
                        <div class="strength-requirements">
                            <div class="strength-item" id="length">
                                <i class="fas fa-times-circle"></i> At least 8 characters
                            </div>
                            <div class="strength-item" id="uppercase">
                                <i class="fas fa-times-circle"></i> One uppercase letter
                            </div>
                            <div class="strength-item" id="lowercase">
                                <i class="fas fa-times-circle"></i> One lowercase letter
                            </div>
                            <div class="strength-item" id="number">
                                <i class="fas fa-times-circle"></i> One number
                            </div>
                            <div class="strength-item" id="special">
                                <i class="fas fa-times-circle"></i> One special character
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn" id="registerBtn">Create Account</button>
            </form>
            
            <div class="form-footer">
                <p>Already have an account? <a href="employee_login.php">Sign in</a></p>
            </div>
        </div>
    </div>
</div>

<script>
    // Process PHP response messages
    <?php if (isset($registrationMessage)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const messageDiv = document.getElementById('registrationMessage');
            messageDiv.className = 'alert <?php echo strpos($registrationMessage, "success") !== false ? "success" : "error"; ?>';
            
            const iconClass = messageDiv.classList.contains('success') ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            const messageContent = `<?php echo trim(strip_tags($registrationMessage)); ?>`;
            messageDiv.innerHTML = `<i class="${iconClass}"></i> ${messageContent}`;
        });
    <?php endif; ?>
    
    // Check password strength
    function checkPasswordStrength() {
        const password = document.getElementById("password").value;
        const lengthElem = document.getElementById("length");
        const uppercaseElem = document.getElementById("uppercase");
        const lowercaseElem = document.getElementById("lowercase");
        const numberElem = document.getElementById("number");
        const specialElem = document.getElementById("special");
        const meterElem = document.getElementById("strengthMeter");
        const strengthLevelElem = document.getElementById("strengthLevel");
        
        // Define requirements
        const lengthValid = password.length >= 8;
        const uppercaseValid = /[A-Z]/.test(password);
        const lowercaseValid = /[a-z]/.test(password);
        const numberValid = /\d/.test(password);
        const specialValid = /[@$!%*?&]/.test(password);
        
        // Update each requirement element
        updateRequirement(lengthElem, lengthValid);
        updateRequirement(uppercaseElem, uppercaseValid);
        updateRequirement(lowercaseElem, lowercaseValid);
        updateRequirement(numberElem, numberValid);
        updateRequirement(specialElem, specialValid);
        
        // Calculate strength
        let strengthScore = 0;
        if (password.length > 0) strengthScore += 1;
        if (lengthValid) strengthScore += 1;
        if (uppercaseValid) strengthScore += 1;
        if (lowercaseValid) strengthScore += 1;
        if (numberValid) strengthScore += 1;
        if (specialValid) strengthScore += 1;
        if (password.length >= 12) strengthScore += 1;
        if (password.length >= 16) strengthScore += 1;
        
        // Update strength meter
        const strengthPercentage = (strengthScore / 8) * 100;
        meterElem.style.width = `${strengthPercentage}%`;
        
        // Set color and text based on strength
        let strengthColor, strengthText;
        if (strengthScore <= 2) {
            strengthColor = '#ef476f'; // Red
            strengthText = 'Weak';
        } else if (strengthScore <= 4) {
            strengthColor = '#ffd166'; // Yellow
            strengthText = 'Moderate';
        } else if (strengthScore <= 6) {
            strengthColor = '#06d6a0'; // Green
            strengthText = 'Strong';
        } else {
            strengthColor = '#118ab2'; // Blue
            strengthText = 'Very Strong';
        }
        
        meterElem.style.backgroundColor = strengthColor;
        strengthLevelElem.textContent = strengthText;
        strengthLevelElem.style.color = strengthColor;
    }
    
    function updateRequirement(element, isValid) {
        if (isValid) {
            element.classList.add("valid");
            element.classList.remove("invalid");
            element.innerHTML = `<i class="fas fa-check-circle"></i> ${element.textContent.substring(element.textContent.indexOf(' ') + 1)}`;
        } else {
            element.classList.add("invalid");
            element.classList.remove("valid");
            element.innerHTML = `<i class="fas fa-times-circle"></i> ${element.textContent.substring(element.textContent.indexOf(' ') + 1)}`;
        }
    }
    
    function validateForm() {
        const password = document.getElementById("password").value;
        const lengthValid = password.length >= 8;
        const uppercaseValid = /[A-Z]/.test(password);
        const lowercaseValid = /[a-z]/.test(password);
        const numberValid = /\d/.test(password);
        const specialValid = /[@$!%*?&]/.test(password);
        
        if (lengthValid && uppercaseValid && lowercaseValid && numberValid && specialValid) {
            // Disable button to prevent double submission
            document.getElementById('registerBtn').disabled = true;
            document.getElementById('registerBtn').textContent = 'Creating Account...';
            return true;
        } else {
            const messageDiv = document.getElementById('registrationMessage');
            messageDiv.className = 'alert error';
            messageDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please ensure your password meets all security requirements.';
            messageDiv.scrollIntoView({ behavior: 'smooth' });
            return false;
        }
    }
    
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById("password");
        const toggleIcon = document.getElementById("toggleIcon");
        
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggleIcon.classList.remove("fa-eye");
            toggleIcon.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            toggleIcon.classList.remove("fa-eye-slash");
            toggleIcon.classList.add("fa-eye");
        }
    }
    
    // Add basic form validation
    document.getElementById('registrationForm').addEventListener('submit', function(event) {
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        
        // Validate email format
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailInput.value)) {
            event.preventDefault();
            const messageDiv = document.getElementById('registrationMessage');
            messageDiv.className = 'alert error';
            messageDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please enter a valid email address.';
            emailInput.focus();
            return false;
        }
        
        // Validate username format
        const usernamePattern = /^[a-zA-Z0-9_-]+$/;
        if (!usernamePattern.test(usernameInput.value)) {
            event.preventDefault();
            const messageDiv = document.getElementById('registrationMessage');
            messageDiv.className = 'alert error';
            messageDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Username can only contain letters, numbers, underscores and hyphens.';
            usernameInput.focus();
            return false;
        }
    });
</script>
</body>
</html>
