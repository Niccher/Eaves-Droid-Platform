<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Data Usage Report</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/reports') ?>">Reports</a></li>
                        <li class="breadcrumb-item active">Data Usage</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Data Usage Report</h5>
                        <p class="mb-0 small text-muted">Aggregated statistics on data volumes — uploaded file sizes, SMS and call counts, storage consumption per user, and platform-wide growth trends.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Usage by User</h3>
                    <div class="card-tools">
                        <span class="badge badge-primary"><?= number_format($grand_total) ?> total records</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="dataUsageTable">
                            <thead>
                                <tr>
                                    <th style="width: 180px;">User</th>
                                    <th style="width: 100px;" class="text-center">Total</th>
                                    <th>Records by Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($user_data)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No data found.</td></tr>
                                <?php else: ?>
                                <?php foreach ($user_data as $ud): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-user-circle fa-lg text-primary mr-1"></i>
                                        <strong><?= htmlspecialchars($ud['username']) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-dark badge-lg" style="font-size: 1rem;"><?= number_format($ud['total']) ?></span>
                                    </td>
                                    <td>
                                        <?php foreach ($data_tables as $label => $info): ?>
                                        <?php $count = $ud['categories'][$label] ?? 0; ?>
                                        <?php if ($count > 0): ?>
                                        <span class="d-inline-block mr-2 mb-1" style="white-space: nowrap;">
                                            <i class="fas <?= $info['icon'] ?> text-muted mr-1" style="width: 16px;"></i>
                                            <?= $label ?>: <strong><?= number_format($count) ?></strong>
                                        </span>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                        <?php if ($ud['total'] === 0): ?>
                                        <span class="text-muted">No records</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($user_data)): ?>
                            <tfoot>
                                <tr class="font-weight-bold bg-light">
                                    <td><i class="fas fa-users text-primary mr-1"></i> All Users</td>
                                    <td class="text-center">
                                        <span class="badge badge-primary badge-lg" style="font-size: 1rem;"><?= number_format($grand_total) ?></span>
                                    </td>
                                    <td>
                                        <?php foreach ($data_tables as $label => $info): ?>
                                        <?php $count = $all_totals[$label] ?? 0; ?>
                                        <?php if ($count > 0): ?>
                                        <span class="d-inline-block mr-2 mb-1" style="white-space: nowrap;">
                                            <i class="fas <?= $info['icon'] ?> text-primary mr-1" style="width: 16px;"></i>
                                            <?= $label ?>: <strong><?= number_format($count) ?></strong>
                                        </span>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#dataUsageTable').DataTable({
        order: [[1, 'desc']],
        paging: false,
        searching: false,
        responsive: true,
    });
});
</script>