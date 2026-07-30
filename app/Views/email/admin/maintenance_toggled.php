<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">Maintenance Mode <?= ucfirst($mode) ?></h2>
    
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">Maintenance mode has been <strong><?= $mode ?>d</strong> on the Eaves Droid platform.</p>
    
    <?php if ($mode === 'enabled'): ?>
    <div style="padding:14px 16px;background:#fffbeb;border-left:4px solid #f59e0b;border-radius:8px;margin:16px 0;font-size:13px;color:#92400e;">
        <strong>🔧 Maintenance Active</strong> The platform is temporarily unavailable. Web dashboard and API endpoints will return 503 responses. Android app uploads will queue locally and sync when maintenance ends.
    </div>
    <?php else: ?>
    <div style="padding:14px 16px;background:#f0fdf4;border-left:4px solid #22c55e;border-radius:8px;margin:16px 0;font-size:13px;color:#166534;">
        <strong>✅ Maintenance Complete</strong> All services are now fully operational.
    </div>
    <?php endif; ?>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:8px 0;color:#64748b;font-weight:600;">Initiated By</td><td style="padding:8px 0;color:#0f172a;text-align:right;font-weight:600;"><?= esc($initiated_by ?? 'Admin') ?></td></tr>
            <?php if (!empty($window_start)): ?>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:8px 0;color:#64748b;font-weight:600;">Window Start</td><td style="padding:8px 0;color:#22c55e;text-align:right;font-family:monospace;"><?= esc($window_start) ?> UTC</td></tr>
            <?php endif; ?>
            <?php if (!empty($window_end)): ?>
            <tr><td style="padding:8px 0;color:#64748b;font-weight:600;">Window End</td><td style="padding:8px 0;color:#22c55e;text-align:right;font-family:monospace;"><?= esc($window_end) ?> UTC</td></tr>
            <?php endif; ?>
        </table>
    </div>
    
    <?php if (!empty($message)): ?>
    <div style="background:#eff6ff;border-left:4px solid #3b82f6;padding:14px 16px;border-radius:8px;margin:20px 0;color:#1e40af;font-size:13px;">
        <strong>📝 Message:</strong> <?= esc($message) ?>
    </div>
    <?php endif; ?>
</div>