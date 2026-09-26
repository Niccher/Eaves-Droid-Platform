<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">High Severity Anomaly Detected</h2>
    
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">The ML anomaly detection engine has identified a high-severity anomaly.</p>
    
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:20px 0;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:18px;font-weight:700;color:#a855f7;"><?= esc($anomaly['algorithm'] ?? 'Unknown') ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Algorithm</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:18px;font-weight:700;color:#ef4444;"><?= esc($anomaly['severity'] ?? 'High') ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Severity</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:18px;font-weight:700;color:#a855f7;"><?= esc(number_format((float)($anomaly['score'] ?? 0), 4)) ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Score</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:18px;font-weight:700;color:#0066ff;"><?= esc($anomaly['category'] ?? 'Unknown') ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Category</div>
        </div>
    </div>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin:20px 0;">
        <strong style="color:#0f172a;">Anomaly Details:</strong>
        <p style="margin:8px 0 0;color:#475569;font-size:13px;"><?= esc(substr($anomaly['anomaly'] ?? $anomaly['details'] ?? 'No details available', 0, 500)) ?><?= strlen($anomaly['anomaly'] ?? $anomaly['details'] ?? '') > 500 ? '...' : '' ?></p>
    </div>
    
    <div style="margin-top:24px;text-align:center;">
        <a href="<?= esc($results_url ?? site_url('admin/anomalies')) ?>" style="display:inline-block;padding:12px 24px;background:linear-gradient(135deg,#a855f7,#0066ff);color:#fff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">View Full Results</a>
    </div>
</div>