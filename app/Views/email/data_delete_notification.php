<?php
/**
 * Content template for: Data Deletion Notification
 * Used by: Advanced::sendDeleteNotificationEmail
 * Layout: email/_layout
 */
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:28px;box-shadow:0 4px 16px rgba(15,23,42,0.03);">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:22px;font-weight:700;letter-spacing:-0.5px;display:flex;align-items:center;gap:8px;">
        <span style="font-size:24px;">🗑️</span> Data Deletion Complete
    </h2>
    
    <p style="color:#475569;margin:0 0 16px;font-size:14px;line-height:1.6;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 16px;font-size:14px;line-height:1.6;">As requested, your device diagnostics and logs have been permanently purged from our platform as of <strong><?= esc($asAtTimestamp ?? date('Y-m-d H:i:s')) ?></strong>.</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;line-height:1.6;">Below is the summary of the deleted tables and records:</p>
    
    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;margin:16px 0;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;font-size:13px;">
        <thead>
            <tr style="background:linear-gradient(135deg,#ef4444,#ff7675);color:#ffffff;">
                <th style="padding:12px 16px;text-align:left;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:0.8px;">Category</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:0.8px;">Sub-Category</th>
                <th style="padding:12px 16px;text-align:right;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:0.8px;">Rows Deleted</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $catKey => $catData): ?>
                <?php 
                    $catLabel = $categoryLabels[$catKey] ?? ucfirst(str_replace('_', ' ', $catKey));
                    $tables = $catData['tables'] ?? [];
                    $totalRows = $catData['total_rows'] ?? 0;
                    $isMisc = ($catKey === 'misc_hardware' || $catKey === 'misc_software');
                ?>
                <?php if (!empty($tables)): ?>
                    <?php if ($isMisc): ?>
                        <tr style="background:#f8fafc; font-weight:700;">
                            <td colspan="2" style="padding:12px 16px; border-bottom:1px solid #e2e8f0; color:#475569;">
                                📁 <?= esc($catLabel) ?>
                            </td>
                            <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; text-align:right; color:#475569; font-family:monospace;">
                                <?= number_format($totalRows) ?> rows
                            </td>
                        </tr>
                        <?php foreach ($tables as $table): ?>
                        <tr style="background:#ffffff;">
                            <td style="padding:8px 16px 8px 32px; border-bottom:1px solid #f1f5f9; color:#94a3b8; font-size:12px;" colspan="2">
                                └─ <?= esc($table['label']) ?>
                            </td>
                            <td style="padding:8px 16px; border-bottom:1px solid #f1f5f9; text-align:right; color:#64748b; font-size:12px; font-family:monospace;">
                                <?= number_format($table['count']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($tables as $idx => $table): ?>
                        <tr style="<?= $idx % 2 === 0 ? 'background:#f8fafc;' : 'background:#ffffff;' ?>">
                            <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#ef4444; font-weight:600; vertical-align:top;">
                                <?= $idx === 0 ? esc($catLabel) : '' ?>
                            </td>
                            <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; color:#334155;"><?= esc($table['label']) ?></td>
                            <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0; text-align:right; font-weight:700;color:#0f172a; font-family:monospace;"><?= number_format($table['count']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:#fef2f2;font-weight:700;font-size:14px;">
                <td style="padding:14px 16px;border-top:2px solid #fca5a5;color:#dc2626;" colspan="2">Total Rows Purged</td>
                <td style="padding:14px 16px;border-top:2px solid #fca5a5;text-align:right;color:#dc2626;font-family:monospace;"><?= number_format($totalDeleted) ?></td>
            </tr>
        </tfoot>
    </table>
    
    <div style="padding:16px;background:#fef2f2;border-left:4px solid #ef4444;border-radius:8px;margin:24px 0 0;font-size:13px;color:#991b1b;line-height:1.5;">
        <strong>⚠️ Security Notice:</strong> This deletion is absolute and cannot be recovered. Eaves Droid preserves no offline archives or recovery points for security and user-privacy compliance.
    </div>
</div>