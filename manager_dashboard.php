<?php
// Start session management
session_start();

// Check if user is logged in and has manager role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    // Redirect to login page if not logged in or not a manager
    header("Location: employee_login.php");
    exit();
}

// Database connection
$connection = new mysqli("localhost", "mfj_user", "StrongPassword123!", "mfjdb");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Handle service deletion
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $connection->query("DELETE FROM services WHERE id = $id");
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

// Calculate dashboard metrics
$totalServicesQuery = "SELECT COUNT(*) as total FROM services";
$completedServicesQuery = "SELECT COUNT(*) as completed FROM services WHERE status = 'Completed'";
$pendingServicesQuery = "SELECT COUNT(*) as pending FROM services WHERE status = 'Pending'";
$totalRevenueQuery = "SELECT SUM(price) as revenue FROM services WHERE status = 'Completed'";

$totalServices = $connection->query($totalServicesQuery)->fetch_assoc()['total'];
$completedServices = $connection->query($completedServicesQuery)->fetch_assoc()['completed'];
$pendingServices = $connection->query($pendingServicesQuery)->fetch_assoc()['pending'];
$totalRevenue = $connection->query($totalRevenueQuery)->fetch_assoc()['revenue'] ?: 0;

// Get services with advanced search/filter
$search = isset($_GET['search']) ? $connection->real_escape_string($_GET['search']) : '';
$status = isset($_GET['status']) ? $connection->real_escape_string($_GET['status']) : '';
$clientType = isset($_GET['client_type']) ? $connection->real_escape_string($_GET['client_type']) : '';
$dateFrom = isset($_GET['date_from']) ? $connection->real_escape_string($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? $connection->real_escape_string($_GET['date_to']) : '';
$sort = isset($_GET['sort']) ? $connection->real_escape_string($_GET['sort']) : 'date_desc';

// Build the WHERE clause for filtering
$whereClause = [];
$queryParams = [];

if (!empty($search)) {
    $whereClause[] = "(service_type LIKE ? OR client_type LIKE ? OR client_name LIKE ? OR description LIKE ?)";
    $searchTerm = "%$search%";
    $queryParams[] = $searchTerm;
    $queryParams[] = $searchTerm;
    $queryParams[] = $searchTerm;
    $queryParams[] = $searchTerm;
}

if (!empty($status)) {
    $whereClause[] = "status = ?";
    $queryParams[] = $status;
}

if (!empty($clientType)) {
    $whereClause[] = "client_type = ?";
    $queryParams[] = $clientType;
}

if (!empty($dateFrom)) {
    $whereClause[] = "scheduled_date >= ?";
    $queryParams[] = $dateFrom;
}

if (!empty($dateTo)) {
    $whereClause[] = "scheduled_date <= ?";
    $queryParams[] = $dateTo;
}

// Combine all filter conditions
$where = '';
if (!empty($whereClause)) {
    $where = "WHERE " . implode(" AND ", $whereClause);
}

// Determine the ORDER BY clause based on sort parameter
$orderBy = "ORDER BY scheduled_date DESC"; // Default sorting
switch ($sort) {
    case 'date_asc':
        $orderBy = "ORDER BY scheduled_date ASC";
        break;
    case 'date_desc':
        $orderBy = "ORDER BY scheduled_date DESC";
        break;
    case 'price_asc':
        $orderBy = "ORDER BY price ASC";
        break;
    case 'price_desc':
        $orderBy = "ORDER BY price DESC";
        break;
    case 'status':
        $orderBy = "ORDER BY status ASC";
        break;
    case 'client_type':
        $orderBy = "ORDER BY client_type ASC";
        break;
}

// Prepare the query
$query = "SELECT * FROM services $where $orderBy";
$stmt = $connection->prepare($query);

// Bind parameters if there are any
if (!empty($queryParams)) {
    $types = str_repeat('s', count($queryParams)); // Assuming all parameters are strings
    $stmt->bind_param($types, ...$queryParams);
}

$stmt->execute();
$result = $stmt->get_result();

// Calculate total number of pages for pagination
$totalResults = $result->num_rows;
$resultsPerPage = 10;
$totalPages = ceil($totalResults / $resultsPerPage);
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$startFrom = ($currentPage - 1) * $resultsPerPage;

// Update query with pagination
$query .= " LIMIT ?, ?";
$stmt = $connection->prepare($query);

// Bind all parameters including pagination
if (!empty($queryParams)) {
    $queryParams[] = $startFrom;
    $queryParams[] = $resultsPerPage;
    $types = str_repeat('s', count($queryParams) - 2) . 'ii'; // Assuming all parameters are strings except pagination
    $stmt->bind_param($types, ...$queryParams);
} else {
    $stmt->bind_param('ii', $startFrom, $resultsPerPage);
}

$stmt->execute();
$result = $stmt->get_result();

// Get employee assignments for services
function getAssignedEmployees($serviceId, $connection) {
    $assignmentsQuery = "SELECT e.id, e.name FROM employees e 
                         JOIN service_employees se ON e.id = se.employee_id 
                         WHERE se.service_id = ?";
    $stmt = $connection->prepare($assignmentsQuery);
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $assignmentsResult = $stmt->get_result();
    
    $employees = [];
    if ($assignmentsResult && $assignmentsResult->num_rows > 0) {
        while ($employee = $assignmentsResult->fetch_assoc()) {
            $employees[] = $employee;
        }
    }
    
    return $employees;
}

// Get current manager's information
$managerId = $_SESSION['id'];
$managerQuery = "SELECT name FROM employees WHERE id = ?";
$stmt = $connection->prepare($managerQuery);
$stmt->bind_param("i", $managerId);
$stmt->execute();
$managerResult = $stmt->get_result();
$managerName = ($managerResult && $managerResult->num_rows > 0) ? $managerResult->fetch_assoc()['name'] : 'Manager';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFJ Services Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.12.0/cdn.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .sidebar-item {
            transition: all 0.3s ease;
        }
        
        .sidebar-item:hover {
            transform: translateX(5px);
        }
        
        .status-badge {
            transition: all 0.3s ease;
        }
        
        .status-badge:hover {
            transform: scale(1.05);
        }
        
        .search-input {
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.2);
        }
        
        .table-row {
            transition: all 0.2s ease;
        }
        
        .table-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .action-button {
            transition: all 0.2s ease;
        }
        
        .action-button:hover {
            transform: scale(1.05);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .notification-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
        }

        .action-button {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .action-button:active {
            transform: translateY(0) scale(0.95);
        }

        /* Tooltips */
        .action-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-bottom: 8px;
            background-color: #1f2937;
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 4px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            pointer-events: none;
            z-index: 10;
        }

        .action-tooltip:after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #1f2937 transparent transparent transparent;
        }

        .action-button:hover .action-tooltip {
            opacity: 1;
            visibility: visible;
        }

        /* Delete Modal */
        .delete-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }

        .delete-modal-content {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            max-width: 28rem;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Modal animations */
        .modal-enter {
            animation: modal-fade-in 0.3s forwards;
        }

        .modal-leave {
            animation: modal-fade-out 0.3s forwards;
        }

        @keyframes modal-fade-in {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes modal-fade-out {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.95); }
        }

        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .transition-all {
          transition: all 0.2s ease-in-out;
        }
        .hover\:shadow-md:hover {
          box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen">

    <!-- Sidebar -->
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
                    <a href="#" class="flex items-center px-4 py-3 text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg font-medium group">
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

    <!-- Main Content -->
    <main class="flex-1 ml-72 p-8" x-data="{ showNotification: false }">
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Services Management</h1>
                <p class="text-gray-500 mt-1">Manage and monitor all service appointments</p>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex space-x-3">
                <div class="relative">
                    <button @click="showNotification = !showNotification" class="p-2 bg-white rounded-full text-gray-600 hover:text-teal-600 shadow-sm hover:shadow transition-all">
                        <i class="fas fa-bell"></i>
                        <span class="notification-dot"></span>
                    </button>
                    
                    <div x-show="showNotification" x-transition @click.away="showNotification = false" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg overflow-hidden z-10 animate-fadeIn" style="display: none;">
                        <div class="p-3 bg-teal-600 text-white">
                            <h3 class="font-medium">Notifications</h3>
                        </div>
                        <div class="p-4 max-h-96 overflow-y-auto">
                            <?php
                            // Recent notifications (most recent services)
                            $notificationsQuery = "SELECT * FROM services ORDER BY created_at DESC LIMIT 5";
                            $notificationsResult = $connection->query($notificationsQuery);
                            
                            if ($notificationsResult && $notificationsResult->num_rows > 0) {
                                while ($notification = $notificationsResult->fetch_assoc()) {
                                    $timeAgo = time() - strtotime($notification['created_at']);
                                    $timeAgoStr = '';
                                    
                                    if ($timeAgo < 60) {
                                        $timeAgoStr = $timeAgo . ' seconds ago';
                                    } elseif ($timeAgo < 3600) {
                                        $timeAgoStr = floor($timeAgo / 60) . ' minutes ago';
                                    } elseif ($timeAgo < 86400) {
                                        $timeAgoStr = floor($timeAgo / 3600) . ' hours ago';
                                    } else {
                                        $timeAgoStr = floor($timeAgo / 86400) . ' days ago';
                                    }
                            ?>
                            <div class="mb-4 pb-4 border-b border-gray-100">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center mr-3">
                                        <?php if ($notification['status'] == 'Completed'): ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php elseif ($notification['status'] == 'Confirmed'): ?>
                                            <i class="fas fa-calendar-check"></i>
                                        <?php else: ?>
                                            <i class="fas fa-tools"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium"><?= htmlspecialchars($notification['service_type']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($notification['client_type']) ?> client</p>
                                        <p class="text-xs text-gray-400 mt-1"><?= $timeAgoStr ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php
                                }
                            } else {
                                echo '<p class="text-sm text-gray-500">No recent notifications</p>';
                            }
                            ?>
                        </div>
                        <div class="p-2 bg-gray-50 text-center border-t border-gray-100">
                            <a href="#" class="text-xs text-teal-600 hover:text-teal-700">View all notifications</a>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 transition-all hover:shadow-md transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Services</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?= $totalServices ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                        <i class="fas fa-tools text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-green-600">
                    <i class="fas fa-chart-line mr-1"></i> Service analytics
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 transition-all hover:shadow-md transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Completed</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?= $completedServices ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-green-600">
                    <i class="fas fa-tasks mr-1"></i> Completion rate
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 transition-all hover:shadow-md transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Pending</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?= $pendingServices ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center text-yellow-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-yellow-600">
                    <i class="fas fa-stopwatch mr-1"></i> Awaiting completion
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 transition-all hover:shadow-md transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Revenue</p>
                        <h3 class="text-2xl font-bold text-gray-800">₱ <?= number_format($totalRevenue, 2) ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">
                        <i class="fas fa-wallet text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-purple-600">
                    <i class="fas fa-coins mr-1"></i> Total earnings
                </div>
            </div>
        </div>


        
        <!-- Search and Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8" x-data="filterSystem()">
        <form method="GET" class="flex flex-wrap items-center justify-between gap-4" @submit.prevent="applyFilters">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" name="search" x-model="filters.search" placeholder="Search services, clients, employees..." class="search-input w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                    <div class="absolute left-3 top-3 text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
            
            <div class="flex space-x-3">
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="bg-gray-50 px-4 py-3 rounded-lg border border-gray-200 text-gray-700 flex items-center">
                        <i class="fas fa-filter mr-2 text-gray-500"></i>
                        <span>Filter</span>
                        <i class="fas fa-chevron-down ml-2 text-gray-500 text-xs"></i>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg overflow-hidden z-10 animate-fadeIn">
                        <div class="p-3 border-b border-gray-100">
                            <h3 class="font-medium text-sm">Filter by</h3>
                        </div>
                        <div class="p-3">
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">Status</label>
                                <select x-model="filters.status" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-md text-sm">
                                    <option value="">All</option>
                                    <option value="Confirmed">Confirmed</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">Client Type</label>
                                <select x-model="filters.client_type" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-md text-sm">
                                    <option value="">All</option>
                                    <option value="Company">Company</option>
                                    <option value="Household">Household</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Date Range</label>
                                <input type="date" x-model="filters.date_from" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-md text-sm mb-2">
                                <input type="date" x-model="filters.date_to" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-md text-sm">
                            </div>
                            <div class="mt-4 flex justify-between">
                                <button type="button" @click="resetFilters" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm">Reset</button>
                                <button type="button" @click="applyFilters" class="bg-teal-600 text-white px-4 py-2 rounded-lg text-sm">Apply Filters</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="bg-gray-50 px-4 py-3 rounded-lg border border-gray-200 text-gray-700 flex items-center">
                        <i class="fas fa-sort mr-2 text-gray-500"></i>
                        <span>Sort</span>
                        <i class="fas fa-chevron-down ml-2 text-gray-500 text-xs"></i>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg overflow-hidden z-10 animate-fadeIn">
                        <div class="p-3 border-b border-gray-100">
                            <h3 class="font-medium text-sm">Sort by</h3>
                        </div>
                        <div class="p-3">
                            <a href="#" @click.prevent="sortBy('date_desc')" class="block px-3 py-2 hover:bg-gray-50 rounded-md text-sm">Date (Newest first)</a>
                            <a href="#" @click.prevent="sortBy('date_asc')" class="block px-3 py-2 hover:bg-gray-50 rounded-md text-sm">Date (Oldest first)</a>
                            <a href="#" @click.prevent="sortBy('price_desc')" class="block px-3 py-2 hover:bg-gray-50 rounded-md text-sm">Price (High to low)</a>
                            <a href="#" @click.prevent="sortBy('price_asc')" class="block px-3 py-2 hover:bg-gray-50 rounded-md text-sm">Price (Low to high)</a>
                            <a href="#" @click.prevent="sortBy('status')" class="block px-3 py-2 hover:bg-gray-50 rounded-md text-sm">Status</a>
                            <a href="#" @click.prevent="sortBy('client_type')" class="block px-3 py-2 hover:bg-gray-50 rounded-md text-sm">Client Type</a>
                        </div>
                    </div>
                </div>
                
                <a href="manager_add_service.php" class="bg-teal-600 text-white px-5 py-3 rounded-lg flex items-center shadow-md hover:bg-teal-700 transition-all font-medium">
                    <i class="fas fa-plus mr-2"></i>
                    <span>Add Service</span>
                </a>
            </div>
            <!-- Hidden inputs to store the filter state -->
            <input type="hidden" name="status" :value="filters.status">
            <input type="hidden" name="client_type" :value="filters.client_type">
            <input type="hidden" name="date_from" :value="filters.date_from">
            <input type="hidden" name="date_to" :value="filters.date_to">
            <input type="hidden" name="sort" :value="filters.sort">
        </form>
    </div>

        <!-- Services Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="px-6 py-4 text-left font-semibold">Client Type</th>
                            <th class="px-6 py-4 text-left font-semibold">Service Type</th>
                            <th class="px-6 py-4 text-left font-semibold">Price</th>
                            <th class="px-6 py-4 text-left font-semibold">Duration</th>
                            <th class="px-6 py-4 text-left font-semibold">Date</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-left font-semibold">Assigned</th>
                            <th class="px-6 py-4 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                       <?php 
                        $index = 0;
                        if ($result && $result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                                $index++;
                                $assignedEmployees = getAssignedEmployees($row['id'], $connection);
                                
                                // Determine status badge color
                                $statusClass = '';
                                switch ($row['status']) {
                                    case 'Completed':
                                        $statusClass = 'bg-green-100 text-green-800';
                                        break;
                                    case 'Confirmed':
                                        $statusClass = 'bg-blue-100 text-blue-800';
                                        break;
                                    case 'Pending':
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'Cancelled':
                                        $statusClass = 'bg-red-100 text-red-800';
                                        break;
                                    default:
                                        $statusClass = 'bg-gray-100 text-gray-800';
                                }
                                
                                // Format date
                                $scheduledDate = date('M d, Y', strtotime($row['scheduled_date']));
                                $scheduledTime = date('h:i A', strtotime($row['scheduled_time']));
                        ?>
                        <tr class="table-row hover:bg-gray-50 <?= $index % 2 ? 'bg-white' : 'bg-gray-50' ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center <?= $row['client_type'] == 'Company' ? 'bg-purple-100 text-purple-600' : 'bg-indigo-100 text-indigo-600' ?>">
                                        <i class="<?= $row['client_type'] == 'Company' ? 'fas fa-building' : 'fas fa-home' ?>"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($row['client_type']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($row['client_name']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($row['service_type']) ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($row['description']) ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-700">₱ <?= number_format($row['price'], 2) ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($row['duration']) ?> hrs</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-700"><?= $scheduledDate ?></p>
                                <p class="text-xs text-gray-500"><?= $scheduledTime ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="status-badge px-3 py-1 rounded-full text-xs font-medium <?= $statusClass ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex -space-x-2">
                                    <?php 
                                    $employeeCount = count($assignedEmployees);
                                    $displayLimit = 3;
                                    $displayCount = min($employeeCount, $displayLimit);
                                    
                                    for ($i = 0; $i < $displayCount; $i++): 
                                        $employee = $assignedEmployees[$i];
                                    $nameParts = explode(' ', $employee['name']);
                                    $firstInitial = strtoupper(substr($nameParts[0], 0, 1));
                                    $secondInitial = isset($nameParts[1]) ? strtoupper(substr($nameParts[1], 0, 1)) : '';
                                    $initials = $firstInitial . $secondInitial;
                                    ?>
                                    <div class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-xs font-medium text-gray-600" title="<?= htmlspecialchars($employee['name'] ) ?>">
                                        <?= $initials ?>
                                    </div>
                                    <?php endfor; ?>
                                    
                                    <?php if ($employeeCount > $displayLimit): ?>
                                    <div class="w-8 h-8 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center text-xs font-medium text-gray-600" title="<?= $employeeCount - $displayLimit ?> more employees">
                                        +<?= $employeeCount - $displayLimit ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($employeeCount === 0): ?>
                                    <span class="text-xs text-gray-500">No assigned employees</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <button class="btn px-4 py-2 me-3 position-relative bg-gradient-primary text-white border-0 rounded-lg shadow-sm transition-all hover:shadow-md" style="background: linear-gradient(135deg, #4776E6, #8E54E9); font-weight: 500; letter-spacing: 0.3px;" data-bs-toggle="modal" data-bs-target="#viewModal" onclick="loadView(<?= $row['id'] ?>)">
                                      <span class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                          <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                          <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                                        </svg>
                                        View
                                      </span>
                                    </button>

                                    <button class="btn px-4 py-2 position-relative border-0 rounded-lg shadow-sm transition-all hover:shadow-md" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px); color: #5061a9; font-weight: 500; letter-spacing: 0.3px; border: 1px solid rgba(206, 212, 218, 0.5);" data-bs-toggle="modal" data-bs-target="#editModal" onclick="loadEdit(<?= $row['id'] ?>)">
                                      <span class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                          <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
                                        </svg>
                                        Edit
                                      </span>
                                    </button>

                                    <a href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this service?')" class="action-button p-2 bg-red-50 rounded-lg text-red-600 hover:bg-red-100">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 mb-4">
                                        <i class="fas fa-search text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 mb-1">No services found</p>
                                    <p class="text-sm text-gray-400">Try adjusting your search or filter to find what you're looking for.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalResults > 0): ?>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Showing <?= $startFrom + 1 ?> to <?= min($startFrom + $resultsPerPage, $totalResults) ?> of <?= $totalResults ?> services
                    </div>
                    
                    <div class="flex space-x-1">
                        <?php
                        // Previous page link
                        $prevPage = $currentPage - 1;
                        $prevDisabled = $prevPage < 1;
                        $prevUrl = "?page=$prevPage" . ($search ? "&search=$search" : "");
                        
                        // Next page link
                        $nextPage = $currentPage + 1;
                        $nextDisabled = $nextPage > $totalPages;
                        $nextUrl = "?page=$nextPage" . ($search ? "&search=$search" : "");
                        ?>
                        
                        <a href="<?= $prevDisabled ? '#' : $prevUrl ?>" class="<?= $prevDisabled ? 'opacity-50 cursor-not-allowed' : '' ?> px-3 py-2 rounded-md bg-white border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        
                        <?php
                        // Page numbers
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $startPage + 4);
                        
                        if ($startPage > 1) {
                            echo '<span class="px-3 py-2 text-gray-500">...</span>';
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            $isCurrentPage = $i == $currentPage;
                            $pageUrl = "?page=$i" . ($search ? "&search=$search" : "");
                        ?>
                        <a href="<?= $pageUrl ?>" class="px-3 py-2 rounded-md <?= $isCurrentPage ? 'bg-teal-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' ?> text-sm">
                            <?= $i ?>
                        </a>
                        <?php
                        }
                        
                        if ($endPage < $totalPages) {
                            echo '<span class="px-3 py-2 text-gray-500">...</span>';
                        }
                        ?>
                        
                        <a href="<?= $nextDisabled ? '#' : $nextUrl ?>" class="<?= $nextDisabled ? 'opacity-50 cursor-not-allowed' : '' ?> px-3 py-2 rounded-md bg-white border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Export/Print Options -->
        <div class="flex justify-end mt-8 space-x-3">
            <a href="export_services.php?format=csv<?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">
                <i class="fas fa-file-csv mr-2 text-green-600"></i>
                <span>Export CSV</span>
            </a>
            
            <a href="export_services.php?format=excel<?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">
                <i class="fas fa-file-excel mr-2 text-green-600"></i>
                <span>Export Excel</span>
            </a>
            
            <a href="print_services.php<?= isset($_GET['search']) ? '?search=' . urlencode($_GET['search']) : '' ?>" target="_blank" class="flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">
                <i class="fas fa-print mr-2 text-blue-600"></i>
                <span>Print</span>
            </a>
        </div>
    </main>

    <!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"></h5></div>
      <div class="modal-body" id="viewContent">Loading...</div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Edit Details</h5></div>
      <div class="modal-body" id="editContent">Loading...</div>
    </div>
  </div>
</div>


    <!-- JavaScript for additional interactions -->
    <script>
        // Display notification badge only if there are new notifications
        document.addEventListener('DOMContentLoaded', function() {
            const notificationDot = document.querySelector('.notification-dot');
            
            <?php
            // Check if there are recent notifications (last 24 hours)
            $recentNotificationsQuery = "SELECT COUNT(*) as recent FROM services WHERE created_at >= NOW() - INTERVAL 24 HOUR";
            $recentNotificationsResult = $connection->query($recentNotificationsQuery);
            $recentCount = $recentNotificationsResult ? $recentNotificationsResult->fetch_assoc()['recent'] : 0;
            ?>
            
            if (<?= $recentCount ?> === 0) {
                notificationDot.style.display = 'none';
            }
        });
        
        // Tooltip functionality for later implementation
        // For now, we're using the title attribute
        
        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.querySelector('aside');
            sidebar.classList.toggle('-translate-x-full');
        }

          function filterSystem() {
            return {
                filters: {
                    search: new URLSearchParams(window.location.search).get('search') || '',
                    status: new URLSearchParams(window.location.search).get('status') || '',
                    client_type: new URLSearchParams(window.location.search).get('client_type') || '',
                    date_from: new URLSearchParams(window.location.search).get('date_from') || '',
                    date_to: new URLSearchParams(window.location.search).get('date_to') || '',
                    sort: new URLSearchParams(window.location.search).get('sort') || ''
                },
                
                resetFilters() {
                    this.filters = {
                        search: this.filters.search, // Keep the search term
                        status: '',
                        client_type: '',
                        date_from: '',
                        date_to: '',
                        sort: this.filters.sort // Keep the current sort
                    };
                },
                
                applyFilters() {
                    const params = new URLSearchParams();
                    
                    // Only add parameters that have values
                    if (this.filters.search) params.append('search', this.filters.search);
                    if (this.filters.status) params.append('status', this.filters.status);
                    if (this.filters.client_type) params.append('client_type', this.filters.client_type);
                    if (this.filters.date_from) params.append('date_from', this.filters.date_from);
                    if (this.filters.date_to) params.append('date_to', this.filters.date_to);
                    if (this.filters.sort) params.append('sort', this.filters.sort);
                    
                    // Add page=1 when applying new filters to start from the first page
                    params.append('page', '1');
                    
                    // Navigate to the filtered URL
                    window.location.href = `?${params.toString()}`;
                },
                
                sortBy(sortOption) {
                    this.filters.sort = sortOption;
                    this.applyFilters();
                }
            }
        }

                // Action Buttons Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add classes to differentiate button types
    document.querySelectorAll('.action-button').forEach(button => {
        if (button.querySelector('.fa-eye')) {
            button.classList.add('view-button');
            addTooltip(button, 'View Details');
        } else if (button.querySelector('.fa-edit')) {
            button.classList.add('edit-button');
            addTooltip(button, 'Edit Service');
        } else if (button.querySelector('.fa-trash')) {
            button.classList.add('delete-button');
            addTooltip(button, 'Delete Service');
            
            // Override click for delete to show confirmation
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const deleteUrl = this.getAttribute('href');
                showDeleteConfirmation(deleteUrl);
            });
        }
    });
    
    // Function to add tooltips
    function addTooltip(element, text) {
        const tooltip = document.createElement('span');
        tooltip.className = 'action-tooltip';
        tooltip.textContent = text;
        element.appendChild(tooltip);
        element.style.position = 'relative';
    }
    
    // Function to show delete confirmation
    function showDeleteConfirmation(deleteUrl) {
        // Create modal elements
        const modal = document.createElement('div');
        modal.className = 'delete-modal modal-enter';
        
        modal.innerHTML = `
            <div class="delete-modal-content">
                <div class="delete-modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-lg font-medium text-center mb-2">Confirm Deletion</h3>
                <p class="text-sm text-gray-500 text-center mb-4">
                    Are you sure you want to delete this service? This action cannot be undone.
                </p>
                <div class="flex justify-center space-x-4">
                    <button id="cancelDelete" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all">
                        Cancel
                    </button>
                    <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all">
                        Yes, Delete
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Add event listeners for modal buttons
        document.getElementById('cancelDelete').addEventListener('click', function() {
            modal.classList.replace('modal-enter', 'modal-leave');
            setTimeout(() => modal.remove(), 300);
        });
        
        document.getElementById('confirmDelete').addEventListener('click', function() {
            // Show loading state
            this.innerHTML = '<span class="loading-spinner mr-2"></span> Deleting...';
            this.disabled = true;
            
            // Navigate to delete URL after brief delay for animation
            setTimeout(() => {
                window.location.href = deleteUrl;
            }, 600);
        });
        
        // Close when clicking outside the modal content
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.replace('modal-enter', 'modal-leave');
                setTimeout(() => modal.remove(), 300);
            }
        });
    }
    
    // Enhance view buttons with loading effect
    document.querySelectorAll('.view-button').forEach(button => {
        button.addEventListener('click', function(e) {
            // Show loading state
            const originalContent = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // Small delay to show loading effect before navigation
            setTimeout(() => {
                window.location.href = this.getAttribute('href');
            }, 300);
        });
    });
    
    // Add transition effect for edit buttons
    document.querySelectorAll('.edit-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            
            // Subtle page transition
            document.body.style.opacity = '0.8';
            document.body.style.transition = 'opacity 0.3s ease';
            
            setTimeout(() => {
                window.location.href = url;
            }, 300);
        });
    });
});



function loadView(id) {
  fetch('view.php?id=' + id)
    .then(response => response.text())
    .then(data => {
      document.getElementById('viewContent').innerHTML = data;
    });
}

function loadEdit(id) {
  fetch('edit.php?id=' + id)
    .then(response => response.text())
    .then(data => {
      document.getElementById('editContent').innerHTML = data;
    });
}


    </script>
</body>
</html>

<?php
// Close database connection
$connection->close();
?>
