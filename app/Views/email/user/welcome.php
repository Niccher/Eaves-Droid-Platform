<?php
/**
 * Content template for: Welcome Email
 * Used by: RegisterController::sendWelcomeEmail
 * Layout: email/_layout
 */

// Variables: $username, $email, $loginUrl
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">👋 Welcome to Eaves Droid</h2>
    
    <p style="color:#475569;margin:0 0 16px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">Your account has been created successfully. Here are your account details:</p>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Email</td><td style="padding:10px 0;color:#0f172a;text-align:right;font-family:monospace;"><?= esc($email ?? '') ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Username</td><td style="padding:10px 0;color:#0f172a;text-align:right;"><?= esc($username ?? '') ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Password</td><td style="padding:10px 0;color:#94a3b8;text-align:right;font-size:12px;">Set during registration (not stored in plain text)</td></tr>
            <tr><td style="padding:10px 0;color:#64748b;font-weight:600;">Status</td><td style="padding:10px 0;"><span style="display:inline-block;padding:4px 10px;background:#dcfce7;color:#166534;border-radius:20px;font-size:11px;font-weight:700;">Active</span></td></tr>
        </table>
    </div>
    
    <div style="padding:16px;background:#eff6ff;border-left:4px solid #3b82f6;border-radius:8px;margin:20px 0;">
        <p style="margin:0 0 10px;color:#1e40af;font-weight:600;font-size:13px;">🚀 Getting Started</p>
        <ul style="margin:0;padding-left:20px;color:#475569;font-size:13px;line-height:2;">
            <li>Log in using your email and password at <a href="<?= $loginUrl ?? site_url('login') ?>" style="color:#00d4aa;">the dashboard</a></li>
            <li>Generate an API token from <strong>Account → Security</strong> to connect your Android device</li>
            <li>Install the Eaves Droid app on your Android device and scan the token QR code</li>
            <li>Data collection begins automatically once the device is paired</li>
        </ul>
    </div>
    
    <div style="padding:16px;background:#fffbeb;border-left:4px solid #f59e0b;border-radius:8px;margin:20px 0;">
        <p style="margin:0 0 8px;color:#92400e;font-weight:600;font-size:13px;">🔒 Security Tip</p>
        <p style="margin:0;color:#475569;font-size:13px;">Never share your password or API tokens with anyone. Enable two-factor authentication in your security settings for added protection.</p>
    </div>
    
    <p style="color:#94a3b8;font-size:13px;margin-top:20px;">If you have any questions, refer to the <a href="<?= site_url('docs') ?>" style="color:#00d4aa;">documentation</a> or <a href="<?= site_url('support') ?>" style="color:#00d4aa;">contact support</a>.</p>
</div>