<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Generate Custom Report</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/reports') ?>">Reports</a></li>
                        <li class="breadcrumb-item active">Generate</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Report Criteria</h3></div>
                        <form method="post" action="<?= base_url('admin/reports/generate') ?>">
                            <?= csrf_field() ?>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Date From</label>
                                    <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                                </div>
                                <div class="form-group">
                                    <label>Date To</label>
                                    <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                                </div>
                                <div class="form-group">
                                    <label>Data Types</label>
                                    <?php
                                    $types = ['sms' => 'SMS', 'calls' => 'Call Logs', 'contacts' => 'Contacts',
                                              'apps' => 'Apps', 'locations' => 'Locations', 'activities' => 'Activities', 'uploads' => 'Uploads'];
                                    foreach ($types as $val => $label):
                                    ?>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="dt_<?= $val ?>" name="data_types[]" value="<?= $val ?>" <?= in_array($val, $selected_types) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="dt_<?= $val ?>"><?= $label ?></label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Generate Report</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-7">
                    <?php if ($results !== null): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Results</h3>
                            <div class="card-tools">
                                <form method="post" action="<?= base_url('admin/reports/export') ?>" style="display:inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="date_from" value="<?= $date_from ?>">
                                    <input type="hidden" name="date_to" value="<?= $date_to ?>">
                                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-download mr-1"></i> Export CSV</button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <thead><tr><th>Data Type</th><th>Records</th></tr></thead>
                                <tbody>
                                    <?php if (empty($results)): ?>
                                    <tr><td colspan="2" class="text-muted text-center">No data found for the selected criteria.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($results as $r): ?>
                                    <tr><td><?= $r['label'] ?></td><td><?= number_format($r['count']) ?></td></tr>
                                    <?php endforeach; ?>
                                    <tr class="font-weight-bold"><td>Total</td><td><?= number_format($grand_total) ?></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
