<?php
/**
 * Content template for: Anomaly Analysis Complete
 * Used by: Mod_Anomalies::sendAnalysisCompleteEmail
 * Layout: email/_layout
 */

// Variables: $userName, $jobId, $engine, $timeTaken, $totalAlgs, $totalFindings, $completedAt
//            $algorithms, $categorySummary, $topFindings, $resultsUrl
// Security metadata passed from controller
?>

<div class="content-card" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
    <h2 style="margin:0 0 16px;color:#0f172a;font-size:20px;font-weight:600;">Anomaly Analysis Complete</h2>
    
    <p style="color:#475569;margin:0 0 16px;font-size:14px;">Hello <strong><?= esc($userName ?? 'User') ?></strong>,</p>
    <p style="color:#475569;margin:0 0 20px;font-size:14px;">Your anomaly detection analysis has finished. Here is the full summary:</p>
    
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:20px 0;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:22px;font-weight:700;color:#a855f7;"><?= esc($totalAlgs ?? 0) ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Algorithms Run</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:22px;font-weight:700;color:#ef4444;"><?= esc($totalFindings ?? 0) ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Total Findings</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:22px;font-weight:700;color:#0066ff;"><?= esc($timeTaken ?? 'N/A') ?>s</div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Time Taken</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:22px;font-weight:700;color:#00d4aa;"><?= esc($completedAt ?? date('Y-m-d H:i:s')) ?></div>
            <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Completed</div>
        </div>
    </div>
    
    <?php if (!empty($categorySummary)): ?>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin:20px 0;">
        <strong style="color:#0f172a;">Findings by Category:</strong>
        <p style="margin:8px 0 0;color:#475569;font-size:13px;">
            <?php foreach ($categorySummary as $cat => $count): ?>
                <?= esc($cat) ?>: <strong><?= esc($count) ?></strong> | 
            <?php endforeach; ?>
        </p>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($topFindings)): ?>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin:20px 0;">
        <strong style="color:#0f172a;">Top Findings:</strong>
        <ul style="margin:8px 0 0;padding-left:20px;color:#475569;font-size:13px;">
            <?php foreach ($topFindings as $finding): ?>
                <li><?= esc($finding['algorithm'] ?? 'Unknown') ?> - <?= esc($finding['category'] ?? 'N/A') ?> (Score: <?= esc(number_format((float)($finding['score'] ?? 0), 4)) ?>)</li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div style="margin-top:24px;text-align:center;">
        <a href="<?= esc($resultsUrl ?? site_url('admin/anomalies')) ?>" style="display:inline-block;padding:12px 24px;background:linear-gradient(135deg,#a855f7,#0066ff);color:#fff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">View Full Results</a>
    </div>
</div>