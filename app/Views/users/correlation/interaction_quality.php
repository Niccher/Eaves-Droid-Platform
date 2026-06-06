<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-chart-line text-info mr-2"></i> Relationship Dynamics & Quality</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Analysis</a></li>
                        <li class="breadcrumb-item active">Dynamics</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">Interdependence Score (Response Balance)</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-valign-middle">
                                    <thead>
                                        <tr>
                                            <th>Contact Address</th>
                                            <th>Initiation Balance</th>
                                            <th>Avg Response Time</th>
                                            <th>Power Dynamic</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($quality as $q): ?>
                                        <tr>
                                            <td>
                                                <b><?= $q['name'] ?></b><br>
                                                <small class="text-muted"><?= $q['address'] ?></small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="mr-2 text-xs">Self</span>
                                                    <div class="progress progress-xs flex-grow-1">
                                                        <div class="progress-bar bg-info" style="width: <?= $q['initiation_sent'] ?>%"></div>
                                                    </div>
                                                    <span class="ml-2 text-xs">Other</span>
                                                </div>
                                            </td>
                                            <td>
                                                <b><?= $q['avg_latency_min'] ?> mins</b>
                                            </td>
                                            <td>
                                                <?php if ($q['initiation_sent'] > 70): ?>
                                                    <span class="badge badge-secondary">High Effort (Self)</span>
                                                <?php elseif ($q['initiation_sent'] < 30): ?>
                                                    <span class="badge badge-secondary">Recipient Focus</span>
                                                <?php else: ?>
                                                    <span class="badge badge-primary">Balanced Interaction</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($quality)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center p-4">
                                                <i class="fas fa-balance-scale-left text-muted fa-2x mb-2"></i>
                                                <p>Insufficient interaction history to measure relationship dynamics.</p>
                                            </td>
                                        </tr>
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
