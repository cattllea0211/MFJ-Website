<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: employee_login.php');
    exit();
}

$id = $_SESSION['id']; // Session ID
$username = $_SESSION['username']; // (optional use)

// Database connection (PDO)
$host = 'localhost';
$dbname = 'mfjdb';   // <-- your database name
$db_username = 'mfj_user'; // <-- your database username
$db_password = 'StrongPassword123!';     // <-- your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch employee data
$sql = "SELECT * FROM employees WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    echo "<h1>Employee not found. Please contact administrator.</h1>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        .profile-container {
            max-width: 1200px;
        }
        
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
        }
        
        .status-badge {
            position: relative;
        }
        
        .status-badge::before {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
        }
        
        .status-active::before {
            background-color: #10b981;
        }
        
        .status-inactive::before {
            background-color: #ef4444;
        }
        
        .status-pending::before {
            background-color: #f59e0b;
        }
        
        .profile-shadow {
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 8px 10px -6px rgba(59, 130, 246, 0.1);
        }
        
        .section-title {
            position: relative;
            padding-bottom: 0.75rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 3rem;
            height: 3px;
            background: linear-gradient(to right, #3b82f6, #1d4ed8);
            border-radius: 3px;
        }
    </style>
</head>
<body class="font-sans bg-slate-50 text-slate-700">
    <!-- Global Header Navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="employee_dashboard.php" class="flex items-center space-x-2 text-primary-700 hover:text-primary-800 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span class="font-medium">Back to Dashboard</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex items-center space-x-3 text-sm text-slate-500">
                        <i class="fas fa-calendar-alt"></i>
                        <span><?php echo date('F d, Y'); ?></span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm font-medium">
                        <div class="w-8 h-8 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center">
                            <i class="fas fa-building"></i>
                        </div>
                        <span class="hidden md:inline">MFJ Company</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen">
        <div class="max-w-7xl profile-container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Profile Header -->
            <div class="rounded-2xl overflow-hidden shadow-lg bg-white profile-shadow mb-8">
                <!-- Cover Image -->
                <div class="h-48 sm:h-56 md:h-64 bg-gradient-to-r from-blue-500 to-indigo-600 relative overflow-hidden bg-pattern">
                    <div class="absolute top-4 right-4 bg-white bg-opacity-20 backdrop-blur-md rounded-lg px-3 py-1.5 text-white text-sm font-medium shadow-sm">
                        <i class="fas fa-id-badge mr-2"></i>
                        ID: <?php echo htmlspecialchars($employee['id']); ?>
                    </div>
                </div>

                <!-- Profile Info -->
                <div class="relative px-4 sm:px-6 lg:px-8 pb-8">
                    <!-- Profile Photo -->
                    <div class="absolute -top-16 left-1/2 transform -translate-x-1/2">
                        <div class="w-32 h-32 bg-white rounded-full p-1.5 shadow-lg ring-4 ring-white">
                            <div class="w-full h-full rounded-full bg-gradient-to-b from-slate-50 to-slate-100 flex items-center justify-center overflow-hidden">
                                <?php if (!empty($employee['picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($employee['picture']); ?>" class="h-full w-full object-cover rounded-full" alt="Profile Photo" />
                                <?php else: ?>
                                    <span class="text-primary-300 text-5xl">
                                        <i class="fas fa-user"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Details -->
                    <div class="pt-20 text-center">
                        <h1 class="text-3xl font-bold text-slate-800"><?php echo htmlspecialchars($employee['name']); ?></h1>
                        
                        <div class="mt-2 flex items-center justify-center space-x-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                                <i class="fas fa-briefcase mr-2"></i>
                                <?php echo htmlspecialchars($employee['position']); ?>
                            </span>
                            <span class="inline-flex items-center pl-6 pr-3 py-1 rounded-full text-sm font-medium 
                                <?php 
                                    $status = strtolower($employee['status']);
                                    if ($status === 'active') {
                                        echo 'bg-green-100 text-green-800 status-badge status-active';
                                    } elseif ($status === 'inactive') {
                                        echo 'bg-red-100 text-red-800 status-badge status-inactive';
                                    } else {
                                        echo 'bg-yellow-100 text-yellow-800 status-badge status-pending';
                                    }
                                ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </div>
                        
                        <div class="mt-5 flex flex-col sm:flex-row items-center justify-center sm:space-x-6 space-y-2 sm:space-y-0 text-slate-500">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center mr-2">
                                    <i class="fas fa-envelope text-primary-500"></i>
                                </div>
                                <span><?php echo htmlspecialchars($employee['email']); ?></span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center mr-2">
                                    <i class="fas fa-phone text-primary-500"></i>
                                </div>
                                <span><?php echo htmlspecialchars($employee['phone']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Personal Information -->
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow duration-300">
                    <h2 class="text-xl font-semibold text-slate-800 flex items-center section-title">
                        <i class="fas fa-user-circle text-primary-500 mr-3"></i>
                        Personal Information
                    </h2>
                    
                    <div class="mt-6 space-y-4">
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">Full Name</h3>
                                <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($employee['name']); ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-birthday-cake text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">Birthday</h3>
                                <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($employee['birthdate']); ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">Address</h3>
                                <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($employee['address']); ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user-tag text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">Role</h3>
                                <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($employee['roles']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employment Details -->
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow duration-300">
                    <h2 class="text-xl font-semibold text-slate-800 flex items-center section-title">
                        <i class="fas fa-briefcase text-primary-500 mr-3"></i>
                        Employment Details
                    </h2>
                    
                    <div class="mt-6 space-y-4">
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-id-badge text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">Employee ID</h3>
                                <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($employee['id']); ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user-tie text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">Position</h3>
                                <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($employee['position']); ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">Username</h3>
                                <p class="text-slate-800 font-medium"><?php echo htmlspecialchars($employee['username']); ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-money-bill-wave text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">Daily Rate</h3>
                                <p class="text-slate-800 font-medium text-lg">₱<?php echo number_format($employee['rate_per_day'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow duration-300">
                    <h2 class="text-xl font-semibold text-slate-800 flex items-center section-title">
                        <i class="fas fa-heartbeat text-primary-500 mr-3"></i>
                        Emergency Contact
                    </h2>
                    
                    <div class="mt-6 space-y-4">
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user-friends text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">Contact Person</h3>
                                <p class="text-slate-800 font-medium">
                                    <?php 
                                        echo !empty($employee['emergency_contact_person']) 
                                            ? htmlspecialchars($employee['emergency_contact_person']) 
                                            : '<span class="text-slate-400 italic">Not provided</span>'; 
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-phone-alt text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">Contact Number</h3>
                                <p class="text-slate-800 font-medium">
                                    <?php 
                                        echo !empty($employee['emergency_contact_number']) 
                                            ? htmlspecialchars($employee['emergency_contact_number']) 
                                            : '<span class="text-slate-400 italic">Not provided</span>'; 
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <?php if (empty($employee['emergency_contact_person']) || empty($employee['emergency_contact_number'])): ?>
                                            Please provide emergency contact information for safety purposes.
                                        <?php else: ?>
                                            Keep your emergency contact information updated for safety purposes.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Government IDs -->
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow duration-300">
                    <h2 class="text-xl font-semibold text-slate-800 flex items-center section-title">
                        <i class="fas fa-id-card text-primary-500 mr-3"></i>
                        Government IDs
                    </h2>
                    
                    <div class="mt-6 space-y-4">
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-shield-alt text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">SSS Number</h3>
                                <p class="text-slate-800 font-medium">
                                    <?php 
                                        echo !empty($employee['sss_no']) 
                                            ? htmlspecialchars($employee['sss_no']) 
                                            : '<span class="text-slate-400 italic">Not provided</span>'; 
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-plus-square text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">PhilHealth Number</h3>
                                <p class="text-slate-800 font-medium">
                                    <?php 
                                        echo !empty($employee['philhealth_no']) 
                                            ? htmlspecialchars($employee['philhealth_no']) 
                                            : '<span class="text-slate-400 italic">Not provided</span>'; 
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-home text-primary-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-slate-500">Pag-IBIG Number</h3>
                                <p class="text-slate-800 font-medium">
                                    <?php 
                                        echo !empty($employee['pagibig_no']) 
                                            ? htmlspecialchars($employee['pagibig_no']) 
                                            : '<span class="text-slate-400 italic">Not provided</span>'; 
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Edit Profile Button -->
            <div class="mt-10 text-center">
                <a href="edit_profile.php?id=<?php echo htmlspecialchars($employee['id']); ?>" 
                   class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                    <i class="fas fa-user-edit mr-2"></i>
                    Edit Profile
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex space-x-6 md:order-2">
                    <a href="#" class="text-slate-400 hover:text-slate-500">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" class="text-slate-400 hover:text-slate-500">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-slate-400 hover:text-slate-500">
                        <i class="fab fa-linkedin"></i>
                    </a>
                </div>
                <div class="mt-8 md:mt-0 md:order-1">
                    <p class="text-center text-sm text-slate-500">
                        &copy; <?php echo date('Y'); ?> MFJ Company. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>
