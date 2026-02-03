    <footer class="main-footer">
        <strong>Copyright &copy; 2020-<?php echo date('Y') ?>.</strong>
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.4
        </div>
    </footer>
    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->
    <!-- jQuery -->
    <script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
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
    <script src="<?php echo base_url('assets/js/adminlte.js'); ?>"></script>
    </body>
    </html>

