<?php
// Create a database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfjdb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Time In Recording</title>
       <style>
              body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: flex-start;
            animation: fadeIn 1s ease-out;
        }

         .sidebar {
            width: 260px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            padding: 0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            padding-top: 20px;
        }

        .sidebar h2 {
            color: #2c3e50;
            padding: 20px;
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            border-bottom: 1px solid #f0f2f5;
            display: flex;
            align-items: center;
        }

        .sidebar h2::before {
            content: "M";
            display: inline-flex;
            justify-content: center;
            align-items: center;
            background-color: #4478bb;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 4px;
            margin-right: 10px;
            font-weight: bold;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin: 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .sidebar ul li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .sidebar ul li a.active {
            background-color: #eef5ff;
            color: #4478bb;
            border-left: 3px solid #4478bb;
        }

        .sidebar ul li a {
            color: #68758c;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            padding: 15px 20px;
            transition: all 0.2s ease;
        }

        .sidebar ul li a:hover {
            background-color: #f2f7ff;
            color: #4478bb;
        }

        .back-button {
            position: fixed;
            top: 30px;
            left: 330px;
            background-color: #34495e;
            color: white;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            z-index: 1000;
            animation: bounce 2s infinite;
        }

          .container {
            margin-left: 400px; /* Adjust to sidebar width */
            padding: 80px;
            max-width: 1300px;
            width: 100%;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            padding-bottom: 150px;
            animation: fadeInUp 1s ease-out;
        }

        h2 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        form {
            display: grid;
            gap: 15px;
        }

        label {
            font-weight: bold;
            color: #444;
            margin-top: 20px;
        }

        input[type="date"], input[type="time"], select {
            padding: 12px;
            width: 95%;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
            transition: border 0.3s ease-in-out;
        }

        input[type="date"]:focus, input[type="time"]:focus, select:focus {
            border-color: #007bff;
            outline: none;
        }

        button {
            padding: 16px 20px;
            background-color: #34495e;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            font-size: 16px;
            transition: background-color 0.3s ease-in-out;
            margin-top: 20px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        h2 i {
            margin-right: 10px;
            font-size: 37px;
        }

        .back-button a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #34495e;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .back-button a:hover {
            background-color: #2c3e50;
            transform: translateX(-3px);
        }

        .back-button i {
            font-size: 20px;
        }
        h2 i {
            margin-right: 10px; /* Adds space between the icon and the text */
            font-size: 37px;
        }
             @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-250px);
            }
            to {
                transform: translateX(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }


     </style>
</head>
<body>

      <div class="sidebar">
        <h2>MFJ Admin Panel</h2>
        <ul>
            <li><a href="/MFJ/admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/MFJ/manage_products.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="/MFJ/manage_services.php" class="active"><i class="fas fa-calendar-alt "></i>Appointments</a></li>
            <li><a href="/MFJ/manage_employee.php"><i class="fas fa-id-card"></i> Employees</a></li>
            <li><a href="/MFJ/admin_dashboard.php?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

         <div class="back-button">
            <a href="/MFJ/view_attendance.php">
                <i class="fas fa-arrow-left"></i> <!-- FontAwesome left arrow icon -->
            </a>
        </div>

    <div class="container">
        <h2><i class="fas fa-clock"></i> Time In Recording</h2>
    <?php
    // Handle form submission to record time-in
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $employeeId = $_POST['employeeId'];
        $attendanceDate = $_POST['attendanceDate'];
        $clockIn = $_POST['clockIn'];

        // Validate that employeeId and clockIn are not empty
        if (!empty($employeeId) && !empty($clockIn)) {
            // Fetch the name of the employee using employeeId
            $sql = "SELECT name FROM employees WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $employeeId);
            $stmt->execute();
            $stmt->bind_result($name);
            $stmt->fetch();
            $stmt->close();

            // Insert or update the time-in for the employee
            if ($name) {
                $sql = "INSERT INTO attendance (employee_id, attendance_date, clock_in, name)
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE clock_in = VALUES(clock_in)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param('isss', $employeeId, $attendanceDate, $clockIn, $name);

                if ($stmt->execute()) {
                    echo '<div class="message">Time In recorded successfully!</div>';
                } else {
                    echo '<div class="error">Error: Could not record time in. Please try again.</div>';
                }

                $stmt->close();
            } else {
                echo '<div class="error">Error: Employee not found.</div>';
            }
        } else {
            echo '<div class="error">Please fill in all the fields correctly.</div>';
        }
    }
    ?>

    <form method="post" action="">
        <label for="employeeId">Employee Name:</label>
        <select id="employeeId" name="employeeId" required>
            <option value="">Select Employee</option>
            <?php
            $sql = "SELECT id, name FROM employees";  // Fetch employees from the database
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row["id"] . '">' . $row["name"] . '</option>';
                }
            } else {
                echo '<option value="">No employees found</option>';
            }
            ?>
        </select>

        <label for="attendanceDate">Date:</label>
        <input type="date" id="attendanceDate" name="attendanceDate" value="<?php echo date('Y-m-d'); ?>" required>

        <label for="clockIn">Time In:</label>
        <input type="time" id="clockIn" name="clockIn" required>

        <button type="submit">Record Time In</button>
    </form>

    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
