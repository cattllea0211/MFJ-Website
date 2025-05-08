<?php 
// Existing PHP code remains the same
session_start();

// Retrieve the session username (assuming it's set during login)
$username = $_SESSION['username'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'mfj_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch employee ID based on username
$sql_employee_id = "SELECT id FROM employees WHERE username = ?";
$stmt = $conn->prepare($sql_employee_id);

if ($stmt === false) {
    die('SQL Error: ' . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Employee not found.";
    exit;
}

$employee = $result->fetch_assoc();
$user_id = $employee['id'];
$stmt->close();

// Fetch employee transactions
$sql_transactions = "SELECT p.id AS payroll_id 
                     FROM payroll p
                     WHERE p.employee_id = ?";
$stmt = $conn->prepare($sql_transactions);
if ($stmt === false) {
    die('SQL Error: ' . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row['payroll_id'];
}
$stmt->close();

// Date and transaction filters
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$transaction_no = isset($_GET['transaction_no']) ? $_GET['transaction_no'] : '';

$sql = "SELECT 
    e.name AS employee_name, 
    p.*, 
    p.rate_per_day
FROM 
    payroll p
JOIN 
    employees e ON p.employee_id = e.id
WHERE 
    e.id = ?";

if ($date_from && $date_to) {
    $sql .= " AND p.created_at BETWEEN ? AND ?";
}
if ($transaction_no) {
    $sql .= " AND p.id = ?";
}

$sql .= " ORDER BY p.created_at DESC LIMIT 1";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die('SQL Error: ' . $conn->error);
}

if ($date_from && $date_to && $transaction_no) {
    $stmt->bind_param("isss", $user_id, $date_from, $date_to, $transaction_no);
} elseif ($date_from && $date_to) {
    $stmt->bind_param("iss", $user_id, $date_from, $date_to);
} elseif ($transaction_no) {
    $stmt->bind_param("is", $user_id, $transaction_no);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No payslip records found.";
    exit;
}

$row = $result->fetch_assoc();
$calculated_total_regular_wage = $row['rate_per_day'] * $row['days_worked'];
$gross_income = $calculated_total_regular_wage + $row['regular_overtime_pay'] + $row['special_overtime_pay'];
$total_deductions = $row['sss'] + $row['loan'] + $row['vale'];
$net_pay = $gross_income - $total_deductions;

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Payslip - MFJ Airconditioning</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8; 
            --secondary: #64748b;
            --light: #f1f5f9;
            --lighter: #f8fafc;
            --dark: #334155;
            --darker: #1e293b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .filter-container, .action-buttons {
                display: none;
            }
            #payslip-print, #payslip-print * {
                visibility: visible;
            }
            #payslip-print {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background-color: white;
            }
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .filter-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 20px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1.5fr 0.5fr;
            gap: 15px;
            align-items: center;
        }

        .filter-form label {
            font-size: 14px;
            font-weight: 500;
            color: var(--secondary);
            margin-bottom: 5px;
            display: block;
        }

        .filter-form input,
        .filter-form select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .filter-form input:focus,
        .filter-form select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .filter-form button {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 15px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
            height: 42px;
            align-self: flex-end;
        }

        .filter-form button:hover {
            background-color: var(--primary-dark);
        }

        .payslip-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .payslip-header {
            background-color: var(--primary);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-details h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .company-details p {
            font-size: 16px;
            opacity: 0.9;
        }

        .payslip-details {
            text-align: right;
        }

        .payslip-number {
            font-size: 14px;
            font-weight: 500;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 5px;
        }

        .payslip-content {
            padding: 30px;
        }

        .employee-info {
            background-color: var(--lighter);
            border-radius: 8px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .employee-info > div {
            flex: 1;
        }

        .info-label {
            font-size: 14px;
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 500;
        }

        .payslip-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .section {
            background-color: var(--lighter);
            border-radius: 8px;
            padding: 20px;
        }

        .section-title {
            color: var(--primary);
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .data-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .data-label {
            color: var(--dark);
        }

        .data-value {
            font-weight: 500;
        }

        .highlight {
            background-color: rgba(37, 99, 235, 0.1);
            padding: 10px;
            border-radius: 6px;
            margin-top: 15px;
        }

        .highlight .data-label,
        .highlight .data-value {
            font-weight: 600;
            color: var(--primary-dark);
        }

        .net-pay {
            background-color: var(--primary);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        @media (max-width: 768px) {
    .filter-form {
        grid-template-columns: 1fr;
    }

    .employee-info {
        flex-direction: column;
        gap: 15px;
    }

    .payslip-grid {
        grid-template-columns: 1fr;
    }

    .payslip-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .payslip-details {
        text-align: left;
    }

    .action-buttons {
        flex-direction: column;
        align-items: stretch;
    }

    .btn {
        width: 100%;
    }

    body {
        padding: 10px;
    }

    .container {
        padding: 0;
    }

    .payslip-content {
        padding: 20px;
    }

    .payslip-header {
        padding: 20px;
    }

    .section {
        padding: 15px;
    }
}

    </style>
</head>
<body>
    <div class="container">
        <div class="filter-container">
            <form class="filter-form" method="GET">
                <div>
                    <label for="date_from">From Date</label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div>
                    <label for="date_to">To Date</label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div>
                    <label for="transaction_no">Transaction</label>
                    <select id="transaction_no" name="transaction_no">
                        <option value="">Select Transaction</option>
                        <?php foreach ($transactions as $transaction): ?>
                            <option value="<?php echo $transaction; ?>" <?php echo $transaction == $transaction_no ? 'selected' : ''; ?>>
                                Payslip #<?php echo $transaction; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Apply Filter</button>
            </form>
        </div>

        <div class="payslip-container" id="payslip-print">
            <div class="payslip-header">
                <div class="company-details">
                    <h1>MFJ Airconditioning</h1>
                    <p>Supply & Services</p>
                </div>
                <div class="payslip-details">
                    <div class="payslip-number">Payslip #<?php echo $row['id']; ?></div>
                    <div>Cut-off: <?php echo htmlspecialchars($row['created_at']); ?></div>
                </div>
            </div>

            <div class="payslip-content">
                <div class="employee-info">
                    <div>
                        <div class="info-label">Employee Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($row['employee_name']); ?></div>
                    </div>
                    <div>
                        <div class="info-label">Days Worked</div>
                        <div class="info-value"><?php echo $row['days_worked']; ?></div>
                    </div>
                    <div>
                        <div class="info-label">Rate Per Day</div>
                        <div class="info-value">₱<?php echo number_format($row['rate_per_day'], 2); ?></div>
                    </div>
                </div>

                <div class="payslip-grid">
                    <div class="section">
                        <h2 class="section-title">Earnings</h2>
                        <div class="data-row">
                            <div class="data-label">Regular Wage</div>
                            <div class="data-value">₱<?php echo number_format($calculated_total_regular_wage, 2); ?></div>
                        </div>
                        <div class="data-row">
                            <div class="data-label">Regular OT Pay</div>
                            <div class="data-value">₱<?php echo number_format($row['regular_overtime_pay'], 2); ?></div>
                        </div>
                        <div class="data-row">
                            <div class="data-label">Special OT Pay</div>
                            <div class="data-value">₱<?php echo number_format($row['special_overtime_pay'], 2); ?></div>
                        </div>
                        <div class="data-row highlight">
                            <div class="data-label">Gross Income</div>
                            <div class="data-value">₱<?php echo number_format($gross_income, 2); ?></div>
                        </div>
                    </div>

                    <div class="section">
                        <h2 class="section-title">Deductions</h2>
                        <div class="data-row">
                            <div class="data-label">SSS</div>
                            <div class="data-value">₱<?php echo number_format($row['sss'], 2); ?></div>
                        </div>
                        <div class="data-row">
                            <div class="data-label">Loan</div>
                            <div class="data-value">₱<?php echo number_format($row['loan'], 2); ?></div>
                        </div>
                        <div class="data-row">
                            <div class="data-label">Vale</div>
                            <div class="data-value">₱<?php echo number_format($row['vale'], 2); ?></div>
                        </div>
                        <div class="data-row highlight">
                            <div class="data-label">Total Deductions</div>
                            <div class="data-value">₱<?php echo number_format($total_deductions, 2); ?></div>
                        </div>
                    </div>
                </div>

                <div class="net-pay">
                    <div>Net Pay</div>
                    <div>₱<?php echo number_format($net_pay, 2); ?></div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <button class="btn btn-primary" id="print-btn" onclick="window.print()">Print Payslip</button>
            <a href="employee_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <script>
        // Optional: Add print dialog on button click
        document.getElementById('print-btn').addEventListener('click', function() {
            window.print();
        });
    </script>
</body>
</html>