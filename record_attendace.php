<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfj_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize inputs
    $employeeId = $conn->real_escape_string($_POST['employeeId']);
    $attendanceDate = $conn->real_escape_string($_POST['attendanceDate']);
    $action = $conn->real_escape_string($_POST['action']);
    
    // Check if employee exists
    $checkEmployee = "SELECT id FROM employees WHERE id = '$employeeId'";
    $result = $conn->query($checkEmployee);
    
    if ($result->num_rows == 0) {
        echo "Employee not found";
        exit;
    }
    
    if ($action == 'timeIn') {
        $clockIn = $conn->real_escape_string($_POST['clockIn']);
        
        // Check if record for this employee and date already exists
        $checkRecord = "SELECT id FROM attendance WHERE employee_id = '$employeeId' AND attendance_date = '$attendanceDate'";
        $result = $conn->query($checkRecord);
        
        if ($result->num_rows > 0) {
            // Update existing record with clock in time
            $row = $result->fetch_assoc();
            $recordId = $row['id'];
            $sql = "UPDATE attendance SET clock_in = '$clockIn' WHERE id = '$recordId'";
        } else {
            // Create new record
            $sql = "INSERT INTO attendance (employee_id, attendance_date, clock_in) 
                    VALUES ('$employeeId', '$attendanceDate', '$clockIn')";
        }
        
        if ($conn->query($sql) === TRUE) {
            echo "success";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    elseif ($action == 'timeOut') {
        $clockOut = $conn->real_escape_string($_POST['clockOut']);
        
        // Check if record for this employee and date already exists
        $checkRecord = "SELECT id FROM attendance WHERE employee_id = '$employeeId' AND attendance_date = '$attendanceDate'";
        $result = $conn->query($checkRecord);
        
        if ($result->num_rows > 0) {
            // Update existing record with clock out time
            $row = $result->fetch_assoc();
            $recordId = $row['id'];
            $sql = "UPDATE attendance SET clock_out = '$clockOut' WHERE id = '$recordId'";
            
            if ($conn->query($sql) === TRUE) {
                echo "success";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            // No clock-in record found
            echo "No clock-in record found for this date. Please record time-in first.";
        }
    }
    else {
        echo "Invalid action";
    }
} else {
    echo "Invalid request method";
}

// Close connection
$conn->close();
?>