<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-user-secret text-danger mr-2"></i> Behavioral Anomaly Detection</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Analysis</a></li>
                        <li class="breadcrumb-item active">Anomalies</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-danger shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">Detected Deviations</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Risk Event</th>
                                            <th>Severity</th>
                                            <th>Description</th>
                                            <th>Time Detected</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($anomalies as $a): ?>
                                        <tr>
                                            <td><b><?= $a['type'] ?></b></td>
                                            <td>
                                                <span class="badge <?= $a['severity'] == 'High' ? 'badge-danger' : 'badge-warning' ?>">
                                                    <?= $a['severity'] ?>
                                                </span>
                                            </td>
                                            <td><?= $a['desc'] ?></td>
                                            <td><?= date('M j, Y H:i', $a['time'] / 1000) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($anomalies)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center p-4">
                                                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                                <p>No significant behavioral anomalies detected in the recent baseline.</p>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Baseline is calculated using the last 14 days of activity and communication logs.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
