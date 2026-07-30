<?php
/**
 * Content template for: User Account Deleted (sent to admin)
 * Layout: email/_layout
 */

// Variables: $targetUsername, $targetEmail, $deletedBy, $reason, $cascadeSummary (array)
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">🗑️ User Account Deleted</h2>
    
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">A user account has been permanently deleted from Eaves Droid.</p>
    
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Deleted User</td><td style="padding:10px 0;color:#0f172a;text-align:right;"><strong><?= esc($targetUsername ?? 'N/A') ?></strong></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Email</td><td style="padding:10px 0;color:#0f172a;text-align:right;font-family:monospace;"><?= esc($targetEmail ?? 'N/A') ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Deleted By</td><td style="padding:10px 0;color:#22c55e;text-align:right;font-weight:600;"><?= esc($deletedBy ?? 'System') ?></td></tr>
            <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:10px 0;color:#64748b;font-weight:600;">Reason</td><td style="padding:10px 0;color:#64748b;text-align:right;"><?= esc($reason ?? 'Not specified') ?></td></tr>
        </table>
    </div>
    
    <?php if (!empty($cascadeSummary)): ?>
    <h3 style="color:#0f172a;font-size:14px;font-weight:600;margin:24px 0 12px;">Cascade Deletion Summary</h3>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;">
        <ul style="list-style:none;padding:0;margin:0;font-size:13px;">
            <?php foreach ($cascadeSummary as $item): ?>
            <li style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e2e8f0;color:#334155;">
                <span><?= esc($item['label'] ?? $item['table'] ?? 'Unknown') ?></span>
                <span style="color:#22c55e;font-weight:600;"><?= number_format($item['count'] ?? 0) ?> records</span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div style="padding:14px 16px;background:#fffbeb;border-left:4px solid #f59e0b;border-radius:8px;margin:20px 0;font-size:13px;color:#92400e;">
        <strong>⚠️ Audit Notice</strong> This action is irreversible. All associated data has been permanently removed.
    </div>
</div>