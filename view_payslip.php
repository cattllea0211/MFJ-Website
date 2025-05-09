<?php 
// Existing PHP code remains the same
session_start();

// Retrieve the session username (assuming it's set during login)
$username = $_SESSION['username'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'mfjdb');

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
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --primary-dark: #1e3a8a;
            --primary-bg: #eff6ff;
            --secondary: #64748b;
            --light: #f8fafc;
            --lighter: #ffffff;
            --dark: #334155;
            --darker: #1e293b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
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
            background-color: #f1f5f9;
            color: var(--dark);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--darker);
            margin-bottom: 20px;
            text-align: center;
        }

        .filter-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
        }

        .filter-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1.5fr 0.8fr;
            gap: 16px;
            align-items: flex-end;
        }

        .filter-form label {
            font-size: 14px;
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 6px;
            display: block;
        }

        .filter-form input,
        .filter-form select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.2s ease;
            background-color: var(--light);
        }

        .filter-form input:focus,
        .filter-form select:focus {
            border-color: var(--primary-light);
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .filter-form button {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 18px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .filter-form button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .payslip-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .payslip-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-circle {
            background-color: rgba(255, 255, 255, 0.2);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }

        .company-details h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }

        .company-details p {
            font-size: 15px;
            opacity: 0.9;
        }

        .payslip-details {
            text-align: right;
        }

        .payslip-number {
            font-size: 14px;
            font-weight: 500;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 8px;
        }

        .payslip-content {
            padding: 30px;
        }

        .employee-info {
            background-color: var(--primary-bg);
            border-radius: 10px;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .employee-info > div {
            flex: 1;
        }

        .info-label {
            font-size: 13px;
            color: var(--dark);
            margin-bottom: 5px;
            font-weight: 500;
        }

        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--darker);
        }

        .payslip-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .section {
            background-color: var(--lighter);
            border-radius: 10px;
            padding: 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        .section-title {
            color: var(--primary);
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title::before {
            content: '';
            display: block;
            width: 4px;
            height: 18px;
            background-color: var(--primary);
            border-radius: 2px;
        }

        .data-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed var(--border);
        }

        .data-row:last-child {
            border-bottom: none;
        }

        .data-label {
            color: var(--dark);
        }

        .data-value {
            font-weight: 500;
            color: var(--darker);
        }

        .highlight {
            background-color: rgba(59, 130, 246, 0.1);
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 16px;
            border-left: 3px solid var(--primary);
        }

        .highlight .data-label,
        .highlight .data-value {
            font-weight: 600;
            color: var(--primary-dark);
        }

        .net-pay {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 20px 24px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 20px;
            font-weight: 600;
            margin-top: 10px;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 24px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 15px;
        }

        .btn svg {
            width: 18px;
            height: 18px;
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
            background-color: var(--light);
            color: var(--dark);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: var(--secondary);
        }

        .empty-state p {
            margin-top: 10px;
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
        <h1 class="page-title">Employee Payslip</h1>
        
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
                <button type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    Apply Filter
                </button>
            </form>
        </div>

        <div class="payslip-container" id="payslip-print">
            <div class="payslip-header">
                <div class="company-details company-logo">
                    <div class="logo-circle">MFJ</div>
                    <div>
                        <h1>MFJ Airconditioning</h1>
                        <p>Supply & Services</p>
                    </div>
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
                        <div class="highlight">
                            <div class="data-row">
                                <div class="data-label">Gross Income</div>
                                <div class="data-value">₱<?php echo number_format($gross_income, 2); ?></div>
                            </div>
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
                        <div class="highlight">
                            <div class="data-row">
                                <div class="data-label">Total Deductions</div>
                                <div class="data-value">₱<?php echo number_format($total_deductions, 2); ?></div>
                            </div>
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
            <button class="btn btn-primary" id="print-btn" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Print Payslip
            </button>
            <a href="employee_dashboard.php" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        // Add print dialog on button click
        document.getElementById('print-btn').addEventListener('click', function() {
            window.print();
        });

        // Add date validation to ensure from date is before to date
        document.querySelector('.filter-form').addEventListener('submit', function(e) {
            const fromDate = document.getElementById('date_from').value;
            const toDate = document.getElementById('date_to').value;
            
            if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                e.preventDefault();
                alert('From Date must be before or equal to To Date');
            }
        });
    </script>
</body>
</html>
