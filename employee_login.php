<?php
session_start();

$host = 'localhost';
$dbname = 'mfjdb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    // Now also select the 'roles' column
    $sql = "SELECT id, password, roles FROM employees WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $username_input, PDO::PARAM_STR);
    $stmt->execute();
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee) {
        if (password_verify($password_input, $employee['password'])) {
            $_SESSION['username'] = $username_input;
            $_SESSION['id'] = $employee['id'];
            $_SESSION['role'] = $employee['roles']; // Store role in session

            // Redirect based on role
            if ($employee['roles'] === 'Manager') {
                header("Location: manager_dashboard.php");
            } else if ($employee['roles'] === 'Employee') {
                header("Location: employee_dashboard.php");
            } else {
                $error_message = "Unknown role assigned. Contact admin.";
            }
            exit;
        } else {
            $error_message = "Incorrect password. Please try again.";
        }
    } else {
        $error_message = "Username not found. Please check and try again.";
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFJ Employee Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }

        :root {
            --primary: #3B82F6;
            --primary-dark: #2563EB;
            --secondary: #6366F1;
            --text-primary: #1F2937;
            --text-secondary: #4B5563;
            --bg-light: #F9FAFB;
            --border-color: #E5E7EB;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

body {
background: linear-gradient(to bottom, #ff9a9e, #fad0c4);

            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
    color: var(--text-primary);
    position: relative;
    min-height: 100vh;
    overflow-x: hidden;

    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.background {
    position: absolute;
    z-index: -1;
    height: 100%;
    min-height: 100%;
}

      .background::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background-image: 
        radial-gradient(circle at 20% 35%, rgba(144, 238, 216, 0.15) 0%, transparent 25%),
        radial-gradient(circle at 85% 20%, rgba(96, 165, 250, 0.15) 0%, transparent 35%);
}
        .login-container {
    display: flex;
    flex-direction: row;
    width: 900px;
    max-width: 95%;
    margin: 2rem auto;
    background: white;
    border-radius: 16px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    flex-wrap: wrap;
}


       .left-section {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    background: linear-gradient(135deg, #2A8D8D 0%, #3886FF 100%);
    color: white;
    position: relative;
}
        .left-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.2) 0%, transparent 10%),
        radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.2) 0%, transparent 15%);
    opacity: 0.6;
}

        .logo-container {
            position: relative;
            z-index: 1;
            margin-bottom: 2rem;
        }

        .logo-container img {
            width: 100px;
            height: auto;
            filter: brightness(0) invert(1);
        }

        .company-info {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .company-info h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .company-info p {
            font-size: 0.9rem;
            opacity: 0.9;
            max-width: 80%;
            margin: 0 auto;
        }

        .right-section {
            flex: 1.5;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            margin-bottom: 2rem;
        }

        .login-header h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            background-color: #FAFAFA;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .error-message {
            color: #EF4444;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            text-align: center;
        }

        .btn {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            border: none;
            border-radius: 0.5rem;
            background: var(--primary);
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .footer-divider {
            margin: 1.5rem 0;
            height: 1px;
            background: var(--border-color);
        }

        .signup-btn {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            font-weight: 500;
        }

        .signup-btn:hover {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary-dark);
        }

      
        @media (max-width: 768px) {
    .login-container {
        flex-direction: column;
        width: 95%;
        min-height: auto;
        border-radius: 12px;
    }

    .left-section {
        padding: 1.5rem 1rem;
        align-items: center;
        text-align: center;
    }

    .logo-container img {
        width: 70px;
    }

    .company-info h1 {
        font-size: 1.25rem;
    }

    .company-info p {
        font-size: 0.8rem;
        max-width: 90%;
    }

    .right-section {
        padding: 1.5rem 1.25rem;
    }

    .login-header h2 {
        font-size: 1.5rem;
    }

    .login-header p {
        font-size: 0.85rem;
    }

    .form-control {
        font-size: 0.85rem;
        padding: 0.65rem 0.9rem;
    }

    .btn {
        font-size: 0.9rem;
        padding: 0.65rem 1rem;
    }

    .signup-btn {
        padding: 0.6rem 1rem;
    }

    .footer p {
        font-size: 0.85rem;
    }
}

    </style>
</head>
<body>
    <div class="background"></div>
    <div class="login-container">
        <div class="left-section">
            <div class="left-pattern"></div>
            <div class="logo-container">
                <img src="logo_clear.png" alt="MFJ Logo">
            </div>
            <div class="company-info">
                <h1>Welcome to MFJ</h1>
                <p>Your trusted air-conditioning solutions provider</p>
            </div>
        </div>
        <div class="right-section">
            <div class="login-header">
                <h2>Employee Portal</h2>
                <p>Sign in to access your dashboard</p>
            </div>
            
            <?php 
            if (!empty($error_message)) {
                echo "<p class='error-message'>$error_message</p>";
            }
            ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn">Sign In</button>
            </form>
            
            <div class="footer">
                <div class="footer-divider"></div>
                <p>Don't have an account?</p>
                <a href="/employee_register.php" class="btn signup-btn" style="display: inline-block; margin-top: 0.75rem;">Create Account</a>
            </div>
        </div>
    </div>
</body>
</html>
