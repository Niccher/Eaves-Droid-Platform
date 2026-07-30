<?php
/**
 * Content template for: Two-Factor Authentication Disabled
 * Layout: email/_layout
 */

// Variables: $username
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">Two-Factor Authentication Disabled</h2>
    
    <p style="color:#475569;margin:0 0 16px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">Two-factor authentication (2FA) has been <strong>disabled</strong> on your Eaves Droid account.</p>
    
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:16px;margin:20px 0;">
        <p style="margin:0 0 8px;color:#dc2626;font-weight:600;font-size:13px;">⚠️ 2FA Status: Disabled</p>
        <p style="margin:0;color:#475569;font-size:13px;">Your account is now protected only by your password. We strongly recommend re-enabling 2FA for better security.</p>
    </div>
    
    <div style="text-align:center;margin:24px 0;">
        <a href="<?= site_url('account/security') ?>" style="display:inline-block;padding:12px 28px;background:linear-gradient(135deg,#22c55e,#0066ff);color:#fff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Re-enable 2FA</a>
    </div>
    
    <div style="padding:14px 16px;background:#fef2f2;border-left:4px solid #ef4444;border-radius:8px;margin:20px 0;font-size:13px;color:#991b1b;">
        <strong>⚠️ Not you?</strong> If you didn't disable 2FA, someone else may have access to your account. <a href="<?= site_url('support') ?>" style="color:#ef4444;text-decoration:underline;">Contact support immediately</a> and change your password.
    </div>
</div>