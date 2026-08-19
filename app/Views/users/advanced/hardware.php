<?php 
/** @var array $counts */ 
/** @var string $userTier */ 
/** @var array $features */ 

function isLocked($requiredTier, $userTier) {
    if ($requiredTier === 'free' || empty($requiredTier)) return false;
    if ($requiredTier === 'gold') {
        return !in_array($userTier, ['gold', 'platinum'], true);
    }
    if ($requiredTier === 'platinum') {
        return $userTier !== 'platinum';
    }
    return false;
}

function getFeatureCount($slug, $counts) {
    $map = [
        'sensors' => 'total_sensor_profile',
        'processes' => 'total_processes',
    ];
    $key = $map[$slug] ?? 'total_' . str_replace('-', '_', $slug);
    return $counts[$key] ?? 0;
}
?>

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

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <h1 class="h2 mb-0"><i class="fas fa-microchip text-secondary mr-2"></i>Hardware</h1>
                    <p class="text-muted mt-1 mb-0">Individual hardware telemetry categories — click to view snapshots</p>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo session()->getFlashdata('error'); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <?php foreach ($features as $f): ?>
                    <?php 
                    $isLock = isLocked($f['required_tier'], $userTier); 
                    // Set map colors matching button styles on remote device Fetch panel
                    $color = match($f['color_class']) {
                        'card-info'      => '#17a2b8',
                        'card-warning'   => '#fd7e14',
                        'card-danger'    => '#dc3545',
                        'card-success'   => '#28a745',
                        'card-primary'   => '#007bff',
                        'card-secondary' => '#6c757d',
                        default          => '#343a40'
                    };
                    ?>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                        <div class="btn-remote-cmd p-3 shadow-sm h-100 d-flex flex-column align-items-center justify-content-center <?php echo $isLock ? 'locked' : ''; ?>">
                            <?php if ($isLock): ?>
                                <div class="lock-overlay">
                                    <span class="badge <?php echo $f['required_tier'] === 'platinum' ? 'badge-danger' : 'badge-warning'; ?> shadow-sm mb-2 px-2 py-1" style="font-size: 10px;">
                                        <i class="fas fa-lock mr-1"></i> Unlock <?php echo ucfirst($f['required_tier']); ?>
                                    </span>
                                    <a href="<?php echo base_url('billing'); ?>" class="btn btn-xs <?php echo $f['required_tier'] === 'platinum' ? 'btn-danger text-white' : 'btn-warning text-dark'; ?> font-weight-bold px-2 py-0" style="font-size: 9px; border-radius: 4px;">Upgrade</a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-flex flex-column align-items-center text-center <?php echo $isLock ? 'locked-blur' : ''; ?>">
                                <div class="cmd-icon-wrapper mb-2" style="color: <?php echo $color; ?>;">
                                    <i class="<?php echo esc($f['icon']); ?>"></i>
                                </div>
                                <span class="font-weight-bold text-dark mb-1" style="font-size: 13px;"><?php echo esc($f['label']); ?></span>
                                <small class="text-muted mb-2 d-none d-sm-block font-weight-bold" style="font-size: 10.5px; line-height:1.2;"><?php echo esc($f['description']); ?></small>
                                <span class="badge badge-pill text-white" style="font-size: 9.5px; background-color: <?php echo $color; ?>;"><?php echo getFeatureCount($f['slug'], $counts); ?> snaps</span>
                            </div>
                            
                            <?php if (!$isLock): ?>
                                <a href="<?php echo base_url('advanced/hardware/' . $f['slug']); ?>" class="stretched-link"></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Upcoming & Restricted Tools Card -->
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3" data-toggle="modal" data-target="#upcomingHardwareToolsModal">
                    <div class="btn-remote-cmd p-3 shadow-sm h-100 d-flex flex-column align-items-center justify-content-center" style="cursor: pointer;">
                        <div class="cmd-icon-wrapper mb-2 text-secondary">
                            <i class="fas fa-tools"></i>
                        </div>
                        <span class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Upcoming / Restricted</span>
                        <small class="text-muted mb-2 d-none d-sm-block" style="font-size: 10.5px; line-height:1.2;">Features requiring root access or under development</small>
                        <span class="badge badge-pill badge-secondary" style="font-size: 9.5px;">3 items</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal -->
<div class="modal fade" id="upcomingHardwareToolsModal" tabindex="-1" role="dialog" aria-labelledby="upcomingHardwareToolsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="upcomingHardwareToolsModalLabel">
                    <i class="fas fa-tools text-secondary mr-2"></i>Upcoming & Restricted Hardware Tools
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Due to modern Android security sandboxing, these hardware diagnostic interfaces are restricted and require a <strong>rooted device</strong> to read or collect telemetry.</p>
                <hr>
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0">
                        <h6 class="font-weight-bold mb-1"><i class="fas fa-tasks mr-2 text-dark"></i>Running Processes</h6>
                        <p class="text-muted small mb-0">Monitors currently executing system task tables, PIDs, and basic CPU usage limits.</p>
                    </div>
                    <div class="list-group-item px-0">
                        <h6 class="font-weight-bold mb-1"><i class="fas fa-bolt mr-2 text-warning"></i>Power Rails</h6>
                        <p class="text-muted small mb-0">Tracks electrical regulator statuses, microvolt supply bounds, and current per rail.</p>
                    </div>
                    <div class="list-group-item px-0">
                        <h6 class="font-weight-bold mb-1"><i class="fas fa-thermometer-half mr-2 text-danger"></i>Thermal Throttle</h6>
                        <p class="text-muted small mb-0">Queries active core thermal zones, throttling frequency limits, and scaling governors.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>