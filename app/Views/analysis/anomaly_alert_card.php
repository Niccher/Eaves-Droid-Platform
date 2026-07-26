<?php
$engAlertModel = new \App\Models\Mod_Anomalies();
$engAlertEngine = $engAlertModel->getDefaultEngine();
$engAlertLabel = match ($engAlertEngine) {
    'python' => 'Python Engine',
    'both'   => 'Hybrid Engine',
    default  => 'PHP Engine',
};
$engAlertBadge = match ($engAlertEngine) {
    'python' => 'warning',
    'both'   => 'primary',
    default  => 'info',
};
$engAlertIcon = match ($engAlertEngine) {
    'python' => 'fab fa-python',
    'both'   => 'fas fa-project-diagram',
    default  => 'fab fa-php',
};
?>
<?php if (!empty($anomaly_alerts)): ?>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i> Anomaly Alerts <span class="badge badge-<?= $engAlertBadge ?> ml-2"><i class="<?= $engAlertIcon ?> mr-1"></i><?= $engAlertLabel ?></span></h3>
                        <div class="card-tools">
                            <span class="badge badge-warning p-2"><?= count($anomaly_alerts) ?> finding(s)</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="80">Severity</th>
                                        <th width="160">Algorithm</th>
                                        <th>Finding</th>
                                        <th width="140">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($anomaly_alerts as $alert): ?>
                                    <tr>
                                        <td>
                                            <?php
                                            $sevMap = ['High' => 'danger', 'Medium' => 'warning', 'Low' => 'info'];
                                            $sevClass = $sevMap[$alert['severity']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?= $sevClass ?> p-2"><?= $alert['severity'] ?></span>
                                        </td>
                                        <td><small><?= esc($alert['algorithm'] ?? $alert['algorithm_id'] ?? '') ?></small></td>
                                        <td><?= esc($alert['anomaly']) ?></td>
                                        <td><small class="text-muted"><?= date('M j, Y H:i', strtotime($alert['event_timestamp'] ?? $alert['created_at'] ?? 'now')) ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
