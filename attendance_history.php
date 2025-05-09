<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];

$pdo = new PDO("mysql:host=localhost;dbname=mfjdb", "root", "");

// Fetch the user's name
$name_stmt = $pdo->prepare("SELECT name FROM employees WHERE id = :id");
$name_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$name_stmt->execute();
$name_result = $name_stmt->fetch(PDO::FETCH_ASSOC);
$name = $name_result ? $name_result['name'] : 'User';

// Fetch attendance records
$sql = "SELECT attendance_date, clock_in, clock_out FROM attendance WHERE employee_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-teal': '#1B7F97', /* MFJ teal color from first image */
                        'brand-green': '#10b981',
                        'brand-gray': '#f8fafc',
                        'brand-dark': '#1e293b'
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            background-attachment: fixed;
        }
        .glass-effect {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.8);
        }
        .table-container {
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }
        /* Changed the header gradient to match the teal color from first image */
        .header-gradient {
            background-color: #1B7F97; /* Solid teal color */
        }
        .table-header {
            background-color: #10b981; /* Kept green for table header */
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-6xl glass-effect shadow-2xl rounded-3xl overflow-hidden">
        <div class="header-gradient text-white p-6 flex flex-col sm:flex-row sm:justify-between gap-4 sm:items-center">
            <div class="flex flex-col sm:flex-row items-start sm:items-center sm:space-x-4 space-y-2 sm:space-y-0">
                <a href="employee_dashboard.php" class="bg-white/20 hover:bg-white/30 p-2 rounded-full transition transform hover:scale-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold">Attendance History</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-lg">Welcome, <?php echo htmlspecialchars($name); ?></span>
                <a href="logout.php" class="bg-white text-brand-teal px-5 py-2 rounded-lg hover:bg-opacity-90 transition transform hover:scale-105 font-medium">
                    Logout
                </a>
            </div>
        </div>

        <div class="p-8 bg-gradient-to-br from-white to-brand-gray">
            <?php if (empty($attendance_records)): ?>
                <div class="text-center py-16 bg-white rounded-xl shadow-sm">
                    <svg class="mx-auto h-20 w-20 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                    <p class="mt-6 text-xl text-gray-600 font-light">No attendance records found</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto table-container rounded-xl">
                    <table class="w-full min-w-[500px] rounded-xl overflow-hidden border-collapse text-sm sm:text-base">
                        <thead class="table-header text-white">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold">Attendance Date</th>
                                <th class="px-6 py-4 text-left font-semibold">Clock In</th>
                                <th class="px-6 py-4 text-left font-semibold">Clock Out</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php foreach ($attendance_records as $record): ?>
                                <tr class="hover:bg-brand-gray/50 transition duration-200">
                                    <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($record['attendance_date']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($record['clock_in']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($record['clock_out']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="px-8 py-6 text-center text-brand-dark/60 text-sm">
            <p>© 2025 MFJ Air Conditioning Supply & Services | All Rights Reserved</p>
        </div>
    </div>
</body>
</html>
