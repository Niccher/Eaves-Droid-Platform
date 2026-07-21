<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-calendar-check text-success mr-2"></i> Subscription Tracker</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-outline-info btn-sm" href="<?= base_url('analysis') ?>"><i class="fas fa-arrow-left mr-1"></i> Back to Analysis</a>
                </div>
            </div>
        </div>
    </section>

<?php if (isset($ml_insight) && !empty($ml_insight['insights'])): ?>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-brain mr-2"></i> PHP-ML Intelligence</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <span class="badge badge-info p-2"><?= $ml_insight['algorithm'] ?></span>
                                <p class="text-muted mt-2 mb-0"><small><?= $ml_insight['data_source'] ?></small></p>
                            </div>
                            <div class="col-md-8">
                                <p><?= $ml_insight['description'] ?></p>
                                <ul class="mb-0">
                                    <?php foreach ($ml_insight['insights'] as $insight): ?>
                                    <li><?= $insight ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

    <section class="content">
        <div class="container-fluid">
            <!-- Financial Forecast -->
            <div class="row">
                <div class="col-md-4">
                    <div class="small-box bg-success shadow-sm">
                        <div class="inner">
                            <?php 
                            $totalMonthly = 0;
                            foreach ($forecast as $f) $totalMonthly += $f['amount'];
                            ?>
                            <h3>KES <?= number_format($totalMonthly, 2) ?></h3>
                            <p>Projected Monthly Commitment</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Intelligence Logic</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">Detected by scanning incoming SMS for recurring payment patterns, utility tokens, and service renewal keywords (e.g., Zuku, KPLC, Netflix, Insurance).</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscriptions List -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title">Active Recurring Commitments</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-striped table-valign-middle">
                                    <thead>
                                        <tr>
                                            <th>Service Provider</th>
                                            <th>Typical Amount</th>
                                            <th>Frequency (Est)</th>
                                            <th>Last Payment</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($forecast as $sender => $data): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle p-2 mr-3" style="width: 40px; text-align: center;">
                                                        <i class="fas fa-building text-muted"></i>
                                                    </div>
                                                    <b><?= $sender ?></b>
                                                </div>
                                            </td>
                                            <td>
                                                <b>KES <?= number_format($data['amount'], 2) ?></b>
                                            </td>
                                            <td>
                                                Monthly
                                            </td>
                                            <td>
                                                <?= date('M j, Y', $data['last_date'] / 1000) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">Active</span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($forecast)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center p-4">
                                                <i class="fas fa-receipt text-muted fa-2x mb-2"></i>
                                                <p>No recurring subscriptions detected in current SMS history.</p>
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

