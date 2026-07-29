<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Anomaly Analysis Complete – Eaves Droid</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 650px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #6f42c1;">
        <h2 style="margin-top: 0; color: #6f42c1;">Anomaly Analysis Complete</h2>
        <p>Dear <?= esc($userName) ?>,</p>
        <p>Your anomaly detection analysis has finished. Here is the full summary:</p>

        <table style="width: 100%; border-collapse: collapse; margin: 15px 0; background-color: #fff;">
            <thead>
                <tr style="background-color: #6f42c1; color: #fff;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Metric</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Detail</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Job ID</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">#<?= esc($jobId) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Status</td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><span style="color: #28a745; font-weight: bold;">Completed</span></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Engine</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-transform: uppercase;"><?= esc($engine) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Time Taken</td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?= esc($timeTaken) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Total Algorithms</td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?= (int)$totalAlgs ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Total Findings</td>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;"><?= number_format($totalFindings) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Completed At</td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?= esc($completedAt) ?></td>
                </tr>
            </tbody>
        </table>

        <?php if (!empty($algorithms)): ?>
        <h3 style="margin-top: 20px;">Algorithms Used</h3>
        <table style="width: 100%; border-collapse: collapse; margin: 10px 0; background-color: #fff;">
            <thead>
                <tr style="background-color: #e9ecef;">
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Algorithm</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Category</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Compatibility</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($algorithms as $alg): ?>
                <tr>
                    <td style="padding: 6px 8px; border: 1px solid #ddd;"><?= esc($alg['name']) ?></td>
                    <td style="padding: 6px 8px; border: 1px solid #ddd; text-transform: capitalize;"><?= esc(str_replace('_', ' ', $alg['category_key'] ?? $alg['category'] ?? 'other')) ?></td>
                    <td style="padding: 6px 8px; border: 1px solid #ddd;">
                        <span class="badge badge-<?= ($alg['compat'] ?? 'both') === 'python' ? 'warning' : 'info' ?>">
                            <?= esc(strtoupper($alg['compat'] ?? 'both')) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if (!empty($categorySummary)): ?>
        <h3 style="margin-top: 20px;">Findings by Category</h3>
        <table style="width: 100%; border-collapse: collapse; margin: 10px 0; background-color: #fff;">
            <thead>
                <tr style="background-color: #e9ecef;">
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Category</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">High</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Medium</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Low</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorySummary as $cat => $info): ?>
                <tr>
                    <td style="padding: 6px 8px; border: 1px solid #ddd; text-transform: capitalize;"><?= esc(str_replace('_', ' ', $cat)) ?></td>
                    <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: right; color: <?= $info['High'] > 0 ? '#dc3545' : '#333' ?>; font-weight: bold;"><?= $info['High'] ?></td>
                    <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: right; color: <?= $info['Medium'] > 0 ? '#ffc107' : '#333' ?>; font-weight: bold;"><?= $info['Medium'] ?></td>
                    <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: right; color: <?= $info['Low'] > 0 ? '#28a745' : '#333' ?>; font-weight: bold;"><?= $info['Low'] ?></td>
                    <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: right; font-weight: bold;"><?= $info['total'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if (!empty($topFindings)): ?>
        <h3 style="margin-top: 20px;">Top Findings</h3>
        <ul style="font-size: 14px; line-height: 1.6;">
            <?php foreach ($topFindings as $finding): ?>
            <li>
                <strong><?= esc($finding['algorithm']) ?></strong> — <?= esc(substr($finding['anomaly'], 0, 150)) ?>
                <span style="color: <?= $finding['severity'] === 'High' ? '#dc3545' : ($finding['severity'] === 'Medium' ? '#ffc107' : '#28a745') ?>;">
                    [<?= esc($finding['severity']) ?>]
                </span>
                (score: <?= esc(number_format((float)$finding['score'], 4)) ?>)
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <p style="margin-top: 20px; color: #666; font-size: 14px;">
            View full results: <a href="<?= esc($resultsUrl) ?>" style="color: #6f42c1;"><?= esc($resultsUrl) ?></a>
        </p>
        <p>Thank you,<br>The Eaves Droid Anomaly Detection System</p>

        <div style="margin-top: 20px; padding: 12px 15px; background: #e9ecef; border-radius: 6px; font-size: 11px; color: #555;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr><td style="padding: 2px 5px;"><strong>Action:</strong> Anomaly Analysis</td></tr>
                <tr><td style="padding: 2px 5px;"><strong>Status:</strong> <span style="color: #6f42c1; font-weight: bold;">Completed</span></td></tr>
                <?php if (!empty($browser)): ?><tr><td style="padding: 2px 5px;"><strong>Browser:</strong> <?= esc($browser) ?></td></tr><?php endif; ?>
                <?php if (!empty($browserIp)): ?><tr><td style="padding: 2px 5px;"><strong>Browser IP:</strong> <?= esc($browserIp) ?></td></tr><?php endif; ?>
                <tr><td style="padding: 2px 5px;"><strong>Executed At:</strong> <?= esc($timestamp ?? $completedAt ?? date('Y-m-d H:i:s')) ?></td></tr>
            </table>
        </div>
    </div>
</body>
</html>