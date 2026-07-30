<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($subject ?? 'Data Export Complete') ?></title>
</head>
<body>
<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">📦 Data Export Complete</h2>
    
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">Hello <strong><?= esc($username ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">Your data export has been completed as of <strong><?= esc($asAtTimestamp ?? date('Y-m-d H:i:s')) ?></strong>. Below is a comprehensive summary of all exported data organized by category with sub-category breakdown:</p>
    
    <?php foreach ($categories as $category => $info): ?>
        <?php $catLabel = $categoryLabels[$category] ?? str_replace('_', ' ', $category); ?>
        <div style="margin:24px 0;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#3b82f6,#22c55e);color:#fff;padding:14px 20px;font-weight:600;display:flex;justify-content:space-between;align-items:center;">
                <span><?= esc($catLabel) ?></span>
                <span style="font-weight:400;font-size:12px;opacity:0.9;">Total: <?= number_format($info['total_rows']) ?> rows | <?= esc($info['total_size_human']) ?></span>
            </div>
            <table style="width:100%;border-collapse:collapse;margin:0;">
                <thead>
                    <tr style="background:#f1f5f9;">
                        <th style="padding:12px 16px;border-bottom:1px solid #e2e8f0;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;">Sub-Category</th>
                        <th style="padding:12px 16px;border-bottom:1px solid #e2e8f0;text-align:right;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;">Rows</th>
                        <th style="padding:12px 16px;border-bottom:1px solid #e2e8f0;text-align:right;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;">Est. Size</th>
                    </thead>
                <tbody>
                    <?php foreach ($info['tables'] as $tbl): ?>
                    <tr style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:10px 16px;color:#334155;font-size:13px;"><?= esc($tbl['label']) ?></td>
                        <td style="padding:10px 16px;text-align:right;font-weight:700;color:#0f172a;"><?= number_format($tbl['count']) ?></td>
                        <td style="padding:10px 16px;text-align:right;color:#94a3b8;font-size:12px;"><?= esc($tbl['estimated_size_human']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#f1f5f9;font-weight:700;">
                        <td style="padding:14px 16px;border-top:1px solid #e2e8f0;color:#22c55e;">Category Total</td>
                        <td style="padding:14px 16px;border-top:1px solid #e2e8f0;text-align:right;color:#0f172a;"><?= number_format($info['total_rows']) ?></td>
                        <td style="padding:14px 16px;border-top:1px solid #e2e8f0;text-align:right;color:#22c55e;"><?= esc($info['total_size_human']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endforeach; ?>
    
    <div style="margin-top:24px;padding:16px;background:linear-gradient(135deg,rgba(59,130,246,0.1),rgba(34,197,94,0.1));border:1px solid #22c55e;border-radius:8px;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <strong style="color:#22c55e;">Grand Total:</strong>
            <span style="color:#0f172a;font-family:monospace;margin-left:12px;"><?= number_format($totalRows) ?> rows | <?= esc($totalSizeHuman) ?></span>
        </div>
        <a href="<?= site_url('account/export') ?>" class="btn" style="display:inline-block;padding:10px 20px;background:linear-gradient(135deg,#22c55e,#3b82f6);color:#fff;text-decoration:none;border-radius:8px;font-weight:600;font-size:13px;">Download Export</a>
    </div>
    
    <p style="color:#94a3b8;font-size:13px;margin-top:20px;">A JSON file containing the full export is available for download from your dashboard.</p>
</div>
</body>
</html>