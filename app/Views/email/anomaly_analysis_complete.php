<?php
/**
 * Content template for: Anomaly Analysis Complete
 * Used by: AnomaliesModel::sendAnalysisCompleteEmail
 * Layout: email/_layout
 */
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:28px;box-shadow:0 4px 16px rgba(15,23,42,0.03);">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:22px;font-weight:700;letter-spacing:-0.5px;display:flex;align-items:center;gap:8px;">
        <span style="font-size:24px;">🛡️</span> Anomaly Detection Results Ready
    </h2>
    
    <p style="color:#475569;margin:0 0 16px;font-size:14px;line-height:1.6;">Hello <strong><?= esc($userName ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;line-height:1.6;">The anomaly detection engine has finished executing the security diagnostic scanner. Below is a comprehensive overview of the diagnostic data ingested, algorithms used, and anomalies flagged:</p>
    
    <!-- 1. Metadata Table -->
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;margin-bottom:24px;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;background:#f8fafc;font-size:13px;line-height:1.6;">
        <tr style="background:#f1f5f9;border-bottom:1px solid #e2e8f0;">
            <td colspan="2" style="padding:10px 16px;font-weight:700;color:#0f172a;">📊 Scan Metadata</td>
        </tr>
        <tr>
            <td style="padding:10px 16px;width:30%;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Ingested Records</td>
            <td style="padding:10px 16px;color:#334155;border-bottom:1px solid #e2e8f0;line-height:1.5;">
                <strong>SMS:</strong> <?= number_format($analysis_counts['sms']['total'] ?? 0) ?> |
                <strong>Calls:</strong> <?= number_format($analysis_counts['call_logs']['total'] ?? 0) ?> |
                <strong>Contacts:</strong> <?= number_format($analysis_counts['contacts']['total'] ?? 0) ?> |
                <strong>Locations:</strong> <?= number_format($analysis_counts['locations']['total'] ?? 0) ?> |
                <strong>Apps:</strong> <?= number_format($analysis_counts['apps']['total'] ?? 0) ?> |
                <strong>FilesController:</strong> <?= number_format($analysis_counts['files']['total'] ?? 0) ?> |
                <strong>Activity:</strong> <?= number_format($analysis_counts['activity']['total'] ?? 0) ?> |
                <strong>Hardware/Device Info:</strong> <?= number_format($analysis_counts['device_info']['total'] ?? 0) ?>
            </td>
        </tr>
        <tr>
            <td style="padding:10px 16px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;vertical-align:top;">Algorithms Run</td>
            <td style="padding:10px 16px;color:#334155;border-bottom:1px solid #e2e8f0;">
                <?php if (!empty($algorithms)): ?>
                    <div style="margin:-2px;">
                        <?php foreach ($algorithms as $alg): ?>
                            <span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;background:#e2e8f0;color:#475569;margin:2px;font-weight:500;">
                                <?= esc($alg['name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <span style="color:#94a3b8;font-style:italic;">None</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td style="padding:10px 16px;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Time Taken</td>
            <td style="padding:10px 16px;color:#334155;border-bottom:1px solid #e2e8f0;font-weight:600;font-family:monospace;"><?= esc($timeTaken ?? 'N/A') ?></td>
        </tr>
        <tr>
            <td style="padding:10px 16px;color:#64748b;font-weight:600;">Total Findings</td>
            <td style="padding:10px 16px;color:#ef4444;font-weight:700;font-size:14px;"><?= esc($totalFindings ?? 0) ?> anomalies flagged</td>
        </tr>
    </table>
    
    <!-- 2. Grouped Anomaly Results -->
    <h3 style="color:#0f172a;font-size:16px;font-weight:700;margin:24px 0 12px;border-bottom:2px solid #e2e8f0;padding-bottom:6px;">⚠️ Flagged AnomaliesController by Category</h3>
    
    <?php
    // Map view category keys to standard DB count keys
    $catIngestKeys = [
        'SMS' => 'sms',
        'Call Log' => 'call_logs',
        'Contacts' => 'contacts',
        'Location' => 'locations',
        'Installed Apps' => 'apps',
        'Files' => 'files',
        'Activity' => 'activity',
        'Device Info' => 'device_info'
    ];

    // Group findings by category
    $groupedFindings = [];
    foreach ($topFindings as $f) {
        $groupedFindings[$f['category']][] = $f;
    }
    
    // Iterate through all categories to show summary
    $allCats = ['SMS', 'Call Log', 'Contacts', 'Location', 'Installed Apps', 'Files', 'Activity', 'Device Info'];
    foreach ($allCats as $cat):
        $findings = $groupedFindings[$cat] ?? [];
        $flaggedCount = count($findings);
        $dbKey = $catIngestKeys[$cat] ?? '';
        $totalInCat = $analysis_counts[$dbKey]['total'] ?? 0;
    ?>
        <div style="margin:16px 0;padding:16px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;box-shadow:0 2px 6px rgba(0,0,0,0.01);">
            <div style="display:flex;justify-content:space-between;align-items:center;font-weight:700;font-size:14px;color:#0f172a;border-bottom:1px solid #e2e8f0;padding-bottom:8px;margin-bottom:10px;">
                <span><?= esc($cat) ?> Diagnostics</span>
                <span style="font-size:12px;font-weight:500;color:#64748b;">
                    <?= number_format($totalInCat) ?> total <?= strtolower($cat) ?>s | 
                    <span style="color:<?= $flaggedCount > 0 ? '#ff4757' : '#00b894' ?>;font-weight:700;">
                        <?= $flaggedCount ?> flagged
                    </span>
                </span>
            </div>
            
            <?php if (!empty($findings)): ?>
                <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;font-size:12px;line-height:1.5;">
                    <?php foreach ($findings as $f): ?>
                    <tr>
                        <td style="padding:6px 0;vertical-align:top;width:75%;color:#475569;">
                            <span style="display:inline-block;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;text-transform:uppercase;margin-right:6px;background:<?= $f['severity'] === 'High' ? '#fff2f2' : ($f['severity'] === 'Medium' ? '#fff9db' : '#e6f4ea') ?>;color:<?= $f['severity'] === 'High' ? '#ef4444' : ($f['severity'] === 'Medium' ? '#f59f00' : '#34a853') ?>;">
                                <?= esc($f['severity']) ?>
                            </span>
                            <strong><?= esc($f['algorithm']) ?>:</strong> <?= esc($f['anomaly']) ?>
                        </td>
                        <td style="padding:6px 0;vertical-align:top;text-align:right;color:#64748b;font-family:monospace;width:25%;">
                            Score: <?= esc(number_format((float)($f['score'] ?? 0), 4)) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p style="margin:0;color:#94a3b8;font-size:12px;font-style:italic;">No anomalies flagged in this category.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    
    <div style="margin-top:28px;text-align:center;">
        <a href="<?= esc($resultsUrl ?? site_url('admin/anomalies')) ?>" style="display:inline-block;padding:12px 28px;background:linear-gradient(135deg,#7042c1,#0066ff);color:#ffffff;text-decoration:none;border-radius:8px;font-weight:700;font-size:14px;box-shadow:0 4px 12px rgba(112,66,193,0.25);">
            View Full Diagnostic Report
        </a>
    </div>
</div>