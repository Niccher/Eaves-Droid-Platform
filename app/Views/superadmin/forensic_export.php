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
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-file-export mr-2"></i>Forensic Data Export</h5>
                        <p class="mb-1">Export a user's complete dataset for legal/forensic handover. Select categories, date range, and format. Export includes manifest, README, and CSV files per category in a ZIP archive.</p>
                        <ul class="mb-0 small">
                            <li><strong>Data Integrity:</strong> Each CSV includes all columns. Manifest.json provides export metadata and record counts.</li>
                            <li><strong>Chain of Custody:</strong> Export is logged with exporter identity, timestamp, and selected parameters.</li>
                            <li><strong>Legal Compliance:</strong> Only superadmins can export. Each export is logged as a critical action.</li>
                        </ul>
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
                            <form id="exportForm" action="<?= base_url('superadmin/forensic-export/export') ?>" method="post" target="_blank">
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
                                            'sms' => 'SMS',
                                            'calls' => 'Call Logs',
                                            'contacts' => 'Contacts',
                                            'apps' => 'Apps',
                                            'files' => 'Files',
                                            'locations' => 'Locations',
                                            'activities' => 'Activities',
                                            'accounts' => 'Accounts',
                                            'network' => 'Network Info',
                                            'device_context' => 'Device Context',
                                            'bluetooth' => 'Bluetooth',
                                            'sensors' => 'Sensors',
                                            'security_audit' => 'Security Audit',
                                            'notifications' => 'Notifications',
                                            'calendar' => 'Calendar',
                                            'app_usage' => 'App Usage',
                                            'media' => 'Media',
                                            'sim' => 'SIM Configs',
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
                            <h6>Export Contents</h6>
                            <ul class="small">
                                <li>ZIP archive with CSV files per category</li>
                                <li>manifest.json - Export metadata</li>
                                <li>README.txt - Export details</li>
                            </ul>
                            
                            <h6 class="mt-3">Data Categories</h6>
                            <ul class="small">
                                <li>SMS Messages</li>
                                <li>Call Logs</li>
                                <li>Contacts</li>
                                <li>Installed Apps</li>
                                <li>Files</li>
                                <li>Locations & Activities</li>
                                <li>Accounts & Network Info</li>
                                <li>Device Context & Sensors</li>
                                <li>Bluetooth & Security Audit</li>
                                <li>Notifications & Calendar</li>
                                <li>App Usage & Media</li>
                                <li>SIM Configs</li>
                            </ul>

                            <h6 class="mt-3">Export Format</h6>
                            <ul class="small">
                                <li>ZIP archive with CSV files</li>
                                <li>manifest.json with metadata</li>
                                <li>README.txt with export details</li>
                            </ul>

                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Legal Notice:</strong> This export contains sensitive personal data. Handle according to applicable privacy laws (GDPR, CCPA, etc.). Ensure proper chain of custody for legal proceedings.
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
        
        $('#exportBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Generating Export...');
        $('#exportStatus').text('Preparing export... this may take a moment for large datasets.');
    });
});
</script>