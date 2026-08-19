    <footer class="main-footer">
        <div class="row align-items-center w-100 m-0">
            <div class="col-4 text-left p-0">
                <strong>Copyright &copy; 2020-<?php echo date('Y') ?>.</strong>
            </div>
            <div class="col-4 text-center p-0 text-muted small" style="font-weight: 500;">
                Eaves Droid Platform &bull; Mobile Telemetry &amp; Forensic Intelligence
            </div>
            <div class="col-4 text-right p-0">
                <button type="button" class="btn btn-xs btn-outline-primary px-2" data-toggle="modal" data-target="#versionChangelogModal" style="font-weight: 600;">
                    <i class="fas fa-code-branch mr-1"></i>v<?php echo htmlspecialchars($platform_version ?? '2.4.0'); ?>
                </button>
            </div>
        </div>
    </footer>

    <!-- Version & Capability Modal -->
    <div class="modal fade" id="versionChangelogModal" tabindex="-1" role="dialog" aria-labelledby="versionChangelogLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="versionChangelogLabel">
                        <i class="fas fa-layer-group mr-2"></i>Eaves Droid Platform v<?php echo htmlspecialchars($platform_version ?? '2.4.0'); ?>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded border">
                        <div>
                            <h6 class="mb-1 font-weight-bold text-dark"><?php echo htmlspecialchars($platform_name ?? 'Enterprise Telemetry & Forensic Suite'); ?></h6>
                            <span class="badge badge-info">Build #<?php echo htmlspecialchars($platform_build ?? '20400'); ?></span>
                            <span class="badge badge-success ml-1">Active Release</span>
                        </div>
                        <div class="text-right small text-muted">
                            System Status: <span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Operational</span>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mb-2"><i class="fas fa-list-ul mr-1 text-primary"></i> Registered Capabilities & Changelogs</h6>
                    <?php if (!empty($version_changelogs)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($version_changelogs as $log): 
                                $badgeClass = 'badge-secondary';
                                if ($log['category'] === 'feature') $badgeClass = 'badge-primary';
                                elseif ($log['category'] === 'capability') $badgeClass = 'badge-success';
                                elseif ($log['category'] === 'security') $badgeClass = 'badge-danger';
                                elseif ($log['category'] === 'fix') $badgeClass = 'badge-warning';
                            ?>
                                <div class="list-group-item px-0 py-2">
                                    <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                        <strong class="text-dark"><i class="fas fa-caret-right text-primary mr-1"></i><?php echo htmlspecialchars($log['title']); ?></strong>
                                        <div>
                                            <span class="badge <?php echo $badgeClass; ?> text-uppercase"><?php echo htmlspecialchars($log['category']); ?></span>
                                            <span class="badge badge-light border ml-1"><?php echo htmlspecialchars($log['component']); ?></span>
                                        </div>
                                    </div>
                                    <p class="mb-0 small text-muted"><?php echo htmlspecialchars($log['description'] ?? ''); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">No specific capability logs registered for this version.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->
    <!-- jQuery UI 1.11.4 -->
    <script src="<?php echo base_url('assets/plugins/jquery-ui/jquery-ui.min.js'); ?>"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="<?php echo base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/plugins/datatables/datatables.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/plugins/chartjs/chart.min.js'); ?>"></script>

    <?php
    $label_sms = array();
    $label_calls = array();

    $data_sms = array();
    $data_calls = array();

    foreach ($active_calls as $call_item) {
        array_push($label_calls, "'" . $call_item['Saved'] . "'");
        array_push($data_calls, "'" . $call_item['Totals'] . "'");
    }

    foreach ($active_sms as $sms_item) {
        array_push($label_sms, "'" . $sms_item['sms_number'] . "'");
        array_push($data_sms, "'" . $sms_item['Totals'] . "'");
    }
    $label_smss = implode(",", $label_sms);
    $data_smss = implode(",", $data_sms);

    $label_calls = implode(",", $label_calls);
    $data_calls = implode(",", $data_calls);

    ?>
    <script type="text/javascript">

        const ctx_call = document.getElementById('canvas_call_distribution').getContext('2d');
        const myChart_call = new Chart(ctx_call, {
            type: 'pie',
            data: {
                labels: [<?php echo $label_calls; ?>],
                datasets: [{
                    label: '# of Votes',
                    data: [<?php echo $data_calls; ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(40, 159, 64, 1)',
                        'rgba(210, 199, 199, 1)',
                        'rgba(78, 52, 199, 1)',
                        'rgba(52, 152, 219, 1)',
                        'rgba(46, 204, 113, 1)',
                        'rgba(155, 89, 182, 1)',
                        'rgba(241, 196, 15, 1)'
                    ],
                    borderColor: "#fff",
                    borderWidth: 1
                }]
            },
            options: {
                tooltips: {
                    enabled: true
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                },
                layout: {
                    padding: 25
                }
            }
        });

        const ctx_sms = document.getElementById('canvas_sms_distribution').getContext('2d');
        const myChart_sms = new Chart(ctx_sms, {
            type: 'pie',
            data: {
                labels: [<?php echo $label_smss; ?>],
                datasets: [{
                    data: [<?php echo $data_smss; ?>],
                    backgroundColor: [
                        'rgba(231, 76, 60, 1)',
                        'rgba(52, 152, 219, 1)',
                        'rgba(46, 204, 113, 1)',
                        'rgba(155, 89, 182, 1)',
                        'rgba(241, 196, 15, 1)',
                        'rgba(230, 126, 34, 1)',
                        'rgba(52, 73, 94, 1)',
                        'rgba(26, 188, 156, 1)',
                        'rgba(149, 165, 166, 1)',
                        'rgba(189, 195, 199, 1)',
                        'rgba(243, 156, 18, 1)',
                        'rgba(211, 84, 0, 1)',
                        'rgba(192, 57, 43, 1)',
                        'rgba(41, 128, 185, 1)',
                        'rgba(39, 174, 96, 1)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                },
                layout: {
                    padding: 25
                }
            }
        });

    </script>
    <!-- overlayScrollbars -->
    <script src="<?php echo base_url('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js'); ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?php echo base_url('assets/js/adminlte.min.js'); ?>"></script>
    </body>
    </html>

