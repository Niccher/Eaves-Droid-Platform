<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">Backup Failed</h2>
    
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">The scheduled database backup has failed.</p>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:8px 0;color:#64748b;font-weight:600;">Filename</td><td style="padding:8px 0;color:#0f172a;text-align:right;font-family:monospace;"><?= esc($filename ?? 'N/A') ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:8px 0;color:#64748b;font-weight:600;">Size</td><td style="padding:8px 0;color:#0f172a;text-align:right;"><?= esc($size ?? 'Unknown') ?></td></tr>
            <tr><td style="padding:8px 0;color:#64748b;font-weight:600;">Failed At</td><td style="padding:8px 0;color:#0f172a;text-align:right;font-family:monospace;"><?= esc($completed_at ?? date('Y-m-d H:i:s')) ?></td></tr>
        </table>
    </div>
    
    <div style="padding:14px 16px;background:#fef2f2;border-left:4px solid #ef4444;border-radius:8px;margin:20px 0;font-size:13px;color:#991b1b;">
        <strong>⚠️ Backup Failed</strong> The automated backup did not complete. Check disk space, permissions, and database connectivity.
    </div>
    
    <p style="color:#64748b;font-size:13px;margin-top:20px;">You can trigger a manual backup from the admin panel: <a href="<?= site_url('admin/settings/backup') ?>" style="color:#00d4aa;">Admin → Settings → Backup</a></p>
</div>