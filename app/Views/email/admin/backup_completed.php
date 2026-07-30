<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">✅ Database Backup Completed</h2>
    
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">The scheduled database backup has finished successfully.</p>
    
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:20px 0;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:16px;font-weight:700;color:#0f172a;"><?= esc($filename) ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Filename</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:18px;font-weight:700;color:#22c55e;"><?= esc($size_formatted) ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Size</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:18px;font-weight:700;color:#22c55e;"><?= esc($completed_at) ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Completed</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:18px;font-weight:700;color:#22c55e;">Success</div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Status</div>
        </div>
    </div>
    
    <div style="margin-top:24px;text-align:center;">
        <a href="<?= esc($download_url) ?>" style="display:inline-block;padding:12px 24px;background:#22c55e;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Download Backup</a>
    </div>
</div>