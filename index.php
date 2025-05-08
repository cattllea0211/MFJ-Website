<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MFJ - Air Conditioning Experts</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #0ea5e9;
            --accent-color: #e11d48;
            --light-background: #f8fafc;
            --text-color: #1e293b;
            --dark-bg: #0f172a;
            --card-bg: #ffffff;
            --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            overflow-x: hidden;
        }
        html {
            scroll-behavior: smooth;
        }



        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            background-color: var(--light-background);
            color: var(--text-color);
            overflow-x: hidden;
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
            padding: 15px 5%;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }


        .navbar.scrolled {
            padding: 10px 5%;
            background-color: rgba(255, 255, 255, 0.98);
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 60px;
            width: auto;
            margin-right: 15px;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .company-name h2 {
            font-size: 28px;
            color: #1e3a8a;
            margin: 0;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .company-name .tagline {
            font-size: 14px;
            color: #0ea5e9;
            margin: 0;
            font-weight: 500;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            align-items: center;
        }
        .nav-links ul {
            list-style-type: none;
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
        }

        .nav-links ul li {
            margin: 0 18px;
        }

        .nav-links ul li a {
            color: #1e293b;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            position: relative;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .nav-links ul li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: #0ea5e9;
            left: 0;
            bottom: -5px;
            transition: width 0.3s ease;
        }


        .nav-links ul li a:hover {
            color: #0ea5e9;
        }

                .nav-links ul li a:hover::after,
        .nav-links ul li a.active::after {
            width: 100%;
        }

        .nav-links ul li a.active {
            color: #0ea5e9;
        }

        .contact-button {
            background-color: #e11d48;
            color: white;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.15);
            margin-left: 15px;
        }

        .contact-button:hover {
            background-color: #be123c;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(225, 29, 72, 0.25);
        }

        .hamburger {
            display: none;
            cursor: pointer;
        }

        .hamburger span {
            display: block;
            width: 30px;
            height: 3px;
            background-color: var(--text-color);
            margin: 6px 0;
            transition: var(--transition);
        }

 .hero {
          background: linear-gradient(to right, rgba(15, 23, 42, 0.8), rgba(30, 58, 138, 0.7)), url('/modernbg1.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 0 5%;
            position: relative;
            overflow: hidden;
            padding-top: 100px; /* Added padding to create space below navbar */
        }


        .hero::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(to top, var(--light-background), transparent);
            z-index: 1;
        }

        .hero-content {
            max-width: 650px;
            z-index: 2;
            animation: fadeInLeft 1s ease-out;
        }

        .hero h1 {
            font-size: 64px;
            line-height: 1.1;
            margin-bottom: 24px;
            font-weight: 800;
            color: white;
            letter-spacing: -1px;
        }

        .hero h1 span {
            color: var(--secondary-color);
        }

        .hero p {
            font-size: 20px;
            margin-bottom: 32px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }

        .cta-buttons {
            display: flex;
            gap: 16px;
            margin-top: 12px;
        }
        .cta-buttons a {
            width: 100%;
            text-align: center;
        }


        .cta-primary {
            display: inline-block;
            background-color: var(--secondary-color);
            color: white;
            padding: 14px 32px;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            transition: var(--transition);
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3);
        }

        .cta-primary:hover {
            background-color: #0284c7;
            transform: translateY(-4px);
            box-shadow: 0 15px 25px rgba(14, 165, 233, 0.4);
        }

        .cta-secondary {
            display: inline-flex;
            align-items: center;
            background-color: transparent;
            border: 2px solid white;
            color: white;
            padding: 14px 32px;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            transition: var(--transition);
            gap: 8px;
        }

        .cta-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-4px);
        }

        .features {
            padding: 120px 5%;
            background-color: white;
        }

        .section-header {
            text-align: center;
            margin-bottom: 80px;
        }

        .section-header span {
            color: var(--secondary-color);
            font-weight: 600;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .section-header h2 {
            font-size: 42px;
            color: var(--primary-color);
            margin: 16px 0;
            font-weight: 700;
            position: relative;
            display: inline-block;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: var(--secondary-color);
            left: 50%;
            bottom: -16px;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .section-header p {
            max-width: 650px;
            margin: 0 auto;
            color: #64748b;
            font-size: 18px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 30px;
        }

        .feature-box {
            background-color: white;
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            transition: var(--transition);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .feature-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            z-index: -1;
            transition: var(--transition);
        }

        .feature-box:hover {
            transform: translateY(-15px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .feature-box:hover::before {
            height: 100%;
            opacity: 0.05;
        }

        .feature-icon {
            font-size: 48px;
            color: var(--secondary-color);
            margin-bottom: 24px;
            display: inline-block;
            transition: var(--transition);
        }

        .feature-box:hover .feature-icon {
            transform: scale(1.1) rotate(10deg);
            color: var(--accent-color);
        }

        .feature-box h3 {
            font-size: 24px;
            margin-bottom: 16px;
            color: var(--primary-color);
            font-weight: 700;
        }

        .feature-box p {
            color: #64748b;
            font-size: 16px;
            line-height: 1.6;
        }

        .learn-more {
            display: inline-block;
            margin-top: 16px;
            color: var(--secondary-color);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .learn-more i {
            margin-left: 5px;
            transition: var(--transition);
        }

        .learn-more:hover {
            color: var(--accent-color);
        }

        .learn-more:hover i {
            transform: translateX(5px);
        }

        .services {
            padding: 120px 5%;
            background-color: var(--light-background);
            position: relative;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
            margin-top: 30px;
        }

        .service-card {
            background-color: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .service-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .service-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
            display: flex;
            align-items: flex-end;
            padding: 20px;
        }

        .service-overlay h3 {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .service-content {
            padding: 30px;
        }

        .service-content p {
            margin-bottom: 20px;
            color: #64748b;
        }

        .service-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--secondary-color);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
        }

        .service-btn:hover {
            background-color: var(--accent-color);
            transform: translateY(-3px);
        }

        .testimonials {
            padding: 120px 5%;
            background-color: white;
            position: relative;
        }

        .testimonial-slider {
            margin-top: 50px;
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            gap: 30px;
            padding: 20px 0;
            scrollbar-width: none; /* Firefox */
        }

        .testimonial-slider::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }

        .testimonial-card {
            min-width: 350px;
            flex: 0 0 350px;
            background-color: var(--light-background);
            border-radius: 20px;
            padding: 30px;
            scroll-snap-align: start;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .testimonial-quote {
            font-size: 18px;
            color: var(--text-color);
            margin-bottom: 20px;
            line-height: 1.7;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
        }

        .author-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }

        .author-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .author-info h4 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 700;
        }

        .author-info p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }

        .quote-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 40px;
            color: rgba(14, 165, 233, 0.1);
        }

        .cta-section {
            padding: 80px 5%;
            background: linear-gradient(135deg, var(--primary-color), var(--dark-bg));
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1581093588401-cddd95570fd2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            opacity: 0.1;
        }

        .cta-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }

        .cta-content h2 {
            font-size: 42px;
            margin-bottom: 24px;
            font-weight: 700;
        }

        .cta-content p {
            font-size: 18px;
            margin-bottom: 40px;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        footer {
            background-color: var(--dark-bg);
            color: white;
            padding: 80px 5% 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        
        }

        .footer-logo {
            margin-bottom: 20px;
        }

        .footer-logo img {
            height: 50px;
            width: auto;
        }

        .footer-info p {
            margin-bottom: 15px;
            color: #cbd5e1;
            font-size: 15px;
        }

        .footer-contact span {
            display: block;
            margin-bottom: 10px;
            color: #cbd5e1;
            font-size: 15px;
        }

        .footer-contact span i {
            margin-right: 10px;
            color: var(--secondary-color);
        }

        .footer-links h3, .footer-contact h3, .footer-newsletter h3 {
            color: white;
            font-size: 20px;
            margin-bottom: 25px;
            font-weight: 600;
            position: relative;
            padding-bottom: 12px;
        }

        .footer-links h3::after, .footer-contact h3::after, .footer-newsletter h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background-color: var(--secondary-color);
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
        }

        .footer-links ul li {
            margin-bottom: 12px;
        }

        .footer-links ul li a {
            color: #cbd5e1;
            text-decoration: none;
            transition: var(--transition);
            font-size: 15px;
        }

        .footer-links ul li a:hover {
            color: var(--secondary-color);
            padding-left: 5px;
        }

        .footer-newsletter p {
            color: #cbd5e1;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .newsletter-form {
            display: flex;
        }

        .newsletter-form input {
            flex: 1;
            padding: 12px 15px;
            border: none;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 4px 0 0 4px;
            outline: none;
        }

        .newsletter-form button {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: var(--transition);
        }

        .newsletter-form button:hover {
            background-color: #0284c7;
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 50%;
            transition: var(--transition);
        }

        .social-icons a:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
        }

        .footer-bottom {
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            font-size: 14px;
            color: #94a3b8;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
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
            width: 450px;
            padding: 50px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            transform: scale(0.7);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .modal-show .modal-content {
            transform: scale(1);
        }

        .modal-content h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 700;
        }

        .modal-content .user-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .modal-content button {
            padding: 14px 30px;
            font-size: 16px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .modal-content .employee-btn {
            background-color: var(--secondary-color);
            color: white;
        }

        .modal-content .admin-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .modal-content .employee-btn:hover {
            background-color: #0284c7;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(14, 165, 233, 0.3);
        }

        .modal-content .admin-btn:hover {
            background-color: #1e40af;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(30, 58, 138, 0.3);
        }

        .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #94a3b8;
            transition: var(--transition);
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: #f1f5f9;
        }

        .close-btn:hover {
            color: var(--accent-color);
            background-color: #fef2f2;
        }

        /* Animation classes */
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Styles */
        @media (max-width: 1024px) {
            .hero h1 {
                font-size: 54px;
            }
            .hero p {
                font-size: 18px;
            }
        }

        @media (max-width: 768px) {
            .navbar {
        padding: 15px 20px;
        flex-wrap: wrap;
    }
            
            .nav-links {
        position: fixed;
        top: 0;
        right: -100%;
        width: 80%;
        height: 100vh;
        background-color: white;
        flex-direction: column;
        align-items: flex-start;
        justify-content: start;
        padding: 6rem 2rem;
        transition: right 0.3s ease;
        box-shadow: -5px 0 30px rgba(0, 0, 0, 0.1);
        z-index: 999;
    }

            
           .nav-links.active {
        right: 0;
    }
            
            .nav-links ul {
        flex-direction: column;
        width: 100%;
    }
            
            .nav-links ul li {
        margin: 1.5rem 0;
        width: 100%;
    }
            
            .contact-button {
        width: 100%;
        text-align: center;
        margin-top: 2rem;
    }
            
             .hamburger {
        display: block;
        z-index: 1000;
    }
            
            .hamburger.active span:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
    }
            .hamburger.active span:nth-child(2) {
                opacity: 0;
            }
            
            .hamburger.active span:nth-child(3) {
                transform: rotate(-45deg) translate(7px, -8px);
            }
            
            .hero {
                height: 80vh;
                padding: 0 20px;
            }
            
            .hero-content {
                max-width: 100%;
            }
            
            .hero h1 {
                font-size: 42px;
            }
            
            .hero p {
                font-size: 16px;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
            
            .feature-box, .service-card {
                transform: none !important;
            }
        }

        @media (max-width: 480px) {
            .logo img {
                height: 50px;
            }
            
            .company-name h2 {
                font-size: 22px;
            }
            .hero {
                padding-top: 90px;
            }
            
            .hero h1 {
                font-size: 36px;
            }
            
            .section-header h2 {
                font-size: 32px;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .testimonial-card {
                min-width: 280px;
                flex: 0 0 280px;
            }
            
            .modal-content {
                width: 90%;
                padding: 40px 20px;
            }
        }

    </style>
</head>
<body>
    <div id="userTypeModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()"><i class="fas fa-times"></i></span>
            <h2><i class="fas fa-user-lock"></i> Login Portal</h2>
            <div class="user-buttons">
                <button class="employee-btn" onclick="redirectToPage('employee')">
                    <i class="fas fa-briefcase"></i> Employee Login
                </button>
                <!-- Added Admin button (hidden by default) -->
                <button id="adminLoginBtn" class="admin-btn" onclick="redirectToPage('admin')" style="display: none;">
                    <i class="fas fa-shield-alt"></i> Admin Login
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
                <div class="nav-links">
                    <ul>
                        <li><a href="index.php" class="active">HOME</a></li>
                        <li><a href="aboutpage.php">ABOUT</a></li>
                        <li><a href="services.php">SERVICES</a></li>
                        <li><a href="contact.php">CONTACT</a></li>
                    </ul>
                    <a href="#" onclick="openModal()" class="contact-button">LOGIN</a>
                </div>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </header>

   <section class="hero">
        <div class="hero-content">
            <h1>Modern Climate <span>Solutions</span></h1>
            <p>Expert cooling solutions for ultimate comfort. Delivering reliable service, innovative technology, and a customer-first approach.</p>
            <div class="cta-buttons">
                <a href="services.php" class="cta-primary">Explore Services</a>
                <a href="contact.php" class="cta-secondary"><i class="fas fa-phone-alt"></i> Get a Free Quote</a>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="section-header">
            <span>WHY CHOOSE US</span>
            <h2>The MFJ Difference</h2>
            <p>We combine technical expertise with customer care to deliver superior air conditioning solutions.</p>
        </div>
    </section>

    <script>
        function openModal() {
            const modal = document.getElementById('userTypeModal');
            modal.classList.add('modal-show');
        }

        function closeModal() {
            const modal = document.getElementById('userTypeModal');
            modal.classList.remove('modal-show');
            
            // Hide admin button when closing modal
            document.getElementById('adminLoginBtn').style.display = 'none';
        }

        function redirectToPage(userType) {
            if (userType === 'client') {
                window.location.href = 'login.php';
            } else if (userType === 'employee') {
                window.location.href = 'employee_login.php';
            } else if (userType === 'admin') {
                window.location.href = 'admin_login.php';
            }
        }

        // Add event listener for Ctrl+Alt+A keyboard shortcut
        document.addEventListener('keydown', function(event) {
            // Check for Ctrl + Alt + A (keyCode 65 is for 'A')
            if (event.ctrlKey && event.altKey && event.keyCode === 65) {
                // Prevent default browser action
                event.preventDefault();
                
                // Show the modal if not already visible
                const modal = document.getElementById('userTypeModal');
                if (!modal.classList.contains('modal-show')) {
                    modal.classList.add('modal-show');
                }
                
                // Show the admin login button
                document.getElementById('adminLoginBtn').style.display = 'flex';
            }
        });


    // Set active state based on current page
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-links ul li a');
        
        navLinks.forEach(link => {
            // Reset all active states first
            link.classList.remove('active');
            
            // Get the href value and extract the page name
            const href = link.getAttribute('href');
            
            // Check if current path contains the href value
            if (currentPage.includes(href)) {
                link.classList.add('active');
            }
            
            // Special case for home page
            if (currentPage.endsWith('/') || currentPage.endsWith('index.php')) {
                if (href === 'index.php') {
                    link.classList.add('active');
                }
            }
        });
    });

         document.addEventListener('DOMContentLoaded', function () {
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');

        hamburger.addEventListener('click', function () {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('active');
        });
    });
    </script>
</body>
</html>
