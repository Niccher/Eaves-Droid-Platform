    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer bg-dark border-top-0 py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-5 mb-4">
                    <h5 class="text-white text-bold mb-4">
                        <i class="fas fa-chart-bar mr-2 text-primary"></i>
                        <span class="font-weight-light">Eaves Droid</span>
                    </h5>
                    <p class="text-light" style="font-size: 1.1rem; line-height: 1.7; opacity: 0.9;">Eaves Droid is a cutting-edge mobile data intelligence platform that helps users collect, analyze, and visualize Android device data with AI-powered insights and enterprise-grade security.</p>
                    <div class="mt-4">
                        <a href="#" class="text-light mr-4"><i class="fab fa-facebook fa-xl"></i></a>
                        <a href="#" class="text-light mr-4"><i class="fab fa-twitter fa-xl"></i></a>
                        <a href="#" class="text-light mr-4"><i class="fab fa-linkedin fa-xl"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-github fa-xl"></i></a>
                    </div>
                </div>
                <div class="col-md-3 mb-4 ml-auto">
                    <h6 class="text-white text-bold mb-4" style="font-size: 1.2rem;">Quick Links</h6>
                    <ul class="list-unstyled" style="font-size: 1.05rem;">
                        <li class="mb-3"><a href="<?= base_url('landing') ?>" class="text-light hover-primary">Home</a></li>
                        <li class="mb-3"><a href="<?= base_url('aboutus') ?>" class="text-light hover-primary">About Us</a></li>
                        <li class="mb-3"><a href="<?= base_url('download') ?>" class="text-light hover-primary">Download</a></li>
                        <li class="mb-3"><a href="<?= base_url('contactus') ?>" class="text-light hover-primary">Support</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6 class="text-white text-bold mb-4" style="font-size: 1.2rem;">Resources</h6>
                    <ul class="list-unstyled" style="font-size: 1.05rem;">
                        <li class="mb-3"><a href="<?= base_url('faqs_terms') ?>" class="text-light hover-primary">FAQ</a></li>
                        <li class="mb-3"><a href="<?= base_url('faqs_terms') ?>" class="text-light hover-primary">Terms of Service</a></li>
                        <li class="mb-3"><a href="<?= base_url('how_to') ?>" class="text-light hover-primary">How It Works</a></li>
                        <li class="mb-3"><a href="<?= base_url('privacy-policy') ?>" class="text-light hover-primary">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary my-5">
            <div class="row" style="font-size: 1rem;">
                <div class="col-md-12 text-center text-light" style="opacity: 0.8;">
                    <strong>Copyright &copy; 2020-<?php echo date('Y') ?> <a href="<?= base_url('landing') ?>" class="text-primary">Eaves Droid</a>.</strong> All rights reserved.
                </div>
            </div>
        </div>
    </footer>
    
    <style>
        .hover-primary:hover { color: #007bff !important; transition: 0.3s; text-decoration: none; }
        .text-light { color: #f8f9fa !important; }
        .fa-xl { font-size: 1.5em; }
    </style>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- Bootstrap 4 -->
<script src="<?php echo base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js?v=1.4'); ?>"></script>
<!-- AdminLTE App -->
<script src="<?php echo base_url('assets/js/adminlte.min.js?v=1.4'); ?>"></script>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        
        // Active link refinement
        var path = window.location.pathname;
        $('.nav-link').each(function() {
            if (path.includes($(this).attr('href'))) {
                $(this).addClass('active');
            }
        });
    });
</script>
</body>
</html>