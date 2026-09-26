<?php
/** @var string $userTier */
$userTier = $userTier ?? 'free';

if (!function_exists('isAnalysisLocked')) {
    function isAnalysisLocked($requiredTier, $userTier) {
        if ($requiredTier === 'free' || empty($requiredTier)) return false;
        if ($requiredTier === 'gold') {
            return !in_array($userTier, ['gold', 'platinum'], true);
        }
        if ($requiredTier === 'platinum') {
            return $userTier !== 'platinum';
        }
        return false;
    }
}

$analysisTiers = [
    'finance'       => 'platinum',
    'location'      => 'platinum',
    'social'        => 'gold',
    'lifestyle'     => 'free',
    'privacy'       => 'gold',
    'subscriptions' => 'gold',
    'apps'          => 'free',
    'storage'       => 'free',
    'sentiment'     => 'gold',
    'hotspots'      => 'platinum',
    'report'        => 'platinum',
];

$analysisFeatures = [
    [
        'slug' => 'finance',
        'label' => 'Financial Intelligence',
        'icon' => 'fas fa-money-bill-wave',
        'color' => '#28a745',
        'desc' => 'Mobile money transaction history & analysis',
        'stat' => 'Ksh ' . number_format($financial_summary['totalSpending'] ?? 0, 0)
    ],
    [
        'slug' => 'location',
        'label' => 'Location Intelligence',
        'icon' => 'fas fa-map-marked-alt',
        'color' => '#007bff',
        'desc' => 'Stay durations & interactive heatmap paths',
        'stat' => 'View heatmap'
    ],
    [
        'slug' => 'social',
        'label' => 'Relationship Mapping',
        'icon' => 'fas fa-users',
        'color' => '#fd7e14',
        'desc' => 'Communication frequency & social graph',
        'stat' => 'Social map'
    ],
    [
        'slug' => 'lifestyle',
        'label' => 'Lifestyle & Mobility',
        'icon' => 'fas fa-walking',
        'color' => '#6c757d',
        'desc' => 'Activity pattern (walking/still) & screen habits',
        'stat' => 'Lifestyle'
    ],
    [
        'slug' => 'privacy',
        'label' => 'Privacy & Permission Audit',
        'icon' => 'fas fa-user-shield',
        'color' => '#dc3545',
        'desc' => 'Dangerous app permissions & APK risk scoring',
        'stat' => 'Security Audit'
    ],
    [
        'slug' => 'subscriptions',
        'label' => 'Subscription Tracker',
        'icon' => 'fas fa-calendar-check',
        'color' => '#28a745',
        'desc' => 'Detect recurring billing patterns in SMS',
        'stat' => 'Subscription forecast'
    ],
    [
        'slug' => 'apps',
        'label' => 'App Portfolio Profiling',
        'icon' => 'fas fa-th-large',
        'color' => '#007bff',
        'desc' => 'Installed apps distribution & categorization',
        'stat' => 'Apps list'
    ],
    [
        'slug' => 'storage',
        'label' => 'Media & Storage Forensics',
        'icon' => 'fas fa-hdd',
        'color' => '#17a2b8',
        'desc' => 'WhatsApp vs Camera storage distribution',
        'stat' => 'Storage clean'
    ],
    [
        'slug' => 'sentiment',
        'label' => 'Sentiment & Social Tone',
        'icon' => 'fas fa-smile',
        'color' => '#fd7e14',
        'desc' => 'Tone tracking & relationship health score',
        'stat' => 'Tone score'
    ],
    [
        'slug' => 'hotspots',
        'label' => 'Geo-Hotspot Clustering',
        'icon' => 'fas fa-draw-polygon',
        'color' => '#28a745',
        'desc' => 'Name and cluster bases (Home, Work, etc.)',
        'stat' => 'Physical bases'
    ],
    [
        'slug' => 'report',
        'label' => 'Automated Reports',
        'icon' => 'fas fa-file-pdf',
        'color' => '#dc3545',
        'desc' => 'Download forensic intelligence PDF reports',
        'stat' => 'PDF export'
    ]
];
?>
<div class="content-wrapper">
<style>
    .btn-remote-cmd {
        border-radius: 10px;
        transition: all 0.25s ease;
        background: #fff;
        position: relative;
        overflow: hidden;
        border: 1px solid #dee2e6;
        min-height: 165px;
    }
    .btn-remote-cmd:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.08) !important;
    }
    .btn-remote-cmd.locked {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        cursor: default;
    }
    .locked-blur {
        filter: grayscale(0.8) blur(0.6px);
        opacity: 0.5;
        pointer-events: none;
    }
    .lock-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 5;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.4);
        border-radius: 10px;
    }
    .cmd-icon-wrapper {
        font-size: 1.9rem; 
        width: 52px; 
        height: 52px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        background: rgba(0,0,0,0.03); 
        border-radius: 50%;
    }
</style>
    <?php if (session()->getFlashdata('success')): ?>
    <div class="container-fluid mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    </div>
    <?php endif; ?>
        <!-- Content Header -->
        <section class="content-header pt-3 pb-2">
            <div class="container-fluid">
                <div class="row align-items-center mb-2">
                    <div class="col-sm-6">
                        <h1 class="h3 mb-0 text-dark font-weight-bold">
                            <i class="fas fa-microchip text-info mr-2"></i> Cross-Stream Analysis Suite
                        </h1>
                        <p class="text-muted mb-0 small">Cross-vector correlation engine analyzing financial, spatial, social, and telemetry streams.</p>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a class="btn btn-info btn-sm shadow-sm" href="<?= base_url('analysis/refresh-ml') ?>">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh ML Models
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        
        <!-- Device Threat Index Gauge & Analyzer Control -->
        <section class="content mb-4 animate__animated animate__fadeIn">
            <div class="container-fluid">
                <div class="card bg-dark shadow-sm border-0" style="border-radius: 8px; background: linear-gradient(135deg, #1e2225 0%, #121416 100%);">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center border-right" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="letter-spacing: 0.5px; font-size: 11px;">Device Threat Index</h6>
                                <div class="d-inline-block position-relative">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 110px; height: 110px; border: 8px solid <?= $threat_color_border ?>; background-color: #1a1d20; box-shadow: inset 0 0 10px rgba(0,0,0,0.5);">
                                        <h2 class="font-weight-bold mb-0 text-white" style="font-size: 1.8rem;"><?= $threat_index ?>%</h2>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="badge badge-<?= $threat_badge_color ?> px-3 py-2 text-uppercase font-weight-bold shadow-sm" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                        <?= $threat_status ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 pl-md-4 mb-3 mb-md-0">
                                <h5 class="text-white font-weight-bold mb-2"><i class="fas fa-shield-alt mr-2 text-<?= $threat_badge_color ?>"></i> Security Status Overview</h5>
                                <p class="text-light opacity-75 small mb-3" style="line-height: 1.6; font-size: 11.5px;">
                                    The Device Threat Index aggregates anomalies from both the PHP heuristic scanner and the Python ML correlation engine (including network, apps, contacts graph, and device telemetry).
                                </p>
                                <div class="row">
                                    <div class="col-6">
                                        <span class="text-muted small d-block">Active Telemetry Scanners</span>
                                        <h4 class="font-weight-bold text-info mb-0" style="font-size: 1.3rem;"><?= $active_scanners_count ?> <small class="text-muted font-weight-normal" style="font-size: 10px;">Scanners</small></h4>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted small d-block">High Severity Alerts</span>
                                        <h4 class="font-weight-bold text-danger mb-0" style="font-size: 1.3rem;"><?= $high_threat_count ?> <small class="text-muted font-weight-normal" style="font-size: 10px;">Warnings</small></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center border-left" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="letter-spacing: 0.5px; font-size: 11px;">On-Demand ML Scanner</h6>
                                <form action="<?= base_url('analysis/force-scan') ?>" method="post" id="forceScanForm">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-block btn-<?= $threat_badge_color ?> font-weight-bold text-white shadow-sm py-2" id="btnRunScan" style="border-radius: 6px; font-size: 12.5px;">
                                        <i class="fas fa-sync-alt mr-1"></i> RUN FULL SCAN
                                    </button>
                                </form>
                                <small class="text-muted d-block mt-2" style="font-size: 9.5px;">
                                    <i class="fas fa-info-circle mr-1"></i> Scans rate-limited to once per 4 hours.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content mb-4">
            <?php if (isset($ml_insight_finance) && !empty($ml_insight_finance['insights'])): ?>
            <?php $_eng = (new \App\Models\AnomaliesModel())->getDefaultEngine(); $_engLabel = match($_eng){'python'=>'Python Engine','both'=>'Hybrid Engine',default=>'PHP Engine'}; ?>
            <div class="container-fluid">
                <div class="card bg-secondary text-white shadow-sm border-0" style="border-radius: 8px;">
                    <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0 d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <div class="rounded-circle p-3 mr-3 shadow" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                                <i class="fas fa-brain fa-lg text-white"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 font-weight-bold text-white"><?= $_engLabel ?> Cross-Stream Correlation &amp; Synthesis</h4>
                                <small class="text-light opacity-75">Multi-vector analysis of spending, location paths, and social connections</small>
                            </div>
                        </div>
                        <div>
                            <span class="badge badge-pill badge-info px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                                <i class="fas fa-microchip mr-1"></i> <?= esc($ml_insight_finance['algorithm'] ?? 'PHP-ML Correlation') ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-lg-4 col-md-5 mb-3 mb-md-0 border-right-md border-secondary pr-md-4">
                                <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                                    <h6 class="text-info font-weight-bold mb-2"><i class="fas fa-database mr-2"></i> What Is Happening</h6>
                                    <p class="mb-0 text-light opacity-90" style="font-size: 0.9rem; line-height: 1.5;">
                                        <?= esc($ml_insight_finance['description'] ?? 'Correlates mobile wallet transactions, location clusters, and communication frequency to detect behavioral anomalies.') ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-8 col-md-7 pl-md-4">
                                <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-lightbulb mr-2"></i> Key Intelligence Findings</h6>
                                <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                                    <?php foreach ($ml_insight_finance['insights'] as $insight): ?>
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                        <span><?= $insight ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </section>
            <?php endif; ?>

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <?php foreach ($analysisFeatures as $feat): ?>
                            <?php 
                            $reqTier = $analysisTiers[$feat['slug']] ?? 'free';
                            $isLock = isAnalysisLocked($reqTier, $userTier);
                            ?>
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                                <div class="btn-remote-cmd p-3 shadow-sm h-100 d-flex flex-column align-items-center justify-content-center <?php echo $isLock ? 'locked' : ''; ?>">
                                    <?php if ($isLock): ?>
                                        <div class="lock-overlay">
                                            <span class="badge <?php echo $reqTier === 'platinum' ? 'badge-danger' : 'badge-warning'; ?> shadow-sm mb-2 px-2 py-1" style="font-size: 10px;">
                                                <i class="fas fa-lock mr-1"></i> Unlock <?php echo ucfirst($reqTier); ?>
                                            </span>
                                            <a href="<?php echo base_url('billing'); ?>" class="btn btn-xs <?php echo $reqTier === 'platinum' ? 'btn-danger text-white' : 'btn-warning text-dark'; ?> font-weight-bold px-2 py-0" style="font-size: 9px; border-radius: 4px;">Upgrade</a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex flex-column align-items-center text-center <?php echo $isLock ? 'locked-blur' : ''; ?>">
                                        <div class="cmd-icon-wrapper mb-2" style="color: <?php echo $feat['color']; ?>;">
                                            <i class="<?php echo esc($feat['icon']); ?>"></i>
                                        </div>
                                        <span class="font-weight-bold text-dark mb-1" style="font-size: 13px;"><?php echo esc($feat['label']); ?></span>
                                        <small class="text-muted mb-2 d-none d-sm-block font-weight-bold" style="font-size: 10.5px; line-height:1.2;"><?php echo esc($feat['desc']); ?></small>
                                        <span class="badge badge-pill text-white" style="font-size: 9.5px; background-color: <?php echo $feat['color']; ?>;"><?php echo $feat['stat']; ?></span>
                                    </div>
                                    
                                    <?php if (!$isLock): ?>
                                        <a href="<?php echo base_url('analysis/' . $feat['slug']); ?>" <?php echo $feat['slug'] === 'report' ? 'target="_blank"' : ''; ?> class="stretched-link"></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
