<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 - Service Unavailable</title>

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
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
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
            animation: spin 4s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

        .maintenance-info {
            background: #e8f4fd;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            text-align: left;
            border-left: 4px solid var(--info-color);
        }

        .maintenance-info h5 {
            color: var(--info-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .progress-container {
            margin: 20px 0;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .progress {
            height: 10px;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar {
            background: var(--info-color);
            transition: width 1s ease;
        }

        .countdown-timer {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border: 2px solid #e0e0e0;
        }

        .timer-display {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--info-color);
            margin: 15px 0;
        }

        .timer-label {
            font-size: 1rem;
            color: #666;
            margin-bottom: 15px;
        }

        .status-updates {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
        }

        .status-updates h5 {
            color: var(--dark-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .update-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .update-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .update-time {
            font-size: 0.9rem;
            color: #666;
            white-space: nowrap;
        }

        .update-text {
            flex: 1;
            color: #333;
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
            color: var(--warning-color);
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

            .timer-display {
                font-size: 2rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-custom {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="error-container">
    <div class="error-header">
        <div class="error-icon">
            <i class="fas fa-tools"></i>
        </div>
        <h1>503</h1>
        <h2>Service Unavailable</h2>
    </div>

    <div class="error-body">
        <p class="error-message">
            We're currently performing scheduled maintenance to improve your experience. The service will be back shortly.
        </p>

        <div class="maintenance-info">
            <h5><i class="fas fa-calendar-alt"></i> Maintenance Details</h5>
            <p>We're upgrading our systems to provide better performance and new features.</p>

            <div class="progress-container">
                <div class="progress-label">
                    <span>Progress</span>
                    <span id="progressPercent">65%</span>
                </div>
                <div class="progress">
                    <div id="progressBar" class="progress-bar" style="width: 65%"></div>
                </div>
            </div>
        </div>

        <div class="countdown-timer">
            <div class="timer-label">Estimated time until service is restored:</div>
            <div class="timer-display" id="countdownTimer">01:45:30</div>
            <small class="text-muted">This is an estimate and may change</small>
        </div>

        <div class="status-updates">
            <h5><i class="fas fa-bullhorn"></i> Latest Updates</h5>
            <div class="update-item">
                <div class="update-time">15:30</div>
                <div class="update-text">Database migration completed successfully</div>
            </div>
            <div class="update-item">
                <div class="update-time">14:45</div>
                <div class="update-text">Server optimization in progress - 65% complete</div>
            </div>
            <div class="update-item">
                <div class="update-time">13:20</div>
                <div class="update-text">Maintenance started as scheduled</div>
            </div>
        </div>

        <div class="action-buttons">
            <button onclick="location.reload()" class="btn-custom btn-primary">
                <i class="fas fa-redo"></i> Check Again
            </button>
            <a href="<?php echo base_url('status'); ?>" class="btn-custom btn-secondary">
                <i class="fas fa-info-circle"></i> Status Page
            </a>
            <a href="mailto:support@yourapp.com" class="btn-custom btn-outline">
                <i class="fas fa-envelope"></i> Contact Support
            </a>
        </div>

        <?php if (ENVIRONMENT !== 'production'): ?>
            <div class="error-info">
                <div class="info-item">
                    <i class="fas fa-code"></i>
                    <strong>Error Code:</strong> HTTP 503 - Service Unavailable
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                </div>
                <div class="info-item">
                    <i class="fas fa-server"></i>
                    <strong>Maintenance:</strong> Scheduled System Update
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
        // Countdown timer
        let countdownTime = 2 * 60 * 60 + 45 * 60 + 30; // 2 hours, 45 minutes, 30 seconds

        function updateCountdown() {
            const hours = Math.floor(countdownTime / 3600);
            const minutes = Math.floor((countdownTime % 3600) / 60);
            const seconds = countdownTime % 60;

            document.getElementById('countdownTimer').textContent =
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            if (countdownTime > 0) {
                countdownTime--;
            }
        }

        // Start countdown
        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Animate progress bar
        let progress = 65;
        const progressBar = document.getElementById('progressBar');
        const progressPercent = document.getElementById('progressPercent');

        function animateProgress() {
            if (progress < 100) {
                progress += Math.random() * 2;
                if (progress > 100) progress = 100;

                progressBar.style.width = progress + '%';
                progressPercent.textContent = Math.round(progress) + '%';
            }
        }

        setInterval(animateProgress, 3000);

        // Auto-refresh page every 60 seconds
        setInterval(() => {
            const reloadBtn = document.querySelector('[onclick="location.reload()"]');
            if (reloadBtn) {
                reloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Checking...';
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        }, 60000);

        // Add click effect to buttons
        const buttons = document.querySelectorAll('.btn-custom');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    });

    // Service status notification
    if ('Notification' in window && Notification.permission === 'granted') {
        setTimeout(() => {
            new Notification('Service Update', {
                body: 'Maintenance is progressing. Service will be restored shortly.',
                icon: '/favicon.ico'
            });
        }, 30000);
    }
</script>
</body>
</html>