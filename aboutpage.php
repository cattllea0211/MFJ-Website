<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | MFJ Airconditioning Supply and Services</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3a86ff;
            --secondary: #0a2463;
            --accent: #ff7b00;
            --light: #f8f9fa;
            --dark: #212529;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            background-color: var(--light);
            color: var(--dark);
            padding-top: 80px;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: white;
            box-shadow: var(--shadow);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 60px;
            margin-right: 15px;
        }

        .company-name h2 {
            font-size: 1.5rem;
            color: var(--secondary);
            font-weight: 600;
            margin: 0;
        }

        .company-name .tagline {
            font-size: 0.75rem;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        .nav-links {
            list-style-type: none;
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            position: relative;
            padding: 0.5rem 0;
            transition: var(--transition);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--accent);
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--accent);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .mobile-menu {
            display: none;
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--secondary);
        }

        .main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            margin-bottom: 5rem;
        }

        .hero-content {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .section-label {
            color: var(--primary);
            font-weight: 500;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 0.5rem;
        }

        .hero-title {
            font-size: 2.5rem;
            color: var(--secondary);
            font-weight: 700;
            line-height: 1.2;
        }

        .hero-text {
            font-size: 1rem;
            color: #555;
            margin-bottom: 0.5rem;
        }

        .cta-button {
            display: inline-block;
            background-color: var(--accent);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(255, 123, 0, 0.2);
            margin-top: 1rem;
            align-self: flex-start;
        }

        .cta-button:hover {
            background-color: #e56e00;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(255, 123, 0, 0.3);
        }

        .team-section {
            background-color: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .team-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--accent));
        }

        .team-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-top: 1.5rem;
        }

        .team-member {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .avatar:hover img {
            transform: scale(1.1);
        }

        .member-info {
            display: flex;
            flex-direction: column;
        }

        .member-name {
            font-weight: 600;
            color: var(--secondary);
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }

        .member-title {
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 5rem;
        }

        .feature-card {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-top: 4px solid transparent;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
        }

        .feature-card:nth-child(1) {
            border-color: #3a86ff;
        }

        .feature-card:nth-child(2) {
            border-color: #8338ec;
        }

        .feature-card:nth-child(3) {
            border-color: #ff7b00;
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
            background-color: rgba(58, 134, 255, 0.1);
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .feature-card:nth-child(2) .feature-icon {
            color: #8338ec;
            background-color: rgba(131, 56, 236, 0.1);
        }

        .feature-card:nth-child(3) .feature-icon {
            color: #ff7b00;
            background-color: rgba(255, 123, 0, 0.1);
        }

        .feature-title {
            font-size: 1.25rem;
            color: var(--secondary);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .feature-list {
            list-style: none;
            color: #555;
        }

        .feature-list li {
            margin-bottom: 0.75rem;
            position: relative;
            padding-left: 1.5rem;
        }

        .feature-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--primary);
            font-weight: bold;
        }

        .feature-card:nth-child(2) .feature-list li::before {
            color: #8338ec;
        }

        .feature-card:nth-child(3) .feature-list li::before {
            color: #ff7b00;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 2rem 0;
            text-align: center;
            color: #6c757d;
            margin-top: 5rem;
            border-top: 1px solid #e9ecef;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        @media (max-width: 992px) {
            .hero {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .features {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu {
                display: block;
            }

            .features {
                grid-template-columns: 1fr;
            }

            .team-container {
                grid-template-columns: 1fr;
            }
        }
        /* Mobile menu styling */
@media (max-width: 768px) {
    .nav-links {
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            height: 100vh;
            background-color: white;
            padding: 5rem 2rem;
            box-shadow: -2px 0 15px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            z-index: 999;
        }


     .nav-links.active {
            right: 0;
        }

        .nav-links li {
            margin: 1.5rem 0;
        }
  .mobile-menu {
            display: block;
            cursor: pointer;
            z-index: 1000;
        }
        
        .mobile-menu.active i::before {
            content: "\f00d"; /* Font Awesome X (close) icon */
        }

    </style>
</head>
<body>
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
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="aboutpage.php">About</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="contact.php">Contact</a></li>
            <!-- Add LOGIN button to match index page -->
        </ul>
        <div class="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>
    </nav>
    <main class="main">
        <div class="hero">
            <div class="hero-content">
                <span class="section-label">About Us</span>
                <h1 class="hero-title">Your Trusted Partner in Climate Control Solutions</h1>
                <p class="hero-text">
                    Welcome to MFJ Airconditioning Supply and Services! We are a leading provider of premium air conditioning solutions, offering a comprehensive range of high-quality products and expert services to meet all your cooling needs.
                </p>
                <p class="hero-text">
                    With years of experience in the industry, our team of skilled professionals is dedicated to delivering exceptional service and innovative solutions tailored to your specific requirements. We pride ourselves on our commitment to quality, reliability, and customer satisfaction.
                </p>
                <button class="cta-button">Learn More</button>
            </div>
            <div class="team-section">
                <h2 class="feature-title">Meet Our Team</h2>
                <div class="team-container">
                    <div class="team-member">
                        <div class="avatar">
                            <img src="lea.png" alt="Lea Angelica C. Manliguez">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Lea Angelica C. Manliguez</h3>
                            <p class="member-title">Team Leader</p>
                        </div>
                    </div>
                    <div class="team-member">
                        <div class="avatar">
                            <img src="alliah.jpg" alt="Alliah Camposano">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Alliah Camposano</h3>
                            <p class="member-title">Member</p>
                        </div>
                    </div>
                    <div class="team-member">
                        <div class="avatar">
                            <img src="leoj.png" alt="Leoj Ace Dimacutac">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Leoj Ace Dimacutac</h3>
                            <p class="member-title">Member</p>
                        </div>
                    </div>
                    <div class="team-member">
                        <div class="avatar">
                            <img src="jc.png" alt="Jeremy Christian Mamaril">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Jeremy Christian Mamaril</h3>
                            <p class="member-title">Member</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h2 class="feature-title">Our Mission</h2>
                <p>Our mission is to provide exceptional air conditioning products and services to both residential and commercial clients, ensuring optimal comfort and energy efficiency in every space we serve.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-box"></i>
                </div>
                <h2 class="feature-title">What We Offer</h2>
                <ul class="feature-list">
                    <li>Premium Air Conditioning Units</li>
                    <li>Professional Installation Services</li>
                    <li>Comprehensive Repairs and Maintenance</li>
                    <li>Energy Efficiency Consultations</li>
                </ul>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-award"></i>
                </div>
                <h2 class="feature-title">Why Choose Us</h2>
                <ul class="feature-list">
                    <li>Industry-Leading Expertise</li>
                    <li>High-Quality Products</li>
                    <li>Competitive Pricing</li>
                    <li>Exceptional Customer Service</li>
                </ul>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2024 MFJ Airconditioning Supply and Services. All rights reserved.</p>
        </div>
    </footer>

 <script>
    document.addEventListener('DOMContentLoaded', function () {
        const mobileMenuIcon = document.querySelector('.mobile-menu');
        const navLinks = document.querySelector('.nav-links');

        mobileMenuIcon.addEventListener('click', function () {
            navLinks.classList.toggle('active');
            mobileMenuIcon.classList.toggle('active');
        });
    });
    
</script>


</body>
</html>