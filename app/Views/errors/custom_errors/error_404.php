<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #007bff;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-header {
            background: var(--warning-color);
            color: #212529;
            padding: 30px 40px;
            text-align: center;
        }

        .error-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .error-header h2 {
            font-size: 1.8rem;
            font-weight: 500;
        }

        .error-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .error-body {
            padding: 40px;
            text-align: center;
        }

        .error-message {
            font-size: 1.2rem;
            color: var(--dark-color);
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .search-container {
            max-width: 500px;
            margin: 30px auto;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.1);
        }

        .search-box button {
            position: absolute;
            right: 5px;
            top: 5px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-box button:hover {
            background: #0056b3;
            transform: scale(1.05);
        }

        .popular-links {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }

        .popular-links h5 {
            color: var(--info-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .link-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark-color);
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .link-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .link-item i {
            color: var(--primary-color);
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn-custom {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .error-footer {
            background: var(--light-color);
            padding: 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
            color: #666;
            font-size: 0.9rem;
        }

        .error-info {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .info-item {
            flex: 1;
            min-width: 200px;
            text-align: left;
        }

        .info-item i {
            color: var(--info-color);
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .error-container {
                margin: 10px;
            }

            .error-header h1 {
                font-size: 2.5rem;
            }

            .error-header h2 {
                font-size: 1.5rem;
            }

            .error-body {
                padding: 30px 20px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-custom {
                width: 100%;
                justify-content: center;
            }

            .links-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="error-container">
    <div class="error-header">
        <div class="error-icon">
            <i class="fas fa-map-marked-alt"></i>
        </div>
        <h1>404</h1>
        <h2>Page Not Found</h2>
    </div>

    <div class="error-body">
        <p class="error-message">
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>

        <div class="search-container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search for something else...">
                <button onclick="performSearch()">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>

        <div class="popular-links">
            <h5><i class="fas fa-link"></i> Quick Links</h5>
            <div class="links-grid">
                <a href="<?php echo base_url(''); ?>" class="link-item">
                    <i class="fas fa-home"></i> Homepage
                </a>
                <a href="<?php echo base_url('account'); ?>" class="link-item">
                    <i class="fas fa-user"></i> My Account
                </a>
                <a href="<?php echo base_url('apps'); ?>" class="link-item">
                    <i class="fas fa-mobile-alt"></i> Apps
                </a>
                <a href="<?php echo base_url('call_logs'); ?>" class="link-item">
                    <i class="fas fa-phone"></i> Call Logs
                </a>
            </div>
        </div>

        <div class="action-buttons">
            <a href="<?php echo base_url(''); ?>" class="btn-custom btn-primary">
                <i class="fas fa-home"></i> Go to Homepage
            </a>
            <a href="javascript:history.back()" class="btn-custom btn-secondary">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
            <a href="<?php echo base_url('help'); ?>" class="btn-custom btn-outline">
                <i class="fas fa-question-circle"></i> Get Help
            </a>
        </div>

        <?php if (ENVIRONMENT !== 'production'): ?>
            <div class="error-info">
                <div class="info-item">
                    <i class="fas fa-code"></i>
                    <strong>Error Code:</strong> HTTP 404 - Not Found
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                </div>
                <div class="info-item">
                    <i class="fas fa-link"></i>
                    <strong>Requested URL:</strong> <?php echo current_url(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="error-footer">
        <p>&copy; <?php echo date('Y'); ?> Your Application. All rights reserved.</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');

        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Add click effect to buttons
        const buttons = document.querySelectorAll('.btn-custom, .link-item');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    });

    function performSearch() {
        const query = document.getElementById('searchInput').value.trim();
        if (query) {
            // In a real application, you would redirect to search results
            alert('Searching for: ' + query);
            // window.location.href = '<?php echo base_url("search"); ?>?q=' + encodeURIComponent(query);
        } else {
            document.getElementById('searchInput').focus();
        }
    }

    // Show requested URL in console for debugging
    console.log('404 Error - Requested URL:', '<?php echo current_url(); ?>');
</script>
</body>
</html>