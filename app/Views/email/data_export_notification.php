<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Export Notification</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 750px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="background-color: #f0f8ff; padding: 20px; border-radius: 8px; border-left: 4px solid #0d6efd;">
        <h2 style="margin-top: 0; color: #0d6efd;">Data Export Complete</h2>
        <p>Dear <?= esc($username) ?>,</p>
        <p>Your data export has been completed as of <strong><?= esc($asAtTimestamp) ?></strong>. Below is a comprehensive summary of all exported data organized by category with sub-category breakdown:</p>
        
        <?php foreach ($categories as $category => $info): ?>
        <div style="margin: 20px 0; border: 1px solid #d0e8f5; border-radius: 6px; background: #fff;">
            <div style="background-color: #0d6efd; color: #fff; padding: 10px 15px; border-radius: 6px 6px 0 0; font-weight: bold;">
                <?= esc($categoryLabels[$category] ?? str_replace('_', ' ', $category)) ?>
                <span style="float: right; font-weight: normal; font-size: 13px;">
                    Total: <?= number_format($info['total_rows']) ?> rows | <?= esc($info['total_size_human']) ?>
                </span>
            </div>
            <table style="width: 100%; border-collapse: collapse; margin: 0;">
                <thead>
                    <tr style="background-color: #e8f0fe;">
                        <th style="padding: 10px; border: 1px solid #b0d4f1; text-align: left;">Sub-Category</th>
                        <th style="padding: 10px; border: 1px solid #b0d4f1; text-align: right;">Rows</th>
                        <th style="padding: 10px; border: 1px solid #b0d4f1; text-align: right;">Est. Size</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($info['tables'] as $tbl): ?>
                    <tr>
                        <td style="padding: 8px 15px; border: 1px solid #d0e8f5; font-size: 13px;"><?= esc($tbl['label']) ?></td>
                        <td style="padding: 8px 15px; border: 1px solid #d0e8f5; text-align: right; font-weight: bold;"><?= number_format($tbl['count']) ?></td>
                        <td style="padding: 8px 15px; border: 1px solid #d0e8f5; text-align: right; font-size: 13px;"><?= esc($tbl['estimated_size_human']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background-color: #e8f0fe; font-weight: bold;">
                        <td style="padding: 10px 15px; border: 1px solid #b0d4f1;">Category Total</td>
                        <td style="padding: 10px 15px; border: 1px solid #b0d4f1; text-align: right;"><?= number_format($info['total_rows']) ?></td>
                        <td style="padding: 10px 15px; border: 1px solid #b0d4f1; text-align: right;"><?= esc($info['total_size_human']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endforeach; ?>

        <div style="margin-top: 20px; padding: 15px; background: #e8f0fe; border-radius: 6px; border: 1px solid #b0d4f1;">
            <strong>Grand Total:</strong> <?= number_format($totalRows) ?> rows | <?= esc($totalSizeHuman) ?>
        </div>

        <p style="color: #666; font-size: 14px; margin-top: 20px;">A JSON file containing the full export is available for download from your dashboard.</p>
        <p>Thank you,<br>The Eaves Droid Team</p>

        <div style="margin-top: 20px; padding: 12px 15px; background: #e9ecef; border-radius: 6px; font-size: 11px; color: #555;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr><td style="padding: 2px 5px;"><strong>Action:</strong> Data Export</td></tr>
                <tr><td style="padding: 2px 5px;"><strong>Status:</strong> <span style="color: #28a745; font-weight: bold;">Success</span></td></tr>
                <?php if (!empty($browser)): ?><tr><td style="padding: 2px 5px;"><strong>Browser:</strong> <?= esc($browser) ?></td></tr><?php endif; ?>
                <?php if (!empty($browserIp)): ?><tr><td style="padding: 2px 5px;"><strong>Browser IP:</strong> <?= esc($browserIp) ?></td></tr><?php endif; ?>
                <tr><td style="padding: 2px 5px;"><strong>Executed At:</strong> <?= esc($timestamp ?? $asAtTimestamp ?? date('Y-m-d H:i:s')) ?></td></tr>
            </table>
        </div>
    </div>
</body>
</html>