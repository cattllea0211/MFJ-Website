<?php 
// Start session management
session_start();

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in, redirect if not
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: employee_login.php");
    exit; 
}

// Get session variables
$id = $_SESSION['id'];
$username = $_SESSION['username']; 

// Database connection with error handling
try {
    $conn = new mysqli('localhost', 'mfj_user', 'StrongPassword123!', 'mfjdb');
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Prepare SQL statement
    $sql = "SELECT name FROM employees WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if statement preparation was successful
    if (!$stmt) {
        throw new Exception("Statement preparation failed: " . $conn->error);
    }
    
    // Bind parameters and execute
    $stmt->bind_param("i", $id);
    
    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Bind and fetch result
    $stmt->bind_result($employee_name);
    $stmt->fetch();
    $stmt->close();
    
    // Check if employee name was found
    if (empty($employee_name)) {
        $employee_name = $username; // Fallback to username if name not found
    }
    
    $conn->close();
} catch (Exception $e) {
    // Log the error but show a user-friendly message
    error_log("Database error in employee_dashboard.php: " . $e->getMessage());
    
    // Set a default employee name for display
    $employee_name = $username; // Use username as fallback
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFJ Air Conditioning - Employee Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a5f7a;
            --primary-light: #2d8bac;
            --secondary-color: #f5f5f5;
            --accent-color: #e67e22;
            --text-dark: #333333;
            --text-medium: #555555;
            --text-light: #ffffff;
            --shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: url('employeebg1.jpg') no-repeat center center;
            background-attachment: fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            padding: 40px 0 60px;
            text-align: center;
            position: relative;
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background-color: var(--text-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .logo span {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.8rem;
        }

        .header h1 {
            color: var(--text-light);
            font-size: 2.2rem;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1rem;
        }

        .button-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            margin-top: -40px;
        }

        .dashboard-btn {
            background-color: var(--text-light);
            border: none;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 100%;
            min-height: 100px;
        }

        .dashboard-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.12);
        }

        .dashboard-btn i {
            font-size: 1.6rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            transition: var(--transition);
        }

        .dashboard-btn span {
            color: var(--text-medium);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .dashboard-btn:hover i {
            color: var(--accent-color);
        }

        .dashboard-btn:hover span {
            color: var(--text-dark);
        }

        .footer-container {
            padding: 20px;
            text-align: center;
        }

        .logout-link {
            display: inline-block;
            color: var(--text-medium);
            text-decoration: none;
            font-size: 0.95rem;
            margin-bottom: 20px;
            transition: var(--transition);
            border: 1px solid #e0e0e0;
            padding: 8px 20px;
            border-radius: 30px;
        }

        .logout-link:hover {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .footer {
            padding: 15px 0;
            background-color: var(--secondary-color);
            color: var(--text-medium);
            font-size: 0.8rem;
            border-top: 1px solid #e0e0e0;
        }

        @media (max-width: 768px) {
            .button-container {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .button-container {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 1.8rem;
            }
        
            .logo {
                width: 60px;
                height: 60px;
            }

            .logo span {
                font-size: 1.4rem;
            }

            .header p {
                font-size: 0.9rem;
            }

            .dashboard-btn {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <div class="logo">
                    <span>MFJ</span>
                </div>
            </div>
            <h1>Welcome, <?php echo htmlspecialchars($employee_name); ?>!</h1>
            <p>Air Conditioning Supply & Services Employee Portal</p>
        </div>

        <div class="button-container">
            <form action="attendance_history.php" method="get">
                <button type="submit" class="dashboard-btn">
                    <i class="fas fa-calendar-check"></i>
                    <span>Attendance History</span>
                </button>
            </form>

            <form action="job_assignment.php" method="get">
                <button type="submit" class="dashboard-btn">
                    <i class="fas fa-briefcase"></i>
                    <span>Job Assignments</span>
                </button>
            </form>

            <form action="view_payslip.php" method="get">
                <button type="submit" class="dashboard-btn">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Payslip Details</span>
                </button>
            </form>

            <form action="attendance_form.php" method="get">
                <button type="submit" class="dashboard-btn">
                    <i class="fas fa-user-clock"></i>
                    <span>Record Attendance</span>
                </button>
            </form>

            <form action="employee_profile.php" method="get">
                <button type="submit" class="dashboard-btn">
                    <i class="fas fa-user"></i>
                    <span>Employee Profile</span>
                </button>
            </form>
        </div>

        <div class="footer-container">
            <a href="logout.php" class="logout-link">Logout</a>
            
            <div class="footer">
                &copy; 2024 MFJ Air Conditioning Supply & Services. All Rights Reserved.
            </div>
        </div>
    </div>
</body>
</html>
