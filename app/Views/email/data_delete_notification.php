<?php
/**
 * Content template for: Data Deletion Notification
 * Used by: Advanced::sendDeleteNotificationEmail
 * Layout: email/_layout
 */

// Variables: $username, $asAtTimestamp, $categories, $categoryLabels, $totalDeleted
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">🗑️ Data Deletion Confirmation</h2>
    
    <p style="color:#475569;margin:0 0 16px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 16px;font-size:14px;">Your data has been permanently deleted from the <strong>Eaves Droid</strong> system as of <strong><?= esc($asAtTimestamp ?? date('Y-m-d H:i:s')) ?></strong>.</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">The following categories and sub-categories were affected:</p>
    
    <table style="width:100%;border-collapse:collapse;margin:16px 0;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
        <thead>
            <tr style="background:linear-gradient(135deg,#ef4444,#f97316);color:#fff;">
                <th style="padding:12px 16px;text-align:left;font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Category</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Sub-Category</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Rows Deleted</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $catKey => $catData): ?>
                <?php $catLabel = $categoryLabels[$catKey] ?? ucfirst(str_replace('_', ' ', $catKey)); ?>
                <?php $tables = $catData['tables'] ?? []; ?>
                <?php if (!empty($tables)): ?>
                    <?php foreach ($tables as $idx => $table): ?>
                        <tr style="<?= $idx % 2 === 0 ? 'background:#f8fafc;' : 'background:#fff;' ?>">
                            <td style="padding:10px 16px;border-bottom:1px solid #e2e8f0;color:#ef4444;font-weight:600;vertical-align:top;">
                                <?= $idx === 0 ? esc($catLabel) : '' ?>
                            </td>
                            <td style="padding:10px 16px;border-bottom:1px solid #e2e8f0;color:#334155;font-size:13px;"><?= esc($table['label']) ?></td>
                            <td style="padding:10px 16px;border-bottom:1px solid #e2e8f0;text-align:right;font-weight:700;color:#0f172a;"><?= number_format($table['count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td style="padding:12px 16px;color:#ef4444;font-weight:600;"><?= esc($catLabel) ?></td>
                        <td style="padding:12px 16px;color:#94a3b8;font-style:italic;" colspan="2">No data in this category</td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:#fef2f2;font-weight:700;">
                <td style="padding:14px 16px;border-top:1px solid #fecaca;color:#dc2626;" colspan="2">Total Rows Deleted</td>
                <td style="padding:14px 16px;border-top:1px solid #fecaca;text-align:right;color:#dc2626;"><?= number_format($totalDeleted) ?></td>
            </tr>
        </tfoot>
    </table>
    
    <div style="padding:14px 16px;background:#fef2f2;border-left:4px solid #ef4444;border-radius:8px;margin:20px 0;font-size:13px;color:#991b1b;">
        <strong>⚠️ This deletion is permanent and cannot be undone.</strong> If you believe this was performed in error, please <a href="<?= site_url('support') ?>" style="color:#ef4444;text-decoration:underline;">contact support immediately</a>.
    </div>
</div>