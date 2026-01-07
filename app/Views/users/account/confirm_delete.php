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
                                    'call_logs' => 'all your call logs',
                                    'contacts' => 'all your contacts',
                                    'sms' => 'all your SMS messages',
                                    'all' => 'ALL your data (apps, calls, contacts, SMS)'
                                ];
                                echo $typeNames[$delete_type] ?? 'selected data';
                                ?>.
                            </div>

                            <div class="callout callout-danger">
                                <h5><i class="fas fa-info-circle"></i> Important Information</h5>
                                <ul>
                                    <li>This action cannot be undone</li>
                                    <li>All selected data will be permanently removed</li>
                                    <li>You will not be able to recover this data</li>
                                    <li>This action will be logged in your access history</li>
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