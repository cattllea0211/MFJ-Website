<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
       body {
        font-family: 'Roboto', Arial, sans-serif;
        background: linear-gradient(135deg, #61a2ba, #2b5876);
        margin: 0;
        padding: 0;
        color: #ffffff;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh; /* Ensure the body takes full viewport height */
        animation: fadeInBody 1s ease-out; /* Apply fade-in effect */
    }

        @keyframes fadeInBody {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }


        video.background-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1; 
        }

        .container {
            width: 800px; 
            max-width: 90%; 
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #e3e3e3;
            margin: auto; 
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
        }

        form {
            display: grid;
            gap: 20px;
        }

        label {
            font-weight: 500;
            color: #333;
        }

        input[type="text"], input[type="date"], input[type="time"] {
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
            box-sizing: border-box;
            background-color: #f9f9f9;
            font-family: 'Roboto', Arial, sans-serif;
        }

        input[type="text"]:disabled, input[type="date"]:disabled {
            background-color: #e9ecef;
        }

        input[type="text"]:focus, input[type="date"]:focus, input[type="time"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        button {
            padding: 12px 25px;
            background-color:#61a2ba;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-family: 'Roboto', Arial, sans-serif;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-button {
            text-align: center;
            margin-top: 20px;
        }

        .back-button a {
            text-decoration: none;
            color:#61a2ba;
            font-size: 16px;
            font-weight: 500;
        }

        .back-button a:hover {
            text-decoration: underline;
        }

        .message, .error {
            padding: 10px;
            margin-top: 15px;
            text-align: center;
            border-radius: 5px;
            font-family: 'Roboto', Arial, sans-serif;
            font-weight: 500;
        }

        .message {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        img {
            position: fixed; 
            bottom: 10px;    
            left: -30px;      
            width: 300px;    
            z-index: 10;   
        }
    </style>

</head>
<body>
  
        <video class="background-video" autoplay muted loop>
            <source src="bluebg2.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>

    <div class="container">
        <h2>Edit Attendance</h2>

        <form method="post" action="">
            <div>
                <label for="employeeName">Employee Name:</label>
                <input type="text" id="employeeName" name="employeeName" value="<?php echo $row['employee_name']; ?>" disabled>
            </div>

            <div>
                <label for="attendanceDate">Attendance Date:</label>
                <input type="date" id="attendanceDate" name="attendanceDate" value="<?php echo $row['attendance_date']; ?>" disabled>
            </div>

            <div>
                <label for="clockIn">Clock In:</label>
                <input type="time" id="clockIn" name="clockIn" value="<?php echo $row['clock_in']; ?>" required>
            </div>

            <div>
                <label for="clockOut">Clock Out:</label>
                <input type="time" id="clockOut" name="clockOut" value="<?php echo $row['clock_out']; ?>" required>
            </div>

            <button type="submit">Update Attendance</button>
        </form>

        <div class="back-button">
            <a href="view_attendance.php">Back to Attendance</a>
        </div>
    </div>

</body>
<img src="whitelogo2.png" alt="Company Logo" />

</html>
