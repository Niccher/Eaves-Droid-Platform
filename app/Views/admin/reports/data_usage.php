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
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Total Records by Type</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <thead><tr><th>Data Type</th><th>Records</th><th>%</th></tr></thead>
                                <tbody>
                                    <?php foreach ($totals as $t): ?>
                                    <tr>
                                        <td><?= $t['label'] ?></td>
                                        <td><?= number_format($t['count']) ?></td>
                                        <td><?= $grand_total > 0 ? round($t['count'] / $grand_total * 100, 1) : 0 ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot><tr class="font-weight-bold"><td>Total</td><td><?= number_format($grand_total) ?></td><td>100%</td></tr></tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Records per User</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <thead><tr><th>User</th><th>Total Records</th></tr></thead>
                                <tbody>
                                    <?php foreach ($user_data as $ud): ?>
                                    <tr><td><?= htmlspecialchars($ud['username']) ?></td><td><?= number_format($ud['total']) ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
