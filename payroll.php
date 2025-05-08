<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <title>MFJ Payroll System</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #27ae60;
            --accent: #3498db;
            --light: #f8f9fa;
            --dark: #343a40;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --info: #3498db;
        }
        
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
        }
        
        .main-container {
            max-width: 95%; /* Change from 1600px to percentage-based */
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
                
        .payroll-header {
            background: linear-gradient(135deg, var(--primary) 70%, var(--secondary) 30%);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .payroll-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        .payroll-header h1 {
            font-size: 2.5rem;
            color: white;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .date-picker {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .date-picker .form-control {
            max-width: 300px;
            border-radius: 8px;
            padding: 10px 15px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background-color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        
        .date-picker .form-control:focus {
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.3);
            border-color: var(--accent);
        }
        
        .date-label {
            color: white;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1rem;
        }
        
        .period-covered {
            color: white;
            font-size: 1.2rem;
            text-align: center;
            margin-top: 10px;
            font-weight: 500;
        }
        
        .table-container {
            position: relative;
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .payroll-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: white;
        }
        
        .payroll-table thead {
            background-color: var(--accent);
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .payroll-table th {
            padding: 15px 10px;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            white-space: nowrap;
            vertical-align: middle;
        }
        
        .payroll-table tbody tr {
            transition: background-color 0.2s;
        }
        
        .payroll-table tbody tr:nth-child(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .payroll-table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .payroll-table td {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            border-top: 1px solid #eee;
        }
        
        .payroll-table td input, 
        .payroll-table td select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .payroll-table td input:focus, 
        .payroll-table td select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        
        .payroll-table td input[readonly] {
            background-color: #f8f9fa;
            cursor: default;
        }
        
        .highlight-column {
            background-color: rgba(39, 174, 96, 0.1);
            font-weight: 600;
        }
        
        .highlight-column input {
            background-color: rgba(39, 174, 96, 0.05);
            font-weight: 600;
            color: var(--success);
        }
        
        .section-header {
            background-color: #f0f2f5;
            color: var(--primary);
            font-weight: 600;
            padding: 10px;
            text-align: center;
        }
        
        .btn-calculate {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-calculate:hover {
            background-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
        }
        
        .grand-total-row {
            background-color: var(--primary);
            color: white;
            font-weight: 700;
        }
        
        .grand-total-row td {
            padding: 15px;
            border-top: 3px solid var(--accent);
        }
        
        #grand_total {
            font-size: 1.3rem;
            color: var(--light);
            font-weight: 700;
        }
        
        .btn-save {
            background-color: var(--success);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-save:hover {
            background-color: #219653;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-back {
            background-color: var(--dark);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-back:hover {
            background-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-view {
            background-color: var(--accent);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-view:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast {
            background-color: var(--danger);
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 0;
            transform: translateX(50px);
            transition: all 0.3s ease;
        }
        
        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .toast.hide {
            opacity: 0;
            transform: translateX(50px);
        }
        
        .modal-content {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            background-color: var(--primary);
            color: white;
            border-bottom: none;
            padding: 15px 20px;
        }
        
        .modal-title {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .modal-table th {
            background-color: var(--accent);
            color: white;
            padding: 12px;
            font-weight: 600;
            border: none;
        }
        
        .modal-table td {
            padding: 12px;
            border-top: 1px solid #eee;
        }
        
        .modal-table tr:nth-child(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .modal-table tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        /* Responsive styling */
        @media (max-width: 1200px) {
            .table-container {
                margin-bottom: 20px;
            }
            
            .payroll-table th,
            .payroll-table td {
                padding: 10px 8px;
                font-size: 0.9rem;
            }
            
            .payroll-header h1 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .main-container {
                padding: 15px;
                margin-top: 10px;
                margin-bottom: 10px;
            }
            
            .payroll-header {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .payroll-header h1 {
                font-size: 1.5rem;
                margin-bottom: 15px;
            }
            
            .date-picker {
                flex-direction: column;
                gap: 10px;
            }
            
            .date-picker .form-control {
                max-width: 100%;
            }
            
            .btn-save,
            .btn-back,
            .btn-view {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
            
            #grand_total {
                font-size: 1.1rem;
            }
        }

        /* Table grouping styles */
        .column-group {
            border-bottom: 2px solid #ddd;
        }
        
        .group-header {
            background-color: #f0f2f5;
            color: var(--primary);
            font-weight: 600;
            text-align: center;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        
        /* Additional styling */
        .required-field::after {
            content: "*";
            color: var(--danger);
            margin-left: 3px;
        }
        
        .row-number {
            background-color: #f0f2f5;
            font-weight: 600;
            color: var(--primary);
        }
        
        .section-divide {
            border-left: 2px solid #eee;
        }
        
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <button class="btn-back" onclick="window.history.back()">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </button>
        
        <div class="payroll-header">
            <h1><i class="bi bi-cash-stack me-2"></i> MFJ Payroll System</h1>
            
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="date-label">Start Date</div>
                    <input type="date" class="form-control" id="start_date" onchange="updateDateRange()">
                </div>
                <div class="col-md-4">
                    <div class="date-label">End Date</div>
                    <input type="date" class="form-control" id="end_date" onchange="updateDateRange()">
                </div>
            </div>
            
            <div class="period-covered mt-3">
                <i class="bi bi-calendar-range me-2"></i>
                <span id="coverage_period">For the period covered: Select dates above</span>
            </div>
        </div>
        
        <div class="table-container">
            <table class="table payroll-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="row-number">#</th>
                        <th rowspan="2" class="required-field">Employee</th>
                        <th colspan="5" class="group-header">Work Information</th>
                        <th colspan="3" class="group-header">Payment Calculation</th>
                        <th rowspan="2">Gross Income</th>
                        <th colspan="4" class="group-header">Deductions</th>
                        <th rowspan="2" class="highlight-column">Net Pay</th>
                        <th rowspan="2">Actions</th>
                    </tr>
                    <tr>
                        <th>Days</th>
                        <th>Hours</th>
                        <th>Rate/Day</th>
                        <th>Regular OT</th>
                        <th>Special OT</th>
                        <th class="highlight-column">Total Wage</th>
                        <th>Regular OT Pay</th>
                        <th>Special OT Pay</th>
                        <th>SSS</th>
                        <th>Loan</th>
                        <th>Vale</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conn = new mysqli('localhost', 'root', '', 'mfj_db');
                    $result = $conn->query("SELECT id, name FROM employees");

                    for ($i = 1; $i <= 8; $i++) {
                        echo "<tr>
                            <td class='row-number'>$i</td>
                            <td>
                                <select class='form-select' id='employee_$i' onchange='fetchBasicRate(this.value, $i)'>
                                    <option value=''>Select Employee</option>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        $result->data_seek(0);
                        echo "</select>
                            </td>
                            <td><input type='number' class='form-control' id='days_worked_$i' placeholder='Days' step='0.5' min='0' onchange='calculatePayroll($i)'></td>
                            <td><input type='number' class='form-control' id='hours_worked_$i' placeholder='Hours' step='0.5' min='0' onchange='calculatePayroll($i)'></td>
                            <td><input type='number' class='form-control' id='rate_per_day_$i' readonly></td>
                            <td><input type='number' class='form-control' id='regular_overtime_hours_$i' placeholder='Hours' step='0.5' min='0' onchange='calculatePayroll($i)'></td>
                            <td><input type='number' class='form-control' id='special_overtime_hours_$i' placeholder='Hours' step='0.5' min='0' onchange='calculatePayroll($i)'></td>
                            <td class='highlight-column'><input type='text' class='form-control' id='total_regular_wage_$i' readonly></td>
                            <td><input type='text' class='form-control' id='regular_overtime_pay_$i' readonly></td>
                            <td><input type='text' class='form-control' id='special_overtime_pay_$i' readonly></td>
                            <td><input type='text' class='form-control' id='gross_income_$i' readonly></td>
                            <td><input type='number' class='form-control' id='sss_$i' placeholder='SSS' step='0.01' min='0' onchange='calculatePayroll($i)'></td>
                            <td><input type='number' class='form-control' id='loan_$i' placeholder='Loan' step='0.01' min='0' onchange='calculatePayroll($i)'></td>
                            <td><input type='number' class='form-control' id='vale_$i' placeholder='Vale' step='0.01' min='0' onchange='calculatePayroll($i)'></td>
                            <td><input type='text' class='form-control' id='total_deductions_$i' readonly></td>
                            <td class='highlight-column'><input type='text' class='form-control' id='net_pay_$i' readonly></td>
                            <td><button class='btn-calculate' onclick='calculatePayroll($i)'><i class='bi bi-calculator'></i> Calculate</button></td>
                        </tr>";
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr class="grand-total-row">
                        <td colspan="14" class="text-end">Grand Total:</td>
                        <td id="grand_total">0.00</td>
                        <td>
                            <button class="btn-save" onclick="savePayrollData()">
                                <i class="bi bi-save"></i> Save Payroll
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="text-center">
            <button type="button" class="btn-view" data-bs-toggle="modal" data-bs-target="#payrollModal">
                <i class="bi bi-table"></i> View Payroll Records
            </button>
        </div>
    </div>

    <!-- Payroll Modal -->
    <div class="modal fade" id="payrollModal" tabindex="-1" aria-labelledby="payrollModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payrollModalLabel">
                        <i class="bi bi-list-check me-2"></i> Payroll Records
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover modal-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Days Worked</th>
                                    <th>Hours Worked</th>
                                    <th>Rate/Day</th>
                                    <th>Gross Income</th>
                                    <th>Net Pay</th>
                                    <th>Date Coverage</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = mysqli_query($conn, "SELECT p.*, e.name AS employee_name 
                                                            FROM payroll p 
                                                            JOIN employees e ON p.employee_id = e.id 
                                                            ORDER BY p.created_at DESC");
                                                            
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>
                                            <td>{$row['id']}</td>
                                            <td>{$row['employee_name']}</td>
                                            <td>{$row['days_worked']}</td>
                                            <td>{$row['hours_worked']}</td>
                                            <td>₱{$row['rate_per_day']}</td>
                                            <td>₱{$row['gross_income']}</td>
                                            <td>₱{$row['net_pay']}</td>
                                            <td>{$row['date_coverage']}</td>
                                            <td>{$row['created_at']}</td>
                                        </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        function fetchBasicRate(employeeId, row) {
            if (!employeeId) return;

            fetch(`/MFJ/get_employee_rate.php?employee_id=${employeeId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.rate_per_day) {
                        document.getElementById(`rate_per_day_${row}`).value = data.rate_per_day;
                        calculatePayroll(row); 
                    } else {
                        console.error('Rate not found for employee:', employeeId);
                        document.getElementById(`rate_per_day_${row}`).value = 0;
                    }
                })
                .catch(error => console.error('Error fetching rate:', error));
        }

        function calculatePayroll(row) {
            let daysWorked = parseFloat(document.getElementById(`days_worked_${row}`).value) || 0;
            let hoursWorked = parseFloat(document.getElementById(`hours_worked_${row}`).value) || 0;
            let ratePerDay = parseFloat(document.getElementById(`rate_per_day_${row}`).value) || 0;
            let regularOvertimeHours = parseFloat(document.getElementById(`regular_overtime_hours_${row}`).value) || 0;
            let specialOvertimeHours = parseFloat(document.getElementById(`special_overtime_hours_${row}`).value) || 0;

            let perHourRate = ratePerDay / 8; 
            let totalRegularWage = hoursWorked * perHourRate;

            let regularOvertimePay = regularOvertimeHours * perHourRate;

            let specialOvertimePay = specialOvertimeHours * perHourRate * 1.2;

            let grossIncome = totalRegularWage + regularOvertimePay + specialOvertimePay;

            document.getElementById(`total_regular_wage_${row}`).value = totalRegularWage.toFixed(2);
            document.getElementById(`regular_overtime_pay_${row}`).value = regularOvertimePay.toFixed(2);
            document.getElementById(`special_overtime_pay_${row}`).value = specialOvertimePay.toFixed(2);
            document.getElementById(`gross_income_${row}`).value = grossIncome.toFixed(2);

            let sss = parseFloat(document.getElementById(`sss_${row}`).value) || 0;
            let loan = parseFloat(document.getElementById(`loan_${row}`).value) || 0;
            let vale = parseFloat(document.getElementById(`vale_${row}`).value) || 0;

            let totalDeductions = sss + loan + vale;
            document.getElementById(`total_deductions_${row}`).value = totalDeductions.toFixed(2);

            let netPay = grossIncome - totalDeductions;
            document.getElementById(`net_pay_${row}`).value = netPay.toFixed(2);

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let grandTotal = 0;
            for (let i = 1; i <= 8; i++) {
                let netPay = parseFloat(document.getElementById(`net_pay_${i}`).value) || 0;
                grandTotal += netPay;
            }
            document.getElementById('grand_total').innerText = grandTotal.toFixed(2);
        }

        function savePayrollData() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) {
                showToast("Please select both start and end dates first!");
                return;
            }

            const payrollData = [];
            const dateCoverage = `${startDate} - ${endDate}`;

            console.log('Date Coverage:', dateCoverage);

            for (let i = 1; i <= 8; i++) {
                const employeeId = document.getElementById(`employee_${i}`).value;
                
                if (employeeId) {
                    const daysWorked = document.getElementById(`days_worked_${i}`).value;
                    const hoursWorked = document.getElementById(`hours_worked_${i}`).value;
                    const ratePerDay = document.getElementById(`rate_per_day_${i}`).value;
                    const regularOvertimeHours = document.getElementById(`regular_overtime_hours_${i}`).value;
                    const specialOvertimeHours = document.getElementById(`special_overtime_hours_${i}`).value;
                    const sss = document.getElementById(`sss_${i}`).value;
                    const loan = document.getElementById(`loan_${i}`).value;
                    const vale = document.getElementById(`vale_${i}`).value;
                    const netPay = document.getElementById(`net_pay_${i}`).value;
                    const regularOvertimePay = document.getElementById(`regular_overtime_pay_${i}`).value;
                    const specialOvertimePay = document.getElementById(`special_overtime_pay_${i}`).value;

                    payrollData.push({
                        employeeId,
                        daysWorked,
                        hoursWorked,
                        ratePerDay,
                        regularOvertimeHours,
                        specialOvertimeHours,
                        sss,
                        loan,
                        vale,
                        netPay,
                        dateCoverage,
                        regularOvertimePay,
                        specialOvertimePay
                    });
                }
            }

            if (payrollData.length === 0) {
                showToast("Please select at least one employee!");
                return;
            }

            fetch('/MFJ/save_payroll.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payrollData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast("Payroll data saved successfully!", "success");
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showToast("Error saving payroll data.", "error");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast("An error occurred while saving.", "error");
            });
        }

        function updateDateRange() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (startDate && endDate) {
                // Format dates for better readability
                const formattedStartDate = new Date(startDate).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                const formattedEndDate = new Date(endDate).toLocaleDateString('en-US', { 
                    year: 'numeric',

                    month: 'short', 
                    day: 'numeric' 
                });
                
                document.getElementById('coverage_period').innerHTML = 
                    `<i class="bi bi-calendar-range me-2"></i> For the period covered: <strong>${formattedStartDate} - ${formattedEndDate}</strong>`;
            } else {
                document.getElementById('coverage_period').innerHTML = 
                    `<i class="bi bi-calendar-range me-2"></i> For the period covered: <span class="fst-italic">Select dates above</span>`;
            }
        }

        function showToast(message, type = 'error') {
            const toastContainer = document.getElementById('toastContainer');
            
            // Create toast element
            const toast = document.createElement('div');
            toast.className = 'toast';
            
            // Set icon based on type
            let icon = '';
            if (type === 'error') {
                toast.style.backgroundColor = '#e74c3c';
                icon = '<i class="bi bi-exclamation-circle"></i>';
            } else if (type === 'success') {
                toast.style.backgroundColor = '#2ecc71';
                icon = '<i class="bi bi-check-circle"></i>';
            } else if (type === 'warning') {
                toast.style.backgroundColor = '#f39c12';
                icon = '<i class="bi bi-exclamation-triangle"></i>';
            }
            
            toast.innerHTML = `${icon} ${message}`;
            toastContainer.appendChild(toast);
            
            // Show toast with animation
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => {
                    toastContainer.removeChild(toast);
                }, 300);
            }, 3000);
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date values to current month
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            // Format dates to YYYY-MM-DD for input elements
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            document.getElementById('start_date').value = formatDate(firstDay);
            document.getElementById('end_date').value = formatDate(lastDay);
            
            // Update display
            updateDateRange();
        });

        // Add keyboard shortcuts for navigation
        document.addEventListener('keydown', function(e) {
            // Ctrl+S to save payroll
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                savePayrollData();
            }
            
            // Ctrl+C to calculate all rows
            if (e.ctrlKey && e.key === 'c') {
                e.preventDefault();
                for (let i = 1; i <= 8; i++) {
                    calculatePayroll(i);
                }
            }
        });

        // Add validation for special fields
        function validateInput(input, min, max) {
            const value = parseFloat(input.value);
            if (isNaN(value)) {
                input.value = '';
                return;
            }
            
            if (value < min) {
                input.value = min;
            } else if (max !== undefined && value > max) {
                input.value = max;
            }
            
            // Trigger calculation if part of a payroll row
            const rowMatch = input.id.match(/^(\w+)_(\d+)$/);
            if (rowMatch) {
                const row = rowMatch[2];
                calculatePayroll(row);
            }
        }

        // Extend functionality to allow for employee search
        function searchEmployee() {
            const searchTerm = document.getElementById('employeeSearch').value.toLowerCase();
            const selectElements = document.querySelectorAll('[id^="employee_"]');
            
            selectElements.forEach(select => {
                const options = select.querySelectorAll('option');
                let matchFound = false;
                
                options.forEach(option => {
                    if (option.textContent.toLowerCase().includes(searchTerm)) {
                        matchFound = true;
                    }
                });
                
                if (matchFound) {
                    select.closest('tr').style.display = '';
                } else {
                    select.closest('tr').style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>