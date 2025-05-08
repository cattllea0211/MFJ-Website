<?php
session_start();

if (!isset($_SESSION['username'])) {
    die("Access denied. Please log in first.");
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfj_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];

// Get employee information
$stmt = $conn->prepare("SELECT id, name FROM employees WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($employee_id, $employee_name);
$stmt->fetch();
$stmt->close();

if (empty($employee_id)) {
    die("Employee not found.");
}

date_default_timezone_set('Asia/Manila');

// Handle clock-in
if (isset($_POST['clock_in_button'])) {
    $employee_id = $_POST['employee_id'];
    $name = $employee_name;
    $clock_in = date('H:i');
    $attendance_date = date('Y-m-d');

    // Prevent duplicate clock-in
    $stmt = $conn->prepare("SELECT id FROM attendance WHERE employee_id = ? AND attendance_date = ?");
    $stmt->bind_param("is", $employee_id, $attendance_date);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO attendance (employee_id, name, clock_in, attendance_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $employee_id, $name, $clock_in, $attendance_date);

        if ($stmt->execute()) {
            $message = "Clock-in time recorded successfully!";
            $message_type = "success";
        } else {
            $message = "Error recording clock-in time.";
            $message_type = "error";
        }
    } else {
        $message = "You have already clocked in today!";
        $message_type = "error";
    }

    $stmt->close();
}

// Handle clock-out
if (isset($_POST['clock_out_button'])) {
    $employee_id = $_POST['employee_id'];
    $clock_out = date('H:i');

    $stmt = $conn->prepare("UPDATE attendance SET clock_out = ? WHERE employee_id = ? AND clock_out IS NULL");
    $stmt->bind_param("si", $clock_out, $employee_id);

    if ($stmt->execute()) {
        $message = "Clock-out time recorded successfully!";
        $message_type = "success";
    } else {
        $message = "Error recording clock-out time.";
        $message_type = "error";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFJ Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'accent': {
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
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background-image: 
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23e0f2fe' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"),
                linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
            background-attachment: fixed;
        }
        
        .card {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
        }
        
        .time-display {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            transition: all 0.3s ease;
            border: 1px solid rgba(224, 242, 254, 0.7);
        }
        
        .time-display:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.05);
            background: rgba(255, 255, 255, 0.8);
        }
        
        .btn {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        
        .btn:hover::after {
            transform: scaleX(1);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .back-button {
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            transform: translateX(-3px);
        }
        
        .header-bg {
            background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
        }
        
        .floating-card {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .modal-bg {
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body class="flex items-center justify-center p-6">
    <div class="w-full max-w-3xl card bg-white rounded-2xl overflow-hidden fade-in floating-card">
        <!-- Header -->
        <div class="p-8 flex items-center justify-between border-b border-slate-100 header-bg text-white">
            <div class="flex items-center">
                <a href="employee_dashboard.php" class="back-button mr-6 bg-white bg-opacity-20 text-white hover:bg-opacity-30 p-2 rounded-full transition flex items-center justify-center">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold">Attendance Portal</h1>
                    <p class="text-sm text-white text-opacity-80 flex items-center">
                        <i data-lucide="calendar" class="w-3 h-3 mr-1"></i> Track your work hours
                    </p>
                </div>
            </div>
            <div class="text-right flex items-center">
                <div class="mr-3 bg-white bg-opacity-20 text-white p-2 rounded-full">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-sm font-medium"><?php echo htmlspecialchars($employee_name); ?></div>
                    <div class="text-xs text-white text-opacity-70 flex items-center justify-end">
                        <i data-lucide="id-card" class="w-3 h-3 mr-1"></i> #<?php echo $employee_id; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8 space-y-8 bg-white bg-opacity-90">
            <!-- Notification message -->
            <?php if (isset($message)) : ?>
                <div class="<?php echo $message_type === 'success' 
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                    : 'bg-rose-50 text-rose-700 border-rose-200'; ?> 
                    border px-4 py-3 rounded-lg text-sm fade-in flex items-center">
                    <i data-lucide="<?php echo $message_type === 'success' ? 'check-circle' : 'alert-circle'; ?>" class="w-5 h-5 mr-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Time display -->
            <div class="time-display p-6 mb-8 text-center">
                <div id="date" class="text-base text-slate-500 mb-1 flex items-center justify-center">
                    <i data-lucide="calendar-days" class="w-4 h-4 mr-2"></i>
                    <span id="date-text">Loading...</span>
                </div>
                <div id="clock" class="text-5xl font-light text-slate-800 mb-1">--:--:-- --</div>
                <div class="text-xs text-slate-400 flex items-center justify-center">
                    <i data-lucide="globe" class="w-3 h-3 mr-1"></i> Manila Time
                </div>
            </div>

            <!-- Clock in/out buttons -->
            <form action="attendance_form.php" method="POST">
                <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">
                
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button 
                        type="submit" 
                        name="clock_in_button" 
                        class="btn bg-accent-600 text-white py-3 px-6 rounded-lg hover:bg-accent-700 flex items-center justify-center shadow-md hover:shadow-lg"
                    >
                        <i data-lucide="log-in" class="w-5 h-5 mr-2"></i>
                        Clock In
                    </button>
                    <button 
                        type="submit" 
                        name="clock_out_button" 
                        class="btn bg-slate-700 text-white py-3 px-6 rounded-lg hover:bg-slate-800 flex items-center justify-center shadow-md hover:shadow-lg"
                    >
                        <i data-lucide="log-out" class="w-5 h-5 mr-2"></i>
                        Clock Out
                    </button>
                </div>
            </form>
            
            <!-- Today's status -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <h3 class="text-sm font-medium text-slate-500 mb-3 flex items-center">
                    <i data-lucide="clipboard-list" class="w-4 h-4 mr-2"></i> Today's Status
                </h3>
                <?php
                // Check today's attendance
                $today = date('Y-m-d');
                $status_stmt = $conn->prepare("SELECT clock_in, clock_out FROM attendance WHERE employee_id = ? AND attendance_date = ?");
                $status_stmt->bind_param("is", $employee_id, $today);
                $status_stmt->execute();
                $status_stmt->bind_result($today_clock_in, $today_clock_out);
                $has_record = $status_stmt->fetch();
                $status_stmt->close();
                ?>
                
                <div class="flex flex-col sm:flex-row justify-between text-sm p-4 bg-slate-50 rounded-lg space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="flex-1">
                        <div class="text-slate-500 flex items-center">
                            <i data-lucide="log-in" class="w-3 h-3 mr-1"></i> Clock In
                        </div>
                        <div class="font-medium text-slate-800"><?php echo $has_record && $today_clock_in ? $today_clock_in : '—'; ?></div>
                    </div>
                    <div class="flex-1">
                        <div class="text-slate-500 flex items-center">
                            <i data-lucide="log-out" class="w-3 h-3 mr-1"></i> Clock Out
                        </div>
                        <div class="font-medium text-slate-800"><?php echo $has_record && $today_clock_out ? $today_clock_out : '—'; ?></div>
                    </div>
                    <div class="flex-1">
                        <div class="text-slate-500 flex items-center">
                            <i data-lucide="activity" class="w-3 h-3 mr-1"></i> Status
                        </div>
                        <div class="font-medium <?php echo $has_record ? ($today_clock_out ? 'text-emerald-600' : 'text-amber-600') : 'text-slate-400'; ?> flex items-center">
                            <i data-lucide="<?php echo $has_record ? ($today_clock_out ? 'check-circle' : 'clock') : 'circle'; ?>" class="w-3 h-3 mr-1"></i>
                            <?php echo $has_record ? ($today_clock_out ? 'Completed' : 'Active') : 'Not Started'; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Navigation -->
            <div class="mt-6 pt-6 border-t border-slate-100">
            <div class="flex flex-wrap justify-center gap-2">
                    <a href="view_attendance.php" class="text-accent-600 hover:text-accent-800 flex items-center text-sm p-2 hover:bg-accent-50 rounded-md transition">
                        <i data-lucide="history" class="w-4 h-4 mr-1"></i> History
                    </a>
                    <a href="employee_dashboard.php" class="text-accent-600 hover:text-accent-800 flex items-center text-sm p-2 hover:bg-accent-50 rounded-md transition">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 mr-1"></i> Dashboard
                    </a>
                    <a href="employee_profile.php" class="text-accent-600 hover:text-accent-800 flex items-center text-sm p-2 hover:bg-accent-50 rounded-md transition">
                        <i data-lucide="user" class="w-4 h-4 mr-1"></i> Profile
                    </a>
                    <a href="#" id="help-button" class="text-accent-600 hover:text-accent-800 flex items-center text-sm p-2 hover:bg-accent-50 rounded-md transition">
                        <i data-lucide="help-circle" class="w-4 h-4 mr-1"></i> Help
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="p-4 bg-slate-50 border-t border-slate-100 text-center text-xs text-slate-400">
            <div class="flex items-center justify-center">
                <i data-lucide="building" class="w-3 h-3 mr-1"></i>
                MFJ Company © <?php echo date('Y'); ?> | Employee Attendance System
            </div>
        </div>
    </div>
    
    <!-- Help Modal (hidden by default) -->
    <div id="help-modal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden modal-bg">
        <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center">
                    <i data-lucide="help-circle" class="w-5 h-5 mr-2 text-accent-600"></i>
                    Attendance Help
                </h3>
                <button id="close-modal" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="space-y-4 text-sm text-slate-600">
                <div class="flex p-3 hover:bg-slate-50 rounded-lg transition">
                    <i data-lucide="info" class="w-5 h-5 mr-3 text-accent-600 flex-shrink-0"></i>
                    <p>Use <b>Clock In</b> when you start your work day and <b>Clock Out</b> when you finish.</p>
                </div>
                <div class="flex p-3 hover:bg-slate-50 rounded-lg transition">
                    <i data-lucide="clock" class="w-5 h-5 mr-3 text-accent-600 flex-shrink-0"></i>
                    <p>The system uses Manila time (GMT+8) for all attendance records.</p>
                </div>
                <div class="flex p-3 hover:bg-slate-50 rounded-lg transition">
                    <i data-lucide="alert-circle" class="w-5 h-5 mr-3 text-accent-600 flex-shrink-0"></i>
                    <p>You can only clock in once per day. Contact HR if you need to correct your attendance.</p>
                </div>
                <div class="flex p-3 hover:bg-slate-50 rounded-lg transition">
                    <i data-lucide="history" class="w-5 h-5 mr-3 text-accent-600 flex-shrink-0"></i>
                    <p>View your attendance history by clicking on the History link below.</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button id="close-help" class="bg-accent-600 text-white py-2 px-4 rounded-lg hover:bg-accent-700 text-sm flex items-center shadow-md">
                    <i data-lucide="check" class="w-4 h-4 mr-1"></i> Got it
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize icons
            lucide.createIcons();
            
            // Clock functionality
            function updateClock() {
                const now = new Date();
                
                const clock = document.getElementById('clock');
                const dateElement = document.getElementById('date-text');
                
                if (!clock || !dateElement) return;
                
                const options = { 
                    timeZone: 'Asia/Manila',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                };
                
                const dateOptions = {
                    timeZone: 'Asia/Manila',
                    weekday: 'long',
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                };
                
                const timeString = new Intl.DateTimeFormat('en-US', options).format(new Date());
                const dateString = new Intl.DateTimeFormat('en-US', dateOptions).format(new Date());
                
                clock.textContent = timeString;
                dateElement.textContent = dateString;
            }
            
            updateClock();
            setInterval(updateClock, 1000);
            
            // Help modal functionality
            const helpButton = document.getElementById('help-button');
            const helpModal = document.getElementById('help-modal');
            const closeModal = document.getElementById('close-modal');
            const closeHelp = document.getElementById('close-help');
            
            if (helpButton && helpModal && closeModal && closeHelp) {
                helpButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    helpModal.classList.remove('hidden');
                });
                
                closeModal.addEventListener('click', () => {
                    helpModal.classList.add('hidden');
                });
                
                closeHelp.addEventListener('click', () => {
                    helpModal.classList.add('hidden');
                });
                
                // Close modal if clicking outside
                helpModal.addEventListener('click', (e) => {
                    if (e.target === helpModal) {
                        helpModal.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>