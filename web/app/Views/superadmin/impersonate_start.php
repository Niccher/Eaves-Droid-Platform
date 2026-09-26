<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starting Impersonation...</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css?v=1.4'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/fontawesome-free/css/all.min.css?v=1.4'); ?>">
    <style>
        body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); border: 1px solid rgba(0,0,0,.125); }
    </style>
</head>
<body>
    <div class="card p-4 text-center" style="max-width: 400px; width: 100%;">
        <div class="spinner-border text-primary mb-3" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <h5 class="mb-2">Starting Impersonation</h5>
        <p class="text-muted mb-3">Opening dashboard for <strong><?php echo htmlspecialchars($targetUser->username); ?></strong> in a new tab...</p>
        <p class="small text-muted">If the new tab doesn't open, please allow popups for this site.</p>
        <a href="<?php echo htmlspecialchars($redirectUrl); ?>" class="btn btn-outline-primary mt-2" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-external-link-alt mr-1"></i> Open Dashboard Manually
        </a>
    </div>

    <script>
        // Redirect directly to target user dashboard in same window
        window.location.href = '<?php echo htmlspecialchars($redirectUrl); ?>';
    </script>
</body>
</html>