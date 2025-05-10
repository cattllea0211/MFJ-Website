<?php
// Start session management
session_start();

// For testing purposes - comment this out in production
// This bypasses login to test the calendar functionality
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'TestManager';
    $_SESSION['role'] = 'Manager';
    $_SESSION['id'] = 1;
}

// Check if user is logged in and has manager role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    // Redirect to login page if not logged in or not a manager
    header("Location: login.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'mfjdb';
$username = 'mfj_user';
$password = 'StrongPassword123!';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to get color based on status
function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'completed':
            return '#28C76F'; // Green
        case 'cancelled':
            return '#EA5455'; // Red
        case 'pending':
            return '#FF9F43'; // Orange/Yellow
        case 'confirmed':
            return '#00CFE8'; // Light Blue
        default:
            return '#6E6B7B'; // Gray
    }
}

// Get current manager's information
$managerId = $_SESSION['id'];
$query = "SELECT name FROM employees WHERE id = :managerId";
$stmt = $conn->prepare($query);
$stmt->bindParam(':managerId', $managerId, PDO::PARAM_INT);
$stmt->execute();
$managerResult = $stmt->fetch(PDO::FETCH_ASSOC);
$managerName = $managerResult ? $managerResult['name'] : 'Manager';

// For testing - Create sample data if none exists
// This is for demonstration purposes only - remove in production
$checkData = $conn->query("SELECT COUNT(*) FROM services")->fetchColumn();
if ($checkData == 0) {
    // Create sample services
    $sampleServices = [
        [
            'client_name' => 'John Smith',
            'client_address' => '123 Main St, Anytown',
            'client_contact' => '555-1234',
            'service_type' => 'AC Installation',
            'description' => 'Install new split-type AC unit',
            'scheduled_date' => date('Y-m-d'), // Today
            'scheduled_time' => '10:00:00',
            'status' => 'Confirmed',
            'price' => '15000',
            'duration' => 120
        ],
        [
            'client_name' => 'Sarah Johnson',
            'client_address' => '456 Oak Ave, Somewhere',
            'client_contact' => '555-5678',
            'service_type' => 'AC Repair',
            'description' => 'Fix cooling issue on central unit',
            'scheduled_date' => date('Y-m-d', strtotime('+1 day')), // Tomorrow
            'scheduled_time' => '14:00:00',
            'status' => 'Pending',
            'price' => '5000',
            'duration' => 90
        ],
        [
            'client_name' => 'Mark Davis',
            'client_address' => '789 Pine St, Elsewhere',
            'client_contact' => '555-9101',
            'service_type' => 'AC Maintenance',
            'description' => 'Annual maintenance service',
            'scheduled_date' => date('Y-m-d', strtotime('-1 day')), // Yesterday
            'scheduled_time' => '09:00:00',
            'status' => 'Completed',
            'price' => '2500',
            'duration' => 60
        ]
    ];
    
    $insertQuery = "INSERT INTO services (client_name, client_address, client_contact, service_type, description, scheduled_date, scheduled_time, status, price, duration) VALUES (:client_name, :client_address, :client_contact, :service_type, :description, :scheduled_date, :scheduled_time, :status, :price, :duration)";
    $insertStmt = $conn->prepare($insertQuery);
    
    foreach ($sampleServices as $service) {
        $insertStmt->execute($service);
    }
}

// Fetch all scheduled services
$query = "SELECT s.*, e.name as employee_name
          FROM services s 
          LEFT JOIN service_employees se ON s.id = se.service_id
          LEFT JOIN employees e ON se.employee_id = e.id
          ORDER BY s.scheduled_date, s.scheduled_time";
try {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // If there's an error with the query, create a default array
    $services = [];
    echo "<!-- Error fetching services: " . $e->getMessage() . " -->";
}

// Group services by ID to handle multiple employees per service
$groupedServices = [];
foreach ($services as $service) {
    $serviceId = $service['id'];
    
    if (!isset($groupedServices[$serviceId])) {
        $groupedServices[$serviceId] = $service;
        $groupedServices[$serviceId]['employees'] = [];
    }
    
    if (!empty($service['employee_name'])) {
        $groupedServices[$serviceId]['employees'][] = $service['employee_name'];
    }
}

// Convert services to JSON for JavaScript
$events = [];
foreach ($groupedServices as $service) {
    // Format date and time for FullCalendar
    $dateTime = $service['scheduled_date'] . 'T' . $service['scheduled_time'];
    
    // Calculate end time using the duration field
    $start = new DateTime($dateTime);
    $end = clone $start;
    $duration = isset($service['duration']) ? $service['duration'] : 60; // Default to 60 minutes if not set
    $end->modify("+{$duration} minutes");
    
    // Format employee names for display
    $employeeNames = !empty($service['employees']) ? implode(", ", $service['employees']) : "Unassigned";
    
    $events[] = [
        'id' => $service['id'],
        'title' => $service['service_type'] . ' - ' . $service['client_name'],
        'start' => $dateTime,
        'end' => $end->format('Y-m-d\TH:i:s'),
        'description' => $service['description'],
        'extendedProps' => [
            'customer' => $service['client_name'],
            'address' => $service['client_address'],
            'contact' => $service['client_contact'],
            'service_type' => $service['service_type'],
            'status' => $service['status'],
            'price' => $service['price'],
            'employees' => $employeeNames,
            'duration' => $duration
        ],
        'backgroundColor' => getStatusColor($service['status']),
        'borderColor' => getStatusColor($service['status'])
    ];
}

// Get stats for calendar header
$totalServices = count($groupedServices);
$pendingServices = 0;
$completedServices = 0;
$totalRevenue = 0;

foreach ($groupedServices as $service) {
    $status = strtolower($service['status'] ?? '');
    if ($status === 'pending') {
        $pendingServices++;
    } elseif ($status === 'completed') {
        $completedServices++;
        $totalRevenue += floatval($service['price'] ?? 0);
    }
}

// Encode events to JSON for use in JavaScript
$eventsJson = json_encode($events);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com"></script>

    <title>Service Calendar | MFJ Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #5D5FEF;
            --primary-light: #EAEAFF;
            --secondary-color: #64748B;
            --success-color: #22C55E;
            --info-color: #0EA5E9;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
            --light-color: #F9FAFB;
            --dark-color: #1E293B;
            --border-color: #E2E8F0;
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            --transition: all 0.25s ease;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F1F5F9;
            margin: 0;
            padding: 0;
            color: #334155;
        }
        
        /* Layout */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.03);
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            z-index: 10;
            transition: var(--transition);
            border-right: 1px solid var(--border-color);
        }
        
        .content-wrapper {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            transition: var(--transition);
        }
        
        /* Navbar */
        .navbar {
            background-color: #fff;
            box-shadow: var(--card-shadow);
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            border-radius: 12px;
        }
        
        /* Logo */
        .brand-logo {
            display: flex;
            align-items: center;
            padding: 1.75rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .brand-icon {
            width: 42px;
            height: 42px;
            background-color: var(--primary-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 12px;
        }
        
        .brand-text {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
        }
        
        /* Sidebar Menu */
        .sidebar-menu {
            padding: 1.5rem 0;
            list-style: none;
            margin: 0;
        }
        
        .nav-item {
            padding: 0;
            margin: 8px 0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--secondary-color);
            border-radius: 8px;
            margin: 0 15px;
            transition: var(--transition);
            text-decoration: none;
            font-weight: 500;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 22px;
            text-align: center;
        }
        
        /* Stats Cards */
        .calendar-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .calendar-stat-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }
        
        .calendar-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .total-icon {
            background-color: rgba(93, 95, 239, 0.1);
            color: var(--primary-color);
        }
        
        .pending-icon {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }
        
        .completed-icon {
            background-color: rgba(34, 197, 94, 0.1);
            color: var(--success-color);
        }
        
        .revenue-icon {
            background-color: rgba(14, 165, 233, 0.1);
            color: var(--info-color);
        }
        
        .calendar-stat-card h4 {
            font-size: 1.75rem;
            margin-bottom: 0.25rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .calendar-stat-card p {
            color: var(--secondary-color);
            margin: 0;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        /* Calendar */
        .calendar-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .calendar-title {
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .calendar-title i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        #calendar {
            padding: 1.5rem;
            height: calc(100vh - 320px);
            min-height: 600px;
        }
        
        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .fc .fc-button-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            text-transform: capitalize;
            box-shadow: none !important;
        }
        
        .fc .fc-button-primary:hover {
            background-color: #4B4DDB;
            border-color: #4B4DDB;
        }
        
        .fc .fc-button-primary:not(:disabled).fc-button-active, 
        .fc .fc-button-primary:not(:disabled):active {
            background-color: #4142C2;
            border-color: #4142C2;
        }
        
        .fc .fc-button {
            font-size: 0.875rem;
        }
        
        .fc .fc-toolbar.fc-header-toolbar {
            margin-bottom: 1.5rem;
        }
        
        .fc-theme-standard th {
            border-color: var(--border-color);
            padding: 10px;
        }
        
        .fc-theme-standard td {
            border-color: var(--border-color);
        }
        
        .fc-day-today {
            background-color: var(--primary-light) !important;
        }
        
        .fc-event {
            cursor: pointer;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.8rem;
            font-weight: 500;
            border: none !important;
            margin: 1px;
        }
        
        .fc-event-title {
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .fc-daygrid-event-dot {
            display: none; /* Hide default dot */
        }
        
        .fc-view-harness {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        /* Modal */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: #fff;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 1.25rem 1.5rem;
        }
        
        .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .modal-body {
            padding: 1.75rem;
        }
        
        .modal-footer {
            padding: 1.25rem 1.75rem;
            border-top: 1px solid var(--border-color);
        }
        
        /* Status Badges */
        .badge {
            padding: 0.5rem 0.875rem;
            font-weight: 500;
            font-size: 0.8rem;
            border-radius: 6px;
        }
        
        .badge-completed {
            background-color: rgba(34, 197, 94, 0.1);
            color: var(--success-color);
        }
        
        .badge-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }
        
        .badge-cancelled {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .badge-confirmed {
            background-color: rgba(14, 165, 233, 0.1);
            color: var(--info-color);
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #4B4DDB;
            border-color: #4B4DDB;
        }
        
        .btn-secondary {
            background-color: #E2E8F0;
            border-color: #E2E8F0;
            color: var(--secondary-color);
        }
        
        .btn-secondary:hover {
            background-color: #CBD5E1;
            border-color: #CBD5E1;
            color: var(--dark-color);
        }
        
        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: #fff;
        }
        
        /* User Menu */
        .user-dropdown {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .user-dropdown:hover {
            background-color: var(--light-color);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background-color: var(--primary-color);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 12px;
        }
        
        .user-info {
            line-height: 1.2;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--dark-color);
        }
        
        .user-role {
            font-size: 0.75rem;
            color: var(--secondary-color);
        }
        
        /* Dropdown Menu */
        .dropdown-menu {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
            padding: 0.5rem 0;
        }
        
        .dropdown-item {
            padding: 0.625rem 1.5rem;
            color: var(--secondary-color);
            font-weight: 500;
            transition: var(--transition);
        }
        
        .dropdown-item:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .dropdown-item i {
            margin-right: 10px;
            width: 18px;
        }
        
        /* Modal Details */
        .detail-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .detail-group {
            flex: 1;
        }
        
        .detail-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 4px;
        }
        
        .detail-value {
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .service-type-heading {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        /* View Toggle Buttons */
        .view-toggle {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: inline-flex;
            overflow: hidden;
        }
        
        .view-toggle button {
            background: none;
            border: none;
            padding: 6px 12px;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--secondary-color);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .view-toggle button.active {
            background-color: var(--primary-color);
            color: #fff;
        }
        .sidebar-item {
            transition: all 0.3s ease;
        }
        
        .sidebar-item:hover {
            transform: translateX(5px);
        }
        
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 240px;
            }
            .content-wrapper {
                margin-left: 240px;
            }
            .calendar-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .content-wrapper {
                margin-left: 0;
                padding: 20px;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .calendar-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <aside class="w-72 bg-white shadow-lg h-screen fixed transition-all duration-300" x-data="{ open: true }">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="bg-gradient-to-r from-teal-500 to-emerald-500 text-white w-10 h-10 flex items-center justify-center rounded-lg font-bold shadow-md">
                    <i class="fas fa-wrench"></i>
                </div>
                <span class="ml-3 text-xl font-semibold text-gray-800">MFJ Dashboard</span>
            </div>
        </div>
        
        <div class="px-4 py-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-teal-400 to-blue-400 flex items-center justify-center text-white shadow-md">
                        <span class="text-sm font-medium">AD</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-700">Admin User</p>
                        <p class="text-xs text-gray-500">Manager</p>
                    </div>
                </div>
            </div>
        </div>
        
        <nav class="p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4 ml-4">Main Menu</p>
            <ul class="space-y-2">

                <li class="sidebar-item">
                    <a href="manager_dashboard.php" class="flex items-center px-4 py-3 text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg font-medium group">
                        <span class="w-8 h-8 flex items-center justify-center bg-teal-600 text-white rounded-lg mr-3 group-hover:bg-teal-700 transition-all shadow-md">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <span>Appointments</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="manager_calendar.php" class="flex items-center px-4 py-3 text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg font-medium group">
                        <span class="w-8 h-8 flex items-center justify-center bg-teal-600 text-white rounded-lg mr-3 group-hover:bg-teal-700 transition-all shadow-md">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <span>Calendar</span>
                    </a>
                </li>      
               
                <li class="sidebar-item mt-8">
                    <a href="logout.php" class="flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg group">
                        <span class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-600 rounded-lg mr-3 group-hover:bg-red-600 group-hover:text-white transition-all">
                            <i class="fas fa-sign-out-alt"></i>
                        </span>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

        <!-- Content -->
        <div class="content-wrapper">
            <!-- Navbar -->
            <nav class="navbar">
                <div class="d-flex align-items-center">
                    <button class="btn btn-icon btn-secondary d-lg-none me-3" id="toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="mb-0 fw-semibold">Service Calendar</h5>
                </div>
                <div class="dropdown">
                    <div class="user-dropdown" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <?php echo substr($_SESSION['username'], 0, 1); ?>
                        </div>
                        <div class="user-info d-none d-md-block">
                            <div class="user-name"><?php echo $_SESSION['username']; ?></div>
                            <div class="user-role">Manager</div>
                        </div>
                        <i class="fas fa-chevron-down ms-2"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </nav>

    
            
            <!-- Calendar Card -->
            <div class="calendar-card">
                <div class="calendar-header">
                    <div class="calendar-title">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Scheduled Services</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="view-toggle">
                            <button id="month-view" class="active">Month</button>
                            <button id="week-view">Week</button>
                            <button id="day-view">Day</button>
                            <button id="list-view">List</button>
                        </div>
                        <a href="manager_add_service.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Service
                        </a>
                    </div>
                </div>
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Service Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <h6 class="service-type-heading mb-0" id="modal-service-type"></h6>
                        <span id="modal-status-badge" class="badge"></span>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-group">
                            <span class="detail-label">Client</span>
                            <div class="detail-value" id="modal-customer"></div>
                        </div>
                        <div class="detail-group">
                            <span class="detail-label">Contact</span>
                            <div class="detail-value" id="modal-contact"></div>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-group">
                            <span class="detail-label">Address</span>
                            <div class="detail-value" id="modal-address"></div>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-group">
                            <span class="detail-label">Date & Time</span>
                            <div class="detail-value" id="modal-datetime"></div>
                        </div>
                        <div class="detail-group">
                            <span class="detail-label">Duration</span>
                            <div class="detail-value" id="modal-duration"></div>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-group">
                            <span class="detail-label">Price</span>
                            <div class="detail-value" id="modal-price"></div>
                        </div>
                        <div class="detail-group">
                            <span class="detail-label">Assigned Workers</span>
                            <div class="detail-value" id="modal-employees"></div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <span class="detail-label">Description</span>
                        <div class="detail-value" id="modal-notes"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="edit-event" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Service
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if events exist
        let events = <?php echo $eventsJson ?: '[]'; ?>;
        console.log('Calendar events:', events);
        
        // Initialize Calendar
        const calendarEl = document.getElementById('calendar');
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            themeSystem: 'bootstrap5',
            height: 'auto',
            events: events,
            eventTimeFormat: { 
                hour: '2-digit',
                minute: '2-digit',
                meridiem: true
            },
            eventClick: function(info) {
                // Get event data
                const event = info.event;
                const extProps = event.extendedProps;
                
                // Format date and time
                const startDate = new Date(event.start);
                const formattedDate = startDate.toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                const formattedTime = startDate.toLocaleTimeString('en-US', {
                    hour: '2-digit', 
                    minute: '2-digit'
                });
                
                // Set modal content
                document.getElementById('modal-service-type').textContent = extProps.service_type;
                document.getElementById('modal-customer').textContent = extProps.customer;
                document.getElementById('modal-address').textContent = extProps.address || 'N/A';
                document.getElementById('modal-contact').textContent = extProps.contact || 'N/A';
                document.getElementById('modal-datetime').textContent = `${formattedDate} at ${formattedTime}`;
                document.getElementById('modal-duration').textContent = `${extProps.duration} minutes`;
                document.getElementById('modal-price').textContent = `₱ ${extProps.price || '0.00'}`;
                document.getElementById('modal-employees').textContent = extProps.employees;
                document.getElementById('modal-notes').textContent = extProps.description || 'No description provided';
                
                // Set status badge
                const statusBadge = document.getElementById('modal-status-badge');
                statusBadge.textContent = extProps.status;
                statusBadge.className = 'badge';
                
                switch(extProps.status.toLowerCase()) {
                    case 'completed':
                        statusBadge.classList.add('badge-completed');
                        break;
                    case 'cancelled':
                        statusBadge.classList.add('badge-cancelled');
                        break;
                    case 'pending':
                        statusBadge.classList.add('badge-pending');
                        break;
                    case 'confirmed':
                        statusBadge.classList.add('badge-confirmed');
                        break;
                    default:
                        statusBadge.classList.add('bg-secondary');
                }
                
                // Set edit link
                document.getElementById('edit-event').href = 'edit_service.php?id=' + event.id;

// Show the modal
const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
eventModal.show();
},
            eventDidMount: function(info) {
                // Add tooltip to events
                const tooltip = new bootstrap.Tooltip(info.el, {
                    title: info.event.extendedProps.service_type + ' - ' + info.event.extendedProps.customer,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            }
        });
        
        calendar.render();
        
        // Toggle sidebar functionality for mobile devices
        const toggleSidebar = document.getElementById('toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');
        const contentWrapper = document.querySelector('.content-wrapper');
        
        if (toggleSidebar) {
            toggleSidebar.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                
                if (sidebar.classList.contains('show')) {
                    contentWrapper.style.marginLeft = '230px';
                } else {
                    contentWrapper.style.marginLeft = '0';
                }
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggleButton = toggleSidebar && toggleSidebar.contains(event.target);
            
            if (window.innerWidth < 768 && !isClickInsideSidebar && !isClickOnToggleButton && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                contentWrapper.style.marginLeft = '0';
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                contentWrapper.style.marginLeft = '230px';
            } else if (!sidebar.classList.contains('show')) {
                contentWrapper.style.marginLeft = '0';
            }
        });
    });
    </script>
</body>
</html>
