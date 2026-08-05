<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h1><i class="fas fa-clock text-danger mr-2"></i>Data Retention & Purge</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/settings') ?>">Settings</a></li>
                        <li class="breadcrumb-item active">Retention & Purge</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content pt-1">
        <div class="container-fluid">
            <!-- Retention/Purge Callout -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-clock mr-2"></i>Data Retention & Purge</h5>
                        <p class="mb-1">Configure data retention policies per category and run GDPR-style purges. Data older than the retention period will be permanently deleted when purge is executed.</p>
                        <ul class="mb-0 small">
                            <li><strong>Retention Period:</strong> Set per-category retention in days. 0 or empty = never purge.</li>
                            <li><strong>Enabled:</strong> Toggle to enable/disable automatic purge for each category.</li>
                            <li><strong>Manual Purge:</strong> Run immediately with current settings. Deletes all records older than retention period.</li>
                            <li><strong>Automated:</strong> Add cron job <code>retention:purge</code> with schedule <code>0 2 * * *</code> (daily at 2 AM) for automated purges.</li>
                            <li><strong>Warning:</strong> Purged data is permanently deleted and cannot be recovered. Ensure backups exist.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-2">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= count($stats) ?></h3>
                            <p>Categories</p>
                        </div>
                        <div class="icon"><i class="fas fa-list"></i></div>
                        <div class="small-box-footer">
                            Configurable categories
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= count(array_filter($stats, fn($s) => $s['enabled'])) ?></h3>
                            <p>Enabled</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                        <div class="small-box-footer">
                            Auto-purge enabled
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= array_sum(array_column($stats, 'total')) ?></h3>
                            <p>Total Records</p>
                        </div>
                        <div class="icon"><i class="fas fa-database"></i></div>
                        <div class="small-box-footer">
                            Across all categories
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= count(array_filter($stats, fn($s) => $s['retention_days'] <= 30 && $s['enabled'])) ?></h3>
                            <p>Short Retention</p>
                        </div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="small-box-footer">
                            ≤ 30 days & enabled
                        </div>
                    </div>
                </div>
            </div>

            <!-- Retention Configuration -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="card">
                        <form action="<?= base_url('admin/settings/retention/purge') ?>" method="post" id="purgeForm">
                            <?= csrf_field() ?>
                            <div class="card-header d-flex align-items-center">
                                <h3 class="card-title mb-0"><i class="fas fa-cogs mr-2"></i>Retention Configuration</h3>
                                <button type="submit" class="btn btn-danger btn-sm ml-auto" onclick="return confirm('This will PERMANENTLY DELETE data older than retention periods. Are you sure?')">
                                    <i class="fas fa-trash-alt mr-1"></i> Run Purge Now
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 200px;">Category</th>
                                                <th class="text-center" style="width: 100px;">Total Records</th>
                                                <th class="text-center" style="width: 150px;">Retention (Days)</th>
                                                <th class="text-center" style="width: 100px;">Auto Purge</th>
                                                <th class="text-center" style="width: 100px;">Purge?</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats as $label => $stat): ?>
                                                <tr>
                                                    <td class="bg-light"><strong><?= $label ?></strong></td>
                                                    <td class="text-center"><?= number_format($stat['total']) ?></td>
                                                    <td class="text-center">
                                                        <input type="number" name="retention_<?= $label ?>_days" class="form-control form-control-sm" value="<?= $stat['retention_days'] ?>" min="0" style="width: 80px;">
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-switch">
                                                            <input type="hidden" name="retention_<?= $label ?>_enabled" value="0">
                                                            <input type="checkbox" class="custom-control-input" id="ret_<?= $label ?>_enabled" name="retention_<?= $label ?>_enabled" value="1" <?= $stat['enabled'] ? 'checked' : '' ?>>
                                                            <label class="custom-control-label" for="ret_<?= $label ?>_enabled"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="ret_<?= $label ?>_purge" name="categories[]" value="<?= $label ?>">
                                                            <label class="custom-control-label" for="ret_<?= $label ?>_purge"></label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(function() {
    $('#purgeForm').on('submit', function(e) {
        if (!confirm('This will PERMANENTLY DELETE data older than retention periods. This action cannot be undone. Are you sure?')) {
            return false;
        }
    });
});
</script>