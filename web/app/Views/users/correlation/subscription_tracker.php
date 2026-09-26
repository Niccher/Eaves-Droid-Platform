<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header pt-3 pb-2">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-sm-6">
                    <h1 class="h3 mb-0 text-dark font-weight-bold">
                        <i class="fas fa-calendar-check text-indigo mr-2"></i> Subscription Tracker &amp; Bill Forecast
                    </h1>
                    <p class="text-muted mb-0 small">Automated recurring bill detection, renewal forecasting, and monthly commitments.</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-outline-secondary btn-sm shadow-sm" href="<?= base_url('analysis') ?>"><i class="fas fa-arrow-left mr-1"></i> Back to Analysis</a>
                </div>
            </div>
        </div>
    </section>

<?php if (isset($ml_insight) && !empty($ml_insight['insights'])): ?>
<?php $_eng = (new \App\Models\AnomaliesModel())->getDefaultEngine(); $_engLabel = match($_eng){'python'=>'Python Engine','both'=>'Hybrid Engine',default=>'PHP Engine'}; ?>
<section class="content mb-4">
    <div class="container-fluid">
        <div class="card bg-secondary text-white shadow-sm border-0" style="border-radius: 8px;">
            <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0 d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <div class="rounded-circle p-3 mr-3 shadow" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                        <i class="fas fa-calendar-check fa-lg text-white"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold text-white"><?= $_engLabel ?> Subscription &amp; Recurring Bill Forecast</h4>
                        <small class="text-light opacity-75">Automated detection of periodic payees, renewal intervals, &amp; monthly budget impact</small>
                    </div>
                </div>
                <div>
                    <span class="badge badge-pill badge-primary px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                        <i class="fas fa-microchip mr-1"></i> Algorithm: <?= esc($ml_insight['algorithm'] ?? 'Periodicity Detector') ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-5 mb-3 mb-md-0 border-right-md border-indigo pr-md-4">
                        <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <h6 class="text-indigo font-weight-bold mb-2" style="color: #818cf8;"><i class="fas fa-database mr-2"></i> What Is Happening</h6>
                            <p class="mb-0 text-light opacity-90" style="font-size: 0.9rem; line-height: 1.5;">
                                <?= esc($ml_insight['description'] ?? 'Analyzes transaction timestamps and merchant names to identify recurring payment cycles (weekly, monthly, annual) and project upcoming bill due dates.') ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-7 pl-md-4">
                        <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-lightbulb mr-2"></i> Subscription Forecast Insights</h6>
                        <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                            <?php foreach ($ml_insight['insights'] as $insight): ?>
                            <li class="mb-2 d-flex align-items-start">
                                <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                <span><?= $insight ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<?= view('analysis/anomaly_alert_card', ['anomaly_alerts' => $anomaly_alerts ?? []]) ?>

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

            <!-- SubscriptionsController List -->
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
                                            <th>Frequency</th>
                                            <th>Last Payment</th>
                                            <th>Est. Next Renewal</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($forecast as $sender => $data): 
                                            $dispName = !empty($data['name']) ? $data['name'] : $sender;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle p-2 mr-3" style="width: 40px; text-align: center;">
                                                        <i class="fas fa-credit-card text-primary"></i>
                                                    </div>
                                                    <b><?= esc($dispName) ?></b>
                                                </div>
                                            </td>
                                            <td>
                                                <b class="text-danger">KES <?= number_format($data['amount'], 2) ?></b>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">Monthly</span>
                                            </td>
                                            <td>
                                                <?= date('M j, Y', $data['last_date'] / 1000) ?>
                                            </td>
                                            <td>
                                                <span class="text-muted font-weight-bold"><?= date('M j, Y', ($data['next_due'] ?? ($data['last_date'] + 2592000000)) / 1000) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Active</span>
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

