<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MFJ Airconditioning Supply and Services</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: lightsteelblue;
            color: black;
        }

        /* Container styling */
        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
            margin-top: 120px; /* Adjusted for fixed navbar */
            background-color: #fff; /* White background for contrast */
            border-radius: 8px; /* Rounded corners for a soft look */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Soft shadow for depth */
            overflow: hidden; /* Prevents content from overflowing the container */
            margin-top: 50px;
            animation: fadeIn 1s ease-out; /* Fade-in animation */
        }

        /* Adding some internal padding for more breathing space */
        .container h1,
        .container h2,
        .container p {
            margin-bottom: 20px;
            color: #333; /* Dark text for better readability */
        }

        /* Optionally, you can add padding for each individual section within the container */
        .container section {
            padding: 20px 0;
        }

        /* Keyframes for fadeIn animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px); /* Slide in from below */
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

       .contact-form {
            background: linear-gradient(135deg, #007bff, #00aaff);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            color: white;
            max-width: 600px;
            margin: 0 auto;
            animation: fadeIn 1s ease-out; /* Apply fade-in animation to contact form */
            padding-top: 100px;
        }

        .contact-form h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #ffffff;
            font-size: 28px;
        }

        /* Form Group Styling */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: bold;
            display: block;
            color: black; /* Changed to black */
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 16px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #0056b3;
            outline: none;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        /* Submit Button Styling */
        .form-group button {
            background-color: lightsteelblue;
            color: black;
            font-weight: bolder;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center; /* Centers the button horizontally */
        }

        .form-group button:hover {
            background-color: #218838;
        }

        .form-group button:focus {
            outline: none;
        }

    
        /* Contact Information Box Styling */
        .contact-info {
            margin-top: 120px; /* Adjusted margin-top to ensure it doesn't overlap with the navbar */
            text-align: center;
            font-size: 16px;
            padding: 20px;
            background-color: #fff; /* White background for the box */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Soft shadow for depth */
            border: 1px solid #ccc; /* Border around the box */
            max-width: 1900px; /* Maximum width of the contact info box */
            margin: 30px auto; /* Center the box on the page */
            opacity: 0; /* Start with zero opacity */
            animation: fadeIn 1s ease-out forwards; /* Fade-in animation */
            margin-top: 150px;
        }

        /* Keyframes for fadeIn animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px); /* Starts from below */
            }
            to {
                opacity: 1;
                transform: translateY(0); /* Ends at the normal position */
            }
        }


        /* Inside content */
        .contact-info p {
            margin: 5px 0;
            color: #333; /* Dark text color for readability */
            padding-top: 5px;
            margin-top: 8px;
        }
        .contact-info h3{
            font-size: 36px;
        }


        /* Navigation Bar */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            margin-left: 50px;
        }

        .logo img {
            height: 70px;
            width: auto;
            margin-right: 10px;
        }

        .company-name h2 {
            font-size: 28px;
            color: steelblue;
            margin: 0;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            margin-right: 50px;
        }

        .navbar ul li {
            margin: 0 20px;
        }

        .navbar ul li a {
            color: steelblue;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            padding: 10px;
            position: relative;
            transition: color 0.3s ease;
        }

        .navbar ul li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background-color: #ff5733;
            left: 0;
            bottom: -5px;
            transition: width 0.3s ease;
        }

        .navbar ul li a:hover {
            color: #ff5733;
        }

        .navbar ul li a:hover::after {
            width: 100%;
        }

        /* Mobile responsive design */
        @media (max-width: 768px) {
            .container {
                width: 90%;
            }

            .navbar ul {
                flex-direction: column;
                align-items: center;
            }

            .contact-form {
                padding: 20px;
            }

            .form-group input,
            .form-group textarea {
                font-size: 14px;
            }

            .form-group button {
                padding: 10px 20px;
                align-items: center;
                text-align: center; /* Centers the button horizontally */
            }
        }

.navbar {
    background-color: #5a8aa6; /* Muted blue */
    padding: 40px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #fff;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar .logo {
    font-size: 22px;
    font-weight: bold;
    display: flex;
    align-items: center;
}

.navbar .logo img {
    width: 50px;
    height: 50px;
    margin-right: 10px;
    border-radius: 50%;
    object-fit: cover;
}

.navbar .menu {
    display: flex;
    gap: 20px;
}

.navbar .menu li {
    list-style: none;
}

.navbar .menu li a {
    text-decoration: none;
    color: #fff;
    font-size: 16px;
    padding: 10px 20px;
    border-radius: 5px;
    transition: background-color 0.3s ease, transform 0.2s;
}

.navbar .menu li a:hover {
    background-color: #49758c; /* Slightly darker muted blue */
    transform: scale(1.05);
}

    </style>
</head>
<body>
        <div class="navbar">
        <div class="logo">
            <img src="/logo.png" alt="Company Logo">
            MFJ Airconditioning Supply & Services
        </div>

        <ul class="menu">
            <li><a href="/client_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="/update_profile.php"><i class="fas fa-user-edit"></i> Update Profile</a></li>
            <li><a href="/view_transaction.php"><i class="fas fa-calendar-alt"></i> Appointments</a></li>
            <li><a href="/client_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="/submit_feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a></li>
           <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

        </ul>
    </div>

     <div class="container">
        
            <iframe
              id="JotFormIFrame-243203414938454"
              title="Appointment Request Form"
              onload="window.parent.scrollTo(0,0)"
              allowtransparency="true"
              allow="geolocation; microphone; camera; fullscreen"
              src="https://form.jotform.com/243203414938454"
              frameborder="0"
              style="min-width:100%;max-width:100%;height:539px;border:none;"
              scrolling="no"
            >
            </iframe>
            <script src='https://cdn.jotfor.ms/s/umd/latest/for-form-embed-handler.js'></script>
            <script>window.jotformEmbedHandler("iframe[id='JotFormIFrame-243203414938454']", "https://form.jotform.com/")</script>
            
</body>
</html>
