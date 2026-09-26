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
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var toggleBtn = document.getElementById("darkModeToggle");
        var icon = document.getElementById("darkModeIcon");
        var body = document.body;
        var navbars = document.querySelectorAll(".main-header.navbar");
        
        function applyDarkMode(isDark) {
            if (isDark) {
                body.classList.add("dark-mode");
                icon.classList.remove("fa-moon");
                icon.classList.add("fa-sun");
                navbars.forEach(function(nav) {
                    nav.classList.remove("navbar-white", "navbar-light");
                    nav.classList.add("navbar-dark");
                });
            } else {
                body.classList.remove("dark-mode");
                icon.classList.remove("fa-sun");
                icon.classList.add("fa-moon");
                navbars.forEach(function(nav) {
                    nav.classList.remove("navbar-dark");
                    nav.classList.add("navbar-white", "navbar-light");
                });
            }
        }
        
        var currentMode = localStorage.getItem("eaves_dark_mode");
        applyDarkMode(currentMode === "enabled");
        
        if (toggleBtn) {
            toggleBtn.addEventListener("click", function(e) {
                e.preventDefault();
                var isDark = body.classList.contains("dark-mode");
                if (isDark) {
                    localStorage.setItem("eaves_dark_mode", "disabled");
                    applyDarkMode(false);
                } else {
                    localStorage.setItem("eaves_dark_mode", "enabled");
                    applyDarkMode(true);
                }
            });
        }
    });
    </script>
    </body>
    </html>

