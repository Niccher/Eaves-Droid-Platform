<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-export text-danger mr-2"></i>Forensic Export</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/home') ?>">Super Admin</a></li>
                        <li class="breadcrumb-item active">Forensic Export</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Forensic Export Callout -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="callout callout-danger shadow-sm mb-4">
                <h5><i class="fas fa-exclamation-triangle mr-2"></i>DANGER: Highly Sensitive Area</h5>
                <p class="mb-0">Warning: You are extracting unencrypted, raw forensic telemetry data. This action is logged and violates strict privacy policies if mishandled.</p>
            </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user mr-2"></i>Select User</h3>
                        </div>
                        <div class="card-body">
                            <form id="exportForm" action="<?= base_url('superadmin/forensic-export/export') ?>" method="post">
                                <?= csrf_field() ?>
                                
                                <div class="form-group">
                                    <label for="user_id">Target User <span class="text-danger">*</span></label>
                                    <select class="form-control" id="user_id" name="user_id" required>
                                        <option value="">-- Select User --</option>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['email'] ?? 'No email') ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-row mt-3">
                                    <div class="form-group col-md-6">
                                        <label for="date_from">Date From <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="date_from" name="date_from" value="<?= date('Y-m-d', strtotime('-1 year')) ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="date_to">Date To <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="date_to" name="date_to" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Data Categories <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <?php 
                                        $categories = [
                                            'sms'               => 'SMS Messages',
                                            'calls'             => 'Call Logs',
                                            'contacts'          => 'Contacts',
                                            'files'             => 'Device Files',
                                            'locations'         => 'Location & Activity',
                                            'remote_data'       => 'Remote Data (Captured/Downloaded Files)',
                                            'misc_hardware'     => 'Misc Hardware',
                                            'misc_software'     => 'Misc Software',
                                            'apps'              => 'Installed Apps',
                                            'app_usage'         => 'App Usage',
                                            'app_notifications' => 'App Notifications',
                                        ];
                                        foreach ($categories as $key => $label): ?>
                                            <div class="col-md-4 col-sm-6 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="cat_<?= $key ?>" name="categories[]" value="<?= $key ?>">
                                                    <label class="custom-control-label" for="cat_<?= $key ?>"><?= $label ?></label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="format">Export Format</label>
                                    <select class="form-control" id="format" name="format" style="width: 200px;">
                                        <option value="zip">ZIP (CSV files + manifest)</option>
                                    </select>
                                </div>

                                <hr>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-danger btn-lg" id="exportBtn">
                                        <i class="fas fa-file-export mr-2"></i>Generate Export
                                    </button>
                                    <span id="exportStatus" class="ml-3 text-muted"></span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Export Information</h3>
                        </div>
                        <div class="card-body">
                            <h6>How it works</h6>
                            <ul class="small">
                                <li>Exports are processed in the background, so the page does not wait for large datasets.</li>
                                <li>Once ready, a <strong>Download</strong> button appears in the Recent Export Jobs list below.</li>
                                <li>Run <code>php spark export:process</code> (or the <em>export:process</em> cron job) to generate pending exports.</li>
                            </ul>
                            <hr>
                            <h6>Export Contents</h6>
                            <ul class="small">
                                <li>ZIP archive with CSV files per category</li>
                                <li>manifest.json - Export metadata</li>
                                <li>README.txt - Export details</li>
                            </ul>

                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Legal Notice:</strong> This export contains sensitive personal data. Handle according to applicable privacy laws (GDPR, CCPA, etc.). Ensure proper chain of custody for legal proceedings.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Export Jobs -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Recent Export Jobs</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 70px;">Job #</th>
                                            <th>Target User</th>
                                            <th style="width: 150px;">Queued</th>
                                            <th style="width: 130px;">Status</th>
                                            <th style="width: 180px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentJobs)): ?>
                                            <tr><td colspan="5" class="text-center text-muted py-3">No export jobs yet.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($recentJobs as $job):
                                                $jobParams = json_decode($job['params'] ?? '[]', true);
                                                $targetName = $jobParams['username'] ?? ('User #' . $job['target_user_id']);
                                            ?>
                                                <tr data-job-id="<?= (int) $job['id'] ?>">
                                                    <td class="align-middle">#<?= (int) $job['id'] ?></td>
                                                    <td class="align-middle"><?= htmlspecialchars($targetName) ?></td>
                                                    <td class="align-middle"><?= htmlspecialchars($job['queued_at'] ?? '') ?></td>
                                                    <td class="align-middle job-status">
                                                        <?php if ($job['status'] === 'done'): ?>
                                                            <span class="badge badge-success">Done</span>
                                                        <?php elseif ($job['status'] === 'failed'): ?>
                                                            <span class="badge badge-danger">Failed</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning"><i class="fas fa-spinner fa-spin mr-1"></i><?= $job['status'] === 'processing' ? 'Processing' : 'Queued' ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="align-middle job-download">
                                                        <?php if ($job['status'] === 'done'): ?>
                                                            <a class="btn btn-success btn-sm" href="<?= base_url('superadmin/forensic-export/download/' . (int) $job['id']) ?>">
                                                                <i class="fas fa-download mr-1"></i>Download (<?= number_format((int) $job['result_size'] / 1024, 0) ?> KB)
                                                            </a>
                                                        <?php elseif ($job['status'] === 'failed'): ?>
                                                            <span class="text-danger small"><?= htmlspecialchars(mb_substr($job['error_message'] ?? 'Error', 0, 80)) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted small">Preparing...</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(function() {
    $('#exportForm').on('submit', function(e) {
        const userId = $('#user_id').val();
        const categories = $('input[name="categories[]"]:checked').length;

        if (!userId) {
            alert('Please select a user.');
            return false;
        }
        if (categories === 0) {
            alert('Please select at least one data category.');
            return false;
        }

        $('#exportBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Queuing Export...');
        $('#exportStatus').text('Export job queued. It will be processed in the background.');
    });

    const statusUrl = <?= json_encode(base_url('superadmin/forensic-export/jobs/status')) ?>;

    function pollJobs() {
        const ids = $('[data-job-id]').map(function() { return $(this).data('job-id'); }).get();
        if (!ids.length) return;

        $.get(statusUrl, { ids: ids.join(',') }, function(res) {
            if (res && res.jobs) {
                $.each(res.jobs, function(id, j) {
                    const row = $('[data-job-id="' + id + '"]');
                    if (!row.length) return;

                    const statusCell = row.find('.job-status');
                    const dlCell = row.find('.job-download');

                    if (j.status === 'done') {
                        statusCell.html('<span class="badge badge-success">Done</span>');
                        if (j.download_url) {
                            dlCell.html('<a class="btn btn-success btn-sm" href="' + j.download_url + '"><i class="fas fa-download mr-1"></i>Download' + (j.result_size ? ' (' + Math.round(j.result_size / 1024) + ' KB)' : '') + '</a>');
                        }
                    } else if (j.status === 'failed') {
                        statusCell.html('<span class="badge badge-danger">Failed</span>');
                        dlCell.html('<span class="text-danger small">' + (j.error_message || 'Error') + '</span>');
                    } else {
                        statusCell.html('<span class="badge badge-warning"><i class="fas fa-spinner fa-spin mr-1"></i>' + (j.status === 'processing' ? 'Processing' : 'Queued') + '</span>');
                    }
                });
            }
        }).always(function() {
            setTimeout(pollJobs, 5000);
        });
    }

    pollJobs();
});
</script>