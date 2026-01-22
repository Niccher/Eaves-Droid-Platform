</main>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h4 class="mb-3">
                    <i class="fas fa-chart-bar mr-2"></i>Prj Images
                </h4>
                <p class="text-light">Mobile Data Intelligence Platform providing comprehensive analysis and visualization of your mobile data.</p>
                <div class="social-links mt-3">
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-github"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="mb-3">Platform</h5>
                <ul class="list-unstyled footer-links">
                    <li class="mb-2"><a href="<?php echo base_url('landing'); ?>">Home</a></li>
                    <li class="mb-2"><a href="<?php echo base_url('aboutus'); ?>">About</a></li>
                    <li class="mb-2"><a href="<?php echo base_url('download'); ?>">Download</a></li>
                    <li class="mb-2"><a href="<?php echo base_url('how-to'); ?>">How it Works</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="mb-3">Support</h5>
                <ul class="list-unstyled footer-links">
                    <li class="mb-2"><a href="<?php echo base_url('faqs_terms'); ?>">FAQ</a></li>
                    <li class="mb-2"><a href="<?php echo base_url('contactus'); ?>">Contact</a></li>
                    <li class="mb-2"><a href="#">Documentation</a></li>
                    <li class="mb-2"><a href="#">Privacy Policy</a></li>
                </ul>
            </div>

            <div class="col-lg-3 mb-4">
                <h5 class="mb-3">Contact</h5>
                <p class="text-light mb-2">
                    <i class="fas fa-envelope mr-2"></i> support@prjimages.com
                </p>
                <p class="text-light mb-2">
                    <i class="fas fa-phone mr-2"></i> +1 (555) 123-4567
                </p>
                <p class="text-light">
                    <i class="fas fa-clock mr-2"></i> Mon-Fri: 9AM-6PM EST
                </p>
            </div>
        </div>

        <hr class="bg-secondary">

        <div class="row">
            <div class="col-md-6">
                <p class="mb-0 text-light">
                    &copy; <?php echo date('Y'); ?> Prj Images. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-right">
                <p class="mb-0 text-light">
                    <i class="fas fa-shield-alt mr-1"></i> Secure & Encrypted Platform
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Back to Top Button
    const backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });

    backToTop.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#') return;

            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                e.preventDefault();
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Initialize tooltips
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Animate elements on scroll
    function animateOnScroll() {
        const elements = document.querySelectorAll('.feature-icon, .stat-card, .pricing-card');

        elements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const elementVisible = 150;

            if (elementTop < window.innerHeight - elementVisible) {
                element.classList.add('animated');
            }
        });
    }

    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll();
</script>
</body>
</html>