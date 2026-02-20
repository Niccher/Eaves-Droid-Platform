<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-user-shield text-danger mr-2"></i> Privacy & Permission Audit</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('analysis') ?>">Analysis</a></li>
                        <li class="breadcrumb-item active">Privacy Audit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Risk Highlights -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h3 class="card-title">Security & Privacy Posture</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center border-right">
                                    <?php 
                                    $totalRisk = count($audit);
                                    $highRisk = count(array_filter($audit, fn($a) => $a['score'] >= 7));
                                    ?>
                                    <h1 class="display-4 text-danger font-weight-bold"><?= $highRisk ?></h1>
                                    <p class="text-muted">CRITICAL RISK APPS</p>
                                </div>
                                <div class="col-md-8">
                                    <h5>Risk Factors Detected:</h5>
                                    <ul>
                                        <li><b>Data Exfiltration</b>: Apps with SMS access + Internet access.</li>
                                        <li><b>Privacy Intrusion</b>: Apps with Microphone/Camera access in background.</li>
                                        <li><b>Movement Tracking</b>: Apps with Fine Location access.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Table -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">Detailed App Risk Audit</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-valign-middle">
                                    <thead>
                                        <tr>
                                            <th>Application</th>
                                            <th>Risk Level</th>
                                            <th>Sensitivity Flags</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($audit as $app): ?>
                                        <tr>
                                            <td>
                                                <b><?= $app['name'] ?></b><br>
                                                <small class="text-muted"><?= $app['package'] ?></small>
                                            </td>
                                            <td>
                                                <?php if ($app['score'] >= 7): ?>
                                                    <span class="badge badge-danger p-2"><i class="fas fa-exclamation-triangle mr-1"></i> Critical</span>
                                                <?php elseif ($app['score'] >= 4): ?>
                                                    <span class="badge badge-warning p-2"><i class="fas fa-exclamation mr-1"></i> Warning</span>
                                                <?php else: ?>
                                                    <span class="badge badge-info p-2">Elevated</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php foreach ($app['risks'] as $risk): ?>
                                                    <span class="badge badge-light border"><?= $risk ?></span>
                                                <?php endforeach; ?>
                                            </td>
                                            <td>
                                                <span class="text-muted">Score: <?= $app['score'] ?>/10</span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($audit)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center p-4">
                                                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                                <p>No high-risk apps detected on current device scans.</p>
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
