<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfjdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get record ID
$recordId = $_GET['id'] ?? '';

// Validate record ID
if (empty($recordId)) {
    echo "Record ID is required";
    header("Location: /MFJ/view_attendance.php");
    exit;
}

// Sanitize input
$recordId = $conn->real_escape_string($recordId);

// Delete attendance record
$sql = "DELETE FROM attendance WHERE id = '$recordId'";

if ($conn->query($sql) === TRUE) {
    // Redirect back to attendance page with success message
    header("Location: /MFJ/view_attendance.php?deleted=success");
} else {
    // Redirect back with error message
    header("Location: /MFJ/view_attendance.php?deleted=error");
}

// Close connection
$conn->close();
?>
