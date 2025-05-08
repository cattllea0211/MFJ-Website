<?php 
session_start();

$servername = "localhost"; 
$username = "root"; 
$password = "";
$dbname = "mfj_db"; 


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (isset($_SESSION['user_id'])) {
    header("Location: /MFJ/client_dashboard.php");
    exit();
}

$error_message = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = "All fields are required!";
    } else {
        try {
            
            $stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE username = ?");
            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("s", $username); 
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
               
                if ($user['status'] == 'inactive') {
                    $error_message = "Account is inactive. Please contact support.";
                } 
                
                elseif (password_verify($password, $user['password'])) {
                    // Successful login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username']; 
                    $_SESSION['role'] = $user['role']; 

                  
                    $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $update_stmt->bind_param("i", $user['id']);
                    $update_stmt->execute();
                    $update_stmt->close();

                    
                    switch($user['role']) {
                        case 'admin':
                            header("Location: /MFJ/admin_dashboard.php");
                            break;
                        case 'client':
                            header("Location: /MFJ/client_dashboard.php");
                            break;
                        default:
                            header("Location: /MFJ/homepage.php");
                    }
                    exit();
                } else {
                    $error_message = "Invalid username or password!";
                }
            } else {
                $error_message = "Invalid username or password!";
            }
            $stmt->close();
        } catch (Exception $e) {
            $error_message = "An error occurred: " . $e->getMessage();
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
    <title>Login Page</title>
    <style>
         * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.95);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0px);
            }
        }

        body {
            min-height: 100vh;
            display: flex;
            background-image: url("bglogin3.png");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .login-container {
            display: flex;
            width: 100%;
            max-width: 1800px;
            margin: auto;
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            height: 900px;
            animation: scaleIn 0.6s ease-out;
        }

        .login-form-section {
            flex: 1;
            padding: 90px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            margin-bottom: 20px;
            animation: float 6s ease-in-out infinite;
        }

        .logo img {
            height: 120px;
            
        }

        h2 {
            font-size: 32px;
            font-weight: 800;
            color: #333;
            margin-bottom: 40px;
            animation: slideIn 0.6s ease-out;
        }

        .input-group {
            margin-bottom: 30px;
            animation: fadeIn 0.6s ease-out;
            animation-fill-mode: both;
        }

        .input-group:nth-child(2) {
            animation-delay: 0.1s;
        }

        .input-group label {
            display: block;
            font-size: 19px;
            color: #666;
            margin-bottom: 12px;
        }

        .input-group input {
            width: 100%;
            padding: 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #4A90E2;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.1);
        }

        button {
            width: 100%;
            padding: 16px;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            animation: fadeIn 0.6s ease-out;
            animation-delay: 0.2s;
            animation-fill-mode: both;
        }

        button:hover {
            background-color: #333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .signup-link {
            margin-top: 30px;
            text-align: center;
            font-size: 16px;
            color: #666;
            animation: fadeIn 0.6s ease-out;
            animation-delay: 0.3s;
            animation-fill-mode: both;
        }

        .signup-link a {
            color: #4A90E2;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: #2171c7;
        }

        .feature-section {
            flex: 1;
            background-color: steelblue;
            padding: 80px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.6s ease-out;
        }

        .feature-content {
            position: relative;
            z-index: 2;
        }

        .feature-section h3 {
            font-size: 90px;
            margin-bottom: 24px;
            animation: fadeIn 0.6s ease-out;
            animation-delay: 0.4s;
            animation-fill-mode: both;
        }

        .feature-section p {
            font-size: 14px;
            opacity: 0.9;
            animation: fadeIn 0.6s ease-out;
            animation-delay: 0.5s;
            animation-fill-mode: both;
        }

        .geometric-shapes {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.1;
        }

        .dot-pattern {
            position: absolute;
            top: 30px;
            right: 30px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            animation: fadeIn 0.6s ease-out;
            animation-delay: 0.6s;
            animation-fill-mode: both;
        }

        .dot {
            width: 12px;
            height: 12px;
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            transition: transform 0.3s ease;
        }

        .dot:hover {
            transform: scale(1.5);
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
            width: 26px;
            height: 26px;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                margin: 20px;
                height: auto;
            }
            
            .login-form-section,
            .feature-section {
                padding: 60px;
            }
        }
         .one-time-user-btn {
            width: 100%;
            padding: 16px;
            background-color: #4A90E2;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            animation: fadeIn 0.6s ease-out;
            animation-delay: 0.3s;
            animation-fill-mode: both;
        }

        .one-time-user-btn:hover {
            background-color: #2171c7;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.1);
        }
        .onetimeuser {
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 90px;
        
        }

        .onetimeuser p {
            margin-bottom: 15px;
            color: white;
            font-size: 16px;
            font-weight: bolder;
        }

    
        .one-time-user-btn {
            width: 100%;
            max-width: 300px;
            padding: 16px;
            background-color: black;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 30px;
            animation: fadeIn 0.6s ease-out;
            animation-delay: 0.3s;
            animation-fill-mode: both;
            text-align: center;
            display: inline-block;
        }

        .one-time-user-btn:hover {
            background-color: #2171c7;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.1);
        }
        .feature-content h2 {
            color: white;
            font-size: 20px;
        }

    </style>
</head>
<body>


    <a href="/MFJ/homepage.php" class="back-button">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
    </a>

    <div class="login-container">
        <div class="login-form-section">

            <div class="logo">
                <img src="logo_clear.png" alt="MFJ Logo">
            </div>


            <h2>Log In</h2>
            <form action="" method="POST">

                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Log In</button>

                 

                <div class="signup-link">
                    Don't have an account? <a href="/MFJ/signup.php">Sign up</a>
                </div>
            </form>
        </div>
        <div class="feature-section">
            <div class="geometric-shapes">
                <div class="dot-pattern">
                    <!-- 4x4 grid of dots -->
                    <div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div>
                    <div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div>
                    <div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div>
                    <div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div>
                </div>
            </div>
            <div class="feature-content">
                <h3>WELCOME!</h3>
                <h2>MFJ Airconditioning Supply & Services</h2>
               
            </div>

       
        </div>
    </div>

</body>
</html>