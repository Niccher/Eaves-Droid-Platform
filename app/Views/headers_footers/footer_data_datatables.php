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

    <script>
        $('.tabledump').DataTable(
            {
                "pageLength": 25,
                // dom: 'Bfrtip',
                dom: 'Bfrt',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5'
                ]
            }
        );

        // Export data notification handler
        $(document).on('click', '.export-link', function(e) {
            const dataType = $(this).data('type') || 'Data';
            const now = new Date();
            const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
            
            // Add notification item
            const notificationHtml = `
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-file-download mr-2 text-success"></i> Export completed for ${dataType}
                    <span class="float-right text-muted text-sm">${timeStr}</span>
                </a>
            `;
            
            $('#dynamic-notifications').prepend(notificationHtml);
            
            // Update badge count
            const badge = $('.navbar-badge');
            let count = parseInt(badge.text()) || 0;
            badge.text(count + 1);
            
            // Update header count
            const header = $('#notification-header');
            if (header.length) {
                let headerText = header.text().trim();
                let match = headerText.match(/(\d+)/);
                if (match) {
                    let newCount = parseInt(match[1]) + 1;
                    header.html(`<i class="fas fa-bell mr-2"></i> ${newCount} Notifications`);
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

