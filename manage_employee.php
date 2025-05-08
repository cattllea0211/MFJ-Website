<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #dbeafe;
            --secondary: #f1f5f9;
            --accent: #0ea5e9;
            --text: #1e293b;
            --text-light: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --white: #ffffff;
            --light-bg: #f8fafc;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.2s ease;
            --radius: 0.75rem;
        }    

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text);
            overflow-x: hidden;
        }

        /* Sidebar - Kept unchanged */
        .sidebar {
            width: 280px;
            background-color: var(--white);
            box-shadow: var(--shadow);
            padding: 24px 0;
            display: flex;
            flex-direction: column;
            z-index: 10;
            transition: var(--transition);
            position: fixed;
            height: 100vh;
        }

        .sidebar-header {
            padding: 0 24px 24px;
            margin-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .nav-section {
            padding: 12px 24px 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-light);
            font-weight: 600;
        }

        .nav-item {
            margin: 4px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
            font-weight: 500;
            border-radius: 0 8px 8px 0;
            margin-right: 8px;
        }

        .nav-link:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary);
            border-left: 3px solid var(--primary);
            font-weight: 600;
        }

        .nav-icon {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        /* Main content - Improved */
        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: var(--transition);
            background-color: var(--light-bg);
            min-height: 100vh;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .welcome-message {
            font-size: 28px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }

        .date {
            color: var(--text-light);
            font-size: 15px;
            font-weight: 500;
        }

        .quick-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--white);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 16px;
        }

        .stat-icon.blue {
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .stat-icon.green {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stat-icon.orange {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-icon.red {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .stat-info h3 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: var(--text-light);
            font-size: 14px;
            margin: 0;
        }

        .page-title {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            background-color: var(--white);
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .page-title i {
            font-size: 24px;
            margin-right: 15px;
            color: var(--primary);
        }

        .page-title h1 {
            font-size: 22px;
            font-weight: 600;
            color: var(--text);
        }

        /* Dashboard cards - Improved */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .card {
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            display: flex;
            align-items: center;
            padding: 25px 25px 15px;
            border-bottom: 1px solid var(--border);
        }

        .card-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-right: 20px;
            box-shadow: var(--shadow-sm);
        }

        .card-icon.blue {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: var(--white);
        }

        .card-icon.green {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: var(--white);
        }

        .card-icon.orange {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            color: var(--white);
        }

        .card-icon.teal {
            background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
            color: var(--white);
        }

        .card-icon.red {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: var(--white);
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text);
        }

        .card-content {
            padding: 20px 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .card-description {
            color: var(--text-light);
            font-size: 15px;
            margin-bottom: 25px;
            line-height: 1.5;
            flex-grow: 1;
        }

        .card-button {
            display: inline-block;
            padding: 12px 22px;
            background-color: var(--primary);
            color: var(--white);
            border-radius: var(--radius);
            text-decoration: none;
            text-align: center;
            transition: var(--transition);
            font-size: 15px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
        }

        .card-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .card-button.blue {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        }

        .card-button.green {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        }

        .card-button.orange {
            background: linear-gradient(135deg, #d97706, #f59e0b 100%);
        }

        .card-button.teal {
            background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
        }

        .card-button.red {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        }

        @media screen and (max-width: 768px) {
            .sidebar {
                width: 0;
                padding: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block;
            }
            
            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media screen and (max-width: 480px) {
            .quick-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar - Kept unchanged -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon">M</div>
                <span class="logo-text">MFJ Admin</span>
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-section">Main</li>
            <li class="nav-item">
                <a href="/MFJ/admin_dashboard.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-section">Management</li>
            <li class="nav-item">
                <a href="/MFJ/manage_products.php" class="nav-link">
                   <i class="fas fa-box nav-icon"></i>
                    <span class="nav-text">Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/MFJ/manage_services.php" class="nav-link">
                    <i class="fas fa-calendar-alt nav-icon"></i>
                    <span class="nav-text">Appointments</span>
                </a>
            </li>
            <li class="nav-item">
                    <a href="/MFJ/admin_calendar.php" class="nav-link">
                        <i class="fas fa-calendar-alt nav-icon"></i>
                        <span class="nav-text">Calendar</span>
                    </a>
                </li>
            <li class="nav-item">
                <a href="/MFJ/manage_employee.php" class="nav-link">
                    <i class="fas fa-id-card nav-icon"></i>
                    <span class="nav-text">Employees</span>
                </a>
            </li>
            
            <li class="nav-item" style="margin-top: auto;">
                <a href="/MFJ/index.php?logout=true" class="nav-link">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    <span class="nav-text">Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content - Improved -->
    <div class="main-content">
        <div class="header">
            <div>
                <h1 class="welcome-message">Employee Management</h1>
                <div class="date" id="current-date"></div>
            </div>
        </div>
        
        <!-- Quick Stats Section - New -->
       

        <div class="page-title">
            <i class="fas fa-users"></i>
            <h1>Employee Management Dashboard</h1>
        </div>

        <div class="dashboard-cards">
            <!-- Add New Employee Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon green">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="card-title">Add New Employee</h3>
                    </div>
                </div>
                <div class="card-content">
                    <p class="card-description">Quickly add a new employee to the system with all required details including personal information, position, department, and salary.</p>
                    <a href="/MFJ/employee_management.php" class="card-button green">Add Employee</a>
                </div>
            </div>

            <!-- View Employees Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon blue">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div>
                        <h3 class="card-title">View Employees</h3>
                    </div>
                </div>
                <div class="card-content">
                    <p class="card-description">View and manage all employees currently in the system. Search, filter, and export employee information as needed.</p>
                    <a href="/MFJ/employee_list.php" class="card-button blue">View Employees</a>
                </div>
            </div>

            <!-- View Manual Attendance Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon orange">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h3 class="card-title">Manual Attendance</h3>
                    </div>
                </div>
                <div class="card-content">
                    <p class="card-description">Check and manage employee attendance records. Review time entries, approve leaves, and generate attendance reports.</p>
                    <a href="/MFJ/view_attendance.php" class="card-button orange">View Attendance</a>
                </div>
            </div> 

            <!-- Payroll Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon teal">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <h3 class="card-title">Payroll</h3>
                    </div>
                </div>
                <div class="card-content">
                    <p class="card-description">View and manage employee payroll details and payments. Process salaries, bonuses, and deductions for all employees.</p>
                    <a href="/MFJ/payroll.php" class="card-button teal">Manage Payroll</a>
                </div>
            </div>

            <!-- Payroll Records Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon red">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h3 class="card-title">Payroll Records</h3>
                    </div>
                </div>
                <div class="card-content">
                    <p class="card-description">View complete payroll history records for all employees. Generate reports for specific periods or departments.</p>
                    <a href="/MFJ/view_payroll.php" class="card-button red">View Records</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const today = new Date().toLocaleDateString('en-US', options);
        document.getElementById('current-date').textContent = today;
    </script>
</body>
</html>