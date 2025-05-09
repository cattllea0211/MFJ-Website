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
    <title>Edit Employee Profile</title>
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
<!-- Top Navigation Bar -->
<nav class="bg-white shadow-md sticky top-0 z-10">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="employee_profile.php" class="flex items-center text-slate-700 hover:text-blue-600 transition-colors duration-200 group">
                    <i class="fas fa-arrow-left mr-2 group-hover:transform group-hover:-translate-x-1 transition-transform duration-200"></i>
                    <span class="font-medium">Back to Profile</span>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-sm font-medium text-slate-600 bg-slate-100 py-2 px-3 rounded-full flex items-center">
                    <i class="fas fa-building mr-2 text-blue-600"></i>
                    MFJ Company
                </div>
                <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center text-white">
                    <i class="fas fa-user-alt text-sm"></i>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="max-w-9xl profile-container mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow">
    <!-- Page Header -->
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-slate-800 text-center">Edit Employee Profile</h1>
        <div class="flex justify-center mt-2">
            <p class="text-slate-500 max-w-lg text-center">Make changes to your personal information below and save when you're finished.</p>
        </div>
        <div class="mt-6 h-1 w-24 bg-blue-600 mx-auto rounded-full"></div>
    </div>

    <?php if (!empty($message)): ?>
    <div class="mb-8 w-full animate-fade-in">
        <div class="<?php echo $messageType === 'success' ? 'bg-green-100 text-green-800 border-green-200' : ($messageType === 'warning' ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 'bg-red-100 text-red-800 border-red-200'); ?> border px-4 py-3 rounded-lg flex items-center shadow-sm">
            <i class="<?php echo $messageType === 'success' ? 'fas fa-check-circle' : ($messageType === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-times-circle'); ?> mr-3 text-xl"></i>
            <span class="font-medium"><?php echo $message; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Profile Form -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-slate-200">
        <!-- Profile Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-8 sm:px-10 relative overflow-hidden">
            <div class="absolute inset-0 bg-pattern opacity-10"></div>
            <div class="relative z-10 flex items-center">
                <div class="bg-white/20 p-3 rounded-full mr-4">
                    <i class="fas fa-user-edit text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-white">Update Your Information</h2>
                    <p class="text-blue-100 mt-1">Only fill out the fields you want to change</p>
                </div>
            </div>
        </div>

    <?php if (!empty($message)): ?>
    <div class="mb-8 w-full animate-fade-in">
        <div class="<?php echo $messageType === 'success' ? 'bg-green-100 text-green-800 border-green-200' : ($messageType === 'warning' ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 'bg-red-100 text-red-800 border-red-200'); ?> border px-4 py-3 rounded-lg flex items-center shadow-sm">
            <i class="<?php echo $messageType === 'success' ? 'fas fa-check-circle' : ($messageType === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-times-circle'); ?> mr-3 text-xl"></i>
            <span class="font-medium"><?php echo $message; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Profile Form -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-slate-200">
        <!-- Profile Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-8 sm:px-10 relative overflow-hidden">
            <div class="absolute inset-0 bg-pattern opacity-10"></div>
            <div class="relative z-10 flex items-center">
                <div class="bg-white/20 p-3 rounded-full mr-4">
                    <i class="fas fa-user-edit text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-white">Update Your Information</h2>
                    <p class="text-blue-100 mt-1">Only fill out the fields you want to change</p>
                </div>
            </div>
        </div>

            <?php if (!empty($message)): ?>
            <div class="mb-8 w-full">
                <div class="<?php echo $messageType === 'success' ? 'bg-green-100 text-green-800 border-green-200' : ($messageType === 'warning' ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 'bg-red-100 text-red-800 border-red-200'); ?> border px-4 py-3 rounded-lg flex items-center">
                    <i class="<?php echo $messageType === 'success' ? 'fas fa-check-circle' : ($messageType === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-times-circle'); ?> mr-3 text-xl"></i>
                    <?php echo $message; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Profile Form -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">

                <!-- Profile Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-10 sm:px-12 relative overflow-hidden">
                    <div class="absolute inset-0 bg-pattern opacity-10"></div>
                    <h2 class="text-2xl font-bold text-white relative z-10">Update Your Information</h2>
                    <p class="text-blue-100 mt-2 relative z-10">Update only the fields you want to change</p>
                </div>

                <!-- Profile Content -->
                <div class="px-8 py-10 sm:px-12 profile-content relative z-10">

                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $id); ?>" method="post" enctype="multipart/form-data">

                        <!-- Profile Picture -->
                        <div class="flex flex-col items-center mb-10">
                            <div class="relative mb-4">
                                <div class="w-32 h-32 rounded-full bg-white p-1.5 shadow-lg">
                                    <div class="w-full h-full rounded-full bg-gradient-to-r from-blue-100 to-indigo-100 flex items-center justify-center overflow-hidden">
                                        <?php if (!empty($employee['picture'])): ?>
                                            <img src="<?php echo htmlspecialchars($employee['picture']); ?>" id="profile-preview" class="h-full w-full object-cover rounded-full" />
                                        <?php else: ?>
                                            <i class="fas fa-user text-6xl text-blue-300" id="profile-icon"></i>
                                            <img src="" id="profile-preview" class="h-full w-full object-cover rounded-full hidden" />
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <label for="picture" class="absolute bottom-0 right-0 bg-indigo-500 text-white rounded-full w-8 h-8 flex items-center justify-center cursor-pointer shadow-md">
                                    <i class="fas fa-camera"></i>
                                    <input type="file" id="picture" name="picture" class="hidden" accept="image/*" onchange="previewImage(this)">
                                </label>
                            </div>
                            <p class="text-sm text-slate-500">Click the camera icon to upload a new profile picture</p>
                        </div>

                        <!-- Form Sections -->
                        <div class="grid info-grid gap-8">

                            <!-- Personal Information -->
                            <div class="bg-slate-50 p-8 rounded-lg shadow-sm">
                                <h3 class="text-xl font-semibold text-slate-800 mb-6 flex items-center">
                                    <i class="fas fa-user-circle text-indigo-500 mr-3"></i>
                                    Personal Information
                                </h3>
                                <div class="space-y-6">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                                        <input type="text" name="name" id="name" 
                                               value="<?php echo htmlspecialchars($employee['name']); ?>" 
                                               class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label for="birthdate" class="block text-sm font-medium text-slate-700 mb-1">Birthdate</label>
                                        <input type="date" name="birthdate" id="birthdate" 
                                               value="<?php echo htmlspecialchars($employee['birthdate']); ?>" 
                                               class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="position" class="block text-sm font-medium text-slate-700 mb-1">Position</label>
                                            <input type="text" name="position" id="position" disabled
                                                   value="<?php echo htmlspecialchars($employee['position']); ?>" 
                                                   class="w-full rounded-md bg-slate-100 border-slate-300 text-slate-500">
                                            <p class="mt-1 text-xs text-slate-500">Position can only be changed by admin</p>
                                        </div>
                                        <div>
                                            <label for="role" class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                                            <input type="text" name="role" id="role" disabled
                                                   value="<?php echo htmlspecialchars($employee['role']); ?>" 
                                                   class="w-full rounded-md bg-slate-100 border-slate-300 text-slate-500">
                                            <p class="mt-1 text-xs text-slate-500">Role can only be changed by admin</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="bg-slate-50 p-8 rounded-lg shadow-sm">
                                <h3 class="text-xl font-semibold text-slate-800 mb-6 flex items-center">
                                    <i class="fas fa-address-card text-indigo-500 mr-3"></i>
                                    Contact Information
                                </h3>
                                <div class="space-y-6">
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                                        <input type="email" name="email" id="email" 
                                               value="<?php echo htmlspecialchars($employee['email']); ?>" 
                                               class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                                        <input type="tel" name="phone" id="phone" 
                                               value="<?php echo htmlspecialchars($employee['phone']); ?>" 
                                               class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label for="address" class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                                        <textarea name="address" id="address" rows="3" 
                                                  class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><?php echo htmlspecialchars($employee['address']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact -->
                            <div class="bg-slate-50 p-8 rounded-lg shadow-sm">
                                <h3 class="text-xl font-semibold text-slate-800 mb-6 flex items-center">
                                    <i class="fas fa-heartbeat text-indigo-500 mr-3"></i>
                                    Emergency Contact
                                </h3>
                                <div class="space-y-6">
                                    <div>
                                        <label for="emergency_contact_person" class="block text-sm font-medium text-slate-700 mb-1">Contact Person</label>
                                        <input type="text" name="emergency_contact_person" id="emergency_contact_person" 
                                               value="<?php echo htmlspecialchars($employee['emergency_contact_person']); ?>" 
                                               class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label for="emergency_contact_number" class="block text-sm font-medium text-slate-700 mb-1">Contact Number</label>
                                        <input type="tel" name="emergency_contact_number" id="emergency_contact_number" 
                                               value="<?php echo htmlspecialchars($employee['emergency_contact_number']); ?>" 
                                               class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Government IDs -->
                            <div class="bg-slate-50 p-8 rounded-lg shadow-sm">
                                <h3 class="text-xl font-semibold text-slate-800 mb-6 flex items-center">
                                    <i class="fas fa-id-card text-indigo-500 mr-3"></i>
                                    Government IDs
                                </h3>
                                <div class="space-y-6">
                                    <div>
                                        <label for="sss_no" class="block text-sm font-medium text-slate-700 mb-1">SSS Number</label>
                                        <input type="text" name="sss_no" id="sss_no" 
                                               value="<?php echo htmlspecialchars($employee['sss_no']); ?>" 
                                               class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label for="philhealth_no" class="block text-sm font-medium text-slate-700 mb-1">PhilHealth Number</label>
                                        <input type="text" name="philhealth_no" id="philhealth_no" 
                                               value="<?php echo htmlspecialchars($employee['philhealth_no']); ?>" 
                                               class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label for="pagibig_no" class="block text-sm font-medium text-slate-700 mb-1">Pag-IBIG Number</label>
                                        <input type="text" name="pagibig_no" id="pagibig_no" 
                                               value="<?php echo htmlspecialchars($employee['pagibig_no']); ?>" 
                                               class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Employment Details -->
                            <div class="bg-slate-50 p-8 rounded-lg shadow-sm col-span-full">
                                <h3 class="text-xl font-semibold text-slate-800 mb-6 flex items-center">
                                    <i class="fas fa-building text-indigo-500 mr-3"></i>
                                    Employment Details
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                                        <input type="text" name="username" id="username" disabled
                                               value="<?php echo htmlspecialchars($employee['username']); ?>" 
                                               class="w-full rounded-md bg-slate-100 border-slate-300 text-slate-500">
                                        <p class="mt-1 text-xs text-slate-500">Username cannot be changed</p>
                                    </div>
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                                        <input type="text" name="status" id="status" disabled
                                               value="<?php echo htmlspecialchars($employee['status']); ?>" 
                                               class="w-full rounded-md bg-slate-100 border-slate-300 text-slate-500">
                                        <p class="mt-1 text-xs text-slate-500">Status can only be changed by admin</p>
                                    </div>
                                    <div>
                                        <label for="rate" class="block text-sm font-medium text-slate-700 mb-1">Daily Rate</label>
                                        <input type="text" name="rate" id="rate" disabled
                                               value="₱<?php echo number_format($employee['rate_per_day'], 2); ?>" 
                                               class="w-full rounded-md bg-slate-100 border-slate-300 text-slate-500">
                                        <p class="mt-1 text-xs text-slate-500">Rate can only be changed by admin</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Change -->
                            <div class="bg-slate-50 p-8 rounded-lg shadow-sm col-span-full">
                                <h3 class="text-xl font-semibold text-slate-800 mb-6 flex items-center">
                                    <i class="fas fa-lock text-indigo-500 mr-3"></i>
                                    Change Password
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                                        <input type="password" name="password" id="password" 
                                               class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <p class="mt-1 text-xs text-slate-500">Leave blank if you don't want to change</p>
                                    </div>
                                    <div>
                                        <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                                        <input type="password" name="confirm_password" id="confirm_password" 
                                               class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="mt-10 flex justify-center space-x-4">
                            <a href="employee_profile.php" class="px-6 py-3 bg-slate-200 text-slate-700 rounded-md hover:bg-slate-300 transition-colors duration-200 flex items-center">
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </a>
                            <button type="submit" class="px-10 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 flex items-center">
                                <i class="fas fa-save mr-2"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
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
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    const preview = document.getElementById('profile-preview');
                    const icon = document.getElementById('profile-icon');
                    
                    preview.setAttribute('src', e.target.result);
                    preview.classList.remove('hidden');
                    
                    if (icon) {
                        icon.classList.add('hidden');
                    }
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
