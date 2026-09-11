<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>Eaves Droid | Advanced Mobile Forensic & Data Intelligence Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A powerful, free mobile data intelligence platform designed for deep analysis of Android device data, including call logs, SMS correlation, and file structure visualization."/>
    <meta name="keywords" content="mobile forensics, android data analysis, SMS correlation tool, call log analyzer, mobile data intelligence, digital forensics platform"/>
    <meta content="domino" name="author"/>
    <meta content="support@chegecache.co.ke" name="support"/>
    <meta content="https://chegecache.co.ke/" name="Website"/>
    <meta content="Eaves Droid" name="application-name"/>

    <link rel="shortcut icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/x-icon">
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/fontawesome-free/css/all.min.css?v=1.4'); ?>"/>
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/adminlte.min.css?v=1.4'); ?>"/>
    <!-- jQuery -->
    <script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js?v=1.4'); ?>"></script>
    <!-- overlayScrollbars -->
    <link rel="stylesheet"
          href="<?php echo base_url('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css?v=1.4'); ?>"/>
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables/datatables.min.css?v=1.4'); ?>"/>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Dark Mode Pre-loader & UI Utilities -->
    <script>
        if (localStorage.getItem('eaves_dark_mode') === 'enabled') {
            document.documentElement.classList.add('dark-mode-preload');
            // We use DOMContentLoaded to add class to body since body doesn't exist yet
            document.addEventListener("DOMContentLoaded", function() {
                document.body.classList.add('dark-mode');
                document.documentElement.classList.remove('dark-mode-preload');
            });
        }
    </script>
    <style>
        /* Prevent flash of white during load if dark mode is enabled */
        html.dark-mode-preload { background-color: #454d55; }
        
        /* Sticky Table Headers */
        .table-sticky-header thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #f4f6f9; /* AdminLTE light bg */
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }
        body.dark-mode .table-sticky-header thead th {
            background-color: #343a40; /* AdminLTE dark bg */
            border-bottom-color: #4b545c;
        }

        /* Skeleton Loaders */
        .skeleton-box {
            display: inline-block;
            height: 1em;
            position: relative;
            overflow: hidden;
            background-color: #e2e5e7;
            border-radius: 4px;
        }
        .skeleton-box::after {
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            transform: translateX(-100%);
            background-image: linear-gradient(90deg, rgba(255, 255, 255, 0) 0, rgba(255, 255, 255, 0.2) 20%, rgba(255, 255, 255, 0.5) 60%, rgba(255, 255, 255, 0));
            animation: shimmer 2s infinite;
            content: '';
        }
        body.dark-mode .skeleton-box { background-color: #454d55; }
        @keyframes shimmer { 100% { transform: translateX(100%); } }
    </style>
</head>
    