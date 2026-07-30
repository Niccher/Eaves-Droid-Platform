<?php
/**
 * Content template for: Two-Factor Authentication Enabled
 * Layout: email/_layout
 */

// Variables: $username, $backupCodes (array)
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">Two-Factor Authentication Enabled</h2>
    
    <p style="color:#475569;margin:0 0 16px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">Two-factor authentication (2FA) has been successfully enabled on your Eaves Droid account. Your account is now protected with an additional layer of security.</p>
    
    <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:16px;margin:20px 0;">
        <p style="margin:0 0 8px;color:#059669;font-weight:600;font-size:13px;">✅ 2FA Status: Enabled</p>
        <p style="margin:0;color:#475569;font-size:13px;">You will now need to enter a 6-digit code from your authenticator app each time you log in.</p>
    </div>
    
    <?php if (!empty($backupCodes)): ?>
    <div style="padding:16px;background:#fffbeb;border-left:4px solid #f59e0b;border-radius:8px;margin:20px 0;">
        <p style="margin:0 0 12px;color:#92400e;font-weight:600;font-size:13px;">🔐 Backup Codes — Save These!</p>
        <p style="margin:0 0 12px;color:#475569;font-size:13px;">If you lose access to your authenticator app, use one of these codes to log in. Each code can only be used once.</p>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px;font-family:monospace;font-size:12px;line-height:2;color:#059669;overflow-x:auto;">
            <?php foreach (array_chunk($backupCodes, 3) as $row): ?>
                <?= implode('  ', array_map(fn($c) => '<strong>' . esc($c) . '</strong>', $row)) ?><br>
            <?php endforeach; ?>
        </div>
        <p style="margin:12px 0 0;color:#92400e;font-size:12px;">⚠️ Store these codes securely — they won't be shown again.</p>
    </div>
    <?php endif; ?>
    
    <p style="color:#94a3b8;font-size:13px;margin-top:20px;">You can manage 2FA settings in <a href="<?= site_url('account/security') ?>" style="color:#00d4aa;">Account → Security</a>.</p>
</div>