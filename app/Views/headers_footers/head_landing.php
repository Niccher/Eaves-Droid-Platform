<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>Prj Images - Mobile Data Intelligence Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
          content="Prj Images is a mobile data analysis platform that transforms raw mobile data into actionable insights through advanced analysis and visualization"/>
    <meta name="keywords"
          content="mobile data analysis, android data collection, data visualization, call analysis, SMS correlation, file structure generation"/>
    <meta content="domino" name="author"/>
    <meta content="support@chegecache.co.ke" name="support"/>
    <meta content="https://chegecache.co.ke/" name="Website"/>
    <meta content="Prj Images" name="application-name"/>
    <meta content="mobile data intelligence, data analysis platform" name="keywords"/>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Custom Styles -->
    <style>
        :root {
            --primary: #007bff;
            --secondary: #6c757d;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            font-family: 'Source Sans Pro', sans-serif;
            line-height: 1.6;
            padding-top: 70px; /* Space for fixed navbar */
        }

        /* Navigation Styles */
        .main-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary) !important;
        }

        .nav-link {
            color: var(--dark) !important;
            font-weight: 500;
            padding: 10px 15px !important;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary) !important;
            transform: translateY(-2px);
        }

        .nav-link.active {
            color: var(--primary) !important;
            font-weight: 600;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            margin-top: -70px; /* Compensate for navbar */
            padding-top: 170px; /* Extra padding for content */
        }

        /* Feature Icons */
        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        /* Cards */
        .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        /* Section Titles */
        .section-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--primary);
        }

        .section-title.text-center:after {
            left: 50%;
            transform: translateX(-50%);
        }

        /* Stats Cards */
        .stat-card {
            border-radius: 10px;
            padding: 30px 20px;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Process Steps */
        .process-step {
            text-align: center;
            padding: 20px;
        }

        .process-step .step-number {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 20px;
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            padding: 10px 30px;
        }

        .btn-primary:hover {
            background: #0056b3;
            border-color: #0056b3;
        }

        /* Footer */
        footer {
            background: #343a40;
            color: white;
            padding: 60px 0 20px;
        }

        .footer-links a {
            color: #adb5bd;
            text-decoration: none;
        }

        .footer-links a:hover {
            color: white;
        }

        /* Back to Top */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .back-to-top.show {
            opacity: 1;
        }

        .back-to-top:hover {
            background: #0056b3;
            color: white;
        }

        /* Pricing Cards */
        .pricing-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .pricing-card:hover {
            border-color: var(--primary);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .pricing-card .card-header {
            border-radius: 10px 10px 0 0;
        }

        /* Contact Methods */
        .contact-method {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 30px 20px;
            text-align: center;
            height: 100%;
            transition: all 0.3s ease;
        }

        .contact-method:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding-top: 60px;
            }

            .main-navbar {
                padding: 10px 0;
            }

            .nav-link {
                padding: 8px 10px !important;
                font-size: 0.9rem;
            }

            .hero-section {
                padding: 80px 0;
                padding-top: 140px;
                margin-top: -60px;
            }

            .display-4 {
                font-size: 2rem;
            }

            .lead {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
<!-- Simple Top Navigation Bar -->
<nav class="main-navbar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Logo -->
            <a class="navbar-brand" href="<?php echo base_url('landing'); ?>">
                <i class="fas fa-chart-bar mr-2"></i>Prj Images
            </a>

            <!-- Navigation Links -->
            <div class="d-flex">
                <a class="nav-link" href="<?php echo base_url('landing'); ?>">Home</a>
                <a class="nav-link" href="<?php echo base_url('aboutus'); ?>">About</a>
                <a class="nav-link" href="<?php echo base_url('download'); ?>">Download</a>
                <a class="nav-link" href="<?php echo base_url('pricing'); ?>">Pricing</a>
                <a class="nav-link" href="<?php echo base_url('faqs_terms'); ?>">FAQ</a>
                <a class="nav-link" href="<?php echo base_url('contactus'); ?>">Contact</a>
                <a class="btn btn-primary ml-3" href="<?= url_to('login') ?>">Login</a>
            </div>
        </div>
    </div>
</nav>

<!-- Back to Top Button -->
<a href="#" class="back-to-top" id="backToTop">
    <i class="fas fa-chevron-up"></i>
</a>

<!-- Main Content -->
<main>