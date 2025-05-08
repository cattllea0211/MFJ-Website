<?php

session_start();

// Database connection
$servername = "localhost"; 
$username = "root"; 
$password = "";
$dbname = "mfj_db"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate password strength
function validatePassword($password) {
    // Minimum 8 characters
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long";
    }
    
    // Must contain at least one number
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number";
    }
    
    // Must contain at least one letter
    if (!preg_match('/[a-zA-Z]/', $password)) {
        return "Password must contain at least one letter";
    }
    
    // Must contain at least one special character
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        return "Password must contain at least one special character";
    }
    
    return true;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form data
    $username = sanitize_input($_POST["username"]);
    $full_name = sanitize_input($_POST["name"]);
    $email = sanitize_input($_POST["email"]);
    $phone = sanitize_input($_POST["phone"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Initialize error array
    $errors = array();
    
    // Validate input
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    } else {
        // Validate password strength
        $passwordValidation = validatePassword($password);
        if ($passwordValidation !== true) {
            $errors[] = $passwordValidation;
        }
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Username or email already exists";
    }
    $stmt->close();
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
       // Prepare and execute INSERT statement
    $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, phone, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'User', 'Active', CURRENT_TIMESTAMP)");
    if ($stmt === false) {
        die("Error preparing insert statement: " . $conn->error);
    }

    $stmt->bind_param("sssss", $username, $full_name, $email, $phone, $hashed_password);

        
        if ($stmt->execute()) {
            // Registration successful
            session_start();
            $_SESSION['success_message'] = "Registration successful! Please login.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Error occurred during registration. Please try again.";
        }
        $stmt->close();
    }
    
    // If there were errors, store them in session and redirect back to signup page
    if (!empty($errors)) {
        session_start();
        $_SESSION['error_messages'] = $errors;
        header("Location: signup.php");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFJ Airconditioning - Sign Up</title>
    <style>
        /* Existing styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .signup-container {
            display: flex;
            min-height: 100vh;
        }

        .left-panel {
            flex: 1;
            background-color: steelblue;
            color: white;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            animation: slideInLeft 0.8s ease-out;
        }

        .right-panel {
            flex: 1;
            background-color: #f4f4f9;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center; /* Center the content vertically */
            align-items: center; /* Center the content horizontally */
            animation: slideInRight 0.8s ease-out;
        }


        /* Logo and company name animations */
        .logo-container {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease-out;
        }

        .logo img {
            height: 100px;
            margin-right: 1rem;
        }

        .company-name h2 {
            font-size: 28px;
            color: white;
            margin: 0;
        }

        .company-name .tagline {
            font-size: 23px;
            color: white;
            margin: 0;
            font-weight: bold;
        }

        .welcome-text {
            font-size: 4.5rem;
            margin-bottom: 2rem;
            line-height: 1.2;
            animation: fadeInUp 1s ease-out 0.3s;
            opacity: 0;
            animation-fill-mode: forwards;
        }

        .form-container {
            max-width: 500px;
            width: 100%;

        }

        .form-header {
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease-out;
        }

        .form-header h2 {
            color: steelblue;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
            opacity: 0;
            animation: fadeInUp 0.5s ease-out forwards;
        }

        /* Staggered animation delays for input groups */
        .input-group:nth-child(1) { animation-delay: 0.2s; }
        .input-group:nth-child(2) { animation-delay: 0.4s; }
        .input-group:nth-child(3) { animation-delay: 0.6s; }
        .input-group:nth-child(4) { animation-delay: 0.8s; }
        .input-group:nth-child(5) { animation-delay: 1s; }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: bold;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            border-color: steelblue;
            box-shadow: 0 0 0 2px rgba(70, 130, 180, 0.2);
            transform: translateY(-2px);
        }

        .signup-button {
            width: 100%;
            padding: 1rem;
            background-color: steelblue;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeInUp 0.5s ease-out 1.2s forwards;
        }

        .signup-button:hover {
            background-color: #4682b4;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(70, 130, 180, 0.2);
        }

        .signup-button:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            color: #666;
            opacity: 0;
            animation: fadeIn 0.5s ease-out 1.4s forwards;
        }

        .login-link a {
            color: steelblue;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            text-decoration: underline;
            color: #4682b4;
        }

        /* Animation Keyframes */
        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Hover effects for inputs */
        .input-group input:hover {
            border-color: #4682b4;
        }
        .username-status {
            font-size: 0.8em;
            margin-top: 5px;
            padding: 5px;
        }

        #password-requirements {
            font-size: 0.8em;
            margin-top: 5px;
            padding: 5px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
         .back-button {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: white;
            text-decoration: none;
            padding: 0.75rem;
            border-radius: 50%;
            transition: all 0.3s ease;
            z-index: 10;
            animation: fadeIn 0.5s ease-out;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
        }

        .back-button:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        .back-button svg {
            width: 24px;
            height: 24px;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }
    </style>
</head>
<body>

    <a href="homepage.php" class="back-button">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
    </a>
    <div class="signup-container">
        <div class="left-panel">
            <div class="logo-container">
                <div class="logo">
                    <img src="logotriangle.png" alt="MFJ Logo">
                </div>
                <div class="company-name">
                    <h2>MFJ</h2>
                    <p class="tagline">Airconditioning Supply and Services</p>
                </div>
            </div>
            <h1 class="welcome-text">Let's Make Your Space More Comfortable Together!</h1>
        </div>
        <div class="right-panel">
            <div class="form-container">
                <div class="form-header">
                    <h2>Create An Account</h2>
                    <p>Already have an account? <a href="login.php">Sign in here!</a></p>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="name" required>  <!-- Changed name="full_name" to name="name" -->
                </div>
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="input-group">
                    <label for="password">Create Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm_password" required>
                </div>
                <div id="password-requirements" style="font-size: 0.8em; margin-top: 5px;"></div>
                <button type="submit" class="signup-button">Create Account</button>
            </form>
            </div>
        </div>
    </div>

    <script>
       document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm-password');
            const requirementsDiv = document.getElementById('password-requirements');
            const usernameStatus = document.createElement('div');
            usernameStatus.className = 'username-status';
            usernameInput.parentNode.appendChild(usernameStatus);
            
            let timeoutId;
            
            // Username validation (existing code)
            usernameInput.addEventListener('input', function() {
                clearTimeout(timeoutId);
                const username = this.value.trim();
                
                if (username === '') {
                    usernameStatus.textContent = '';
                    return;
                }
                
                timeoutId = setTimeout(() => {
                    const formData = new FormData();
                    formData.append('username', username);
                    
                    fetch('check_username.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        usernameStatus.textContent = data.message;
                        usernameStatus.style.color = data.available ? 'green' : 'red';
                        
                        const submitButton = document.querySelector('.signup-button');
                        if (!data.available) {
                            submitButton.disabled = true;
                            submitButton.style.opacity = '0.5';
                            submitButton.style.cursor = 'not-allowed';
                        } else {
                            submitButton.disabled = false;
                            submitButton.style.opacity = '1';
                            submitButton.style.cursor = 'pointer';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        usernameStatus.textContent = 'Error checking username availability';
                        usernameStatus.style.color = 'red';
                    });
                }, 500);
            });

            // Function to validate password requirements
            function validatePassword(password) {
                const requirements = [];
                
                requirements.push({
                    met: password.length >= 8,
                    text: 'At least 8 characters'
                });
                
                requirements.push({
                    met: /[0-9]/.test(password),
                    text: 'At least one number'
                });
                
                requirements.push({
                    met: /[a-zA-Z]/.test(password),
                    text: 'At least one letter'
                });
                
                requirements.push({
                    met: /[^a-zA-Z0-9]/.test(password),
                    text: 'At least one special character'
                });
                
                return requirements;
            }

            // Function to update password requirements display
            function updatePasswordRequirements() {
                const password = passwordInput.value;
                const requirements = validatePassword(password);
                const confirmPassword = confirmPasswordInput.value;
                
                let requirementsHTML = requirements.map(req => 
                    `${req.met ? '✅' : '❌'} ${req.text}`
                ).join('<br>');
                
                // Add password match requirement if confirm password has any value
                if (confirmPassword) {
                    const passwordsMatch = password === confirmPassword;
                    requirementsHTML += `<br>${passwordsMatch ? '✅' : '❌'} Passwords match`;
                }
                
                requirementsDiv.innerHTML = requirementsHTML;
                
                // Update submit button state
                const submitButton = document.querySelector('.signup-button');
                const allRequirementsMet = requirements.every(req => req.met) && 
                                         (!confirmPassword || password === confirmPassword);
                
                submitButton.disabled = !allRequirementsMet;
                submitButton.style.opacity = allRequirementsMet ? '1' : '0.5';
                submitButton.style.cursor = allRequirementsMet ? 'pointer' : 'not-allowed';
            }

            // Event listeners for password inputs
            passwordInput.addEventListener('input', updatePasswordRequirements);
            confirmPasswordInput.addEventListener('input', updatePasswordRequirements);
        });
        </script>
</body>
</html>