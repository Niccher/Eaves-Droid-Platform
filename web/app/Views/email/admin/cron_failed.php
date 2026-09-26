<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">⏰ Cron Job Failed</h2>
    
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">A scheduled cron job failed to execute.</p>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:8px 0;color:#64748b;font-weight:600;">Command</td><td style="padding:8px 0;color:#0f172a;text-align:right;font-weight:600;font-family:monospace;font-size:14px;"><?= esc($command) ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:8px 0;color:#64748b;font-weight:600;">Schedule</td><td style="padding:8px 0;color:#0f172a;text-align:right;"><span style="display:inline-block;padding:4px 10px;background:#dbeafe;color:#2563eb;border-radius:20px;font-size:11px;font-weight:700;"><?= esc($schedule) ?></span></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:8px 0;color:#64748b;font-weight:600;">Description</td><td style="padding:8px 0;color:#0f172a;text-align:right;"><?= esc($description ?? 'No description') ?></td></tr>
            <tr><td style="padding:8px 0;color:#64748b;font-weight:600;">Failed At</td><td style="padding:8px 0;color:#ef4444;text-align:right;font-family:monospace;"><?= esc(date('Y-m-d H:i:s')) ?> UTC</td></tr>
        </table>
    </div>
    
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:16px;margin:20px 0;">
        <strong style="color:#dc2626;">Error Output:</strong>
        <pre style="background:#fef2f2;border:1px solid #fecaca;border-radius:4px;padding:12px;margin:10px 0;max-height:300px;overflow:auto;font-size:12px;color:#dc2626;font-family:monospace;"><?= esc($error) ?></pre>
    </div>
    
    <div style="background:#eff6ff;border-left:4px solid #3b82f6;padding:14px 16px;border-radius:8px;margin:20px 0;color:#1e40af;font-size:13px;">
        <strong>⚠️ Action Required</strong> This cron job failed and will not retry automatically. Please investigate and re-run manually if needed.
    </div>
</div>