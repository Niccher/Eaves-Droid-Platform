<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header pt-3 pb-2">
            <div class="container-fluid">
                <div class="row align-items-center mb-2">
                    <div class="col-sm-6">
                        <h1 class="h3 mb-0 text-dark font-weight-bold">
                            <i class="fas fa-users text-warning mr-2"></i> Social Graph &amp; Communication Analysis
                        </h1>
                        <p class="text-muted mb-0 small">Relationship mapping based on call logs, SMS interaction frequencies, and reply latency.</p>
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
                    <div class="rounded-circle p-3 mr-3 shadow" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                        <i class="fas fa-project-diagram fa-lg text-white"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold text-white"><?= $_engLabel ?> Social Relationship Network Synthesis</h4>
                        <small class="text-light opacity-75">Graph network analysis, call duration ratios, &amp; response latency metrics</small>
                    </div>
                </div>
                <div>
                    <span class="badge badge-pill badge-warning px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                        <i class="fas fa-microchip mr-1"></i> Algorithm: <?= esc($ml_insight['algorithm'] ?? 'Graph Edge Centrality') ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-5 mb-3 mb-md-0 border-right-md border-warning pr-md-4">
                        <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-database mr-2"></i> What Is Happening</h6>
                            <p class="mb-0 text-light opacity-90" style="font-size: 0.9rem; line-height: 1.5;">
                                <?= esc($ml_insight['description'] ?? 'Constructs an interactive directional node graph linking phone numbers by total call duration, SMS exchange volume, and average reply delay to surface close associates.') ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-7 pl-md-4">
                        <h6 class="text-warning font-weight-bold mb-2"><i class="fas fa-lightbulb mr-2"></i> Key Social Network Findings</h6>
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

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                
                <!-- Enriched Social Intelligence Metrics -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card card-outline card-warning shadow-sm mb-2">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="text-warning font-weight-bold mb-0"><?= $contact_response['in_out_duration_ratio'] ?? '1.5' ?>x</h4>
                                    <span class="text-xs text-muted font-weight-bold">In / Out Call Ratio</span>
                                </div>
                                <div class="rounded-circle bg-warning text-white p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                                    <i class="fas fa-exchange-alt fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-outline card-info shadow-sm mb-2">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="text-info font-weight-bold mb-0"><?= $contact_response['avg_sms_reply_delay_mins'] ?? '4.5' ?> mins</h4>
                                    <span class="text-xs text-muted font-weight-bold">Avg SMS Reply Latency</span>
                                </div>
                                <div class="rounded-circle bg-info text-white p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                                    <i class="fas fa-stopwatch fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-outline card-success shadow-sm mb-2">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="text-success font-weight-bold mb-0"><?= $relationship_age['relationship_age_days'] ?? '30' ?> Days</h4>
                                    <span class="text-xs text-muted font-weight-bold">Communication Horizon</span>
                                </div>
                                <div class="rounded-circle bg-success text-white p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                                    <i class="fas fa-calendar-alt fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($graph_orphans) || !empty($graph_outliers)): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-outline card-danger shadow-sm mb-4">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-user-times text-danger mr-2"></i> Contact Graph Security Alerts (Python Outliers)</h3>
                                <div class="card-tools">
                                    <span class="badge badge-danger p-2"><?= count($graph_orphans) + count($graph_outliers) ?> anomaly/anomalies detected</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php if (!empty($graph_orphans)): ?>
                                    <div class="col-md-6 border-right">
                                        <h6 class="text-danger font-weight-bold"><i class="fas fa-user-slash mr-2"></i> Orphaned Contacts (<?= count($graph_orphans) ?>)</h6>
                                        <p class="text-xs text-muted">These contacts exist in the address book but have zero calls or SMS interaction history. Possible dormant, legacy, or synthetic records.</p>
                                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Phone Number</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($graph_orphans as $c): ?>
                                                    <tr>
                                                        <td><b><?= esc($c['name']) ?></b></td>
                                                        <td><code class="text-muted"><?= esc($c['phone']) ?></code></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($graph_outliers)): ?>
                                    <div class="col-md-6">
                                        <h6 class="text-warning font-weight-bold"><i class="fas fa-project-diagram mr-2"></i> Social Community Outliers (<?= count($graph_outliers) ?>)</h6>
                                        <p class="text-xs text-muted">Contacts who communicate frequently but do not belong to any detected social community (family, work, friends).</p>
                                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Phone</th>
                                                        <th>Interaction Weight</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($graph_outliers as $c): ?>
                                                    <tr>
                                                        <td><b><?= esc($c['name']) ?></b></td>
                                                        <td><code class="text-muted"><?= esc($c['phone']) ?></code></td>
                                                        <td><span class="badge badge-warning"><?= number_format($c['score'], 2) ?></span></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-12">

                        <div class="card card-outline card-warning">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-project-diagram text-warning mr-2"></i> Top Connections Graph</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="socialChart" style="min-height: 400px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Side-by-side Top 10 Calls and Top 10 SMS -->
                <?php 
                $callContacts = $social_graph;
                usort($callContacts, fn($a, $b) => ($b['calls'] ?? 0) <=> ($a['calls'] ?? 0));
                $topCalls = array_slice($callContacts, 0, 10);

                $smsContacts = $social_graph;
                usort($smsContacts, fn($a, $b) => ($b['sms'] ?? 0) <=> ($a['sms'] ?? 0));
                $topSms = array_slice($smsContacts, 0, 10);
                ?>
                <div class="row mt-4">
                    <!-- Top 10 Call Contacts -->
                    <div class="col-md-6">
                        <div class="card card-outline card-success shadow-sm h-100">
                            <div class="card-header border-0 bg-transparent">
                                <h3 class="card-title font-weight-bold">
                                    <i class="fas fa-phone-alt text-success mr-2"></i> Top 10 Call Contacts
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped table-valign-middle mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Contact / Number</th>
                                                <th>Total Calls</th>
                                                <th>Influence Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topCalls as $contact): ?>
                                            <tr>
                                                <td>
                                                    <div><b><?= esc($contact['name'] !== 'Unknown' ? $contact['name'] : 'Unknown') ?></b></div>
                                                    <small class="text-muted"><i class="fas fa-phone text-xs mr-1"></i><?= esc($contact['number']) ?></small>
                                                </td>
                                                <td><span class="badge badge-success px-2 py-1"><i class="fas fa-phone-volume mr-1"></i><?= number_format($contact['calls']) ?> calls</span></td>
                                                <td><span class="badge badge-warning"><?= $contact['score'] ?></span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top 10 SMS Contacts -->
                    <div class="col-md-6">
                        <div class="card card-outline card-info shadow-sm h-100">
                            <div class="card-header border-0 bg-transparent">
                                <h3 class="card-title font-weight-bold">
                                    <i class="fas fa-comment-alt text-info mr-2"></i> Top 10 SMS Contacts
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped table-valign-middle mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Contact / Number</th>
                                                <th>Total SMS</th>
                                                <th>Influence Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topSms as $contact): ?>
                                            <tr>
                                                <td>
                                                    <div><b><?= esc($contact['name'] !== 'Unknown' ? $contact['name'] : 'Unknown') ?></b></div>
                                                    <small class="text-muted"><i class="fas fa-envelope text-xs mr-1"></i><?= esc($contact['number']) ?></small>
                                                </td>
                                                <td><span class="badge badge-info px-2 py-1"><i class="fas fa-sms mr-1"></i><?= number_format($contact['sms']) ?> msgs</span></td>
                                                <td><span class="badge badge-warning"><?= $contact['score'] ?></span></td>
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
    </div>

<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
    $(function () {
        var ctx = document.getElementById('socialChart').getContext('2d');
        var socialData = <?= json_encode(array_slice($social_graph, 0, 20)) ?>;

        var datasets = socialData.map(function(contact) {
            return {
                label: contact.name !== 'Unknown' ? contact.name : contact.number,
                data: [{
                    x: contact.sms,
                    y: contact.calls,
                    r: Math.min(Math.max((contact.score / 10), 5), 30) // Scale radius
                }],
                backgroundColor: contact.calls > contact.sms ? 'rgba(40, 167, 69, 0.6)' : 'rgba(23, 162, 184, 0.6)',
                borderColor: contact.calls > contact.sms ? 'rgba(40, 167, 69, 1)' : 'rgba(23, 162, 184, 1)',
            };
        });

        // Bubble chart for Interactions: X=SMS, Y=Calls, Size=Total Score
        new Chart(ctx, {
            type: 'bubble',
            data: {
                datasets: datasets
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Interaction Analysis (X: SMS, Y: Calls, Size: Score)'
                },
                scales: {
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'SMS Count'
                        }
                    }],
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Call Count'
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(t, d) {
                            var rLabel = d.datasets[t.datasetIndex].label;
                            return rLabel + ': Calls=' + t.yLabel + ', SMS=' + t.xLabel;
                        }
                    }
                }
            }
        });
    });
</script>
