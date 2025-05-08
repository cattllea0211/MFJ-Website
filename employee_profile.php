<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$id = $_SESSION['id']; // Session ID
$username = $_SESSION['username']; // (optional use)

// Database connection (PDO)
$host = 'localhost';
$dbname = 'mfj_db';   // <-- your database name
$db_username = 'root'; // <-- your database username
$db_password = '';     // <-- your database password

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
    <style>
        /* Custom styles for wider layout */
        .profile-container {
            max-width: 1400px;
        }
        
        @media (min-width: 1024px) {
            .info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (min-width: 1280px) {
            .profile-content {
                padding-left: 3rem;
                padding-right: 3rem;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="min-h-screen flex flex-col">

        <!-- Top Navigation Bar -->
        <nav class="bg-white shadow-sm">
            <div class="w-11/12 mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="employee_dashboard.php" class="flex items-center text-slate-700 hover:text-slate-900 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Dashboard
                        </a>
                    </div>
                    <div class="flex items-center">
                        <div class="text-sm font-medium text-slate-500">
                            <i class="fas fa-building mr-2"></i>
                            MFJ Company
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="w-11/12 profile-container mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow">

            <!-- Page Header -->
            <div class="mb-10 text-center">
                <h1 class="text-4xl font-bold text-slate-800">Employee Profile</h1>
                <p class="mt-2 text-lg text-slate-500">View your personal information</p>
            </div>

            <!-- Profile Card -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">

                <!-- Profile Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-20 sm:px-12 relative overflow-hidden">
                    <div class="absolute inset-0 bg-pattern opacity-10"></div>
                    <div class="absolute top-6 right-6">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-white bg-opacity-25 text-white backdrop-blur-sm">
                            <i class="fas fa-id-badge mr-2"></i>
                            ID: <?php echo htmlspecialchars($employee['id']); ?>
                        </span>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="px-8 py-10 sm:px-12 profile-content -mt-28 relative z-10">

                    <!-- Profile Picture and Name -->
                    <div class="flex flex-col items-center mb-10">
                        <div class="relative">
                            <div class="w-40 h-40 rounded-full bg-white p-1.5 shadow-lg">
                                <div class="w-full h-full rounded-full bg-gradient-to-r from-blue-100 to-indigo-100 flex items-center justify-center overflow-hidden">
                                    <?php if (!empty($employee['picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($employee['picture']); ?>" class="h-full w-full object-cover rounded-full" />
                                    <?php else: ?>
                                        <i class="fas fa-user text-7xl text-blue-300"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="absolute bottom-0 right-0 bg-white rounded-full p-1.5 shadow-md">
                                <span class="bg-indigo-500 text-white text-sm px-3 py-1 rounded-full flex items-center">
                                    <i class="fas fa-user-tag mr-1"></i>
                                    <?php echo htmlspecialchars($employee['roles']); ?>
                                </span>
                            </div>
                        </div>
                        <h2 class="mt-6 text-3xl font-bold text-slate-800"><?php echo htmlspecialchars($employee['name']); ?></h2>
                        <p class="text-xl text-slate-500 flex items-center">
                            <i class="fas fa-briefcase text-indigo-400 mr-2"></i>
                            <?php echo htmlspecialchars($employee['position']); ?>
                        </p>
                    </div>

                    <!-- Information Grid -->
                    <div class="grid info-grid gap-8">
                        <div class="col-span-full">
                            <hr class="mb-8 border-slate-200" />
                        </div>

                        <!-- Contact Information -->
                        <div class="bg-slate-50 p-8 rounded-lg shadow-sm hover:shadow transition duration-300">
                            <h3 class="text-xl font-semibold text-slate-800 mb-6 flex items-center">
                                <i class="fas fa-address-card text-indigo-500 mr-3 text-xl"></i>
                                Contact Information
                            </h3>
                            <div class="space-y-5">
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-envelope text-indigo-400 mr-3 w-5 text-center"></i>
                                        Email
                                    </p>
                                    <p class="text-slate-800 pl-8 mt-1"><?php echo htmlspecialchars($employee['email']); ?></p>
                                </div>
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-phone text-indigo-400 mr-3 w-5 text-center"></i>
                                        Phone
                                    </p>
                                    <p class="text-slate-800 pl-8 mt-1"><?php echo htmlspecialchars($employee['phone']); ?></p>
                                </div>
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-map-marker-alt text-indigo-400 mr-3 w-5 text-center"></i>
                                        Address
                                    </p>
                                    <p class="text-slate-800 pl-8 mt-1"><?php echo htmlspecialchars($employee['address']); ?></p>
                                </div>
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-heartbeat text-indigo-400 mr-3 w-5 text-center"></i>
                                        Emergency Contact
                                    </p>
                                    <p class="text-slate-800 pl-8 mt-1">
                                        <?php echo htmlspecialchars($employee['emergency_contact_person']); ?> - <?php echo htmlspecialchars($employee['emergency_contact_number']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Information -->
                        <div class="bg-slate-50 p-8 rounded-lg shadow-sm hover:shadow transition duration-300">
                            <h3 class="text-xl font-semibold text-slate-800 mb-6 flex items-center">
                                <i class="fas fa-building text-indigo-500 mr-3 text-xl"></i>
                                Employment Details
                            </h3>
                            <div class="space-y-5">
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-briefcase text-indigo-400 mr-3 w-5 text-center"></i>
                                        Position
                                    </p>
                                    <p class="text-slate-800 pl-8 mt-1"><?php echo htmlspecialchars($employee['position']); ?></p>
                                </div>
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-chart-line text-indigo-400 mr-3 w-5 text-center"></i>
                                        Status
                                    </p>
                                    <div class="pl-8 mt-1">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            <?php 
                                                $status = strtolower($employee['status']);
                                                echo $status === 'active' ? 'bg-green-100 text-green-800' : ($status === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                            ?>">
                                            <i class="fas fa-circle mr-2 text-xs"></i>
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-user text-indigo-400 mr-3 w-5 text-center"></i>
                                        Username
                                    </p>
                                    <p class="text-slate-800 pl-8 mt-1"><?php echo htmlspecialchars($employee['username']); ?></p>
                                </div>
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-money-bill-wave text-indigo-400 mr-3 w-5 text-center"></i>
                                        Daily Rate
                                    </p>
                                    <p class="text-slate-800 pl-8 mt-1 font-medium">₱<?php echo number_format($employee['rate_per_day'], 2); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-full">
                            <hr class="my-8 border-slate-200" />
                        </div>

                        <!-- Government IDs -->
                        <div class="bg-slate-50 p-8 rounded-lg shadow-sm hover:shadow transition duration-300">
                            <h3 class="text-xl font-semibold text-slate-800 mb-6 flex items-center">
                                <i class="fas fa-id-card text-indigo-500 mr-3 text-xl"></i>
                                Government IDs
                            </h3>
                            <div class="space-y-5">
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-shield-alt text-indigo-400 mr-3 w-5 text-center"></i>
                                        SSS Number
                                    </p>
                                    <p class="text-slate-800 pl-8 mt-1"><?php echo htmlspecialchars($employee['sss_no']) ?: 'Not provided'; ?></p>
                                </div>
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-plus-square text-indigo-400 mr-3 w-5 text-center"></i>
                                        PhilHealth Number
                                    </p>
                                    <p class="text-slate-800 pl-8 mt-1"><?php echo htmlspecialchars($employee['philhealth_no']) ?: 'Not provided'; ?></p>
                                </div>
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-home text-indigo-400 mr-3 w-5 text-center"></i>
                                        Pag-IBIG Number
                                    </p>
                                    <p class="text-slate-800 pl-8 mt-1"><?php echo htmlspecialchars($employee['pagibig_no']) ?: 'Not provided'; ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="bg-slate-50 p-8 rounded-lg shadow-sm hover:shadow transition duration-300">
                            <h3 class="text-xl font-semibold text-slate-800 mb-6 flex items-center">
                                <i class="fas fa-user-circle text-indigo-500 mr-3 text-xl"></i>
                                Personal Information
                            </h3>
                            <div class="space-y-5">
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-birthday-cake text-indigo-400 mr-3 w-5 text-center"></i>
                                        Birthday
                                    </p>
                                    <p class="text-slate-800 pl-8 mt-1"><?php echo htmlspecialchars($employee['birthdate']); ?></p>
                                </div>
                                <div>
                                    <p class="text-base font-medium text-slate-500 flex items-center">
                                        <i class="fas fa-user-tag text-indigo-400 mr-3 w-5 text-center"></i>
                                        Role
                                    </p>
                                    <p class="text-slate-800 pl-8 mt-1"><?php echo htmlspecialchars($employee['roles']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Button -->
                    <div class="mt-12 flex justify-center">
                        <a href="edit_profile.php?id=<?php echo htmlspecialchars($employee['id']); ?>" 
                           class="inline-flex items-center px-8 py-3.5 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <i class="fas fa-user-edit mr-3"></i>
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white py-6 mt-auto">
            <div class="w-11/12 mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-slate-500">
                    &copy; <?php echo date('Y'); ?> MFJ Company. All rights reserved.
                </p>
            </div>
        </footer>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>