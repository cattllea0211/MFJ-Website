<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
            margin-top: 50px;
            font-size: 36px;
            font-weight: 500;
        }

        .dashboard-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 50px;
            flex-wrap: wrap;
        }

        .card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            width: 250px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .card h3 {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 500;
            color: #333;
        }

        .card p {
            font-size: 16px;
            color: #777;
            margin-bottom: 25px;
        }

        .dashboard-button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            font-size: 18px;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            width: 100%;
            text-align: center;
            box-sizing: border-box;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .dashboard-button:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }

        .dashboard-button:active {
            background-color: #388e3c;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
                align-items: center;
            }

            .card {
                width: 80%;
            }
        }

    </style>
</head>
<body>

    <h1>Employee Management Dashboard</h1>

    <div class="dashboard-container">
        <div class="card">
            <h3>Add New Employee</h3>
            <p>Quickly add a new employee to the system.</p>
            <a href="employee_management.php" class="dashboard-button">Add Employee</a>
        </div>

        <div class="card">
            <h3>View Employees</h3>
            <p>View and manage all employees in the system.</p>
            <a href="employee_list.php" class="dashboard-button">View Employees</a>
        </div>

        <div class="card">
            <h3>View Manual Attendance</h3>
            <p>Check employee attendance records here.</p>
            <a href="view_attendance.php" class="dashboard-button">View Attendance</a>
        </div>

        <!-- New Card for Approving Users -->
        <div class="card">
            <h3>Approve Users</h3>
            <p>Review and approve pending user registrations.</p>
            <a href="admin_approve_users.php" class="dashboard-button">Approve Users</a>
        </div>

        <!-- New Card for Payroll -->
        <div class="card">
            <h3>Payroll</h3>
            <p>View and manage employee payroll details.</p>
            <a href="payroll.php" class="dashboard-button">Manage Payroll</a>
        </div>

        <!-- New Card for Attendance History -->
        <div class="card">
            <h3>Attendance History</h3>
            <p>View attendance records for all employees.</p>
            <a href="attendance_history_admin.php" class="dashboard-button">View Attendance History</a>
        </div>
    </div>

</body>
</html>
