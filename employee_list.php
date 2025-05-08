<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "mfjdb"; 

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_employee'])) {
    // Sanitize and validate input data
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $birthdate = filter_var($_POST['birthdate'], FILTER_SANITIZE_STRING);
    $age = filter_var($_POST['age'], FILTER_SANITIZE_NUMBER_INT);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $emergency_contact = filter_var($_POST['emergency_contact'], FILTER_SANITIZE_STRING);
    $roles = filter_var($_POST['roles'], FILTER_SANITIZE_STRING);
    $rate_per_day = filter_var($_POST['rate_per_day'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $pagibig_no = filter_var($_POST['pagibig_no'], FILTER_SANITIZE_STRING);
    $philhealth_no = filter_var($_POST['philhealth_no'], FILTER_SANITIZE_STRING);
    $sss_no = filter_var($_POST['sss_no'], FILTER_SANITIZE_STRING);

    $update_sql = "UPDATE employees SET 
        name=?, phone=?, email=?, birthdate=?, age=?, address=?, 
        emergency_contact_person=?, roles=?, rate_per_day=?, 
        pagibig_no=?, philhealth_no=?, sss_no=? 
        WHERE id=?";
    
    if ($stmt = $conn->prepare($update_sql)) {
        $stmt->bind_param("ssssisssdsssi", 
            $name, $phone, $email, $birthdate, $age, $address, 
            $emergency_contact, $roles, $rate_per_day, 
            $pagibig_no, $philhealth_no, $sss_no, $id);
        
        try {
            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $stmt->error]);
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        $stmt->close();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement']);
    }
    exit;
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM employees WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: /MFJ/employee_list.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}


$sql = "SELECT * FROM employees";
$result = $conn->query($sql);

$employees = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // 10 employees per page
$offset = ($page - 1) * $limit;


$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';


$base_query = "FROM employees WHERE 1=1";
if (!empty($search)) {
    $base_query .= " AND (
    name LIKE '%$search%' OR 
    email LIKE '%$search%' OR 
    phone LIKE '%$search%' OR 
    roles LIKE '%$search%' OR 
    address LIKE '%$search%'
)";

}


$count_query = "SELECT COUNT(*) as total " . $base_query;
$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$sql = "SELECT * " . $base_query . " LIMIT $offset, $limit";
$result = $conn->query($sql);


$employees = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List | MFJ Admin</title>
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
            background-color: #f5f6fa;
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

      /* Sidebar - kept as requested */
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

        /* Main Content */
        .main-content {
            margin-left: 290px;
            width: 2000px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--secondary-dark);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .breadcrumb i {
            margin-right: 5px;
        }

        /* Cards */
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            padding: 15px 20px;
            background-color: white;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--secondary-dark);
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        /* Search and Actions Bar */
        .search-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background-color: white;
            border-radius: 6px;
            padding: 5px 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            width: 300px;
        }

        .search-box input {
            border: none;
            padding: 8px;
            flex-grow: 1;
            outline: none;
            font-size: 14px;
        }

        .search-box button {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--secondary);
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .view-toggle {
            display: flex;
            background-color: white;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .view-toggle button {
            border: none;
            padding: 8px 15px;
            background-color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .view-toggle button i {
            margin-right: 5px;
        }

        .view-toggle button.active {
            background-color: var(--primary);
            color: white;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: var(--secondary-dark);
            font-size: 14px;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background-color: #f5f9ff;
        }

        .avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary);
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-sm {
            padding: 4px 10px;
            font-size: 12px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        /* Card View */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .employee-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .employee-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .employee-header {
            padding: 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border);
        }

        .employee-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--secondary);
        }

        .employee-info h4 {
            font-size: 18px;
            margin-bottom: 5px;
            color: var(--secondary-dark);
        }

        .employee-info p {
            font-size: 14px;
            color: #777;
            margin: 0;
        }

        .employee-details {
            padding: 15px 20px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .detail-item i {
            width: 16px;
            margin-right: 10px;
            color: var(--secondary);
        }

        .employee-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination-item {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 5px;
            border-radius: 4px;
            background-color: white;
            color: var(--secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .pagination-item.active {
            background-color: var(--primary);
            color: white;
        }

        .pagination-item:hover:not(.active) {
            background-color: #f5f5f5;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            width: 70%;
            max-width: 900px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: modalopen 0.3s;
        }

        @keyframes modalopen {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 20px;
            color: var(--secondary-dark);
            margin: 0;
        }

        .modal-close {
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            color: var(--danger);
        }

        .modal-body {
            padding: 20px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .form-group {
            padding: 0 10px;
            margin-bottom: 15px;
            flex: 0 0 33.333%;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: var(--secondary-dark);
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        /* Hidden initially */
        #cardView {
            display: none;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .form-group {
                flex: 0 0 50%;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
                padding: 20px 0;
            }
            
            .sidebar-brand h2 {
                display: none;
            }
            
            .sidebar-menu a span {
                display: none;
            }
            
            .sidebar-menu a i {
                margin-right: 0;
            }
            
            .main-content {
                margin-left: 60px;
                width: calc(100% - 60px);
            }
            
            .search-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .search-box {
                width: 100%;
            }
            
            .card-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group {
                flex: 0 0 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
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
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="breadcrumb">
                <a href="/MFJ/admin_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>
            <div class="page-title">Employee List</div>
        </div>

        <!-- Search and Actions Bar -->
        <div class="search-actions">
            <form class="search-box" method="GET" action="">
                <input type="text" name="search" placeholder="Search employees..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            <div class="actions">
                <div class="view-toggle">
                    <button type="button" id="tableViewBtn" class="active" onclick="toggleView('table')">
                        <i class="fas fa-list"></i> Table View
                    </button>
                    <button type="button" id="cardViewBtn" onclick="toggleView('card')">
                        <i class="fas fa-th-large"></i> Card View
                    </button>
                </div>
            </div>
        </div>

        <!-- Employee List Card -->
        <div class="card">
            <div class="card-header">
                <h3>Employees</h3>
                <a href="/MFJ/employee_management.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Employee</a>
            </div>
            
            <!-- Table View -->
            <div id="tableView" class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Contact</th>
                            <th>Position</th>
                            <th>Location</th>
                            <th>Rate/Day</th>
                            <th>ID Numbers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <?php if (!empty($employee['picture'])): ?>
                                    <div class="avatar">
                                        <img src="<?php echo htmlspecialchars($employee['picture']); ?>" alt="<?php echo htmlspecialchars($employee['name']); ?>" width="35" height="35">
                                    </div>
                                    <?php else: ?>
                                    <div class="avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div style="margin-left: 10px;">
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($employee['name']); ?></div>
                                        <div style="font-size: 12px; color: #777;"><?php echo htmlspecialchars($employee['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($employee['phone']); ?></td>
<td><?php echo isset($employee['roles']) ? htmlspecialchars($employee['roles']) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($employee['address']); ?></td>
                            <td>₱<?php echo htmlspecialchars($employee['rate_per_day']); ?></td>
                            <td>
                                <div style="font-size: 12px;">
                                    <div>SSS: <?php echo htmlspecialchars($employee['sss_no']); ?></div>
                                    <div>PAGIBIG: <?php echo htmlspecialchars($employee['pagibig_no']); ?></div>
                                    <div>PhilHealth: <?php echo htmlspecialchars($employee['philhealth_no']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button type="button" class="btn btn-sm btn-primary edit-btn" data-id="<?php echo $employee['id']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="employee_list.php?delete_id=<?php echo $employee['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this employee?');">
                                       <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Card View -->
            <div id="cardView" class="card-grid card-body">
                <?php foreach ($employees as $employee): ?>
                <div class="employee-card">
                    <div class="employee-header">
                        <?php if (!empty($employee['picture'])): ?>
                        <div class="employee-avatar">
                            <img src="<?php echo htmlspecialchars($employee['picture']); ?>" alt="<?php echo htmlspecialchars($employee['name']); ?>" width="60" height="60">
                        </div>
                        <?php else: ?>
                        <div class="employee-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <?php endif; ?>
                        <div class="employee-info">
                            <h4><?php echo htmlspecialchars($employee['name']); ?></h4>
<p><?php echo isset($employee['roles']) ? htmlspecialchars($employee['roles']) : 'N/A'; ?></p>
                        </div>
                    </div>
                    <div class="employee-details">
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($employee['phone']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($employee['email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($employee['address']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-birthday-cake"></i>
                            <span><?php echo htmlspecialchars($employee['birthdate']); ?> (<?php echo htmlspecialchars($employee['age']); ?> years)</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-dollar-sign"></i>
                            <span>₱<?php echo htmlspecialchars($employee['rate_per_day']); ?> per day</span>
                        </div>
                    </div>
                    <div class="employee-footer">
                        <button type="button" class="btn btn-primary edit-btn" data-id="<?php echo $employee['id']; ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="/MFJ/employee_list.php?delete_id=<?php echo $employee['id']; ?>" 
                            class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to delete this employee?');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search ?? ''); ?>" class="pagination-item">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search ?? ''); ?>" 
                   class="pagination-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search ?? ''); ?>" class="pagination-item">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Employee</h2>
                <span class="modal-close">&times;</span>
            </div>
            <form id="editEmployeeForm">
                <input type="hidden" id="employee_id" name="id">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Contact Number</label>
                            <input type="text" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="birthdate">Birth Date</label>
                            <input type="date" id="birthdate" name="birthdate" required>
                        </div>
                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                        <div class="form-group">
                            <label for="emergency_contact">Emergency Contact</label>
                            <input type="text" id="emergency_contact" name="emergency_contact" required>
                        </div>
                        <div class="form-group">
                            <label for="roles">Position</label>
                            <input type="text" id="roles" name="roles" required>
                        </div>
                        <div class="form-group">
                            <label for="rate_per_day">Rate per Day (₱)</label>
                            <input type="number" id="rate_per_day" name="rate_per_day" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="pagibig_no">PAGIBIG Number</label>
                            <input type="text" id="pagibig_no" name="pagibig_no" required>
                        </div>
                        <div class="form-group">
                            <label for="philhealth_no">PhilHealth Number</label>
                            <input type="text" id="philhealth_no" name="philhealth_no" required>
                        </div>
                        <div class="form-group">
                            <label for="sss_no">SSS Number</label>
                            <input type="text" id="sss_no" name="sss_no" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // View Toggle Function
        function toggleView(view) {
            const tableView = document.getElementById('tableView');
            const cardView = document.getElementById('cardView');
            const tableBtn = document.getElementById('tableViewBtn');
            const cardBtn = document.getElementById('cardViewBtn');
            
            if (view === 'table') {
                tableView.style.display = 'block';
                cardView.style.display = 'none';
                tableBtn.classList.add('active');
                cardBtn.classList.remove('active');
            } else {
                tableView.style.display = 'none';
                cardView.style.display = 'grid';
                cardBtn.classList.add('active');
                tableBtn.classList.remove('active');
            }
            
            // Save preference
            localStorage.setItem('employeeViewPreference', view);
        }
        
        // Modal Functions
       // Modal Functions
const modal = document.getElementById('editModal');
const closeBtn = document.querySelector('.modal-close');
const editForm = document.getElementById('editEmployeeForm');

// Open Modal with Employee Data
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        const employeeId = this.getAttribute('data-id');
        fetchEmployeeData(employeeId);
        modal.style.display = 'block';
    });
});

// Close Modal
function closeModal() {
    modal.style.display = 'none';
}

closeBtn.addEventListener('click', closeModal);

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target === modal) {
        closeModal();
    }
});

// Fetch Employee Data
function fetchEmployeeData(id) {
    // AJAX request to get employee data
    fetch(`/MFJ/get_employee.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            // Populate form with employee data
            document.getElementById('employee_id').value = data.id;
            document.getElementById('name').value = data.name;
            document.getElementById('phone').value = data.phone;
            document.getElementById('email').value = data.email;
            document.getElementById('birthdate').value = data.birthdate;
            document.getElementById('age').value = data.age;
            document.getElementById('address').value = data.address;
            document.getElementById('emergency_contact').value = data.emergency_contact;
            document.getElementById('roles').value = data.roles;
            document.getElementById('rate_per_day').value = data.rate_per_day;
            document.getElementById('pagibig_no').value = data.pagibig_no;
            document.getElementById('philhealth_no').value = data.philhealth_no;
            document.getElementById('sss_no').value = data.sss_no;
        })
        .catch(error => {
            console.error('Error fetching employee data:', error);
            alert('Failed to load employee data. Please try again.');
        });
}

// Handle form submission
editForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(editForm);
    
    // Send AJAX request to update employee
    fetch('/MFJ/update_employee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Employee updated successfully!');
            closeModal();
            // Reload page to show updated data
            window.location.reload();
        } else {
            alert('Failed to update employee: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error updating employee:', error);
        alert('An error occurred while updating the employee.');
    });
});

// Load saved view preference
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('employeeViewPreference');
    if (savedView) {
        toggleView(savedView);
    }
    
    // Calculate age automatically when birthdate changes
    const birthdateInput = document.getElementById('birthdate');
    const ageInput = document.getElementById('age');
    
    birthdateInput.addEventListener('change', function() {
        const birthdate = new Date(this.value);
        const today = new Date();
        let age = today.getFullYear() - birthdate.getFullYear();
        const monthDiff = today.getMonth() - birthdate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
            age--;
        }
        
        ageInput.value = age;
    });
});
    </script>
</body>
</html>

<?php $conn->close(); ?>

