<?php
/**
 * Content template for: Device Unpaired/Removed
 * Layout: email/_layout
 */

// Variables: $username, $deviceName, $unpairedAt
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 data-icon="🔌">Device Removed</h2>
    
    <p style="color:#334155;margin:0 0 20px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#334155;margin:0 0 20px;font-size:14px;">An Android device has been removed from your Eaves Droid account.</p>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Device</td><td style="padding:10px 0;color:#1e293b;text-align:right;"><?= esc($deviceName ?? 'Unknown Device') ?></td></tr>
            <tr><td style="padding:10px 0;color:#64748b;font-weight:600;">Removed At</td><td style="padding:10px 0;color:#22c55e;text-align:right;font-family:monospace;"><?= esc($unpairedAt ?? date('Y-m-d H:i:s')) ?> UTC</td></tr>
        </table>
    </div>
    
    <div style="padding:14px 16px;background:#fffbeb;border-left:4px solid #f59e0b;border-radius:8px;margin:20px 0;font-size:13px;color:#92400e;">
        <strong>⚠️ Not you?</strong> If you didn't remove this device, someone else may have access to your account. <a href="<?= site_url('account/security') ?>" style="color:#f59e0b;text-decoration:underline;">Review active devices</a> and change your password.
    </div>
</div>