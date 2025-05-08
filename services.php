<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services | MFJ Airconditioning Supply and Services</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3a86ff;
            --secondary: #0a2463;
            --accent: #ff7b00;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            background-color: var(--light);
            color: var(--dark);
            padding-top: 80px;
        }

        /* Navbar Styles */
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

        /* Main Content */
        .main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(to right, rgba(255,255,255,0.9), rgba(255,255,255,0.9)), 
                        url('pattern.svg');
            background-size: cover;
            border-radius: 15px;
            padding: 4rem 2rem;
            text-align: center;
            margin-bottom: 4rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow);
            animation: fadeIn 1s ease;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--accent));
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .section-label {
            color: var(--primary);
            font-weight: 500;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1rem;
            display: block;
        }

        .hero h1 {
            color: var(--secondary);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero p {
            color: var(--gray);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .service-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards;
            border-top: 4px solid transparent;
            display: flex;
            flex-direction: column;
        }

        .service-card:nth-child(1) {
            animation-delay: 0.1s;
            border-color: #3a86ff;
        }

        .service-card:nth-child(2) {
            animation-delay: 0.2s;
            border-color: #8338ec;
        }

        .service-card:nth-child(3) {
            animation-delay: 0.3s;
            border-color: #ff7b00;
        }

        .service-card:nth-child(4) {
            animation-delay: 0.4s;
            border-color: #3a86ff;
        }

        .service-card:nth-child(5) {
            animation-delay: 0.5s;
            border-color: #8338ec;
        }

        .service-card:nth-child(6) {
            animation-delay: 0.6s;
            border-color: #ff7b00;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .service-content {
            padding: 2rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .service-icon {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .service-card:nth-child(1) .service-icon {
            color: #3a86ff;
            background-color: rgba(58, 134, 255, 0.1);
        }

        .service-card:nth-child(2) .service-icon {
            color: #8338ec;
            background-color: rgba(131, 56, 236, 0.1);
        }

        .service-card:nth-child(3) .service-icon {
            color: #ff7b00;
            background-color: rgba(255, 123, 0, 0.1);
        }

        .service-card:nth-child(4) .service-icon {
            color: #3a86ff;
            background-color: rgba(58, 134, 255, 0.1);
        }

        .service-card:nth-child(5) .service-icon {
            color: #8338ec;
            background-color: rgba(131, 56, 236, 0.1);
        }

        .service-card:nth-child(6) .service-icon {
            color: #ff7b00;
            background-color: rgba(255, 123, 0, 0.1);
        }

        .service-title {
            color: var(--secondary);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .service-description {
            color: var(--gray);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .service-features {
            list-style: none;
            margin: 1.5rem 0;
            flex-grow: 1;
        }

        .service-features li {
            margin-bottom: 0.75rem;
            padding-left: 1.8rem;
            position: relative;
            color: var(--gray);
        }

        .service-card:nth-child(1) .service-features li::before {
            content: "\f00c";
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #3a86ff;
        }

        .service-card:nth-child(2) .service-features li::before {
            content: "\f00c";
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #8338ec;
        }

        .service-card:nth-child(3) .service-features li::before {
            content: "\f00c";
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #ff7b00;
        }

        .service-card:nth-child(4) .service-features li::before {
            content: "\f00c";
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #3a86ff;
        }

        .service-card:nth-child(5) .service-features li::before {
            content: "\f00c";
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #8338ec;
        }

        .service-card:nth-child(6) .service-features li::before {
            content: "\f00c";
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #ff7b00;
        }

        .cta-button {
            display: inline-block;
            background-color: var(--accent);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(255, 123, 0, 0.2);
            text-align: center;
            margin-top: auto;
            border: none;
            cursor: pointer;
        }

        .service-card:nth-child(1) .cta-button {
            background-color: #3a86ff;
            box-shadow: 0 4px 12px rgba(58, 134, 255, 0.2);
        }

        .service-card:nth-child(2) .cta-button {
            background-color: #8338ec;
            box-shadow: 0 4px 12px rgba(131, 56, 236, 0.2);
        }

        .service-card:nth-child(3) .cta-button {
            background-color: #ff7b00;
            box-shadow: 0 4px 12px rgba(255, 123, 0, 0.2);
        }

        .service-card:nth-child(4) .cta-button {
            background-color: #3a86ff;
            box-shadow: 0 4px 12px rgba(58, 134, 255, 0.2);
        }

        .service-card:nth-child(5) .cta-button {
            background-color: #8338ec;
            box-shadow: 0 4px 12px rgba(131, 56, 236, 0.2);
        }

        .service-card:nth-child(6) .cta-button {
            background-color: #ff7b00;
            box-shadow: 0 4px 12px rgba(255, 123, 0, 0.2);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .service-card:nth-child(1) .cta-button:hover {
            background-color: #2a76ef;
        }

        .service-card:nth-child(2) .cta-button:hover {
            background-color: #7328dc;
        }

        .service-card:nth-child(3) .cta-button:hover {
            background-color: #e56e00;
        }

        .service-card:nth-child(4) .cta-button:hover {
            background-color: #2a76ef;
        }

        .service-card:nth-child(5) .cta-button:hover {
            background-color: #7328dc;
        }

        .service-card:nth-child(6) .cta-button:hover {
            background-color: #e56e00;
        }

        /* Footer */
        .footer {
            background-color: white;
            padding: 2rem 0;
            text-align: center;
            color: var(--gray);
            margin-top: 5rem;
            border-top: 1px solid #e9ecef;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
    .nav-links {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 80px;
        right: 5%;
        background-color: white;
        padding: 1rem 2rem;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        border-radius: 10px;
        gap: 1rem;
    }

    .mobile-menu {
        display: block;
        z-index: 1100;
    }

    .services-grid {
        grid-template-columns: 1fr;
    }
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
            </ul>
            <div class="mobile-menu">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>

    <main class="main">
        <section class="hero">
            <div class="hero-content">
                <span class="section-label">What We Offer</span>
                <h1>Professional Airconditioning Services</h1>
                <p>We provide comprehensive air conditioning solutions for residential and commercial properties. Our team of certified experts ensures quality service and complete customer satisfaction.</p>
            </div>
        </section>

        <section class="services-grid">
            <div class="service-card">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h2 class="service-title">AC Installation</h2>
                    <p class="service-description">Professional installation services for all types of air conditioning units to ensure optimal performance and efficiency.</p>
                    <ul class="service-features">
                        <li>Expert certified technicians</li>
                        <li>Quality assurance guarantee</li>
                        <li>Proper unit sizing assessment</li>
                        <li>Comprehensive warranty coverage</li>
                    </ul>
                    <a href="index.php" class="cta-button">Learn More</a>
                </div>
            </div>

            <div class="service-card">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h2 class="service-title">Maintenance</h2>
                    <p class="service-description">Regular maintenance services to keep your AC running efficiently and extend the lifespan of your system.</p>
                    <ul class="service-features">
                        <li>Scheduled inspections</li>
                        <li>Filter cleaning/replacement</li>
                        <li>Performance optimization</li>
                        <li>Preventive care and troubleshooting</li>
                    </ul>
                    <a href="index.php" class="cta-button">Learn More</a>
                </div>
            </div>

            <div class="service-card">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="fas fa-wrench"></i>
                    </div>
                    <h2 class="service-title">Repair Services</h2>
                    <p class="service-description">Quick and reliable repair services for all AC brands and models to restore comfort to your space.</p>
                    <ul class="service-features">
                        <li>24/7 emergency service</li>
                        <li>Genuine replacement parts</li>
                        <li>Skilled diagnostic technicians</li>
                        <li>Service satisfaction guarantee</li>
                    </ul>
                    <a href="index.php" class="cta-button">Learn More</a>
                </div>
            </div>

            <div class="service-card">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <h2 class="service-title">AC Supply</h2>
                    <p class="service-description">Wide range of quality air conditioning units and parts from trusted manufacturers at competitive prices.</p>
                    <ul class="service-features">
                        <li>Multiple premium brands</li>
                        <li>Authentic products</li>
                        <li>Competitive market prices</li>
                        <li>Expert selection guidance</li>
                    </ul>
                    <a href="index.php" class="cta-button">Learn More</a>
                </div>
            </div>

            <div class="service-card">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h2 class="service-title">Consultation</h2>
                    <p class="service-description">Expert advice on choosing the right AC solution tailored to your specific needs and environment.</p>
                    <ul class="service-features">
                        <li>Professional site assessment</li>
                        <li>Energy efficiency recommendations</li>
                        <li>Detailed cost estimates</li>
                        <li>Customized solution planning</li>
                    </ul>
                    <a href="index.php" class="cta-button">Learn More</a>
                </div>
            </div>

            <div class="service-card">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h2 class="service-title">Commercial Services</h2>
                    <p class="service-description">Specialized cooling solutions for businesses and commercial properties of any size or complexity.</p>
                    <ul class="service-features">
                        <li>Custom system installations</li>
                        <li>Preventive maintenance plans</li>
                        <li>Priority emergency repairs</li>
                        <li>Flexible service contracts</li>
                    </ul>
                    <a href="index.php" class="cta-button">Learn More</a>
                </div>
            </div>
        </section>
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
            if (navLinks.style.display === 'flex') {
                navLinks.style.display = 'none';
            } else {
                navLinks.style.display = 'flex';
                navLinks.style.flexDirection = 'column';
                navLinks.style.position = 'absolute';
                navLinks.style.top = '80px';
                navLinks.style.right = '5%';
                navLinks.style.backgroundColor = 'white';
                navLinks.style.padding = '1rem 2rem';
                navLinks.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
                navLinks.style.zIndex = '1000';
                navLinks.style.borderRadius = '10px';
                navLinks.style.gap = '1rem';
            }
        });
    });
</script>

</body>
</html>