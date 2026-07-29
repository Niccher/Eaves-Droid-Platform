<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Deletion Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 700px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;">
        <h2 style="margin-top: 0; color: #dc3545;">Data Deletion Confirmation</h2>
        <p>Dear <?= esc($username) ?>,</p>
        <p>Your data has been permanently deleted from the <strong>Eaves Droid</strong> system as of <strong><?= esc($asAtTimestamp) ?></strong>.</p>
        <p>The following categories and sub-categories were affected:</p>

        <table style="width: 100%; border-collapse: collapse; margin: 15px 0; background-color: #fff;">
            <thead>
                <tr style="background-color: #dc3545; color: #fff;">
                    <th style="padding: 10px; border: 1px solid #f5c6cb; text-align: left;">Category</th>
                    <th style="padding: 10px; border: 1px solid #f5c6cb; text-align: left;">Sub-Category</th>
                    <th style="padding: 10px; border: 1px solid #f5c6cb; text-align: right;">Rows Deleted</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $catKey => $catData): ?>
                    <?php 
                        $catLabel = $categoryLabels[$catKey] ?? ucfirst(str_replace('_', ' ', $catKey));
                        $tables = $catData['tables'] ?? [];
                    ?>
                    <?php if (!empty($tables)): ?>
                        <?php foreach ($tables as $idx => $table): ?>
                            <tr style="<?= $idx % 2 === 0 ? 'background-color: #f8f9fa;' : '' ?>">
                                <td style="padding: 8px; border: 1px solid #f5c6cb; font-weight: bold; vertical-align: top; color: #dc3545;">
                                    <?= $idx === 0 ? esc($categoryLabels[$catKey] ?? ucfirst(str_replace('_', ' ', $catKey))) : '' ?>
                                </td>
                                <td style="padding: 8px; border: 1px solid #f5c6cb; font-size: 13px; color: #333;">
                                    <?= esc($table['label']) ?>
                                </td>
                                <td style="padding: 8px; border: 1px solid #f5c6cb; text-align: right; font-weight: bold;">
                                    <?= number_format($table['count']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #f5c6cb; font-weight: bold; color: #dc3545;">
                                <?= esc($categoryLabels[$catKey] ?? ucfirst(str_replace('_', ' ', $catKey))) ?>
                            </td>
                            <td style="padding: 8px; border: 1px solid #f5c6cb; color: #888; font-style: italic;" colspan="2">No data in this category</td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #f8d7da; font-weight: bold;">
                    <td style="padding: 10px; border: 1px solid #f5c6cb;" colspan="2">Total Rows Deleted</td>
                    <td style="padding: 10px; border: 1px solid #f5c6cb; text-align: right;"><?= number_format($totalDeleted) ?></td>
                </tr>
            </tfoot>
        </table>

        <p style="color: #666; font-size: 14px;">This deletion is permanent and cannot be undone. If you believe this was performed in error, please contact support immediately.</p>
        <p>Thank you,<br>The Eaves Droid Team</p>

        <div style="margin-top: 20px; padding: 12px 15px; background: #e9ecef; border-radius: 6px; font-size: 11px; color: #555;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr><td style="padding: 2px 5px;"><strong>Action:</strong> Data Deletion</td></tr>
                <tr><td style="padding: 2px 5px;"><strong>Status:</strong> <span style="color: #dc3545; font-weight: bold;">Completed</span></td></tr>
                <?php if (!empty($browser)): ?><tr><td style="padding: 2px 5px;"><strong>Browser:</strong> <?= esc($browser) ?></td></tr><?php endif; ?>
                <?php if (!empty($browserIp)): ?><tr><td style="padding: 2px 5px;"><strong>Browser IP:</strong> <?= esc($browserIp) ?></td></tr><?php endif; ?>
                <tr><td style="padding: 2px 5px;"><strong>Executed At:</strong> <?= esc($timestamp ?? $asAtTimestamp ?? date('Y-m-d H:i:s')) ?></td></tr>
            </table>
        </div>
    </div>
</body>
</html>