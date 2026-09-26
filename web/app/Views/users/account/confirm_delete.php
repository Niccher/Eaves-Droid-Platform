<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Confirm Deletion</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('account/home'); ?>">Account</a></li>
                        <li class="breadcrumb-item active">Confirm Deletion</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card card-danger">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Warning: Data Deletion
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger">
                                <h5><i class="icon fas fa-ban"></i> Warning!</h5>
                                You are about to permanently delete
                                <?php
                                $typeNames = [
                                    'apps' => 'all your applications',
                                    'calls' => 'all your call logs',
                                    'call_logs' => 'all your call logs',
                                    'contacts' => 'all your contacts',
                                    'sms' => 'all your SMS messages',
                                    'files' => 'all your file metadata',
                                    'locations' => 'all your location and activity history',
                                    'advanced' => 'all your advanced extracted data',
                                    'all' => 'ALL your data (apps, calls, contacts, SMS, files, locations, advanced data)'
                                ];
                                echo '<strong>' . ($typeNames[$delete_type] ?? 'selected data') . '</strong>';
                                ?>.
                            </div>

                            <div class="callout callout-danger">
                                <h5><i class="fas fa-list-ul mr-2"></i> Items to be Deleted:</h5>
                                <ul class="mb-0">
                                    <?php if ($delete_type === 'apps' || $delete_type === 'all'): ?>
                                        <li>Applications: <strong><?php echo $total_apps; ?></strong> records</li>
                                    <?php endif; ?>
                                    <?php if ($delete_type === 'calls' || $delete_type === 'call_logs' || $delete_type === 'all'): ?>
                                        <li>Call Logs: <strong><?php echo $total_calls; ?></strong> records</li>
                                    <?php endif; ?>
                                    <?php if ($delete_type === 'contacts' || $delete_type === 'all'): ?>
                                        <li>Contacts: <strong><?php echo $total_contacts; ?></strong> records</li>
                                    <?php endif; ?>
                                    <?php if ($delete_type === 'sms' || $delete_type === 'all'): ?>
                                        <li>SMS Messages: <strong><?php echo $total_sms; ?></strong> records</li>
                                    <?php endif; ?>
                                    <?php if ($delete_type === 'files' || $delete_type === 'all'): ?>
                                        <li>File Metadata: <strong><?php echo $total_files; ?></strong> records</li>
                                    <?php endif; ?>
                                    <?php if ($delete_type === 'locations' || $delete_type === 'all'): ?>
                                        <li>Location Points: <strong><?php echo $total_locations; ?></strong> records</li>
                                        <li>Activities: <strong><?php echo $total_activities; ?></strong> records</li>
                                    <?php endif; ?>
                                    <?php if ($delete_type === 'advanced' || $delete_type === 'all'): ?>
                                        <li>Device Context: <strong><?php echo $total_device; ?></strong> records</li>
                                        <li>Network Information: <strong><?php echo $total_network; ?></strong> records</li>
                                        <li>User Accounts: <strong><?php echo $total_accounts; ?></strong> records</li>
                                        <li>Calendar Events: <strong><?php echo $total_calendar; ?></strong> records</li>
                                        <li>App Usage Stats: <strong><?php echo $total_app_usage; ?></strong> records</li>
                                        <li>Notifications: <strong><?php echo $total_notifications; ?></strong> records</li>
                                        <li>Bluetooth Devices: <strong><?php echo $total_bluetooth; ?></strong> records</li>
                                        <li>Sensor Profiles: <strong><?php echo $total_sensors; ?></strong> records</li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <div class="callout callout-warning">
                                <h5><i class="fas fa-info-circle mr-2"></i> Important Information</h5>
                                <ul class="mb-0">
                                    <li>This action <strong>cannot be undone</strong></li>
                                    <li>Data will be permanently wiped from our secure servers</li>
                                    <li>Associated logs and metadata will also be removed</li>
                                </ul>
                            </div>

                            <p>Are you absolutely sure you want to proceed?</p>

                            <form action="<?php echo base_url('account/deleteData/' . $delete_type); ?>" method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                <div class="form-group">
                                    <label for="confirmation">
                                        Type "DELETE" to confirm:
                                    </label>
                                    <input type="text" class="form-control" name="confirmation"
                                           id="confirmation" placeholder="Type DELETE here" required
                                           oninput="checkConfirmation(this)">
                                    <small class="form-text text-muted">
                                        This is case-sensitive
                                    </small>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <a href="<?php echo base_url('account/home'); ?>"
                                           class="btn btn-secondary btn-block">
                                            <i class="fas fa-times mr-2"></i> Cancel
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-danger btn-block"
                                                id="deleteButton" disabled>
                                            <i class="fas fa-trash mr-2"></i> Permanently Delete
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    function checkConfirmation(input) {
        const deleteButton = document.getElementById('deleteButton');
        if (input.value === 'DELETE') {
            deleteButton.disabled = false;
        } else {
            deleteButton.disabled = true;
        }
    }
</script>