<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Access Denied</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <style>
        :root {
            --primary-color: #007bff;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-header {
            background: var(--danger-color);
            color: white;
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
            opacity: 0.9;
        }

        .error-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            animation: shake 0.8s ease-in-out infinite alternate;
        }

        @keyframes shake {
            0% {
                transform: translateX(-5px);
            }
            100% {
                transform: translateX(5px);
            }
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

        .error-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            text-align: left;
            border-left: 4px solid var(--danger-color);
        }

        .error-details h5 {
            color: var(--danger-color);
            margin-bottom: 10px;
        }

        .error-details ul {
            padding-left: 20px;
            margin-bottom: 0;
        }

        .error-details li {
            margin-bottom: 8px;
            color: #666;
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
            color: var(--primary-color);
            margin-right: 10px;
        }

        .server-status-panel {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            text-align: left;
            border-left: 4px solid var(--primary-color);
        }

        .server-status-panel h5 {
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .status-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid var(--primary-color);
        }

        .status-item strong {
            display: block;
            color: var(--dark-color);
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .status-item span {
            color: #666;
            font-size: 0.9rem;
            word-break: break-all;
        }

        .status-item.online {
            border-left-color: #28a745;
        }

        .status-item.offline {
            border-left-color: #dc3545;
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

            .error-info {
                flex-direction: column;
            }

            .status-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php
/**
 * Get server status data.
 *
 * @return array
 */
function getServerStatus()
{
    return [
        'server_time' => date('Y-m-d H:i:s'),
        'timezone' => date_default_timezone_get(),
        'php_version' => PHP_VERSION,
        'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        'memory_limit' => ini_get('memory_limit')
    ];
}

/**
 * Get health check data.
 *
 * @return array
 */
function getHealthCheck()
{
    return [
        'status' => 'online',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'environment' => defined('ENVIRONMENT') ? ENVIRONMENT : 'unknown'
    ];
}

$serverStatus = getServerStatus();
$healthCheck = getHealthCheck();
?>
<div class="error-container">
    <div class="error-header">
        <div class="error-icon">
            <i class="fas fa-ban"></i>
        </div>
        <h1>403</h1>
        <h2>Access Denied</h2>
    </div>

    <div class="error-body">
        <p class="error-message">
            You don't have permission to access this page. This area is restricted to authorized users only.
        </p>

        <div class="error-details">
            <h5><i class="fas fa-exclamation-triangle"></i> Possible Reasons:</h5>
            <ul>
                <li>Your user account doesn't have the required permissions</li>
                <li>You're trying to access an admin-only area</li>
                <li>Your session may have expired or been invalidated</li>
                <li>IP address restrictions may be in place</li>
            </ul>
        </div>

        <?php if (ENVIRONMENT !== 'production'): ?>
            <div class="server-status-panel">
                <h5><i class="fas fa-heartbeat"></i> System Status</h5>
                <div class="status-grid">
                    <div class="status-item <?php echo $healthCheck['status'] === 'online' ? 'online' : 'offline'; ?>">
                        <strong>Health Status</strong>
                        <span><?php echo $healthCheck['status']; ?></span>
                    </div>
                    <div class="status-item">
                        <strong>Application Version</strong>
                        <span><?php echo $healthCheck['version']; ?></span>
                    </div>
                    <div class="status-item">
                        <strong>PHP Version</strong>
                        <span><?php echo $serverStatus['php_version']; ?></span>
                    </div>
                    <div class="status-item">
                        <strong>Memory Usage</strong>
                        <span><?php echo $serverStatus['memory_usage']; ?> / <?php echo $serverStatus['memory_limit']; ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="<?php echo base_url(''); ?>" class="btn-custom btn-primary">
                <i class="fas fa-home"></i> Go to Homepage
            </a>
            <a href="javascript:history.back()" class="btn-custom btn-secondary">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
            <a href="<?php echo base_url('login'); ?>" class="btn-custom btn-outline">
                <i class="fas fa-sign-in-alt"></i> Login Again
            </a>
        </div>

        <?php if (ENVIRONMENT !== 'production'): ?>
            <div class="error-info">
                <div class="info-item">
                    <i class="fas fa-code"></i>
                    <strong>Error Code:</strong> HTTP 403 - Forbidden
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                </div>
                <div class="info-item">
                    <i class="fas fa-info-circle"></i>
                    <strong>Environment:</strong> <?php echo ENVIRONMENT; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="error-footer">
        <p>&copy; <?php echo date('Y'); ?> Your Application. All rights reserved.</p>
    </div>
</div>

<script>
    // Add some interactive elements
    document.addEventListener('DOMContentLoaded', function () {
        // Add click effect to buttons
        const buttons = document.querySelectorAll('.btn-custom');
        buttons.forEach(button => {
            button.addEventListener('click', function (e) {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });

        // Auto-refresh for expired sessions
        if (window.location.search.includes('session=expired')) {
            setTimeout(() => {
                window.location.href = '<?php echo base_url("login"); ?>';
            }, 5000);
        }
    });
</script>
</body>
</html>