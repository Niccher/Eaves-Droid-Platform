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
    <meta property="og:title" content="<?= isset($page_title) ? esc($page_title) : 'Eaves Droid | Mobile Data Intelligence' ?>"/>
    <meta property="og:description" content="<?= isset($page_desc) ? esc($page_desc) : 'Free mobile data intelligence platform — analyze and visualize your Android device data with ease.' ?>"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="<?= current_url() ?>"/>
    <meta property="og:image" content="<?= base_url('assets/img/logo.png') ?>"/>

    <link rel="shortcut icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/x-icon">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/fontawesome-free/css/all.min.css?v=1.4'); ?>"/>
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/adminlte.min.css?v=1.4'); ?>"/>
    
    <style>
        .navbar-brand .brand-text { font-size: 1.5rem; letter-spacing: -0.5px; }
        .nav-link.active { color: #007bff !important; font-weight: 700; border-bottom: 3px solid #007bff; }
        .nav-link { font-size: 1.1rem; padding-bottom: 5px; margin-right: 10px; transition: 0.3s; }
        .nav-link:hover { color: #007bff !important; }
        .content-wrapper { background-color: #fff !important; }
        .brand-image-custom { height: 35px; width: auto; margin-right: 10px; margin-top: -5px; }
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
                        <a href="<?php echo base_url('faqs_terms'); ?>" class="nav-link <?= (isset($pag) && $pag == 'faqs_terms') ? 'active' : '' ?>">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo base_url('contactus'); ?>" class="nav-link <?= (isset($pag) && $pag == 'contact') ? 'active' : '' ?>">Contact</a>
                    </li>
                </ul>
            </div>

            <!-- Right navbar links -->
            <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
                <li class="nav-item ml-md-3">
                    <a class="btn btn-primary btn-sm px-4 shadow-sm" href="<?= url_to('login') ?>">
                        <i class="fas fa-sign-in-alt mr-2"></i> Log In
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    <!-- /.navbar -->

    <div class="content-wrapper">