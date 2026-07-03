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

        <script src="<?php echo base_url('assets/plugins/select2/select2.min.js'); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $('.source_sms').select2({
                    closeOnSelect: false
                });

                $('.source_sms_finance').select2({
                    closeOnSelect: false
                });

                $(".add_money").click(function(){
                    $("#pop_add_money_point").modal('show');

                });

                $(".source_save_data_points").click(function(){
                    var senders = '"'+ $(".source_sms").select2().val() + '"';
                    <?php $encrypter = model('Mod_Crypt'); ?>
                    var my_id = "<?php echo $encrypter->base64url_encode($user_info['id']);?>";
                    var urls = "<?php echo base_url('analysis/set/set_sms_datapoints/'); ?>";
                    $.ajax({
                        url: urls.concat(my_id),
                        type: 'POST',
                        data: { "points": senders },
                        success: function(res) {
                            $(".source_sms").val('').trigger('change');
                            $("#pop_add_money_point").modal('hide');
                            window.location.reload();
                        }
                    });
                });

                $(".source_clear_data_points").click(function(){
                    $(".source_sms").val('').trigger('change');
                    $("#pop_add_money_point").modal('hide');
                });

                $(function () {
                    $("#finance_datapoints").DataTable({
                        "responsive": true, "lengthChange": false, "autoWidth": false,
                        "buttons": ["csv", "pdf", "print", "colvis"],
                        "pageLength": 25,
                    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
                    $('#example2').DataTable({
                        "paging": true,
                        "lengthChange": false,
                        "searching": false,
                        "ordering": true,
                        "info": true,
                        "autoWidth": false,
                        "responsive": true,
                    });
                });

                $(".init_calculations").click(function(){
                    var senders = $(".source_sms_finance").select2().val();
                    console.log(senders);
                    var base_code = "<?php echo base_url('analysis/sms/finance/'); ?>";
                    var base_add = base_code.concat(senders);
                    window.location.href = base_add;
                });

            });

        </script>
        <!-- overlayScrollbars -->
        <script src="<?php echo base_url('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js'); ?>"></script>
        <!-- AdminLTE App -->
        <script src="<?php echo base_url('assets/js/adminlte.min.js'); ?>"></script>
    </body>
</html>

