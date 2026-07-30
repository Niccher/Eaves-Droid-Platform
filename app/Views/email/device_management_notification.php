<?php
/**
 * Content template for: Remote Device Command Notification
 * Used by: FCMCommandController, RemoteDevice (admin)
 * Layout: email/_layout
 */

// Variables available: $label, $description, $command, $targetUsername, $success, $adminName, $timestamp, $ip, $userAgent
// Plus security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">📱 Remote Command Executed</h2>
    
    <p style="color:#475569;margin:0 0 16px;font-size:14px;">Hello <strong><?= esc($targetUsername ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">A remote command has been executed on your device:</p>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:10px 0;color:#64748b;font-weight:600;width:140px;">Command</td>
                <td style="padding:10px 0;color:#0f172a;font-weight:600;font-size:15px;">
                    <span style="display:inline-flex;align-items:center;gap:8px;background:#ecfdf5;color:#059669;padding:6px 12px;border-radius:6px;font-size:13px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"></polyline><line x1="12" y1="19" x2="20" y2="19"></line></svg>
                        <?= esc($label ?? $command ?? 'Unknown Command') ?>
                    </span>
                </td>
            </tr>
            <?php if (!empty($description)): ?>
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:10px 0;color:#64748b;font-weight:600;">Description</td>
                <td style="padding:10px 0;color:#475569;font-size:13px;"><?= esc($description) ?></td>
            </tr>
            <?php endif; ?>
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:10px 0;color:#64748b;font-weight:600;">Status</td>
                <td style="padding:10px 0;">
                    <span style="display:inline-block;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;background:<?= $success ? '#dcfce7' : '#fef2f2' ?>;color:<?= $success ? '#166534' : '#dc2626' ?>;">
                        <?= $success ? '✅ Success' : '❌ Failed' ?>
                    </span>
                </td>
            </tr>
            <?php if (!empty($adminName)): ?>
            <tr>
                <td style="padding:10px 0;color:#64748b;font-weight:600;">Initiated By</td>
                <td style="padding:10px 0;color:#0f172a;font-weight:500;"><?= esc($adminName) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <?php if (!$success): ?>
    <div style="padding:14px 16px;background:#fef2f2;border-left:4px solid #ef4444;border-radius:8px;margin:16px 0;font-size:13px;color:#991b1b;">
        <strong>⚠️ Command Failed</strong> - The remote command could not be completed on the device. This may be due to the device being offline, permissions not granted, or a temporary network issue.
    </div>
    <?php endif; ?>
    
    <p style="color:#94a3b8;font-size:13px;margin-top:20px;">If you did not authorize this action, please <a href="<?= site_url('support') ?>" style="color:#00d4aa;">contact support immediately</a>.</p>
</div>