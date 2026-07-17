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
    <!-- jQuery UI 1.11.4 -->
    <script src="<?php echo base_url('assets/plugins/jquery-ui/jquery-ui.min.js'); ?>"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="<?php echo base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/plugins/datatables/datatables.min.js'); ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function () {
            try {
                $('.tabledump').DataTable({
                    "paging": false,
                    "info": false,
                    "autoWidth": false,
                    "dom": '<"row mb-2"<"col-sm-6"f><"col-sm-6 text-right"B>>rt',
                    buttons: [{
                        text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                        className: 'btn btn-sm btn-danger',
                        action: function (e, dt, node, config) {
                            Swal.fire({
                                title: 'Generating PDF...',
                                text: 'Please wait while we prepare your document',
                                allowOutsideClick: false,
                                didOpen: () => { Swal.showLoading(); }
                            });
                            var element = document.querySelector('.tabledump');
                            html2pdf().set({
                                margin:       10,
                                filename:     'export_' + Date.now() + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, letterRendering: true },
                                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
                            }).from(element).save().then(function() {
                                Swal.close();
                                Swal.fire({ icon: 'success', title: 'Export Complete', text: 'PDF has been downloaded', timer: 2000, showConfirmButton: false });
                            }).catch(function() {
                                Swal.close();
                                Swal.fire({ icon: 'error', title: 'Export Failed', text: 'Could not generate PDF', timer: 3000, showConfirmButton: false });
                            });
                        }
                    }]
                });
            } catch (e) {
                console.warn('DataTable init failed:', e);
            }
        });
    </script>

    <!-- overlayScrollbars -->
    <script src="<?php echo base_url('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js'); ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?php echo base_url('assets/js/adminlte.min.js'); ?>"></script>
    </body>
    </html>

