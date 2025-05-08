<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MFJ Airconditioning</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #0ea5e9;
            --accent-color: #f59e0b;
            --background-light: #f8fafc;
            --text-color: #334155;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --hover-transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            line-height: 1.6;
            background-color: var(--background-light);
            color: var(--text-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header & Navigation */
        header {
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 60px;
            transition: var(--hover-transition);
        }

        .logo img:hover {
            transform: scale(1.05);
        }

        .company-name {
            margin-left: 15px;
        }

        .company-name h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
            line-height: 1.2;
        }

        .company-name .tagline {
            font-size: 14px;
            color: var(--secondary-color);
            font-weight: 500;
        }

        nav ul {
            list-style-type: none;
            display: flex;
            gap: 30px;
        }

        nav ul li a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            position: relative;
            transition: var(--hover-transition);
            padding: 5px 0;
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            background: var(--accent-color);
            left: 0;
            bottom: -5px;
            transition: var(--hover-transition);
            border-radius: 5px;
        }

        nav ul li a:hover, nav ul li a.active {
            color: var(--accent-color);
        }

        nav ul li a:hover::after, nav ul li a.active::after {
            width: 100%;
        }

        /* Mobile menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--primary-color);
            cursor: pointer;
        }

        /* Main Content */
        main {
            margin-top: 100px;
            padding: 40px 0;
        }

        .page-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title h2 {
            font-size: 36px;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .page-title p {
            font-size: 18px;
            color: var(--text-color);
            max-width: 700px;
            margin: 0 auto;
        }

        /* Contact Content */
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        /* Contact Info Card */
        .contact-info-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            padding: 30px;
            height: 100%;
        }

        .contact-info-card h3 {
            font-size: 22px;
            color: var(--primary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .contact-info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 8px;
            transition: var(--hover-transition);
        }

        .contact-info-item:hover {
            background-color: rgba(14, 165, 233, 0.1);
        }

        .contact-info-item i {
            color: var(--secondary-color);
            font-size: 22px;
            width: 40px;
            margin-right: 15px;
        }

        .contact-info-item .info-content h4 {
            margin: 0;
            font-size: 16px;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .contact-info-item .info-content p {
            margin: 0;
            color: var(--text-color);
            font-size: 15px;
        }

        .business-hours {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .business-hours h4 {
            font-size: 18px;
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hours-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .day-hours {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #e5e7eb;
        }

        .day-hours .day {
            font-weight: 500;
        }

        .day-hours .hours {
            color: var(--secondary-color);
        }

        /* JotForm Section */
        .jotform-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            padding: 30px;
            height: 100%;
        }

        .jotform-section h3 {
            font-size: 22px;
            color: var(--primary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .jotform-iframe {
            width: 100%;
            border: none;
            height: 539px;
            min-width: 100%;
            max-width: 100%;
        }

        /* Map Section */
        .map-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 40px;
        }

        .map-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .map-header h3 {
            margin: 0;
            font-size: 22px;
        }

        .map-container {
            height: 450px;
            width: 100%;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Social Media */
        .social-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            padding: 30px;
            margin-bottom: 40px;
        }

        .social-section h3 {
            text-align: center;
            font-size: 22px;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 25px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            color: white;
            font-size: 24px;
            border-radius: 50%;
            transition: var(--hover-transition);
        }

        .social-link-facebook {
            background-color: #1877f2;
        }

        .social-link-instagram {
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
        }

        .social-link-twitter {
            background-color: #1da1f2;
        }

        .social-link-linkedin {
            background-color: #0077b5;
        }

        .social-links a:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* FAQ Section */
        .faq-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            padding: 30px;
            margin-bottom: 40px;
        }

        .faq-section h3 {
            font-size: 22px;
            color: var(--primary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .faq-item {
            margin-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 15px;
        }

        .faq-question {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-question i {
            color: var(--secondary-color);
            transition: var(--hover-transition);
        }

        .faq-answer {
            padding-top: 10px;
            color: var(--text-color);
        }

        /* Footer */
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 40px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .footer-logo img {
            height: 50px;
            margin-right: 10px;
        }

        .footer-about p {
            margin-bottom: 20px;
            font-size: 14px;
            opacity: 0.9;
        }

        .footer-col h4 {
            font-size: 18px;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-col h4::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background-color: var(--accent-color);
        }

        .footer-nav {
            list-style: none;
        }

        .footer-nav li {
            margin-bottom: 12px;
        }

        .footer-nav li a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: var(--hover-transition);
            font-size: 14px;
        }

        .footer-nav li a:hover {
            opacity: 1;
            padding-left: 5px;
            color: var(--accent-color);
        }

        .footer-contact li {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }

        .footer-contact li i {
            margin-right: 10px;
            color: var(--accent-color);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 14px;
            opacity: 0.8;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            nav ul {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .company-name {
                margin-left: 0;
                margin-top: 10px;
            }

            .mobile-menu-btn {
                position: absolute;
                top: 20px;
                right: 20px;
            }

            .social-links {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo-container">
                    <a href="index.php" class="logo">
                        <img src="logo_clear.png" alt="MFJ Logo">
                    </a>
                    <div class="company-name">
                        <h1>MFJ</h1>
                        <p class="tagline">Airconditioning Supply and Services</p>
                    </div>
                </div>
                <nav>
                    <button class="mobile-menu-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="aboutpage.php">About</a></li>
                        <li><a href="services.php">Services</a></li>
                        <li><a href="contact.php" class="active">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <div class="page-title">
                <h2>Contact Us</h2>
                <p>Have questions or need assistance? Reach out to our team and we'll get back to you as soon as possible.</p>
            </div>

            <div class="contact-grid">
                <!-- Contact Information -->
                <div class="contact-info-card">
                    <h3><i class="fas fa-address-book"></i> Contact Information</h3>
                    
                    <div class="contact-info-item">
                        <i class="fas fa-phone"></i>
                        <div class="info-content">
                            <h4>Telephone</h4>
                            <p>(02) 8855-54-35</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="fas fa-mobile-alt"></i>
                        <div class="info-content">
                            <h4>Mobile</h4>
                            <p>09983465924 / 09937325651</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="fas fa-envelope"></i>
                        <div class="info-content">
                            <h4>Email</h4>
                            <p>info@mfj.com</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="info-content">
                            <h4>Address</h4>
                            <p>BLK 5 L 15 Chesnut St. Annex 41 Upper Subd. Brgy. Sun Valley Paranaque City</p>
                        </div>
                    </div>

                    <div class="business-hours">
                        <h4><i class="far fa-clock"></i> Business Hours</h4>
                        <div class="hours-grid">
                            <div class="day-hours">
                                <span class="day">Monday</span>
                                <span class="hours">8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="day-hours">
                                <span class="day">Tuesday</span>
                                <span class="hours">8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="day-hours">
                                <span class="day">Wednesday</span>
                                <span class="hours">8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="day-hours">
                                <span class="day">Thursday</span>
                                <span class="hours">8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="day-hours">
                                <span class="day">Friday</span>
                                <span class="hours">8:00 AM - 5:00 PM</span>
                            </div>
                            <div class="day-hours">
                                <span class="day">Saturday</span>
                                <span class="hours">8:00 AM - 12:00 PM</span>
                            </div>
                            <div class="day-hours">
                                <span class="day">Sunday</span>
                                <span class="hours">Closed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- JotForm Section -->
                <div class="jotform-section">
                    <h3><i class="fas fa-paper-plane"></i> Appointment Request Form</h3>
                    <iframe
                        id="JotFormIFrame-243203414938454"
                        title="Appointment Request Form"
                        src="https://form.jotform.com/243203414938454"
                        frameborder="0"
                        class="jotform-iframe"
                        scrolling="no"
                    >
                    </iframe>
                    <script src='https://cdn.jotfor.ms/s/umd/latest/for-form-embed-handler.js'></script>
                    <script>window.jotformEmbedHandler("iframe[id='JotFormIFrame-243203414938454']", "https://form.jotform.com/")</script>
                </div>
            </div>

            <!-- Map Section -->
            <div class="map-section">
                <div class="map-header">
                    <i class="fas fa-map-marked-alt"></i>
                    <h3>Our Location</h3>
                </div>
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.945473121655!2d123.872033!3d10.317214999999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33ab6b94e7db0a63%3A0x99832ab4b98e2b53!2sAirconditioning%20Supply%20and%20Services%20Inc.!5e0!3m2!1sen!2sph!4v1612012759515!5m2!1sen!2sph" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="faq-section">
                <h3><i class="fas fa-question-circle"></i> Frequently Asked Questions</h3>
                
                <div class="faq-item">
                    <div class="faq-question">
                        What services do you offer?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        We offer a comprehensive range of air conditioning services including installation, repair, maintenance, cleaning, and equipment supply for both residential and commercial properties.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        Do you offer emergency repair services?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Yes, we provide emergency repair services. Please contact our customer service hotline at (02) 8855-54-35 or 09983465924 for immediate assistance.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        What brands of air conditioners do you carry?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        We carry a wide range of trusted brands including Carrier, Daikin, LG, Samsung, Panasonic, Mitsubishi, and more. Contact us for specific inquiries about brands and models.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        How often should I service my air conditioner?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        For optimal performance and longevity, we recommend servicing your air conditioner at least twice a year. Regular maintenance helps prevent breakdowns, improves efficiency, and extends the lifespan of your unit.
                    </div>
                </div>
            </div>

            <!-- Social Media Section -->
            <div class="social-section">
                <h3><i class="fas fa-share-alt"></i> Connect With Us</h3>
                <div class="social-links">
                    <a href="https://www.facebook.com/MFJAIRCON" target="_blank" class="social-link-facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" target="_blank" class="social-link-instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" target="_blank" class="social-link-twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" target="_blank" class="social-link-linkedin">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-col footer-about">
                    <div class="footer-logo">
                        <img src="logo_clear.png" alt="MFJ Logo">
                        <h3>MFJ Airconditioning</h3>
                    </div>
                    <p>With years of experience in the industry, MFJ Airconditioning is your trusted partner for all heating, ventilation, and air conditioning needs in Paranaque City and surrounding areas.</p>
                </div>
                
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul class="footer-nav">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="aboutpage.php">About Us</a></li>
                        <li><a href="services.php">Services</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Our Services</h4>
                    <ul class="footer-nav">
                        <li><a href="#">AC Installation</a></li>
                        <li><a href="#">AC Repair</a></li>
                        <li><a href="#">Maintenance</a></li>
                        <li><a href="#">Cleaning</a></li>
                        <li><a href="#">Equipment Supply</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Contact Us</h4>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>BLK 5 L 15 Chesnut St. Annex 41 Upper Subd. Brgy. Sun Valley Paranaque City</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>(02) 8855-54-35</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>info@mfj.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 MFJ Airconditioning Supply and Services. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Script for FAQ Functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqQuestions = document.querySelectorAll('.faq-question');
            
            faqQuestions.forEach(question => {
                question.addEventListener('click', function() {
                    const answer = this.nextElementSibling;
                    const icon = this.querySelector('i');
                    
                    // Toggle answer visibility
                    if (answer.style.display === 'block') {
                        answer.style.display = 'none';
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                    } else {
                        answer.style.display = 'block';
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                    }
                });
            });

            // Hide all answers initially
            document.querySelectorAll('.faq-answer').forEach(answer => {
                answer.style.display = 'none';
            });
        });
    </script>
</body>
</html>