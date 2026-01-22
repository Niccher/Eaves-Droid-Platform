<?php //include('head_landing.php'); ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 font-weight-bold mb-4">Enrich your digital life with <span class="text-warning">Prj Images</span></h1>
                    <p class="lead mb-4">The web utility <span class="font-weight-bold">Prj Images</span> is a PaaS (Platform as a service) that lets you easily see how data from calls and SMS correlate.</p>

                    <div class="mb-4">
                        <a href="<?= url_to('login') ?>" class="btn btn-light btn-lg mr-3">
                            <i class="fas fa-rocket mr-2"></i>Get Started
                        </a>
                        <a href="<?= url_to('how-to') ?>" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-play-circle mr-2"></i>How it works
                        </a>
                    </div>

                    <div class="d-flex flex-wrap">
                        <div class="mr-4 mb-2">
                            <i class="fas fa-check-circle text-success mr-1"></i>
                            <span>No credit card required</span>
                        </div>
                        <div class="mr-4 mb-2">
                            <i class="fas fa-check-circle text-success mr-1"></i>
                            <span>7-day free trial</span>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-check-circle text-success mr-1"></i>
                            <span>Cancel anytime</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card bg-white text-dark p-4 shadow-lg">
                        <h4 class="text-primary text-center mb-4">
                            <i class="fas fa-chart-line mr-2"></i>Trusted by Professionals
                        </h4>
                        <div class="row text-center">
                            <div class="col-6 mb-4">
                                <h2 class="text-primary font-weight-bold mb-2">5,000+</h2>
                                <p class="text-muted mb-0">Active Users</p>
                            </div>
                            <div class="col-6 mb-4">
                                <h2 class="text-success font-weight-bold mb-2">150K+</h2>
                                <p class="text-muted mb-0">Files Analyzed</p>
                            </div>
                            <div class="col-6">
                                <h2 class="text-info font-weight-bold mb-2">99.7%</h2>
                                <p class="text-muted mb-0">Accuracy Rate</p>
                            </div>
                            <div class="col-6">
                                <h2 class="text-warning font-weight-bold mb-2">24/7</h2>
                                <p class="text-muted mb-0">Support</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge badge-primary mb-3" style="font-size: 1rem;">Platform Features</span>
                <h2 class="section-title text-center">Complete Mobile Data Intelligence</h2>
                <p class="text-muted lead">Everything you need to analyze and understand your mobile data</p>
            </div>

            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary mx-auto">
                                <i class="fas fa-mobile-alt text-white"></i>
                            </div>
                            <h4 class="text-primary mb-3">Android Data Collection</h4>
                            <p class="text-muted">Secure extraction of calls, messages, apps, and files from Android devices</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-success mx-auto">
                                <i class="fas fa-chart-bar text-white"></i>
                            </div>
                            <h4 class="text-success mb-3">Advanced Analytics</h4>
                            <p class="text-muted">AI-powered analysis with correlation mapping and pattern recognition</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-info mx-auto">
                                <i class="fas fa-shield-alt text-white"></i>
                            </div>
                            <h4 class="text-info mb-3">Secure & Private</h4>
                            <p class="text-muted">End-to-end encryption with full control over your data and privacy</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge badge-success mb-3" style="font-size: 1rem;">Simple Process</span>
                <h2 class="section-title text-center">How Prj Images Works</h2>
                <p class="text-muted lead">Get started in just 4 simple steps</p>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="process-step">
                        <div class="step-number bg-primary">1</div>
                        <h4 class="text-primary mb-2">Create Account</h4>
                        <p class="text-muted">Sign up in 30 seconds with no credit card required</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="process-step">
                        <div class="step-number bg-success">2</div>
                        <h4 class="text-success mb-2">Install App</h4>
                        <p class="text-muted">Download the Android client from Play Store or directly</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="process-step">
                        <div class="step-number bg-info">3</div>
                        <h4 class="text-info mb-2">Collect Data</h4>
                        <p class="text-muted">App collects data intelligently and uploads securely</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="process-step">
                        <div class="step-number bg-warning">4</div>
                        <h4 class="text-warning mb-2">Get Insights</h4>
                        <p class="text-muted">View comprehensive analytics on your dashboard</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="<?= url_to('how-to') ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-info-circle mr-2"></i>Learn More
                </a>
            </div>
        </div>
    </section>

<?php //include('footer_landing.php'); ?>