<?php
/**
 * Content template for: API Token Created
 * Layout: email/_layout
 */

// Variables: $username, $tokenPrefix, $scopes (array), $expiresAt
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">New API Token Created</h2>
    
    <p style="color:#475569;margin:0 0 16px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">A new API token has been generated for your Eaves Droid account.</p>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Token Prefix</td><td style="padding:10px 0;color:#00d4aa;text-align:right;font-family:monospace;font-size:12px;"><?= esc($tokenPrefix ?? 'N/A') ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Scopes</td><td style="padding:10px 0;color:#0f172a;text-align:right;"><?= esc(implode(', ', $scopes ?? ['full_access'])) ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Created At</td><td style="padding:10px 0;color:#00d4aa;text-align:right;font-family:monospace;"><?= date('Y-m-d H:i:s') ?> UTC</td></tr>
            <tr><td style="padding:10px 0;color:#64748b;font-weight:600;">Expires</td><td style="padding:10px 0;color:#0f172a;text-align:right;"><?= esc($expiresAt ?? 'Never') ?></td></tr>
        </table>
    </div>
    
    <div style="padding:14px 16px;background:#fffbeb;border-left:4px solid #f59e0b;border-radius:8px;margin:20px 0;font-size:13px;color:#92400e;">
        <strong>⚠️ Security Notice</strong> This token provides access to your data. Treat it like a password — never share it. If you didn't create this token, <a href="<?= site_url('account/security') ?>" style="color:#f59e0b;text-decoration:underline;">revoke it immediately</a>.
    </div>
</div>