<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">Brute-Force Lockout Triggered</h2>

    <p style="color:#475569;margin:0 0 20px;font-size:14px;">The login throttle detected a burst of failed login attempts and has temporarily locked out the offending IP/account.</p>

    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:20px 0;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:18px;font-weight:700;color:#ef4444;"><?= esc($ip ?? 'Unknown') ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Source IP</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:18px;font-weight:700;color:#ef4444;"><?= esc($accountEmail ?? 'N/A') ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Account</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:18px;font-weight:700;color:#0066ff;"><?= esc($failedAttempts ?? 0) ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Failed Attempts</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:18px;font-weight:700;color:#0066ff;"><?= esc($lockoutMinutes ?? 0) ?> min</div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Lockout Window</div>
        </div>
    </div>

    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin:20px 0;">
        <strong style="color:#0f172a;">Action:</strong>
        <p style="margin:8px 0 0;color:#475569;font-size:13px;">Review the security audit trail for the flagged IP and account. Consider whether a password reset is warranted for the affected account.</p>
    </div>

    <div style="margin-top:24px;text-align:center;">
        <a href="<?= esc($audit_url ?? site_url('admin/logs')) ?>" style="display:inline-block;padding:12px 24px;background:linear-gradient(135deg,#ef4444,#0066ff);color:#fff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">View Security Logs</a>
    </div>
</div>
