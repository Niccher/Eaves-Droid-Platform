<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Prj Images - Mobile Data Intelligence Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
          content="Prj Images is a mobile data analysis platform that transforms raw mobile data into actionable insights through advanced analysis and visualization"/>
    <meta name="keywords"
          content="mobile data analysis, android data collection, data visualization, call analysis, SMS correlation, file structure generation"/>
    <meta content="domino" name="author"/>
    <meta content="support@chegecache.co.ke" name="support"/>
    <meta content="https://chegecache.co.ke/" name="Website"/>
    <meta content="Prj Images" name="application-name"/>
    <meta content="mobile data intelligence, data analysis platform" name="keywords"/>
</head>
<body>
<style>
    body { font-family: sans-serif; color: #333; }
    .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    .header h1 { margin: 0; color: #007bff; }
    .section { margin-bottom: 25px; }
    .section-title { font-size: 16px; font-weight: bold; color: #555; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    th, td { border: 1px solid #eee; padding: 8px; text-align: left; font-size: 12px; }
    th { background-color: #f8f9fa; font-weight: bold; }
    .badge { display: inline-block; padding: 3px 7px; font-size: 10px; font-weight: bold; color: #fff; border-radius: 4px; }
    .bg-success { background-color: #28a745; }
    .bg-info { background-color: #17a2b8; }
    .bg-warning { background-color: #ffc107; color: #000; }
    .bg-danger { background-color: #dc3545; }
    .text-right { text-align: right; }
    .summary-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    .summary-item { display: inline-block; width: 30%; text-align: center; }
    .summary-value { font-size: 18px; font-weight: bold; display: block; }
    .summary-label { font-size: 10px; color: #777; text-transform: uppercase; }
</style>
<div class="header">
    <h1>Intelligence Report</h1>
    <p>Generated on <?= $date ?></p>
    <p>Target: <?= $user_info['email'] ?? 'Unknown' ?></p>
</div>

<!-- Executive Summary -->
<div class="section">
    <div class="section-title">Executive Summary</div>
    <div class="summary-box">
        <div class="summary-item">
            <span class="summary-value"><?= number_format($counts['total_sms']) ?></span>
            <span class="summary-label">Total SMS</span>
        </div>
        <div class="summary-item">
            <span class="summary-value"><?= number_format($counts['total_calls']) ?></span>
            <span class="summary-label">Total Calls</span>
        </div>
        <div class="summary-item">
            <span class="summary-value"><?= number_format($counts['total_contacts']) ?></span>
            <span class="summary-label">Contacts</span>
        </div>
    </div>
</div>

<!-- Financial Intel -->
<div class="section">
    <div class="section-title">Financial Intelligence</div>
    <p><strong>Total Detected Spending:</strong> Ksh <?= number_format($financial_summary['total_spending'], 2) ?></p>
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Sender</th>
            <th>Description</th>
            <th class="text-right">Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($financial_summary['recent_tx'] as $tx): ?>
            <tr>
                <td><?= date('M d, H:i', $tx['date'] / 1000) ?></td>
                <td><?= $tx['sender'] ?></td>
                <td><?= substr($tx['description'], 0, 50) ?>...</td>
                <td class="text-right"><?= number_format($tx['amount'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Social Intel -->
<div class="section">
    <div class="section-title">Top 10 Contacts (Social Graph)</div>
    <table>
        <thead>
        <tr>
            <th>Rank</th>
            <th>Name</th>
            <th>Number</th>
            <th>Score</th>
            <th>Interact. Type</th>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($social_graph as $contact): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $contact['name'] ?></td>
                <td><?= $contact['number'] ?></td>
                <td><?= $contact['score'] ?></td>
                <td>
                    <?php if($contact['calls'] > $contact['sms']): ?>
                        <span class="badge bg-success">Call Dominant</span>
                    <?php else: ?>
                        <span class="badge bg-info">SMS Dominant</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Device Health -->
<?php if (!empty($device)): ?>
    <div class="section">
        <div class="section-title">Device Health Pulse</div>
        <table>
            <tr>
                <td><strong>Model:</strong> <?= $device['device_model'] ?? 'N/A' ?></td>
                <td><strong>Battery:</strong> <?= $device['battery_level'] ?? 0 ?>%</td>
            </tr>
            <tr>
                <td><strong>Network:</strong> <?= $device['network_operator'] ?? 'N/A' ?></td>
                <td><strong>Storage Free:</strong> <?= number_format($device['internal_storage_free_gb'] ?? 0, 1) ?> GB</td>
            </tr>
            <tr>
                <td><strong>Android ID:</strong> <?= $device['android_id'] ?? 'N/A' ?></td>
                <td><strong>Rooted:</strong> <?= ($device['is_rooted'] ?? 0) ? 'Yes' : 'No' ?></td>
            </tr>
        </table>
    </div>
<?php endif; ?>

<div style="font-size: 10px; color: #999; text-align: center; margin-top: 50px;">
    CONFIDENTIAL - Generated by Intelligence Suite
</div>
</body>
</html>