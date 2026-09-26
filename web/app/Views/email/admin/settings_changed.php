<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">⚙️ Critical Settings Changed</h2>
    
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">One or more critical system settings have been modified.</p>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:8px 0;color:#64748b;font-weight:600;">Setting</td><td style="padding:8px 0;color:#0f172a;font-weight:500;text-align:right;"><?= esc($setting_name ?? 'Unknown') ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:8px 0;color:#64748b;font-weight:600;">Changed By</td><td style="padding:8px 0;color:#22c55e;font-weight:600;text-align:right;"><?= esc($changed_by ?? 'Unknown') ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:8px 0;color:#64748b;font-weight:600;">Previous Value</td><td style="padding:8px 0;color:#ef4444;text-align:right;font-family:monospace;font-size:12px;"><?= esc($old_value ?? 'N/A') ?></td></tr>
            <tr><td style="padding:8px 0;color:#64748b;font-weight:600;">New Value</td><td style="padding:8px 0;color:#22c55e;font-weight:600;text-align:right;font-family:monospace;font-size:12px;"><?= esc($new_value ?? 'N/A') ?></td></tr>
        </table>
    </div>
    
    <div style="background:#eff6ff;border-left:4px solid #3b82f6;padding:16px;border-radius:8px;margin:20px 0;color:#1e40af;font-size:13px;">
        <strong>ℹ️ Settings Change Detected</strong> Critical system settings were modified. If this was not authorized, please investigate immediately.
    </div>
    
    <p style="color:#64748b;font-size:13px;margin-top:20px;">Review the change in the admin panel under Settings → Audit Log.</p>
</div>