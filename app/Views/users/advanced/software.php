<?php 
/** @var array $counts */ 
/** @var string $userTier */ 
/** @var array $features */ 

$subModel = new \App\Models\SubscriptionModel();
$limits = $subModel->getPlanLimits(auth()->id());
$featuresArr = $limits['features'] ?? [];
if (is_string($featuresArr)) {
    $featuresArr = json_decode($featuresArr, true) ?: [];
}
$allowedSoftware = $featuresArr['software_profile'] ?? 'basic';

function isLocked($requiredTier, $allowedSoftware) {
    if ($requiredTier === 'free') return false;
    if ($requiredTier === 'gold' && $allowedSoftware === 'basic') return true;
    if ($requiredTier === 'platinum' && $allowedSoftware !== 'all') return true;
    return false;
}

function getFeatureCount($slug, $counts) {
    $map = [
        'running_processes' => 'total_running_processes_detailed',
        'app-usage'         => 'total_app_usage',
        'email'             => 'total_email_accounts',
        'calendar'          => 'total_calendar',
    ];
    $key = $map[$slug] ?? 'total_' . str_replace('-', '_', $slug);
    return $counts[$key] ?? 0;
}
?>

<style>
    .card-locked-wrapper {
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease-in-out;
    }
    .card-locked-wrapper.locked {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    .locked-blur {
        filter: grayscale(0.8) blur(0.5px);
        opacity: 0.6;
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
        background: rgba(255, 255, 255, 0.15);
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-lg-7">
                    <h1 class="h2 mb-0"><i class="fas fa-laptop-code text-secondary mr-2"></i>Software</h1>
                    <p class="text-muted mt-1 mb-0">Installed apps, system services, and configuration data</p>
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
                    <?php $isLock = isLocked($f['required_tier'], $allowedSoftware); ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                        <div class="card card-outline <?php echo esc($f['color_class']); ?> shadow-sm h-100 card-locked-wrapper <?php echo $isLock ? 'locked' : ''; ?>">
                            <?php if ($isLock): ?>
                                <div class="lock-overlay">
                                    <span class="badge <?php echo $f['required_tier'] === 'platinum' ? 'badge-danger' : 'badge-warning'; ?> shadow-sm mb-1 px-3 py-1">
                                        <i class="fas fa-lock mr-1"></i> Unlock <?php echo ucfirst($f['required_tier']); ?>
                                    </span>
                                    <a href="<?php echo base_url('billing'); ?>" class="btn btn-xs <?php echo $f['required_tier'] === 'platinum' ? 'btn-outline-danger' : 'btn-outline-warning'; ?> font-weight-bold px-3">Upgrade Plan</a>
                                </div>
                            <?php endif; ?>
                            <div class="card-body text-center py-4 <?php echo $isLock ? 'locked-blur' : ''; ?>">
                                <div class="rounded-circle <?php echo esc($f['bg_class']); ?> d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                                    <i class="<?php echo esc($f['icon']); ?> fa-2x text-white"></i>
                                </div>
                                <h6 class="card-title mb-1 w-100 font-weight-bold"><?php echo esc($f['label']); ?></h6>
                                <p class="text-muted small mb-2"><?php echo esc($f['description']); ?></p>
                                <span class="badge badge-pill badge-primary"><?php echo getFeatureCount($f['slug'], $counts); ?> records</span>
                                <?php if (!$isLock): ?>
                                    <a href="<?php echo base_url('advanced/software/' . $f['slug']); ?>" class="stretched-link"></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>