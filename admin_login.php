<?php
session_start();

$servername = "localhost";
$username = "mfj_user"; 
$password = "StrongPassword123!"; 
$dbname = "mfjdb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];
    
    $stmt->bind_param("ss", $input_username, $input_password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Login successful
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_dashboard.php');
        exit;
    } else {
        // Login failed
        $login_error = "Invalid username or password";
    }
    
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Admin Portal</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --secondary-color: #f8f9fa;
            --text-color: #212529;
            --light-text: #6c757d;
            --border-color: #dee2e6;
            --success-color: #0cce6b;
            --error-color: #e63946;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.08);
            --animation-timing: cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
            100% {
                transform: translateY(0px);
            }
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.8;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.5;
            }
            100% {
                transform: scale(1);
                opacity: 0.8;
            }
        }
        
        @keyframes shake {
            0%, 100% {transform: translateX(0);}
            20%, 60% {transform: translateX(-5px);}
            40%, 80% {transform: translateX(5px);}
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #4158D0, #C850C0, #4361EE);
            background-size: 300% 300%;
            animation: gradientMove 15s ease infinite;
        }
        
        .login-container {
            display: flex;
            max-width: 1000px;
            width: 90%;
            height: 600px;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            opacity: 0;
            animation: fadeInUp 0.8s var(--animation-timing) forwards;
            animation-delay: 0.2s;
        }
        
        .illustration {
            flex: 1;
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .illustration img {
            width: 80%;
            height: auto;
            object-fit: contain;
            position: relative;
            z-index: 2;
            animation: float 6s ease-in-out infinite;
        }
        
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: pulse 8s infinite;
        }
        
        .bubble-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
            animation-delay: 0s;
        }
        
        .bubble-2 {
            width: 200px;
            height: 200px;
            bottom: -50px;
            left: -50px;
            animation-delay: 2s;
        }
        
        .bubble-3 {
            width: 150px;
            height: 150px;
            top: 50%;
            left: 30%;
            animation-delay: 4s;
        }
        
        .login-form {
            flex: 1;
            padding: 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-header {
            margin-bottom: 2.5rem;
            opacity: 0;
            animation: fadeInUp 0.6s var(--animation-timing) forwards;
            animation-delay: 0.6s;
        }
        
        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.75rem;
        }
        
        .login-header p {
            color: var(--light-text);
            font-size: 1rem;
        }
        
        .input-group {
            margin-bottom: 1.75rem;
            opacity: 0;
            animation: fadeInUp 0.6s var(--animation-timing) forwards;
        }
        
        .input-group:nth-child(1) {
            animation-delay: 0.8s;
        }
        
        .input-group:nth-child(2) {
            animation-delay: 1s;
        }
        
        .input-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }
        
        .input-wrapper {
            position: relative;
            transition: transform 0.3s ease;
        }
        
        .input-wrapper:focus-within {
            transform: translateY(-5px);
        }
        
        .input-wrapper i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 1rem;
            color: var(--light-text);
            transition: color 0.3s ease;
        }
        
        .input-wrapper:focus-within i {
            color: var(--primary-color);
        }
        
        .input-wrapper input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .login-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-md);
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
            opacity: 0;
            animation: fadeInUp 0.6s var(--animation-timing) forwards;
            animation-delay: 1.4s;
        }
        
        .login-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 150%;
            height: 150%;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%) scale(0);
            opacity: 0;
            transition: transform 0.6s, opacity 0.6s;
        }
        
        .login-btn:hover::after {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }
        
        .error-message {
            background-color: rgba(230, 57, 70, 0.08);
            color: var(--error-color);
            padding: 0.875rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--error-color);
            animation: shake 0.5s ease-in-out;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            margin-bottom: 1.75rem;
            opacity: 0;
            animation: fadeInUp 0.6s var(--animation-timing) forwards;
            animation-delay: 1.2s;
        }
        
        .remember {
            display: flex;
            align-items: center;
        }
        
        .remember input {
            margin-right: 0.5rem;
            accent-color: var(--primary-color);
            cursor: pointer;
        }
        
        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
        }
        
        .forgot-password a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary-hover);
            transition: width 0.3s ease;
        }
        
        .forgot-password a:hover {
            color: var(--primary-hover);
        }
        
        .forgot-password a:hover::after {
            width: 100%;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                height: auto;
                min-height: 80vh;
            }
            
            .illustration {
                padding: 2rem;
                height: 180px;
            }
            
            .login-form {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="illustration">
            <div class="bubble bubble-1"></div>
            <div class="bubble bubble-2"></div>
            <div class="bubble bubble-3"></div>
            <img src="adminillustrationssss.png" alt="Admin Portal Illustration">
        </div>
        <div class="login-form">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Sign in to access your admin dashboard</p>
            </div>
            
            <?php if (isset($login_error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $login_error; ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="post">
                <div class="input-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <div class="forgot-password">
                        <a href="#">Forgot password?</a>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">Sign In</button>
            </form>
        </div>
    </div>
    
    <script>
        // Add focus animations to inputs
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', () => {
                input.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html>
