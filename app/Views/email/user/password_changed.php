<?php
/**
 * Content template for: Password Changed Confirmation
 * Layout: email/_layout
 */

// Variables: $username, $loginUrl
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">✅ Password Successfully Changed</h2>
    
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">Your Eaves Droid account password has been successfully changed.</p>
    
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:16px;margin:20px 0;">
        <p style="margin:0 0 8px;color:#166534;font-weight:600;font-size:13px;">✅ Change Confirmed</p>
        <p style="margin:0;color:#166534;font-size:13px;">Your password was updated on <strong><?= date('Y-m-d H:i:s') ?> UTC</strong>. You can now log in with your new credentials.</p>
    </div>
    
    <div style="text-align:center;margin:24px 0;">
        <a href="<?= $loginUrl ?? site_url('login') ?>" class="btn" style="display:inline-block;padding:12px 28px;background:#22c55e;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Log In Now</a>
    </div>
    
    <div style="padding:14px 16px;background:#fffbeb;border-left:4px solid #f59e0b;border-radius:8px;margin:20px 0;font-size:13px;color:#92400e;">
        <strong>⚠️ Not you?</strong> If you didn't change your password, someone else may have access to your account. <a href="<?= site_url('support') ?>" style="color:#f59e0b;text-decoration:underline;">Contact support immediately</a> and secure your account.
    </div>
</div>