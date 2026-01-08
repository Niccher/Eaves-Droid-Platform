<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oops! Something went wrong</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
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
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .error-header {
            background: var(--primary-color);
            color: white;
            padding: 30px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .error-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .error-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
        }

        .error-header h2 {
            font-size: 1.5rem;
            font-weight: 500;
            opacity: 0.9;
            position: relative;
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            position: relative;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .error-body {
            padding: 40px;
        }

        .error-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .error-message {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
        }

        .error-message h4 {
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message p {
            color: var(--dark-color);
            line-height: 1.6;
            margin-bottom: 0;
        }

        .error-actions {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
        }

        .error-actions h4 {
            color: var(--secondary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--dark-color);
        }

        .action-item:hover {
            background: var(--primary-color);
            color: white;
            transform: translateX(5px);
        }

        .action-item:hover i {
            color: white;
        }

        .action-item i {
            color: var(--primary-color);
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .error-details {
            grid-column: 1 / -1;
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px;
            border-top: 2px solid #e0e0e0;
        }

        .error-details h4 {
            color: var(--secondary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid var(--primary-color);
        }

        .detail-item strong {
            display: block;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .detail-item span {
            color: #666;
            font-size: 0.9rem;
        }

        .error-footer {
            background: var(--light-color);
            padding: 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .error-container {
                margin: 10px;
            }

            .error-content {
                grid-template-columns: 1fr;
            }

            .error-header h1 {
                font-size: 2rem;
            }

            .error-header h2 {
                font-size: 1.2rem;
            }

            .error-body {
                padding: 30px 20px;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="error-container">
    <div class="error-header">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1>Oops! Something went wrong</h1>
        <h2>We encountered an unexpected error</h2>
    </div>

    <div class="error-body">
        <div class="error-content">
            <div class="error-message">
                <h4><i class="fas fa-info-circle"></i> What happened?</h4>
                <p>
                    An unexpected error occurred while processing your request. This could be due to various reasons
                    including temporary server issues, network problems, or an unexpected condition in the application.
                </p>
            </div>

            <div class="error-actions">
                <h4><i class="fas fa-cogs"></i> Quick Actions</h4>
                <a href="javascript:location.reload()" class="action-item">
                    <i class="fas fa-redo"></i>
                    <div>
                        <strong>Refresh Page</strong>
                        <small>Try loading the page again</small>
                    </div>
                </a>
                <a href="<?php echo base_url(''); ?>" class="action-item">
                    <i class="fas fa-home"></i>
                    <div>
                        <strong>Go to Homepage</strong>
                        <small>Return to the main page</small>
                    </div>
                </a>
                <a href="javascript:history.back()" class="action-item">
                    <i class="fas fa-arrow-left"></i>
                    <div>
                        <strong>Go Back</strong>
                        <small>Return to previous page</small>
                    </div>
                </a>
                <a href="<?php echo base_url('help'); ?>" class="action-item">
                    <i class="fas fa-question-circle"></i>
                    <div>
                        <strong>Get Help</strong>
                        <small>Visit help center</small>
                    </div>
                </a>
            </div>

            <?php if (ENVIRONMENT !== 'production' && isset($exception)): ?>
                <div class="error-details">
                    <h4><i class="fas fa-bug"></i> Technical Details</h4>
                    <div class="details-grid">
                        <div class="detail-item">
                            <strong>Error Type</strong>
                            <span><?php echo get_class($exception); ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Error Code</strong>
                            <span><?php echo $exception->getCode(); ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Timestamp</strong>
                            <span><?php echo date('Y-m-d H:i:s'); ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Environment</strong>
                            <span><?php echo ENVIRONMENT; ?></span>
                        </div>
                    </div>

                    <?php if ($exception->getMessage()): ?>
                        <div class="detail-item" style="grid-column: 1 / -1; margin-top: 15px;">
                            <strong>Error Message</strong>
                            <span><?php echo $exception->getMessage(); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="error-footer">
        <p>&copy; <?php echo date('Y'); ?> Your Application. All rights reserved.</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to action items
        const actionItems = document.querySelectorAll('.action-item');
        actionItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px) scale(1.02)';
            });

            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0) scale(1)';
            });
        });

        // Auto-refresh suggestion after 30 seconds
        setTimeout(() => {
            const refreshBtn = document.querySelector('[href="javascript:location.reload()"]');
            if (refreshBtn) {
                refreshBtn.style.animation = 'bounce 1s 3';
                refreshBtn.style.backgroundColor = 'var(--primary-color)';
                refreshBtn.style.color = 'white';

                setTimeout(() => {
                    refreshBtn.style.animation = '';
                    refreshBtn.style.backgroundColor = '';
                    refreshBtn.style.color = '';
                }, 3000);
            }
        }, 30000);

        // Log error for debugging
        console.warn('General error occurred. Time:', new Date().toISOString());
    });
</script>
</body>
</html>