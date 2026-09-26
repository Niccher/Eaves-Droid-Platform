<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-cogs mr-2 text-primary"></i>Dynamic Definitions Manager</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/plans') ?>">Plans</a></li>
                        <li class="breadcrumb-item active">Definitions</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-check"></i> Success!</h5>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Global Permissions & Tiers</h5>
                        <p class="mb-0 small text-muted">Dynamically reassign any FCM command, ML algorithm, hardware sensor, or software profile feature to a different plan tier. Updates take effect immediately across all active subscriptions.</p>
                    </div>
                </div>
            </div>

            <form action="<?= base_url('superadmin/plans/updateDefinitions') ?>" method="POST">
                <?= csrf_field() ?>

                <!-- FCM Commands -->
                <div class="card card-outline card-success shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-paper-plane mr-2 text-success"></i>FCM Commands</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <?php foreach ($features as $f): ?>
                                <?php if ($f['category_type'] === 'fcm'): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center justify-content-between p-3 border rounded bg-light h-100 shadow-xs">
                                            <div class="d-flex align-items-center mr-2">
                                                <div class="mr-3 text-success text-center" style="width: 28px;">
                                                    <i class="<?= esc($f['icon']) ?> fa-lg"></i>
                                                </div>
                                                <div>
                                                    <span class="font-weight-bold d-block"><?= esc($f['label']) ?></span>
                                                    <small class="text-muted d-block"><?= esc($f['description']) ?></small>
                                                </div>
                                            </div>
                                            <div style="min-width: 130px;">
                                                <select name="tiers[<?= esc($f['slug']) ?>]" class="form-control form-control-sm border-success">
                                                    <option value="free" <?= $f['required_tier'] === 'free' ? 'selected' : '' ?>>Core (Free)</option>
                                                    <option value="gold" <?= $f['required_tier'] === 'gold' ? 'selected' : '' ?>>Advanced (Gold)</option>
                                                    <option value="platinum" <?= $f['required_tier'] === 'platinum' ? 'selected' : '' ?>>Deep (Platinum)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- ML Algorithms -->
                <div class="card card-outline card-purple shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title text-purple"><i class="fas fa-brain mr-2 text-purple"></i>ML Algorithms</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <?php foreach ($features as $f): ?>
                                <?php if ($f['category_type'] === 'ml'): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center justify-content-between p-3 border rounded bg-light h-100 shadow-xs">
                                            <div class="d-flex align-items-center mr-2">
                                                <div class="mr-3 text-purple text-center" style="width: 28px;">
                                                    <i class="<?= esc($f['icon']) ?> fa-lg"></i>
                                                </div>
                                                <div>
                                                    <span class="font-weight-bold d-block"><?= esc($f['label']) ?></span>
                                                    <small class="text-muted d-block"><?= esc($f['description']) ?></small>
                                                </div>
                                            </div>
                                            <div style="min-width: 130px;">
                                                <select name="tiers[<?= esc($f['slug']) ?>]" class="form-control form-control-sm border-purple">
                                                    <option value="free" <?= $f['required_tier'] === 'free' ? 'selected' : '' ?>>Core (Free)</option>
                                                    <option value="gold" <?= $f['required_tier'] === 'gold' ? 'selected' : '' ?>>Advanced (Gold)</option>
                                                    <option value="platinum" <?= $f['required_tier'] === 'platinum' ? 'selected' : '' ?>>Deep (Platinum)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Hardware Sensors -->
                    <div class="col-lg-6">
                        <div class="card card-outline card-indigo shadow-sm mb-4 h-100">
                            <div class="card-header">
                                <h3 class="card-title text-indigo"><i class="fas fa-microchip mr-2 text-indigo"></i>Hardware Sensors & Details</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="card-body p-3" style="max-height: 550px; overflow-y: auto;">
                                <div class="row">
                                    <?php foreach ($features as $f): ?>
                                        <?php if ($f['category_type'] === 'hardware'): ?>
                                            <div class="col-12 mb-3">
                                                <div class="d-flex align-items-center justify-content-between p-3 border rounded bg-light shadow-xs">
                                                    <div class="d-flex align-items-center mr-2">
                                                        <div class="mr-3 text-indigo text-center" style="width: 24px;">
                                                            <i class="<?= esc($f['icon']) ?> fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <span class="font-weight-bold d-block"><?= esc($f['label']) ?></span>
                                                            <small class="text-muted d-block"><?= esc($f['description']) ?></small>
                                                        </div>
                                                    </div>
                                                    <div style="min-width: 120px;">
                                                        <select name="tiers[<?= esc($f['slug']) ?>]" class="form-control form-control-sm border-indigo">
                                                            <option value="free" <?= $f['required_tier'] === 'free' ? 'selected' : '' ?>>Basic (Free)</option>
                                                            <option value="gold" <?= $f['required_tier'] === 'gold' ? 'selected' : '' ?>>Advanced (Gold)</option>
                                                            <option value="platinum" <?= $f['required_tier'] === 'platinum' ? 'selected' : '' ?>>All (Platinum)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Software Capabilities -->
                    <div class="col-lg-6">
                        <div class="card card-outline card-navy shadow-sm mb-4 h-100">
                            <div class="card-header">
                                <h3 class="card-title text-navy"><i class="fas fa-laptop-code mr-2 text-navy"></i>Software Capabilities</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="card-body p-3" style="max-height: 550px; overflow-y: auto;">
                                <div class="row">
                                    <?php foreach ($features as $f): ?>
                                        <?php if ($f['category_type'] === 'software'): ?>
                                            <div class="col-12 mb-3">
                                                <div class="d-flex align-items-center justify-content-between p-3 border rounded bg-light shadow-xs">
                                                    <div class="d-flex align-items-center mr-2">
                                                        <div class="mr-3 text-navy text-center" style="width: 24px;">
                                                            <i class="<?= esc($f['icon']) ?> fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <span class="font-weight-bold d-block"><?= esc($f['label']) ?></span>
                                                            <small class="text-muted d-block"><?= esc($f['description']) ?></small>
                                                        </div>
                                                    </div>
                                                    <div style="min-width: 120px;">
                                                        <select name="tiers[<?= esc($f['slug']) ?>]" class="form-control form-control-sm border-navy">
                                                            <option value="free" <?= $f['required_tier'] === 'free' ? 'selected' : '' ?>>Basic (Free)</option>
                                                            <option value="gold" <?= $f['required_tier'] === 'gold' ? 'selected' : '' ?>>Advanced (Gold)</option>
                                                            <option value="platinum" <?= $f['required_tier'] === 'platinum' ? 'selected' : '' ?>>All (Platinum)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mt-4 mb-4">
                    <div class="card-footer bg-white d-flex justify-content-between">
                        <a href="<?= base_url('superadmin/plans') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Plans
                        </a>
                        <button type="submit" class="btn btn-success ml-auto">
                            <i class="fas fa-save mr-1"></i> Save Global Tier Definitions
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<style>
.card-outline.card-purple { border-top: 3px solid #6f42c1; }
.card-outline.card-indigo { border-top: 3px solid #6610f2; }
.card-outline.card-navy { border-top: 3px solid #001f3f; }
.text-purple { color: #6f42c1 !important; }
.text-indigo { color: #6610f2 !important; }
.text-navy { color: #001f3f !important; }
.shadow-xs {
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
</style>
