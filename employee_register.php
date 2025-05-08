<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfj_db";

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
    <title>Create an account</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        :root {
            --primary: #3a86ff;
            --primary-dark: #2667cc;
            --secondary: #8ecae6;
            --error: #ef476f;
            --success: #06d6a0;
            --dark: #2b2d42;
            --light: #f8f9fa;
            --gray: #e9ecef;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
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
            min-height: 200px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .image-section {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
            display: none; /* Hidden on mobile */
        }

        .logo {
            width: 70px;
            margin-bottom: 10px;
            position: relative;
            z-index: 5;
            filter: brightness(0) invert(1);
        }

        .welcome-text {
            color: white;
            text-align: center;
            z-index: 5;
            margin-bottom: 20px;
        }

        .welcome-text h2 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .welcome-text p {
            font-size: 16px;
            opacity: 0.9;
        }

        .form-section {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            align-items: center;
            color: white;
            font-weight: 500;
            font-size: 14px;
            text-decoration: none;
            transition: var(--transition);
        }

        .back-button i {
            margin-right: 6px;
        }

        .back-button:hover {
            color: var(--primary-dark);
            transform: translateX(-3px);
        }

        .form-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .form-header h2 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .form-header p {
            color: #6c757d;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
            background-color: var(--gray);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(58, 134, 255, 0.15);
            background-color: white;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            cursor: pointer;
            font-size: 16px;
        }

        .password-strength {
            margin-top: 15px;
        }

        .strength-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            font-size: 12px;
            color: #6c757d;
        }

        .strength-item i {
            margin-right: 8px;
            font-size: 14px;
        }

        .strength-item.valid i {
            color: var(--success);
        }

        .strength-item.invalid i {
            color: var(--error);
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: var(--primary);
            color: white;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .form-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #6c757d;
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
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

        .form-group, .btn, .form-header, .form-footer, .back-button {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .back-button { animation-delay: 0.05s; }
        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .btn { animation-delay: 0.5s; }
        .form-footer { animation-delay: 0.6s; }

        /* Responsive */
        @media (min-width: 768px) {
            .image-section {
                display: flex;
            }
        }

        @media (max-width: 767px) {
            .page-container {
                flex-direction: column;
                max-width: 400px;
            }
            
            .form-section {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="page-container">


    <div class="image-section">

        <a href="employee_login.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
        <img src="logo_clear.png" alt="MFJ Logo" class="logo">
        <div class="welcome-text">
            <h2>Welcome to MFJ</h2>
            <p>Create an account to get started with our services.</p>
        </div>
    </div>
    
    <div class="form-section">
       
        
        <div class="form-header">
            <h2>Create an Account</h2>
            <p>Fill in your details to register</p>
        </div>
        
        <?php if (isset($registrationMessage)) echo $registrationMessage; ?>
        
        <form method="POST" action="employee_register.php" onsubmit="return validatePassword()">
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
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" class="form-control" required onkeyup="checkPasswordStrength()">
                    <span class="password-toggle" onclick="togglePasswordVisibility()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                </div>
                
                <div class="password-strength" id="passwordStrength">
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
            
            <button type="submit" class="btn">Register</button>
        </form>
        
        <div class="form-footer">
            <p>Already have an account? <a href="employee_login.php">Login here</a></p>
        </div>
    </div>
</div>

<script>
    function checkPasswordStrength() {
        var password = document.getElementById("password").value;
        var length = document.getElementById("length");
        var uppercase = document.getElementById("uppercase");
        var lowercase = document.getElementById("lowercase");
        var number = document.getElementById("number");
        var special = document.getElementById("special");

        // Check length
        if (password.length >= 8) {
            length.classList.add("valid");
            length.classList.remove("invalid");
            length.innerHTML = '<i class="fas fa-check-circle"></i> At least 8 characters';
        } else {
            length.classList.add("invalid");
            length.classList.remove("valid");
            length.innerHTML = '<i class="fas fa-times-circle"></i> At least 8 characters';
        }

        // Check uppercase
        if (/[A-Z]/.test(password)) {
            uppercase.classList.add("valid");
            uppercase.classList.remove("invalid");
            uppercase.innerHTML = '<i class="fas fa-check-circle"></i> One uppercase letter';
        } else {
            uppercase.classList.add("invalid");
            uppercase.classList.remove("valid");
            uppercase.innerHTML = '<i class="fas fa-times-circle"></i> One uppercase letter';
        }

        // Check lowercase
        if (/[a-z]/.test(password)) {
            lowercase.classList.add("valid");
            lowercase.classList.remove("invalid");
            lowercase.innerHTML = '<i class="fas fa-check-circle"></i> One lowercase letter';
        } else {
            lowercase.classList.add("invalid");
            lowercase.classList.remove("valid");
            lowercase.innerHTML = '<i class="fas fa-times-circle"></i> One lowercase letter';
        }

        // Check number
        if (/\d/.test(password)) {
            number.classList.add("valid");
            number.classList.remove("invalid");
            number.innerHTML = '<i class="fas fa-check-circle"></i> One number';
        } else {
            number.classList.add("invalid");
            number.classList.remove("valid");
            number.innerHTML = '<i class="fas fa-times-circle"></i> One number';
        }

        // Check special character
        if (/[@$!%*?&]/.test(password)) {
            special.classList.add("valid");
            special.classList.remove("invalid");
            special.innerHTML = '<i class="fas fa-check-circle"></i> One special character';
        } else {
            special.classList.add("invalid");
            special.classList.remove("valid");
            special.innerHTML = '<i class="fas fa-times-circle"></i> One special character';
        }
    }

    function validatePassword() {
        var password = document.getElementById("password").value;
        var lengthValid = password.length >= 8;
        var uppercaseValid = /[A-Z]/.test(password);
        var lowercaseValid = /[a-z]/.test(password);
        var numberValid = /\d/.test(password);
        var specialValid = /[@$!%*?&]/.test(password);

        if (lengthValid && uppercaseValid && lowercaseValid && numberValid && specialValid) {
            return true;
        } else {
            alert("Please ensure your password meets all criteria.");
            return false;
        }
    }

    function togglePasswordVisibility() {
        var passwordInput = document.getElementById("password");
        var toggleIcon = document.getElementById("toggleIcon");
        
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
</script>
</body>
</html>