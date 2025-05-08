<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'root', '', 'mfjdb');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

// Get the raw POST data
$rawData = file_get_contents('php://input');
$payrollData = json_decode($rawData, true);

if (!$payrollData) {
    die(json_encode(['success' => false, 'error' => 'Invalid JSON data']));
}

// Prepare an SQL statement
$stmt = $conn->prepare("INSERT INTO payroll (
    employee_id, 
    days_worked, 
    hours_worked, 
    rate_per_day, 
    regular_overtime_hours, 
    special_overtime_hours, 
    sss, 
    loan, 
    vale, 
    net_pay, 
    date_coverage,
    regular_overtime_pay,
    special_overtime_pay
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$successCount = 0;
$errorCount = 0;

foreach ($payrollData as $record) {
    $stmt->bind_param(
        'sssssssssssss', 
        $record['employeeId'], 
        $record['daysWorked'], 
        $record['hoursWorked'], 
        $record['rate_per_day'], 
        $record['regularOvertimeHours'], 
        $record['specialOvertimeHours'], 
        $record['sss'], 
        $record['loan'], 
        $record['vale'], 
        $record['netPay'], 
        $record['dateCoverage'],
        $record['regularOvertimePay'],
        $record['specialOvertimePay']
    );

    if ($stmt->execute()) {
        $successCount++;
    } else {
        $errorCount++;
    }
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => $errorCount === 0,
    'successCount' => $successCount,
    'errorCount' => $errorCount
]);
