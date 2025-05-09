<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: employee_login.php');
    exit();
}

// Get employee ID from URL or session
$id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['id'];

// Security check: Make sure users can only edit their own profile unless they're admin
if ($_SESSION['id'] != $id && $_SESSION['role'] != 'admin') {
    header('Location: employee_profile.php');
    exit();
}

// Database connection (PDO)
$host = 'localhost';
$dbname = 'mfjdb';
$db_username = 'root';
$db_password = '';

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

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start building the SQL query with only the fields that were changed
        $sql = "UPDATE employees SET ";
        $updateFields = [];
        $params = [];
        
        // Check each field and only update if it's provided and different from current value
        $fields = [
            'name', 'email', 'phone', 'address', 
            'emergency_contact_person', 'emergency_contact_number',
            'sss_no', 'philhealth_no', 'pagibig_no', 'birthdate'
        ];
        
        foreach ($fields as $field) {
            // Only include field if it was submitted and is different from current value
            if (isset($_POST[$field]) && $_POST[$field] !== '') {
                $updateFields[] = "$field = :$field";
                $params[":$field"] = $_POST[$field];
            }
        }
        
        // Only proceed with update if there are fields to update
        if (!empty($updateFields)) {
            $sql .= implode(', ', $updateFields);
            $sql .= " WHERE id = :id";
            $params[':id'] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
        
        // Handle profile picture upload if provided
        if (isset($_FILES['picture']) && $_FILES['picture']['size'] > 0) {
            $target_dir = "uploads/";
            $file_extension = pathinfo($_FILES["picture"]["name"], PATHINFO_EXTENSION);
            $target_file = $target_dir . "employee_" . $id . "_" . time() . "." . $file_extension;
            
            // Check if image file is valid
            $valid_file = true;
            $check = getimagesize($_FILES["picture"]["tmp_name"]);
            if ($check === false) {
                $valid_file = false;
            }
            
            // Check file size (limit to 5MB)
            if ($_FILES["picture"]["size"] > 5000000) {
                $valid_file = false;
            }
            
            // Allow certain file formats
            if ($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif") {
                $valid_file = false;
            }
            
            if ($valid_file) {
                if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
                    // Update the database with new picture path
                    $sql = "UPDATE employees SET picture = :picture WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':picture', $target_file);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
            }
        }
        
        // Handle password update if provided
        if (!empty($_POST['password']) && !empty($_POST['confirm_password'])) {
            if ($_POST['password'] === $_POST['confirm_password']) {
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                $sql = "UPDATE employees SET password = :password WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            } else {
                $message = "Passwords do not match. Other information has been updated.";
                $messageType = "warning";
            }
        }
        
        if (empty($message)) {
            $message = "Profile updated successfully!";
            $messageType = "success";
        }
        
        // Refresh employee data after update
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $message = "Error updating profile: " . $e->getMessage();
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee Profile | MFJ</title>
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
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .form-input:focus + .floating-label,
        .form-input:not(:placeholder-shown) + .floating-label {
            transform: translateY(-1.5rem) scale(0.85);
            color: #0284c7;
        }
        
        .floating-label {
            transition: all 0.2s ease;
            pointer-events: none;
        }
        
        .input-group:focus-within {
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
        }
        
        .profile-picture-overlay {
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .profile-picture-container:hover .profile-picture-overlay {
            opacity: 1;
        }
        
        .gradient-card {
            background: linear-gradient(135deg, #0ea5e9 0%, #1e40af 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 fixed w-full z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <img class="h-8 w-auto" src="/api/placeholder/32/32" alt="MFJ Logo">
                        <span class="ml-2 text-lg font-semibold text-gray-800">MFJ Company</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="employee_profile.php" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors">
                        <i class="fas fa-user-circle mr-2"></i>
                        My Profile
                    </a>
                    <a href="#" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors">
                        <i class="fas fa-bell mr-2"></i>
                        <span class="relative">
                            Notifications
                            <span class="absolute -top-1.5 -right-1.5 bg-red-500 rounded-full w-4 h-4 flex items-center justify-center text-xs text-white">2</span>
                        </span>
                    </a>
                    <div class="border-l border-gray-200 h-6 mx-4"></div>
                    <div class="flex items-center">
                        <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-medium">AD</div>
                        <div class="ml-2">
                            <p class="text-sm font-medium text-gray-700">Admin</p>
                            <p class="text-xs text-gray-500">Administrator</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="py-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li>
                        <a href="dashboard.php" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-home"></i>
                            <span class="sr-only">Home</span>
                        </a>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 text-xs mx-1"></i>
                        <a href="employee_profile.php" class="text-gray-500 hover:text-gray-700">Profile</a>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 text-xs mx-1"></i>
                        <span class="text-gray-900 font-medium">Edit Profile</span>
                    </li>
                </ol>
            </nav>

            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Edit Employee Profile</h1>
                    <a href="employee_profile.php" class="flex items-center text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors">
                        <i class="fas fa-arrow-left mr-1.5"></i>
                        Back to Profile
                    </a>
                </div>
                <p class="mt-1 text-sm text-gray-500">Update your personal information and settings</p>
            </div>

            <!-- Alert Message -->
            <div id="alert-message" class="mb-6 hidden">
                <div class="rounded-lg p-4 flex items-center border" id="alert-container">
                    <i class="mr-3 text-lg" id="alert-icon"></i>
                    <div class="flex-1" id="alert-content"></div>
                    <button type="button" class="ml-auto text-gray-400 hover:text-gray-500" onclick="document.getElementById('alert-message').classList.add('hidden')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Main Form -->
            <form action="#" method="post" enctype="multipart/form-data">
                <!-- Main Card -->
                <div class="bg-white shadow-sm rounded-xl overflow-hidden mb-6">
                    <!-- Profile Header -->
                    <div class="gradient-card relative px-6 py-12 sm:px-10 text-white">
                        <div class="absolute right-0 top-0 mt-4 mr-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white text-primary-700">
                                <i class="fas fa-circle text-green-500 mr-1.5 text-xs"></i>
                                Active Employee
                            </span>
                        </div>
                        <div class="flex flex-col sm:flex-row items-center">
                            <!-- Profile Picture -->
                            <div class="profile-picture-container relative mb-6 sm:mb-0">
                                <div class="w-28 h-28 rounded-full bg-white p-1 shadow-lg">
                                    <div class="w-full h-full rounded-full bg-gradient-to-r from-blue-50 to-indigo-50 flex items-center justify-center overflow-hidden">
                                        <img src="/api/placeholder/100/100" alt="Profile" id="profile-preview" class="h-full w-full object-cover rounded-full">
                                    </div>
                                </div>
                                <label for="picture" class="profile-picture-overlay absolute inset-0 w-28 h-28 rounded-full bg-black bg-opacity-50 flex items-center justify-center cursor-pointer">
                                    <div class="text-white text-center">
                                        <i class="fas fa-camera text-lg"></i>
                                        <p class="text-xs mt-1">Change Photo</p>
                                    </div>
                                    <input type="file" id="picture" name="picture" class="hidden" accept="image/*" onchange="previewImage(this)">
                                </label>
                            </div>
                            
                            <!-- Profile Info -->
                            <div class="sm:ml-8 text-center sm:text-left">
                                <h2 class="text-2xl font-bold">Athena Dizon</h2>
                                <p class="text-primary-100 mt-1">Software Engineer • Employee ID: #MFJ-10023</p>
                                <div class="flex items-center justify-center sm:justify-start mt-4 space-x-3">
                                    <span class="flex items-center text-sm">
                                        <i class="fas fa-calendar-alt mr-1.5"></i>
                                        Joined March 2023
                                    </span>
                                    <span class="h-1 w-1 rounded-full bg-primary-200"></span>
                                    <span class="flex items-center text-sm">
                                        <i class="fas fa-map-marker-alt mr-1.5"></i>
                                        Manila Office
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="border-b border-gray-200">
                        <div class="px-6 sm:px-10">
                            <nav class="-mb-px flex space-x-8">
                                <a href="#" class="border-b-2 border-primary-500 py-4 px-1 text-sm font-medium text-primary-600">
                                    Personal Information
                                </a>
                                <a href="#" class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                    Account Settings
                                </a>
                                <a href="#" class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                    Notifications
                                </a>
                            </nav>
                        </div>
                    </div>

                    <!-- Form Content -->
                    <div class="px-6 py-6 sm:px-10">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-6">
                            <!-- Left Column -->
                            <div class="space-y-8">
                                <!-- Personal Information Section -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 pb-2 border-b border-gray-200 mb-4">
                                        <i class="fas fa-user text-primary-500 mr-2"></i>
                                        Personal Information
                                    </h3>
                                    
                                    <div class="space-y-4">
                                        <!-- Full Name -->
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-user-circle text-gray-400"></i>
                                                </div>
                                                <input type="text" name="name" id="name" value="Athena Dizon" 
                                                    class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                        </div>

                                        <!-- Birthdate -->
                                        <div>
                                            <label for="birthdate" class="block text-sm font-medium text-gray-700 mb-1">Birthdate</label>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-birthday-cake text-gray-400"></i>
                                                </div>
                                                <input type="date" name="birthdate" id="birthdate" value="2004-03-10" 
                                                    class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                        </div>

                                        <!-- Position & Role Group -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- Position -->
                                            <div>
                                                <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                                <div class="relative rounded-md shadow-sm">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <i class="fas fa-briefcase text-gray-400"></i>
                                                    </div>
                                                    <input type="text" name="position" id="position" value="Software Engineer" 
                                                        class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                                                        disabled>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500">Position can only be changed by admin</p>
                                            </div>

                                            <!-- Role -->
                                            <div>
                                                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                                <div class="relative rounded-md shadow-sm">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <i class="fas fa-user-tag text-gray-400"></i>
                                                    </div>
                                                    <input type="text" name="role" id="role" value="Employee" 
                                                        class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                                                        disabled>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500">Role can only be changed by admin</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Government IDs Section -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 pb-2 border-b border-gray-200 mb-4">
                                        <i class="fas fa-id-card text-primary-500 mr-2"></i>
                                        Government IDs
                                    </h3>
                                    
                                    <div class="space-y-4">
                                        <!-- SSS Number -->
                                        <div>
                                            <label for="sss_no" class="block text-sm font-medium text-gray-700 mb-1">SSS Number</label>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-id-badge text-gray-400"></i>
                                                </div>
                                                <input type="text" name="sss_no" id="sss_no" value="33-1234567-8" 
                                                    class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                        </div>

                                        <!-- PhilHealth Number -->
                                        <div>
                                            <label for="philhealth_no" class="block text-sm font-medium text-gray-700 mb-1">PhilHealth Number</label>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-heart text-gray-400"></i>
                                                </div>
                                                <input type="text" name="philhealth_no" id="philhealth_no" value="12-345678901-2" 
                                                    class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                        </div>

                                        <!-- Pag-IBIG Number -->
                                        <div>
                                            <label for="pagibig_no" class="block text-sm font-medium text-gray-700 mb-1">Pag-IBIG Number</label>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-home text-gray-400"></i>
                                                </div>
                                                <input type="text" name="pagibig_no" id="pagibig_no" value="1234-5678-9012" 
                                                    class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-8">
                                <!-- Contact Information Section -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 pb-2 border-b border-gray-200 mb-4">
                                        <i class="fas fa-address-card text-primary-500 mr-2"></i>
                                        Contact Information
                                    </h3>
                                    
                                    <div class="space-y-4">
                                        <!-- Email -->
                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-envelope text-gray-400"></i>
                                                </div>
                                                <input type="email" name="email" id="email" value="manilguezleangelica@gmail.com" 
                                                    class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                        </div>

                                        <!-- Phone Number -->
                                        <div>
                                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-phone-alt text-gray-400"></i>
                                                </div>
                                                <input type="tel" name="phone" id="phone" value="09434242" 
                                                    class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                        </div>

                                        <!-- Address -->
                                        <div>
                                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute top-3 left-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                                </div>
                                                <textarea name="address" id="address" rows="3" 
                                                    class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">42324242</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Emergency Contact Section -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 pb-2 border-b border-gray-200 mb-4">
                                        <i class="fas fa-heartbeat text-primary-500 mr-2"></i>
                                        Emergency Contact
                                    </h3>
                                    
                                    <div class="space-y-4">
                                        <!-- Emergency Contact Person -->
                                        <div>
                                            <label for="emergency_contact_person" class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-user text-gray-400"></i>
                                                </div>
                                                <input type="text" name="emergency_contact_person" id="emergency_contact_person" value="Mary Jane Dizon" 
                                                    class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                        </div>

                                        <!-- Emergency Contact Number -->
                                        <div>
                                            <label for="emergency_contact_number" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-phone-alt text-gray-400"></i>
                                                </div>
                                                <input type="tel" name="emergency_contact_number" id="emergency_contact_number" value="09123456789" 
                                                    class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                        </div>

                                        <!-- Relationship -->
                                        <div>
                                            <label for="relationship" class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-users text-gray-400"></i>
                                                </div>
                                                <select name="relationship" id="relationship" 
                                                    class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                                                    <option value="parent">Parent</option>
                                                    <option value="spouse">Spouse</option>
                                                    <option value="sibling">Sibling</option>
                                                    <option value="relative">Other Relative</option>
                                                    <option value="friend">Friend</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Details Section -->
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 pb-2 border-b border-gray-200 mb-4">
                                <i class="fas fa-building text-primary-500 mr-2"></i>
                                Employment Details
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Username -->
                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-user-tag text-gray-400"></i>
                                        </div>
                                        <input type="text" name="username" id="username" value="athena.dizon" 
                                            class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                                            disabled>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Username cannot be changed</p>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-check-circle text-gray-400"></i>
                                        </div>
                                        <input type="text" name="status" id="status" value="Active" 
                                            class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                                            disabled>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Status can only be changed by admin</p>
                                </div>

                                <!-- Daily Rate -->
                                <div>
                                    <label for="rate" class="block text-sm font-medium text-gray-700 mb-1">Daily Rate</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-money-bill-wave text-gray-400"></i>
                                        </div>
                                        <input type="text" name="rate" id="rate" value="₱1,200.00" 
                                            class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                                            disabled>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Rate can only be changed by admin</p>
                                </div>
                            </div>
                        </div>

                        <!-- Password Change Section -->
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 pb-2 border-b border-gray-200 mb-4">
                                <i class="fas fa-lock text-primary-500 mr-2"></i>
                                Change Password
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- New Password -->
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-lock text-gray-400"></i>
                                        </div>
                                        <input type="password" name="password" id="password" placeholder="••••••••" 
                                            class="block w-full pl-10 py-2.5 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('password')">
                                            <i class="fas fa-eye" id="password-toggle-icon"></i>
                                        </button>
                                    </div>
                                    <p class="mt
