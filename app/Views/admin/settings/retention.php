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
                        <?= form_open('admin/settings/retention/save', ['id' => 'configForm', 'method' => 'post']) ?>
                        <?= form_open('admin/settings/retention/purge', ['id' => 'purgeForm', 'method' => 'post']) ?>
                            <div class="card-header d-flex align-items-center flex-wrap">
                                <h3 class="card-title mb-0"><i class="fas fa-cogs mr-2"></i>Retention Configuration</h3>
                                <div class="ml-auto d-flex">
                                    <button type="submit" form="configForm" class="btn btn-primary btn-sm mr-2" title="Save the retention days and auto-purge toggles for all categories">
                                        <i class="fas fa-save mr-1"></i> Save Configuration
                                    </button>
                                    <button type="submit" form="purgeForm" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt mr-1"></i> Run Purge Now
                                    </button>
                                </div>
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
                                                        <input type="number" form="configForm" name="retention_<?= $label ?>_days" class="form-control form-control-sm" value="<?= $stat['retention_days'] ?>" min="0" style="width: 80px;">
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-switch">
                                                            <input type="hidden" form="configForm" name="retention_<?= $label ?>_enabled" value="0">
                                                            <input type="checkbox" form="configForm" class="custom-control-input" id="ret_<?= $label ?>_enabled" name="retention_<?= $label ?>_enabled" value="1" <?= $stat['enabled'] ? 'checked' : '' ?>>
                                                            <label class="custom-control-label" for="ret_<?= $label ?>_enabled"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" form="purgeForm" class="custom-control-input" id="ret_<?= $label ?>_purge" name="categories[]" value="<?= $label ?>">
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
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($can_reset)): ?>
    <section class="content pt-1">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <div class="card card-outline card-danger">
                        <div class="card-header bg-danger">
                            <h3 class="card-title mb-0 text-white">
                                <i class="fas fa-bomb mr-2"></i>Danger Zone — Factory Reset
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>Factory reset returns the platform to a fresh install state.</strong> This will:</p>
                            <ul class="mb-3">
                                <li>Keep <strong>all user accounts</strong> intact — users retain their logins, but their accounts become <strong>completely empty</strong>.</li>
                                <li>Permanently delete <strong>all of every user's data</strong>: SMS, calls, contacts, locations, media, activity, app usage, uploads, captured media, exports, reports, backups and billing/subscription records.</li>
                                <li>Reset every data table to defaults and clear the cache/temporary files.</li>
                                <li>Keep system configuration (settings, cron jobs, subscription plans).</li>
                            </ul>
                            <p class="mb-2 text-danger"><strong>This action is IRREVERSIBLE.</strong> It is recommended to create a database backup first.</p>
                            <form method="post" action="<?= base_url('admin/settings/retention/reset') ?>" id="factoryResetForm">
                                <?= csrf_field() ?>
                                <input type="hidden" name="confirm" id="factoryResetConfirm" value="">
                                <button type="button" class="btn btn-danger btn-sm" id="factoryResetBtn">
                                    <i class="fas fa-bomb mr-1"></i> Wipe Everything & Reset
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
$(function() {
    // Proper confirmation popup for the retention purge.
    $('#purgeForm').on('submit', function(e) {
        e.preventDefault();
        var $form = this;
        Swal.fire({
            title: 'Run Purge Now?',
            html: 'This will <strong>PERMANENTLY DELETE</strong> data older than the retention period for the selected categories.<br><br>This action <strong>cannot be undone</strong>.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, purge it',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then(function(result) {
            if (result.isConfirmed) {
                $form.submit();
            }
        });
        return false;
    });

    // Proper confirmation popup for the factory reset.
    $('#factoryResetBtn').on('click', function(e) {
        Swal.fire({
            title: 'Factory Reset',
            html: 'This will <strong>PERMANENTLY DELETE all data</strong> for <strong>every user</strong> — SMS, calls, contacts, locations, media, app usage, uploaded files, reports, backups and billing records.<br><br>' +
                  '<strong>User accounts are retained</strong> (everyone keeps their login), but become completely empty.<br><br>' +
                  'Type <strong>RESET</strong> in the box below to confirm.',
            icon: 'warning',
            input: 'text',
            inputPlaceholder: 'Type RESET to confirm',
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, wipe everything',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            inputValidator: function(value) {
                if (!value || value.toUpperCase() !== 'RESET') {
                    return 'You must type "RESET" to confirm';
                }
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                $('#factoryResetConfirm').val('RESET');
                $('#factoryResetForm')[0].submit();
            }
        });
    });
});
</script>