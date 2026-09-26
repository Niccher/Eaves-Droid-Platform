<?php
/**
 * Content template for: Password Reset Request
 * Layout: email/_layout
 */

// Variables: $username, $resetLink, $expiresAt, $ip, $userAgent
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">🔐 Password Reset Request</h2>
    
    <p style="color:#475569;margin:0 0 16px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">We received a request to reset your Eaves Droid account password.</p>
    
    <div style="text-align:center;margin:28px 0;">
        <a href="<?= esc($resetLink) ?>" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#22c55e,#3b82f6);color:#fff;text-decoration:none;border-radius:8px;font-weight:700;font-size:15px;">Reset Password</a>
    </div>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin:20px 0;font-size:13px;">
        <p style="margin:0 0 8px;color:#f59e0b;"><strong>⏰ Link expires:</strong> <?= esc($expiresAt ?? '1 hour') ?></p>
        <p style="margin:0;color:#64748b;">If the button doesn't work, copy this link:<br><code style="background:#f1f5f9;padding:4px 8px;border-radius:4px;color:#22c55e;word-break:break-all;"><?= esc($resetLink) ?></code></p>
    </div>
    
    <div style="padding:14px 16px;background:#eff6ff;border-left:4px solid #3b82f6;border-radius:8px;margin:20px 0;font-size:13px;color:#1e40af;">
        <strong>ℹ️ Didn't request this?</strong> You can safely ignore this email. Your password won't be changed unless you click the link above.
    </div>
    
    <p style="color:#94a3b8;font-size:13px;margin-top:20px;">Request IP: <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;"><?= esc($ip ?? 'Unknown') ?></code></p>
</div>