<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Mobile Intelligence Report</title>
<style>
@page { margin: 18mm 14mm 20mm 14mm; }
body { font-family: 'DejaVu Sans', sans-serif; color: #212529; font-size: 10pt; line-height: 1.5; margin: 0; padding: 0; }

/* ── Header ── */
.report-header { text-align: center; margin-bottom: 22pt; border-bottom: 3px solid #0d6efd; padding-bottom: 12pt; }
.report-header h1 { margin: 0; font-size: 22pt; color: #0d6efd; letter-spacing: 0.5pt; }
.report-header .sub { font-size: 9pt; color: #6c757d; margin-top: 4pt; }
.report-header .target { font-size: 8pt; color: #adb5bd; }

/* ── Cards ── */
.card { border: 1px solid #dee2e6; border-radius: 6pt; margin-bottom: 14pt; overflow: hidden; }
.card-header { padding: 8pt 12pt; font-size: 11pt; font-weight: 700; border-bottom: 1px solid #dee2e6; }
.card-body { padding: 10pt 12pt; }
.bg-primary { background-color: #0d6efd; color: #fff; }
.bg-success { background-color: #198754; color: #fff; }
.bg-danger { background-color: #dc3545; color: #fff; }
.bg-warning { background-color: #ffc107; color: #212529; }
.bg-info { background-color: #0dcaf0; color: #212529; }
.bg-secondary { background-color: #6c757d; color: #fff; }
.bg-light { background-color: #f8f9fa; color: #212529; }
.bg-dark { background-color: #212529; color: #fff; }
.text-primary { color: #0d6efd; }
.text-success { color: #198754; }
.text-danger { color: #dc3545; }
.text-warning { color: #e68a00; }
.text-muted { color: #6c757d; }
.text-white { color: #fff; }

/* ── Badges ── */
.badge { display: inline-block; padding: 2pt 6pt; font-size: 8pt; font-weight: 700; border-radius: 3pt; line-height: 1.4; }
.badge-success { background-color: #198754; color: #fff; }
.badge-danger { background-color: #dc3545; color: #fff; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-info { background-color: #0dcaf0; color: #212529; }
.badge-secondary { background-color: #6c757d; color: #fff; }
.badge-primary { background-color: #0d6efd; color: #fff; }
.badge-light { background-color: #f8f9fa; color: #212529; border: 1px solid #dee2e6; }

/* ── Tables ── */
table { width: 100%; border-collapse: collapse; font-size: 9pt; }
th, td { border: 1px solid #dee2e6; padding: 5pt 7pt; text-align: left; vertical-align: middle; }
th { background-color: #f8f9fa; font-weight: 700; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.3pt; color: #495057; }
tr:nth-child(even) td { background-color: #f8f9fa; }
.table-small th, .table-small td { padding: 3pt 5pt; font-size: 8pt; }
.text-right { text-align: right; }
.text-center { text-align: center; }

/* ── Summary Scorecards ── */
.scorecard-row { width: 100%; }
.scorecard-row td { border: none; padding: 4pt; vertical-align: top; }
.scorecard { border: 1px solid #dee2e6; border-radius: 5pt; padding: 8pt; text-align: center; }
.scorecard .value { font-size: 18pt; font-weight: 800; display: block; }
.scorecard .label { font-size: 7pt; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5pt; display: block; margin-top: 2pt; }

/* ── Progress bars ── */
.progress { width: 100%; background-color: #e9ecef; border-radius: 3pt; height: 12pt; overflow: hidden; margin: 3pt 0; }
.progress-bar { height: 100%; border-radius: 3pt; }
.progress-bar-success { background-color: #198754; }
.progress-bar-danger { background-color: #dc3545; }
.progress-bar-warning { background-color: #ffc107; }
.progress-bar-info { background-color: #0dcaf0; }
.progress-bar-primary { background-color: #0d6efd; }
.progress-bar-secondary { background-color: #6c757d; }

/* ── Insight boxes ── */
.insight-box { border-left: 3pt solid #0d6efd; padding: 6pt 10pt; margin: 5pt 0; background-color: #f0f7ff; font-size: 9pt; }
.insight-box.warning { border-left-color: #ffc107; background-color: #fffbe6; }
.insight-box.danger { border-left-color: #dc3545; background-color: #fff0f0; }
.insight-box.success { border-left-color: #198754; background-color: #f0fff4; }

/* ── Rule / HR ── */
hr { border: none; border-top: 1px solid #dee2e6; margin: 10pt 0; }

/* ── Two-column layout ── */
.cols { width: 100%; }
.cols td { border: none; padding: 4pt 6pt; vertical-align: top; }
.cols .col { border: none; }

/* ── Footer ── */
.report-footer { font-size: 7.5pt; color: #adb5bd; text-align: center; margin-top: 30pt; border-top: 1px solid #dee2e6; padding-top: 8pt; }

/* ── Section title ── */
.section-title { font-size: 12pt; font-weight: 700; color: #0d6efd; margin: 14pt 0 8pt 0; padding-bottom: 4pt; border-bottom: 2px solid #0d6efd; }
</style>
</head>
<body>

<!-- ═══════════════════ HEADER ═══════════════════ -->
<div class="report-header">
    <h1>&#9670; Intelligence Report</h1>
    <div class="sub">Comprehensive Mobile Data Analysis &amp; ML-Generated Insights</div>
    <div class="target">Generated on <?= $date ?> &nbsp;|&nbsp; Target: <?= $user_info['email'] ?? 'Unknown' ?></div>
</div>

<!-- ═══════════════════ EXECUTIVE SUMMARY ═══════════════════ -->
<div class="section-title">&#9632; Executive Summary</div>
<div class="card">
    <div class="card-body">
        <table class="scorecard-row">
            <tr>
                <td style="width:16.66%"><div class="scorecard"><span class="value"><?= number_format($counts['total_sms']) ?></span><span class="label">SMS</span></div></td>
                <td style="width:16.66%"><div class="scorecard"><span class="value"><?= number_format($counts['total_calls']) ?></span><span class="label">Calls</span></div></td>
                <td style="width:16.66%"><div class="scorecard"><span class="value"><?= number_format($counts['total_contacts']) ?></span><span class="label">Contacts</span></div></td>
                <td style="width:16.66%"><div class="scorecard"><span class="value"><?= number_format($counts['total_apps']) ?></span><span class="label">Apps</span></div></td>
                <td style="width:16.66%"><div class="scorecard"><span class="value"><?= number_format($counts['total_locations']) ?></span><span class="label">Locations</span></div></td>
                <td style="width:16.66%"><div class="scorecard"><span class="value"><?= number_format($counts['total_files']) ?></span><span class="label">FilesController</span></div></td>
            </tr>
        </table>
    </div>
</div>

<!-- ═══════════════════ COMMUNICATION PROFILE ═══════════════════ -->
<div class="section-title">&#9632; Communication Intelligence Profile</div>
<div class="card">
    <div class="card-body">
        <table class="cols">
            <tr>
                <td class="col" style="width:50%">
                    <h4 style="margin:0 0 6pt 0; font-size:10pt; color:#0d6efd;">&#9993; SMS Breakdown</h4>
                    <table>
                        <tr><th>Category</th><th class="text-right">Count</th><th style="width:50%"></th></tr>
                        <?php $smsMax = max($sms_analysis['financial'], $sms_analysis['promo'], $sms_analysis['malicious'], $sms_analysis['personal'], $sms_analysis['service'], $sms_analysis['otp'], $sms_analysis['utility'], 1); ?>
                        <tr><td>Financial</td><td class="text-right"><?= $sms_analysis['financial'] ?></td><td><div class="progress"><div class="progress-bar progress-bar-primary" style="width:<?= round($sms_analysis['financial']/$smsMax*100) ?>%"></div></div></td></tr>
                        <tr><td>Promotional</td><td class="text-right"><?= $sms_analysis['promo'] ?></td><td><div class="progress"><div class="progress-bar progress-bar-info" style="width:<?= round($sms_analysis['promo']/$smsMax*100) ?>%"></div></div></td></tr>
                        <tr><td>Personal</td><td class="text-right"><?= $sms_analysis['personal'] ?></td><td><div class="progress"><div class="progress-bar progress-bar-success" style="width:<?= round($sms_analysis['personal']/$smsMax*100) ?>%"></div></div></td></tr>
                        <tr><td>Service Alerts</td><td class="text-right"><?= $sms_analysis['service'] ?></td><td><div class="progress"><div class="progress-bar progress-bar-secondary" style="width:<?= round($sms_analysis['service']/$smsMax*100) ?>%"></div></div></td></tr>
                        <tr><td>OTP / 2FA</td><td class="text-right"><?= $sms_analysis['otp'] ?></td><td><div class="progress"><div class="progress-bar progress-bar-warning" style="width:<?= round($sms_analysis['otp']/$smsMax*100) ?>%"></div></div></td></tr>
                        <tr><td>Utility</td><td class="text-right"><?= $sms_analysis['utility'] ?></td><td><div class="progress"><div class="progress-bar progress-bar-warning" style="width:<?= round($sms_analysis['utility']/$smsMax*100) ?>%"></div></div></td></tr>
                        <tr><td><span class="badge badge-danger">Malicious</span></td><td class="text-right"><b><?= $sms_analysis['malicious'] ?></b></td><td><div class="progress"><div class="progress-bar progress-bar-danger" style="width:<?= round($sms_analysis['malicious']/$smsMax*100) ?>%"></div></div></td></tr>
                    </table>
                </td>
                <td class="col" style="width:50%">
                    <h4 style="margin:0 0 6pt 0; font-size:10pt; color:#0d6efd;">&#9742; Call Breakdown</h4>
                    <table>
                        <tr><th>Category</th><th class="text-right">Count</th><th style="width:50%"></th></tr>
                        <?php $callMax = max($call_analysis['family'], $call_analysis['business'], $call_analysis['spam'], $call_analysis['urgent'], $call_analysis['new'], $call_analysis['intl'], 1); ?>
                        <tr><td>Family</td><td class="text-right"><?= $call_analysis['family'] ?></td><td><div class="progress"><div class="progress-bar progress-bar-success" style="width:<?= round($call_analysis['family']/$callMax*100) ?>%"></div></div></td></tr>
                        <tr><td>Business</td><td class="text-right"><?= $call_analysis['business'] ?></td><td><div class="progress"><div class="progress-bar progress-bar-primary" style="width:<?= round($call_analysis['business']/$callMax*100) ?>%"></div></div></td></tr>
                        <tr><td>International</td><td class="text-right"><?= $call_analysis['intl'] ?></td><td><div class="progress"><div class="progress-bar progress-bar-info" style="width:<?= round($call_analysis['intl']/$callMax*100) ?>%"></div></div></td></tr>
                        <tr><td>Urgent</td><td class="text-right"><?= $call_analysis['urgent'] ?></td><td><div class="progress"><div class="progress-bar progress-bar-warning" style="width:<?= round($call_analysis['urgent']/$callMax*100) ?>%"></div></div></td></tr>
                        <tr><td>New Contacts</td><td class="text-right"><?= $call_analysis['new'] ?></td><td><div class="progress"><div class="progress-bar progress-bar-secondary" style="width:<?= round($call_analysis['new']/$callMax*100) ?>%"></div></div></td></tr>
                        <tr><td><span class="badge badge-danger">Spam/Scam</span></td><td class="text-right"><b><?= $call_analysis['spam'] ?></b></td><td><div class="progress"><div class="progress-bar progress-bar-danger" style="width:<?= round($call_analysis['spam']/$callMax*100) ?>%"></div></div></td></tr>
                    </table>
                </td>
            </tr>
        </table>
        <div style="font-size:8pt; color:#6c757d; margin-top:6pt;">
            SMS classified via NaiveBayes (TF-IDF vectorization). Calls classified via heuristic rules (frequency, name matching, recency).
        </div>
    </div>
</div>

<!-- ═══════════════════ RELATIONSHIP TIERS (ML SOCIAL) ═══════════════════ -->
<?php if (!empty($ml_social['insights'])): ?>
<div class="section-title" style="page-break-before: always;">&#9632; Relationship Tiers &mdash; KMeans Social Graph</div>
<div class="card">
    <div class="card-header bg-primary text-white">&#9670; ML Insight: <?= $ml_social['algorithm'] ?></div>
    <div class="card-body">
        <table class="cols">
            <tr>
                <td class="col" style="width:55%">
                    <table>
                        <tr><th>Tier</th><th>Contacts</th><th>Avg Score</th><th>Sample Members</th></tr>
                        <?php
                        $tiers = [];
                        preg_match_all('/<b>(Inner Circle|Regular Contact|Acquaintance|Tier \d+)<\/b>: (\d+) contact\(s\), avg score ([\d.]+)/', implode(' ', $ml_social['insights']), $tierMatches, PREG_SET_ORDER);
                        foreach ($tierMatches as $m) {
                            echo '<tr><td><b>' . $m[1] . '</b></td><td class="text-center">' . $m[2] . '</td><td class="text-center">' . $m[3] . '</td><td>—</td></tr>';
                        }
                        ?>
                    </table>
                </td>
                <td class="col" style="width:45%">
                    <div style="font-size:9pt;">
                        <?php foreach ($ml_social['insights'] as $i): ?>
                        <div class="insight-box">&#9679; <?= $i ?></div>
                        <?php endforeach; ?>
                    </div>
                </td>
            </tr>
        </table>
        <div style="font-size:7.5pt; color:#6c757d; margin-top:4pt;">&#9432; <?= $ml_social['description'] ?></div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ FINANCIAL INTELLIGENCE ═══════════════════ -->
<div class="section-title" style="page-break-before: always;">&#9632; Financial Intelligence</div>
<div class="card">
    <?php if (!empty($ml_finance['insights'])): ?>
    <div class="card-header bg-success text-white">&#9670; ML Insight: <?= $ml_finance['algorithm'] ?></div>
    <?php endif; ?>
    <div class="card-body">
        <?php if (!empty($ml_finance['insights'])): ?>
        <table class="cols" style="margin-bottom:8pt;">
            <tr>
                <td class="col" style="width:50%">
                    <table class="scorecard-row">
                        <tr>
                            <td style="width:50%"><div class="scorecard"><span class="value text-success">Ksh <?= number_format($financial_summary['total_spending'], 0) ?></span><span class="label">Total Spending</span></div></td>
                            <td style="width:50%"><div class="scorecard"><span class="value text-primary"><?= $financial_summary['tx_count'] ?></span><span class="label">Transactions</span></div></td>
                        </tr>
                    </table>
                </td>
                <td class="col" style="width:50%">
                    <?php foreach ($ml_finance['insights'] as $i): ?>
                    <div class="insight-box success">&#9679; <?= $i ?></div>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <?php endif; ?>
        <table>
            <thead>
                <tr><th>Date</th><th>Sender</th><th>Description</th><th>Type</th><th class="text-right">Amount (KES)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($financial_summary['recent_tx'] as $tx): ?>
                <tr>
                    <td><?= date('M d, H:i', $tx['date'] / 1000) ?></td>
                    <td><b><?= $tx['sender'] ?></b></td>
                    <td><?= htmlspecialchars(substr($tx['description'], 0, 50)) ?>...</td>
                    <td><span class="badge badge-<?= $tx['type'] == 'income' ? 'success' : ($tx['type'] == 'utility' ? 'warning' : 'info') ?>"><?= ucfirst($tx['type']) ?></span></td>
                    <td class="text-right <?= $tx['type'] == 'income' ? 'text-success' : 'text-danger' ?>"><b><?= $tx['type'] == 'income' ? '+' : '-' ?><?= number_format($tx['amount'], 2) ?></b></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="font-size:7.5pt; color:#6c757d; margin-top:4pt;">&#9432; <?= ($ml_finance['description'] ?? 'Transaction data extracted from SMS using regex + NaiveBayes classification.') ?></div>
    </div>
</div>

<!-- ═══════════════════ SUBSCRIPTIONS & RECURRING BILLS ═══════════════════ -->
<?php if (!empty($ml_subscript['insights'])): ?>
<div class="section-title" style="page-break-before: always;">&#9632; SubscriptionsController &amp; Recurring Bills</div>
<div class="card">
    <div class="card-header bg-warning">&#9670; ML Insight: <?= $ml_subscript['algorithm'] ?></div>
    <div class="card-body">
        <table class="cols">
            <tr>
                <td class="col" style="width:55%">
                    <?php foreach ($ml_subscript['insights'] as $i): ?>
                    <div class="insight-box warning">&#9679; <?= $i ?></div>
                    <?php endforeach; ?>
                </td>
                <td class="col" style="width:45%">
                    <table>
                        <tr><th>Service/Sender</th><th class="text-right">Amount</th></tr>
                        <?php
                        $forecastData = $subscription_data ?? [];
                        foreach ($forecastData as $sender => $sub):
                        ?>
                        <tr><td><?= $sender ?></td><td class="text-right">KES <?= number_format($sub['amount'], 2) ?></td></tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
        </table>
        <div style="font-size:7.5pt; color:#6c757d; margin-top:4pt;">&#9432; <?= $ml_subscript['description'] ?></div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ PRIVACY RISK SUMMARY ═══════════════════ -->
<?php if (!empty($ml_privacy['insights'])): ?>
<div class="section-title" style="page-break-before: always;">&#9632; Privacy &amp; Permission Risk Audit</div>
<div class="card">
    <div class="card-header bg-danger text-white">&#9670; ML Insight: <?= $ml_privacy['algorithm'] ?></div>
    <div class="card-body">
        <table class="cols">
            <tr>
                <td class="col" style="width:60%">
                    <?php foreach ($ml_privacy['insights'] as $i): ?>
                    <div class="insight-box danger">&#9679; <?= $i ?></div>
                    <?php endforeach; ?>
                </td>
                <td class="col" style="width:40%">
                    <table class="scorecard-row">
                        <tr>
                            <td style="width:50%"><div class="scorecard" style="border-color:#dc3545;"><span class="value text-danger"><?php preg_match('/(\d+) app\(s\) statistically above normal/', strip_tags(implode(' ', $ml_privacy['insights'])), $m); echo $m[1] ?? '0'; ?></span><span class="label">Z-Score AnomaliesController</span></div></td>
                            <td style="width:50%"><div class="scorecard"><span class="value"><?php preg_match('/(\d+) are critical risk/', strip_tags(implode(' ', $ml_privacy['insights'])), $m); echo $m[1] ?? '0'; ?></span><span class="label">Critical Risk Apps</span></div></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div style="font-size:7.5pt; color:#6c757d; margin-top:4pt;">&#9432; <?= $ml_privacy['description'] ?></div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ SENTIMENT HEALTH ═══════════════════ -->
<?php if (!empty($ml_sentiment['insights'])): ?>
<div class="section-title" style="page-break-before: always;">&#9632; Sentiment &amp; Relationship Health</div>
<div class="card">
    <div class="card-header bg-info">&#9670; ML Insight: <?= $ml_sentiment['algorithm'] ?></div>
    <div class="card-body">
        <table class="cols">
            <tr>
                <td class="col" style="width:55%">
                    <?php
                    $moodTxt = '';
                    $scoreVal = 0;
                    foreach ($ml_sentiment['insights'] as $ins) {
                        $clean = strip_tags($ins);
                        if (strpos($clean, 'Overall relationship mood') !== false) {
                            if (strpos($clean, 'Positive') !== false) $moodTxt = 'Positive';
                            elseif (strpos($clean, 'Negative') !== false) $moodTxt = 'Negative';
                            else $moodTxt = 'Neutral';
                            preg_match('/score: (-?\d+)%/', $clean, $sm); $scoreVal = $sm[1] ?? 0;
                        }
                    }
                    $moodBadge = $moodTxt === 'Positive' ? 'badge-success' : ($moodTxt === 'Negative' ? 'badge-danger' : 'badge-warning');
                    $clusterCount = 0;
                    preg_match('/KMeans clustered into (\d+) sentiment/', strip_tags(implode(' ', $ml_sentiment['insights'])), $m); $clusterCount = $m[1] ?? '—';
                    ?>
                    <table class="scorecard-row" style="margin-bottom:6pt;">
                        <tr>
                            <td style="width:33%"><div class="scorecard"><span class="value"><span class="badge <?= $moodBadge ?>"><?= $moodTxt ?></span></span><span class="label">Overall Mood</span></div></td>
                            <td style="width:33%"><div class="scorecard"><span class="value <?= $scoreVal >= 0 ? 'text-success' : 'text-danger' ?>"><?= $scoreVal ?>%</span><span class="label">Sentiment Score</span></div></td>
                            <td style="width:33%"><div class="scorecard"><span class="value"><?= $clusterCount ?></span><span class="label">KMeans Clusters</span></div></td>
                        </tr>
                    </table>
                    <?php foreach ($ml_sentiment['insights'] as $i): ?>
                    <div class="insight-box">&#9679; <?= $i ?></div>
                    <?php endforeach; ?>
                </td>
                <td class="col" style="width:45%">
                    <table>
                        <tr><th>Contact</th><th class="text-center">Positive</th><th class="text-center">Negative</th><th class="text-center">Health</th></tr>
                        <?php
                        $sentimentData = $sentiment_profile ?? [];
                        $displaySentiment = array_slice($sentimentData, 0, 8);
                        foreach ($displaySentiment as $addr => $sd):
                            $s = ($sd['total'] > 0) ? round(($sd['positive'] - $sd['negative']) / max(1, $sd['total']) * 100) : 0;
                        ?>
                        <tr>
                            <td><b><?= $sd['name'] ?></b></td>
                            <td class="text-center text-success"><?= $sd['positive'] ?></td>
                            <td class="text-center text-danger"><?= $sd['negative'] ?></td>
                            <td class="text-center">
                                <?php if ($s > 15): ?><span class="badge badge-success">&#9786;</span>
                                <?php elseif ($s < -15): ?><span class="badge badge-danger">&#9785;</span>
                                <?php else: ?><span class="badge badge-warning">&#9786;</span><?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
        </table>
        <div style="font-size:7.5pt; color:#6c757d; margin-top:4pt;">&#9432; <?= $ml_sentiment['description'] ?></div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ LIFESTYLE ARCHETYPE ═══════════════════ -->
<?php if (!empty($ml_mobility['insights'])): ?>
<div class="section-title" style="page-break-before: always;">&#9632; Lifestyle &amp; Mobility Archetype</div>
<div class="card">
    <div class="card-header bg-success text-white">&#9670; ML Insight: <?= $ml_mobility['algorithm'] ?></div>
    <div class="card-body">
        <table class="cols">
            <tr>
                <td class="col" style="width:50%">
                    <?php
                    $archetype = '';
                    foreach ($ml_mobility['insights'] as $i) {
                        $clean = strip_tags($i);
                        if (strpos($clean, 'Lifestyle archetype') !== false) {
                            $archetype = substr($clean, strpos($clean, ':') + 2);
                        }
                    }
                    ?>
                    <div style="text-align:center; padding:12pt; border:2px solid #198754; border-radius:6pt;">
                        <div style="font-size:8pt; color:#6c757d; text-transform:uppercase;">Lifestyle Archetype</div>
                        <div style="font-size:16pt; font-weight:800; color:#198754;"><?= $archetype ?></div>
                    </div>
                    <?php preg_match('/Sedentary: (\d+)%/', strip_tags(implode(' ', $ml_mobility['insights'])), $sed);
                    preg_match('/Active: (\d+)%/', strip_tags(implode(' ', $ml_mobility['insights'])), $act);
                    preg_match('/Transit: (\d+)%/', strip_tags(implode(' ', $ml_mobility['insights'])), $trn); ?>
                    <table style="margin-top:6pt;">
                        <tr><th>Activity</th><th class="text-right">%</th><th style="width:60%"></th></tr>
                        <tr><td><span class="badge badge-secondary">Sedentary</span></td><td class="text-right"><?= $sed[1] ?? 0 ?>%</td><td><div class="progress"><div class="progress-bar progress-bar-secondary" style="width:<?= $sed[1] ?? 0 ?>%"></div></div></td></tr>
                        <tr><td><span class="badge badge-success">Active</span></td><td class="text-right"><?= $act[1] ?? 0 ?>%</td><td><div class="progress"><div class="progress-bar progress-bar-success" style="width:<?= $act[1] ?? 0 ?>%"></div></div></td></tr>
                        <tr><td><span class="badge badge-primary">Transit</span></td><td class="text-right"><?= $trn[1] ?? 0 ?>%</td><td><div class="progress"><div class="progress-bar progress-bar-primary" style="width:<?= $trn[1] ?? 0 ?>%"></div></div></td></tr>
                    </table>
                </td>
                <td class="col" style="width:50%">
                    <?php foreach ($ml_mobility['insights'] as $i): ?>
                    <div class="insight-box success">&#9679; <?= $i ?></div>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <div style="font-size:7.5pt; color:#6c757d; margin-top:4pt;">&#9432; <?= $ml_mobility['description'] ?></div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ GEOGRAPHIC BASES (ML HOTSPOTS) ═══════════════════ -->
<?php if (!empty($ml_hotspots['insights'])): ?>
<div class="section-title" style="page-break-before: always;">&#9632; Geographic Bases &mdash; DBSCAN Clustering</div>
<div class="card">
    <div class="card-header bg-primary text-white">&#9670; ML Insight: <?= $ml_hotspots['algorithm'] ?></div>
    <div class="card-body">
        <table class="cols">
            <tr>
                <td class="col" style="width:50%">
                    <table>
                        <tr><th>Base</th><th>Latitude</th><th>Longitude</th><th class="text-center">Pings</th></tr>
                        <?php
                        $clusterData = $cluster_data ?? [];
                        foreach ($clusterData as $cl):
                        ?>
                        <tr>
                            <td><b><?= $cl['label'] ?></b></td>
                            <td><?= $cl['lat'] ?></td>
                            <td><?= $cl['lng'] ?></td>
                            <td class="text-center"><span class="badge badge-primary"><?= $cl['pings'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
                <td class="col" style="width:50%">
                    <?php foreach ($ml_hotspots['insights'] as $i): ?>
                    <div class="insight-box">&#9679; <?= $i ?></div>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <div style="font-size:7.5pt; color:#6c757d; margin-top:4pt;">&#9432; <?= $ml_hotspots['description'] ?></div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ APP CATEGORY DNA ═══════════════════ -->
<?php if (!empty($ml_apps['insights'])): ?>
<div class="section-title" style="page-break-before: always;">&#9632; App Category DNA</div>
<div class="card">
    <div class="card-header bg-dark text-white">&#9670; ML Insight: <?= $ml_apps['algorithm'] ?></div>
    <div class="card-body">
        <table class="cols">
            <tr>
                <td class="col" style="width:55%">
                    <?php foreach ($ml_apps['insights'] as $i): ?>
                    <div class="insight-box">&#9679; <?= $i ?></div>
                    <?php endforeach; ?>
                </td>
                <td class="col" style="width:45%">
                    <table class="table-small">
                        <tr><th>Category</th><th class="text-center">Count</th><th style="width:50%"></th></tr>
                        <?php
                        $categoriesData = $categories_data ?? [];
                        $catMax = max(array_merge($categoriesData, [1]));
                        foreach ($categoriesData as $cat => $cnt):
                        ?>
                        <tr>
                            <td><?= $cat ?></td>
                            <td class="text-center"><?= $cnt ?></td>
                            <td><div class="progress"><div class="progress-bar progress-bar-primary" style="width:<?= round($cnt/$catMax*100) ?>%"></div></div></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
        </table>
        <div style="font-size:7.5pt; color:#6c757d; margin-top:4pt;">&#9432; <?= $ml_apps['description'] ?></div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ STORAGE FORENSICS ═══════════════════ -->
<?php if (!empty($ml_storage['insights'])): ?>
<div class="section-title" style="page-break-before: always;">&#9632; Storage Forensics</div>
<div class="card">
    <div class="card-header bg-danger text-white">&#9670; ML Insight: <?= $ml_storage['algorithm'] ?></div>
    <div class="card-body">
        <table class="cols">
            <tr>
                <td class="col" style="width:55%">
                    <?php foreach ($ml_storage['insights'] as $i): ?>
                    <div class="insight-box danger">&#9679; <?= $i ?></div>
                    <?php endforeach; ?>
                </td>
                <td class="col" style="width:45%">
                    <table class="scorecard-row">
                        <tr>
                            <td style="width:50%"><div class="scorecard"><span class="value"><?php preg_match('/Storage: ([\d.]+) MB/', strip_tags($ml_storage['insights'][0] ?? ''), $m); echo $m[1] ?? '—'; ?></span><span class="label">Total (MB)</span></div></td>
                            <td style="width:50%"><div class="scorecard"><span class="value"><?php preg_match('/across (\d+) files/', strip_tags($ml_storage['insights'][0] ?? ''), $m); echo $m[1] ?? '—'; ?></span><span class="label">FilesController</span></div></td>
                        </tr>
                    </table>
                    <?php
                    $storageData = $storage_data ?? [];
                    $srcMax = max(array_merge($storageData['by_source'] ?? [], [1]));
                    ?>
                    <table class="table-small" style="margin-top:6pt;">
                        <tr><th>Source</th><th class="text-right">MB</th><th style="width:50%"></th></tr>
                        <?php foreach ($storageData['by_source'] as $src => $bytes): $mb = round($bytes / (1024*1024), 1); ?>
                        <tr>
                            <td><?= $src ?></td>
                            <td class="text-right"><?= $mb ?></td>
                            <td><div class="progress"><div class="progress-bar progress-bar-<?= $src == 'WhatsApp' ? 'success' : ($src == 'Camera/DCIM' ? 'danger' : ($src == 'Downloads' ? 'warning' : 'secondary')) ?>" style="width:<?= round($mb / max(1, $srcMax/(1024*1024))*100) ?>%"></div></div></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
        </table>
        <div style="font-size:7.5pt; color:#6c757d; margin-top:4pt;">&#9432; <?= $ml_storage['description'] ?></div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ TOP CONTACTS TABLE ═══════════════════ -->
<div class="section-title" style="page-break-before: always;">&#9632; Top Contacts &mdash; Social Graph</div>
<div class="card">
    <div class="card-body">
        <table>
            <thead>
                <tr><th>Rank</th><th>Name</th><th>Number</th><th class="text-right">Score</th><th class="text-center">SMS</th><th class="text-center">Calls</th><th>Dominance</th></tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($social_graph as $contact): ?>
                <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td><b><?= $contact['name'] ?></b></td>
                    <td><?= $contact['number'] ?></td>
                    <td class="text-right"><span class="badge badge-primary"><?= $contact['score'] ?></span></td>
                    <td class="text-center"><?= $contact['sms'] ?></td>
                    <td class="text-center"><?= $contact['calls'] ?></td>
                    <td><?php if($contact['calls'] > $contact['sms']): ?><span class="badge badge-success">&#9742; Call</span><?php else: ?><span class="badge badge-info">&#9993; SMS</span><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══════════════════ DEVICE HEALTH ═══════════════════ -->
<?php if (!empty($device)): ?>
<div class="section-title" style="page-break-before: always;">&#9632; Device Health Pulse</div>
<div class="card">
    <div class="card-body">
        <table>
            <tr><td style="width:25%; font-weight:700;">Model</td><td style="width:25%;"><?= $device['device_model'] ?? 'N/A' ?></td>
                <td style="width:25%; font-weight:700;">Battery</td><td style="width:25%;"><?= $device['battery_level'] ?? 0 ?>%</td></tr>
            <tr><td style="font-weight:700;">Network</td><td><?= $device['network_operator'] ?? 'N/A' ?></td>
                <td style="font-weight:700;">Free Storage</td><td><?= number_format($device['internal_storage_free_gb'] ?? 0, 1) ?> GB</td></tr>
            <tr><td style="font-weight:700;">Android ID</td><td><?= $device['android_id'] ?? 'N/A' ?></td>
                <td style="font-weight:700;">Rooted</td><td><?= ($device['is_rooted'] ?? 0) ? '<span class="badge badge-danger">Yes</span>' : '<span class="badge badge-success">No</span>' ?></td></tr>
            <tr><td style="font-weight:700;">Charging</td><td><?= ($device['charging_status'] ?? 0) ? 'Yes' : 'No' ?></td>
                <td style="font-weight:700;">Last Seen</td><td><?= !empty($device['last_active']) ? date('M d, H:i', strtotime($device['last_active'])) : 'N/A' ?></td></tr>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ APP INTELLIGENCE ═══════════════════ -->
<?php if (!empty($top_apps)): ?>
<div class="section-title" style="page-break-before: always;">&#9632; App Intelligence (Top 10)</div>
<div class="card">
    <div class="card-body">
        <table>
            <thead>
                <tr><th>#</th><th>App Name</th><th>Package</th><th>Installed At</th></tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($top_apps as $app): ?>
                <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td><b><?= $app['name'] ?></b></td>
                    <td style="font-size:8pt; color:#6c757d;"><?= $app['package'] ?></td>
                    <td><?= !empty($app['install_time']) ? date('M d, Y', $app['install_time'] / 1000) : 'N/A' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════ LOCATION TRAILS ═══════════════════ -->
<?php if (!empty($recent_locations)): ?>
<div class="section-title" style="page-break-before: always;">&#9632; Location Trails (Recent)</div>
<div class="card">
    <div class="card-body">
        <table>
            <thead>
                <tr><th>Time</th><th>Latitude</th><th>Longitude</th><th class="text-right">Accuracy</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recent_locations as $loc): ?>
                <tr>
                    <td><?= date('M d, H:i', is_numeric($loc['extracted_at']) ? $loc['extracted_at']/1000 : strtotime($loc['extracted_at'])) ?></td>
                    <td><?= $loc['latitude'] ?></td>
                    <td><?= $loc['longitude'] ?></td>
                    <td class="text-right"><?= $loc['accuracy'] ?>m</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="report-footer">
    CONFIDENTIAL — Generated by Eaves Droid &bull; <?= $date ?>, <?= date('g:i a') ?> &bull; <?= $user_info['email'] ?? '' ?>
</div>

</body>
</html>