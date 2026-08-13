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
    <script src="<?php echo base_url('assets/plugins/jquery-ui/jquery-ui.min.js?v=1.4'); ?>"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="<?php echo base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js?v=1.4'); ?>"></script>
    <script src="<?php echo base_url('assets/plugins/datatables/datatables.min.js?v=1.4'); ?>"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- Toastr -->
    <script src="<?php echo base_url('assets/plugins/toastr/toastr.min.js?v=1.4'); ?>"></script>

    <!-- overlayScrollbars -->
    <script src="<?php echo base_url('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js?v=1.4'); ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?php echo base_url('assets/js/adminlte.min.js?v=1.4'); ?>"></script>
    <!-- Global SweetAlert2 confirm for .action-form submissions -->
    <script>
    $(document).ready(function() {
        $(document).on('submit', '.action-form', function(e) {
            e.preventDefault();
            const form = this;
            const title = $(form).data('confirm-title') || 'Confirm Action';
            const text = $(form).data('confirm-text') || 'Are you sure?';
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
    </script>

    <!-- Auto-stop impersonation when page is closed -->
    <?php if (session()->get('impersonated_by')): ?>
    <script>
    window.addEventListener('beforeunload', function() {
        navigator.sendBeacon('<?php echo base_url('superadmin/impersonate/stop'); ?>');
    });
    </script>
    <?php endif; ?>
    </body>
</html>

