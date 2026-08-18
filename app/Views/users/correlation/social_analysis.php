<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-users text-warning mr-2"></i>
                            Social Analysis
                        </h1>
                        <p class="text-muted mt-2 mb-0">Relationship mapping based on interaction frequency</p>
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
                        <?php $_eng = (new \App\Models\AnomaliesModel())->getDefaultEngine(); $_engLabel = match($_eng){'python'=>'Python Engine','both'=>'Hybrid Engine',default=>'PHP Engine'}; ?>
                        <h3 class="card-title"><i class="fas fa-brain mr-2"></i> <?= $_engLabel ?> Intelligence</h3>
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

<?= view('analysis/anomaly_alert_card', ['anomaly_alerts' => $anomaly_alerts ?? []]) ?>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-outline card-warning">
                            <div class="card-header">
                                <h3 class="card-title">Top Connections Graph</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="socialChart" style="min-height: 400px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card card-outline card-warning">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Top 10 Influencers
                                    <small class="text-muted ml-2">Showing <?= count($social_graph) ?> of <?= $total ?></small>
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                <table class="table table-hover table-bordered table-striped table-valign-middle">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>Contact</th>
                                        <th>Number</th>
                                        <th>Calls</th>
                                        <th>SMS</th>
                                        <th>Score</th>
                                        <th>Type</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($social_graph as $contact): ?>
                                    <tr>
                                        <td><b><?= $contact['name'] !== 'Unknown' ? $contact['name'] : 'Unknown' ?></b></td>
                                        <td><small class="text-muted"><?= $contact['number'] ?></small></td>
                                        <td><?= $contact['calls'] ?></td>
                                        <td><?= $contact['sms'] ?></td>
                                        <td><span class="badge badge-warning"><?= $contact['score'] ?></span></td>
                                        <td>
                                            <?php if($contact['calls'] > $contact['sms']): ?>
                                                <i class="fas fa-phone text-success mr-1"></i> Call Dominant
                                            <?php else: ?>
                                                <i class="fas fa-comment text-info mr-1"></i> SMS Dominant
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                            <?php if (isset($pager_links)): ?>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="entry-info">
                                            Showing <?= (($currentPage-1)*$perPage+1) ?> to <?= min($currentPage*$perPage, $total) ?> of <?= $total ?> entries
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="float-right">
                                            <?= $pager_links ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
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
