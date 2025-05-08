<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFJ - Air Conditioning Experts</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-background: #f8f9fa;
            --text-color: #333;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            line-height: 1.6;
            background-color: var(--light-background);
            color: var(--text-color);
        }

        .navbar {
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 15px 50px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
        }

        .navbar:hover {
            background-color: #f9f9f9;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 70px;
            width: auto;
            margin-right: 15px;
            transition: transform 0.3s ease;
        }

        .logo img:hover {
            transform: scale(1.1);
        }

        .company-name h2 {
            font-size: 24px;
            color: var(--primary-color);
            margin: 0;
            letter-spacing: 1px;
        }

        .company-name .tagline {
            font-size: 14px;
            color: var(--secondary-color);
            margin: 0;
            font-weight: 500;
        }

        .navbar ul {
            list-style-type: none;
            display: flex;
            align-items: center;
        }

        .navbar ul li {
            margin: 0 15px;
        }

        .navbar ul li a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            position: relative;
            transition: color 0.3s ease;
        }

        .navbar ul li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--accent-color);
            left: 0;
            bottom: -5px;
            transition: width 0.3s ease;
        }

        .navbar ul li a:hover {
            color: var(--accent-color);
        }

        .navbar ul li a:hover::after {
            width: 100%;
        }


        .container {
            background-image: linear-gradient(rgba(44, 62, 80, 0.3), rgba(44, 62, 80, 0.7)), url('background.png');
            background-size: cover;
            background-position: center;
            height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .hero-content {
            max-width: 800px;
            padding: 0 20px;
        }

        .hero h1 {
            font-size: 70px;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .hero p {
            font-size: 1.5rem;
            margin-bottom: 30px;
            line-height: 1.4;
            color: rgba(255, 255, 255, 0.9);
        }

        .cta-button {
            display: inline-block;
            background-color: var(--accent-color);
            color: white;
            padding: 12px 30px;
            font-size: 18px;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .cta-button:hover {
            background-color: #c0392b;
            transform: translateY(-3px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
        }

     
        .services {
            padding: 80px 50px;
            background-color: white;
        }

        .services h2 {
            text-align: center;
            font-size: 36px;
            color: var(--primary-color);
            margin-bottom: 50px;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .service-box {
            background-color: var(--light-background);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .service-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .service-box i {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }

        .service-box h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .service-box p {
            color: #666;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.modal-show {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background: white;
            width: 400px;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            transform: scale(0.7);
            transition: all 0.3s ease;
        }

        .modal-show .modal-content {
            transform: scale(1);
        }

        .modal-content h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
        }

        .modal-content .user-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .modal-content button {
            padding: 12px 30px;
            font-size: 16px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-content .client-btn {
            background-color: var(--secondary-color);
            color: white;
        }

        .modal-content .employee-btn {
            background-color: var(--accent-color);
            color: white;
        }

        .modal-content button:hover {
            opacity: 0.9;
            transform: translateY(-3px);
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #888;
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: var(--accent-color);
        }

        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div id="userTypeModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h2>Select Your Access</h2>
            <div class="user-buttons">
                <button class="client-btn" onclick="redirectToPage('client')">
                    <i class="fas fa-user"></i> Client Login
                </button>
                <button class="employee-btn" onclick="redirectToPage('employee')">
                    <i class="fas fa-briefcase"></i> Employee Login
                </button>
            </div>
        </div>
    </div>

    <header>
        <nav class="navbar">
            <div class="logo-container">
                <div class="logo">
                    <img src="logo_clear.png" alt="MFJ Logo">
                </div>
                <div class="company-name">
                    <h2>MFJ</h2>
                    <p class="tagline">Airconditioning Supply and Services</p>
                </div>
            </div>
            <ul>
                <li><a href="homepage.php">HOME</a></li>
                <li><a href="aboutpage.php">ABOUT</a></li>
                <li><a href="services.php">SERVICES</a></li>
                <li><a href="contact.php">CONTACT</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <section class="hero">
            <div class="hero-content">
                <h1>MFJ Air Conditioning</h1>

                <p>Expert solutions for your comfort. Reliable service, innovative technology, and customer-first approach.</p>
                <a href="#" onclick="openModal()" class="cta-button">Get Started</a>
            </div>
        </section>
    </div>

    <section class="services">
        <h2>Our Services</h2>
        <div class="services-grid">
            <div class="service-box">
                <i class="fas fa-tools"></i>
                <h3>Installation</h3>
                <p>Professional air conditioning system installation tailored to your space.</p>
            </div>
            <div class="service-box">
                <i class="fas fa-wrench"></i>
                <h3>Maintenance</h3>
                <p>Regular servicing to ensure optimal performance and longevity.</p>
            </div>
            <div class="service-box">
                <i class="fas fa-sync"></i>
                <h3>Repair</h3>
                <p>Quick and efficient repairs to get your system back to peak condition.</p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 MFJ Airconditioning Supply and Services. All rights reserved.</p>
    </footer>

    <script>
        function openModal() {
            const modal = document.getElementById('userTypeModal');
            modal.classList.add('modal-show');
        }

        function closeModal() {
            const modal = document.getElementById('userTypeModal');
            modal.classList.remove('modal-show');
        }

        function redirectToPage(userType) {
            if (userType === 'client') {
                window.location.href = 'login.php';
            } else if (userType === 'employee') {
                window.location.href = 'employee_login.php';
            }
        }
    </script>
</body>
</html>