<?php    


if (file_exists('/MFJ/logout_session_management.php')) {
    require_once '/MFJ/logout_session_management.php';
} else {
   
    error_log('Logout session management file not found');
}

$servername = "localhost";
$username = "mfj_user"; 
$password = "StrongPassword123!"; 
$dbname = "mfjdb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT COUNT(*) AS total_products FROM products";
    $result = $conn->query($sql);
    $totalProducts = $result->num_rows > 0 ? $result->fetch_assoc()['total_products'] : 0;

// Fetch total services
    $sql = "SELECT COUNT(*) AS total_services FROM services"; 
    $resultServices = $conn->query($sql); 
    $totalServices = $resultServices->num_rows > 0 ? $resultServices->fetch_assoc()['total_services'] : 0; 



// Fetch service details with employees
// Fetch service details with employees (UPDATED QUERY)
$sql = "SELECT s.id AS service_id, s.service_type, s.duration, s.price, e.name 
        FROM services s
        LEFT JOIN service_employees se ON s.id = se.service_id
        LEFT JOIN employees e ON se.employee_id = e.id
        ORDER BY s.service_type";

$resultServicesEmployees = $conn->query($sql);

if (!$resultServicesEmployees) {
    die("Query failed: " . $conn->error);
}

$servicesEmployees = [];
if ($resultServicesEmployees->num_rows > 0) {
    while ($row = $resultServicesEmployees->fetch_assoc()) {
        $serviceId = $row['service_id'];
        $serviceType = $row['service_type'];
        
        // Initialize service if not exists
        if (!isset($servicesEmployees[$serviceId])) {
            $servicesEmployees[$serviceId] = [
                'service_type' => $serviceType,
                'duration' => $row['duration'],
                'price' => $row['price'],
                'employees' => []
            ];
        }
        
        // Add employee if exists (LEFT JOIN might return NULL)
        if (!empty($row['name'])) {
            $servicesEmployees[$serviceId]['employees'][] = $row['name'];
        }
    }
}

// Fetch product counts by category
$sql = "SELECT category, COUNT(*) AS product_count FROM products GROUP BY category";
$resultCategories = $conn->query($sql);

$categories = [];
$productCounts = [];

if ($resultCategories->num_rows > 0) {
    while ($row = $resultCategories->fetch_assoc()) {
        $categories[] = $row['category'];
        $productCounts[] = $row['product_count'];
    }
} else {
    // No categories found
    $categories = [];
    $productCounts = [];
}


    $sql = "SELECT SUM(stock) AS total_stock FROM products"; // Updated to 'stock'
    $resultStock = $conn->query($sql); // Use a different variable for stock count
    $totalStock = $resultStock->num_rows > 0 ? $resultStock->fetch_assoc()['total_stock'] : 0;

    $sqlCompleted = "SELECT COUNT(*) AS completed FROM services WHERE status = 'completed'";
$resultCompleted = $conn->query($sqlCompleted);
$completedServices = $resultCompleted->num_rows > 0 ? $resultCompleted->fetch_assoc()['completed'] : 0;

// Count pending services (before line 589)
$sqlPending = "SELECT COUNT(*) AS pending FROM services WHERE status = 'pending'";
$resultPending = $conn->query($sqlPending);
$pendingServices = $resultPending->num_rows > 0 ? $resultPending->fetch_assoc()['pending'] : 0;

// Calculate total revenue (before line 606)
$sqlRevenue = "SELECT SUM(price) AS total FROM services WHERE status = 'completed'";
// Alternatively, if revenue comes from another table:
// $sqlRevenue = "SELECT SUM(amount) AS total FROM transactions";
$resultRevenue = $conn->query($sqlRevenue);
$totalRevenue = $resultRevenue->num_rows > 0 ? $resultRevenue->fetch_assoc()['total'] : 0;

    $conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFJ Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
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
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text);
            background-color: var(--light-bg);
            line-height: 1.6;
            font-size: 15px;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

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

        .main-content {
            flex: 1;
            padding: 32px;
            margin-left: 280px;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        #currentDate {
            font-size: 15px;
            color: var(--text-light);
            background-color: var(--secondary);
            padding: 8px 16px;
            border-radius: var(--radius);
            font-weight: 500;
        }

        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 50px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card:nth-child(1)::before {
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .stat-card:nth-child(2)::before {
            background: linear-gradient(90deg, var(--success) 0%, #059669 100%);
        }

        .stat-card:nth-child(3)::before {
            background: linear-gradient(90deg, var(--accent) 0%, #0284c7 100%);
        }

        .stat-card:nth-child(4)::before {
            background: linear-gradient(90deg, var(--warning) 0%, #d97706 100%);
        }

        .stat-card:nth-child(5)::before {
            background: linear-gradient(90deg, var(--danger) 0%, #b91c1c 100%);
        }

        .stat-card:nth-child(6)::before {
            background: linear-gradient(90deg, #8b5cf6 0%, #6d28d9 100%);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-title {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text);
            line-height: 1.2;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: white;
            font-size: 18px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-footer {
            margin-top: 16px;
        }

        .stat-footer a {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            padding: 8px 0;
        }

        .stat-footer a:hover {
            gap: 12px;
        }

        .stat-icon.products {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .stat-icon.services {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        }

        .stat-icon.stock {
            background: linear-gradient(135deg, var(--accent) 0%, #0284c7 100%);
        }

        .stat-icon.completed {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        }

        .stat-icon.pending {
            background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);
        }

        .stat-icon.revenue {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        }

        .grid-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        @media (min-width: 1024px) {
            .grid-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        .panel {
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .panel:hover {
            box-shadow: var(--shadow-lg);
        }

        .panel-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--white);
        }

        .panel-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-title i {
            color: var(--primary);
            font-size: 16px;
        }

        .panel-body {
            padding: 24px;
        }

        .panel-body.no-padding {
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 16px 24px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            font-weight: 600;
            color: var(--text);
            background-color: var(--secondary);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr:hover {
            background-color: rgba(37, 99, 235, 0.05);
        }

        td {
            color: var(--text-light);
            font-weight: 500;
        }

        .chart-container {
            height: 400px;
            width: 100%;
            position: relative;
        }

        canvas {
            max-width: 100%;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stat-cards {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 20px 0;
            }

            .logo-text, .nav-text, .nav-section {
                display: none;
            }

            .nav-link {
                justify-content: center;
                padding: 16px;
                margin-right: 0;
                border-radius: 0;
                border-left: none;
            }

            .nav-link.active {
                border-left: none;
                border-right: 3px solid var(--primary);
            }

            .nav-icon {
                margin-right: 0;
                font-size: 18px;
            }

            .sidebar-header {
                padding: 0 16px 16px;
                display: flex;
                justify-content: center;
            }

            .main-content {
                margin-left: 80px;
                padding: 24px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 16px;
            }
            
            .stat-cards {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            th, td {
                padding: 12px 16px;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .content-header {
            animation: fadeIn 0.5s ease forwards;
        }

        .stat-cards {
            animation: fadeIn 0.6s ease forwards;
        }

        .stat-card {
            animation: slideUp 0.5s ease forwards;
            opacity: 0;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(5) { animation-delay: 0.5s; }
        .stat-card:nth-child(6) { animation-delay: 0.6s; }

        .panel {
            animation: fadeIn 0.7s ease forwards;
            animation-delay: 0.7s;
            opacity: 0;
        }
    </style>
</head>
<body>
    <div class="dashboard">
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

        <main class="main-content">
            <div class="content-header">
                <h1 class="page-title">
                    <i class="fas fa-home" style="color: var(--primary); font-size: 24px;"></i>
                    Welcome, Admin!
                </h1>
                <div class="header-actions">
                    <span id="currentDate"></span>
                </div>
            </div>

            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Total Products</div>
                            <div class="stat-value"><?php echo $totalProducts; ?></div>
                        </div>
                        <div class="stat-icon products">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="/MFJ/manage_products.php" style="color: var(--primary); text-decoration: none; font-size: 14px;">
                            View details <i class="fas fa-arrow-right" style="font-size: 12px;"></i>
                        </a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Total Services</div>
                            <div class="stat-value"><?php echo $totalServices; ?></div>
                        </div>
                        <div class="stat-icon services">
                            <i class="fas fa-concierge-bell"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="/MFJ/manage_services.php" style="color: var(--success); text-decoration: none; font-size: 14px;">
                            View details <i class="fas fa-arrow-right" style="font-size: 12px;"></i>
                        </a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Total Stock</div>
                            <div class="stat-value"><?php echo $totalStock; ?></div>
                        </div>
                        <div class="stat-icon stock">
                            <i class="fas fa-warehouse"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="/MFJ/manage_products.php" style="color: var(--accent); text-decoration: none; font-size: 14px;">
                            View details <i class="fas fa-arrow-right" style="font-size: 12px;"></i>
                        </a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Completed Services</div>
                            <div class="stat-value"><?php echo $completedServices; ?></div>
                        </div>
                        <div class="stat-icon completed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="/MFJ/manage_services.php" style="color: var(--warning); text-decoration: none; font-size: 14px;">
                            View details <i class="fas fa-arrow-right" style="font-size: 12px;"></i>
                        </a>
                    </div>
                </div>
        
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Pending Services</div>
                            <div class="stat-value"><?php echo $pendingServices; ?></div>
                        </div>
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="/MFJ/manage_services.php" style="color: var(--danger); text-decoration: none; font-size: 14px;">
                            View details <i class="fas fa-arrow-right" style="font-size: 12px;"></i>
                        </a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Total Revenue</div>
                            <div class="stat-value">₱<?php echo number_format($totalRevenue, 2) ?></div>
                        </div>
                        <div class="stat-icon revenue">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="/MFJ/financial_reports.php" style="color: #8b5cf6; text-decoration: none; font-size: 14px;">
                            View details <i class="fas fa-arrow-right" style="font-size: 12px;"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid-row">
                <div class="panel">
                    <div class="panel-header">
                        <h2 class="panel-title">
                            <i class="fas fa-list-alt"></i>
                            Service Details
                        </h2>
                    </div>
                    <div class="panel-body no-padding">
                        <table>
                            <thead>
                                <tr>
                                    <th>Service Type</th>
                                    <th>Employees</th>
                                    <th>Duration</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($servicesEmployees)): ?>
                                    <?php foreach ($servicesEmployees as $service): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($service['service_type']) ?></td>
                                            <td>
                                                <?= !empty($service['employees']) 
                                                    ? htmlspecialchars(implode(", ", $service['employees'])) 
                                                    : 'No employees assigned' ?>
                                            </td>
                                            <td><?= htmlspecialchars($service['duration']) ?> Hours</td>
                                            <td>₱ <?= htmlspecialchars($service['price']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan='4'>No services found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h2 class="panel-title">
                            <i class="fas fa-chart-bar"></i>
                            Products by Category
                        </h2>
                    </div>
                    <div class="panel-body">
                        <div class="chart-container">
                            <canvas id="productCategoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Display current date
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', options);

        // Product category chart
        const ctx = document.getElementById('productCategoryChart').getContext('2d');
        const productCategoryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($categories); ?>,
                datasets: [{
                    label: 'Product Count',
                    data: <?php echo json_encode($productCounts); ?>,
                    backgroundColor: [
                        'rgba(37, 99, 235, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(14, 165, 233, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                        'rgba(20, 184, 166, 0.7)',
                        'rgba(236, 72, 153, 0.7)'
                    ],
                    borderColor: [
                        'rgba(37, 99, 235, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(14, 165, 233, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(139, 92, 246, 1)',
                        'rgba(20, 184, 166, 1)',
                        'rgba(236, 72, 153, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        padding: 12,
                        bodyFont: {
                            size: 14
                        },
                        titleFont: {
                            size: 16,
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
    </script>
</body>
</html>
