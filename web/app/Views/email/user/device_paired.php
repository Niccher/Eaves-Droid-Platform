<?php
/**
 * Content template for: New Device Paired
 * Layout: email/_layout
 */

// Variables: $username, $deviceModel, $deviceName, $pairedAt
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">📱 New Device Paired</h2>
    
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">A new Android device has been successfully paired with your Eaves Droid account.</p>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Device Model</td><td style="padding:10px 0;color:#0f172a;text-align:right;"><?= esc($deviceModel ?? 'Unknown') ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Device Name</td><td style="padding:10px 0;color:#0f172a;text-align:right;"><?= esc($deviceName ?? 'N/A') ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Paired At</td><td style="padding:10px 0;color:#22c55e;text-align:right;font-family:monospace;"><?= esc($pairedAt ?? date('Y-m-d H:i:s')) ?> UTC</td></tr>
            <tr><td style="padding:10px 0;color:#64748b;font-weight:600;">FCM Status</td><td style="padding:10px 0;"><span style="display:inline-block;padding:4px 10px;background:#dcfce7;color:#166534;border-radius:20px;font-size:11px;font-weight:700;">Connected</span></td></tr>
        </table>
    </div>
    
    <div style="padding:14px 16px;background:#fffbeb;border-left:4px solid #f59e0b;border-radius:8px;margin:20px 0;font-size:13px;color:#92400e;">
        <strong>⚠️ Not you?</strong> If you didn't pair this device, someone else may have your API token. <a href="<?= site_url('account/security') ?>" style="color:#f59e0b;text-decoration:underline;">Revoke tokens immediately</a> and change your password.
    </div>
</div>