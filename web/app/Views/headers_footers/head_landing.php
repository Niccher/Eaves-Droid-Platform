<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title><?= isset($page_title) ? esc($page_title) : 'Eaves Droid | Advanced Mobile Forensic & Data Intelligence Platform' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($page_desc) ? esc($page_desc) : 'A powerful, free mobile data intelligence platform designed for deep analysis of Android device data, including call logs, SMS correlation, and file structure visualization.' ?>"/>
    <meta name="keywords" content="<?= isset($page_keys) ? esc($page_keys) : 'mobile forensics, android data analysis, SMS correlation tool, call log analyzer, mobile data intelligence, digital forensics platform' ?>"/>
    <meta name="author" content="Eaves Droid"/>
    <meta name="robots" content="index, follow"/>
    <meta name="theme-color" content="#007bff">
    <link rel="canonical" href="<?= current_url() ?>" />

    <!-- OpenGraph Tags -->
    <meta property="og:title" content="<?= isset($page_title) ? esc($page_title) : 'Eaves Droid | Mobile Data Intelligence' ?>"/>
    <meta property="og:description" content="<?= isset($page_desc) ? esc($page_desc) : 'Free mobile data intelligence platform — analyze and visualize your Android device data with ease.' ?>"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="<?= current_url() ?>"/>
    <meta property="og:image" content="<?= base_url('assets/img/logo.png') ?>"/>
    <meta property="og:site_name" content="Eaves Droid" />
    <meta property="og:locale" content="en_US" />

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= isset($page_title) ? esc($page_title) : 'Eaves Droid | Mobile Data Intelligence' ?>">
    <meta name="twitter:description" content="<?= isset($page_desc) ? esc($page_desc) : 'Free mobile data intelligence platform — analyze and visualize your Android device data.' ?>">
    <meta name="twitter:image" content="<?= base_url('assets/img/logo.png') ?>">

    <link rel="shortcut icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/x-icon">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/fontawesome-free/css/all.min.css?v=1.4'); ?>"/>
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css'); ?>"/>
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/adminlte.min.css?v=1.4'); ?>"/>
    
    <!-- Dark Mode Pre-loader -->
    <script>
        if (localStorage.getItem('eaves_dark_mode') === 'enabled') {
            document.documentElement.classList.add('dark-mode-preload');
            document.addEventListener("DOMContentLoaded", function() {
                document.body.classList.add('dark-mode');
                document.documentElement.classList.remove('dark-mode-preload');
            });
        }
    </script>
    <style>
        .navbar-brand .brand-text { font-size: 1.5rem; letter-spacing: -0.5px; }
        .nav-link.active { color: #007bff !important; font-weight: 700; border-bottom: 3px solid #007bff; }
        .nav-link { font-size: 1.1rem; padding-bottom: 5px; margin-right: 10px; transition: 0.3s; }
        .nav-link:hover { color: #007bff !important; }
        .content-wrapper { background-color: #fff !important; }
        .brand-image-custom { height: 35px; width: auto; margin-right: 10px; margin-top: -5px; }

        /* Dark Mode Preload & AdminLTE Theme Styles */
        html.dark-mode-preload { background-color: #1a222d; }
        body.dark-mode { background-color: #1a222d; color: #f8fafc; }
        body.dark-mode .content-wrapper { background-color: #1a222d !important; }
        body.dark-mode .main-header.navbar { background-color: #111827 !important; border-bottom: 1px solid #1f2937 !important; }
        body.dark-mode .main-header .brand-text { color: #f8fafc !important; }
        body.dark-mode .main-header .nav-link { color: #cbd5e1 !important; }
        body.dark-mode .main-header .nav-link:hover,
        body.dark-mode .main-header .nav-link.active { color: #38bdf8 !important; border-bottom-color: #38bdf8; }
        body.dark-mode .card:not(.bg-primary):not(.bg-success):not(.bg-danger):not(.bg-info):not(.bg-warning) {
            background-color: #1e293b;
            color: #f8fafc;
            border-color: #334155;
        }
        body.dark-mode .bg-light { background-color: #1e293b !important; }
        body.dark-mode .text-dark { color: #f8fafc !important; }
        body.dark-mode .text-muted { color: #94a3b8 !important; }
        body.dark-mode .list-group-item { background-color: #1e293b; color: #f8fafc; border-color: #334155; }
        body.dark-mode .info-box { background-color: #1e293b !important; color: #f8fafc !important; }
        body.dark-mode .info-box-text { color: #f8fafc !important; }

        /* Dark Mode Auth Split Layout */
        body.dark-mode .auth-split-layout { background-color: #0b1120 !important; }
        body.dark-mode .auth-split-layout .bg-white { background-color: #1e293b !important; }
        body.dark-mode .auth-split-layout .border-top { border-top-color: #334155 !important; }
        body.dark-mode .auth-split-layout .input-group-text { background-color: #334155 !important; border-color: #475569 !important; color: #94a3b8 !important; }
        body.dark-mode .auth-split-layout .form-control { background-color: #0f172a !important; border-color: #475569 !important; color: #f8fafc !important; }
        body.dark-mode .auth-split-layout .form-control:focus { background-color: #0f172a !important; border-color: #38bdf8 !important; color: #ffffff !important; }
        body.dark-mode .auth-split-layout .btn-light { background-color: #334155 !important; border-color: #475569 !important; color: #cbd5e1 !important; }
    </style>

    <!-- jQuery -->
    <script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js?v=1.4'); ?>"></script>
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand-md navbar-light navbar-white shadow-sm border-bottom-0">
        <div class="container">
            <a href="<?php echo base_url('landing'); ?>" class="navbar-brand">
                <img src="<?= base_url('assets/img/logo.png') ?>" alt="Eaves Droid Logo" class="brand-image-custom img-circle elevation-2">
                <span class="brand-text font-weight-light text-dark">Eaves Droid</span>
            </a>

            <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse"
                    aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse order-3" id="navbarCollapse">
                <!-- Left navbar links -->
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a href="<?php echo base_url('landing'); ?>" class="nav-link <?= (isset($pag) && $pag == 'landing') ? 'active' : '' ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo base_url('aboutus'); ?>" class="nav-link <?= (isset($pag) && $pag == 'about') ? 'active' : '' ?>">About</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo base_url('download'); ?>" class="nav-link <?= (isset($pag) && $pag == 'download') ? 'active' : '' ?>">Download</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo base_url('pricing'); ?>" class="nav-link <?= (isset($pag) && $pag == 'pricing') ? 'active' : '' ?>">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo base_url('faqs_terms'); ?>" class="nav-link <?= (isset($pag) && $pag == 'faqs_terms') ? 'active' : '' ?>">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo base_url('contactus'); ?>" class="nav-link <?= (isset($pag) && $pag == 'contact') ? 'active' : '' ?>">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a href="https://github.com/Niccher/Eaves-Droid-Platform" target="_blank" rel="noopener" class="nav-link" title="GitHub Repository"><i class="fab fa-github mr-1"></i>GitHub</a>
                    </li>
                </ul>
            </div>

            <!-- Right navbar links -->
            <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto align-items-center">
                <!-- Theme Toggle Button (AdminLTE Based) -->
                <li class="nav-item mr-2">
                    <a class="nav-link" href="#" id="darkModeToggle" role="button" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon" id="darkModeIcon"></i>
                    </a>
                </li>
                <li class="nav-item ml-md-2">
                    <a class="btn btn-primary btn-sm px-4 shadow-sm" href="<?= url_to('login') ?>">
                        <i class="fas fa-sign-in-alt mr-2"></i> Log In
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    <!-- /.navbar -->

    <div class="content-wrapper">