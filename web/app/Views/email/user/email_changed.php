<?php
/**
 * Content template for: Email Address Changed
 * Layout: email/_layout
 */

// Variables: $username, $oldEmail, $newEmail
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">📧 Email Address Updated</h2>
    
    <p style="color:#475569;margin:0 0 16px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">The email address on your Eaves Droid account has been changed.</p>
    
    <table style="width:100%;border-collapse:collapse;margin:20px 0;">
        <tr style="border-bottom:1px solid #e2e8f0;">
            <td style="padding:12px 0;color:#64748b;font-weight:600;">Previous Email</td>
            <td style="padding:12px 0;color:#64748b;text-align:right;"><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;"><?= esc($oldEmail ?? 'N/A') ?></code></td>
        </tr>
        <tr>
            <td style="padding:12px 0;color:#64748b;font-weight:600;">New Email</td>
            <td style="padding:12px 0;color:#22c55e;font-weight:600;text-align:right;"><?= esc($newEmail ?? 'N/A') ?></td>
        </tr>
    </table>
    
    <div style="padding:14px 16px;background:#fffbeb;border-left:4px solid #f59e0b;border-radius:8px;margin:20px 0;font-size:13px;color:#92400e;">
        <strong>⚠️ Not you?</strong> If you didn't request this change, someone else may have access to your account. <a href="<?= site_url('support') ?>" style="color:#f59e0b;text-decoration:underline;">Contact support immediately</a>.
    </div>
    
    <p style="color:#94a3b8;font-size:13px;">Your account notifications will now be sent to the new email address.</p>
</div>