<?php
/**
 * Content template for: API Token Revoked
 * Layout: email/_layout
 */

// Variables: $username, $tokenPrefix, $revokedAt
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">API Token Revoked</h2>
    
    <p style="color:#475569;margin:0 0 16px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">An API token has been revoked on your Eaves Droid account.</p>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Token Prefix</td><td style="padding:10px 0;color:#ef4444;text-align:right;font-family:monospace;font-size:12px;"><?= esc($tokenPrefix ?? 'N/A') ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Revoked At</td><td style="padding:10px 0;color:#0f172a;text-align:right;font-family:monospace;"><?= esc($revokedAt ?? date('Y-m-d H:i:s')) ?> UTC</td></tr>
            <tr><td style="padding:10px 0;color:#64748b;font-weight:600;">Revoked By</td><td style="padding:10px 0;color:#0f172a;text-align:right;">You (or admin)</td></tr>
        </table>
    </div>
    
    <div style="padding:14px 16px;background:#eff6ff;border-left:4px solid #3b82f6;border-radius:8px;margin:20px 0;font-size:13px;color:#1e40af;">
        <strong>ℹ️ Note</strong> If you didn't revoke this token, please <a href="<?= site_url('account/security') ?>" style="color:#3b82f6;text-decoration:underline;">check your active tokens</a> and contact support.
    </div>
</div>