<?php //include('head_landing.php'); ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 font-weight-bold mb-4">How It Works</h1>
                    <p class="lead mb-4">Simple process to get started with Prj Images</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="py-5">
        <div class="container">
            <!-- Process Steps -->
            <div class="row mb-5">
                <div class="col-12 text-center mb-4">
                    <h2 class="section-title text-center">Simple 4-Step Process</h2>
                    <p class="text-muted lead">Get started in minutes with our easy process</p>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="process-step">
                        <div class="step-number bg-primary">1</div>
                        <h4 class="text-primary mb-2">Create Account</h4>
                        <p class="text-muted mb-0">Sign up in 30 seconds with your email</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="process-step">
                        <div class="step-number bg-success">2</div>
                        <h4 class="text-success mb-2">Download App</h4>
                        <p class="text-muted mb-0">Install the Android client on your device</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="process-step">
                        <div class="step-number bg-info">3</div>
                        <h4 class="text-info mb-2">Collect Data</h4>
                        <p class="text-muted mb-0">App intelligently collects and uploads data</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="process-step">
                        <div class="step-number bg-warning">4</div>
                        <h4 class="text-warning mb-2">Get Insights</h4>
                        <p class="text-muted mb-0">View comprehensive analytics on dashboard</p>
                    </div>
                </div>
            </div>

            <!-- Detailed Steps -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <div class="mr-3">
                                    <i class="fas fa-user-plus fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h4 class="text-primary mb-1">Creating an Account</h4>
                                    <p class="text-muted">This is the first step and once an account has been created, a unique verification code is generated that you use to authenticate the Android client.</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge badge-primary">30 seconds</span>
                                <span class="badge badge-success ml-2">Free</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <div class="mr-3">
                                    <i class="fab fa-android fa-2x text-success"></i>
                                </div>
                                <div>
                                    <h4 class="text-success mb-1">Installing Android Client</h4>
                                    <p class="text-muted">The user installs the Android client, authenticates with the generated code, grants necessary permissions, and launches the extraction feature.</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge badge-info">15MB</span>
                                <span class="badge badge-warning ml-2">Android 8+</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <div class="mr-3">
                                    <i class="fas fa-database fa-2x text-info"></i>
                                </div>
                                <div>
                                    <h4 class="text-info mb-1">Data Extraction</h4>
                                    <p class="text-muted">The application intelligently collects data and securely sends it to our encrypted cloud storage for processing.</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge badge-success">Encrypted</span>
                                <span class="badge badge-primary ml-2">Auto-sync</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <div class="mr-3">
                                    <i class="fas fa-cloud fa-2x text-warning"></i>
                                </div>
                                <div>
                                    <h4 class="text-warning mb-1">Cloud Data Processing</h4>
                                    <p class="text-muted">Received data is analyzed by our advanced algorithms and generates well-labelled insights for users to understand their mobile data patterns.</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge badge-danger">AI Analysis</span>
                                <span class="badge badge-info ml-2">Real-time</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Get Started -->
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body p-5">
                            <h2 class="mb-4">Ready to Get Started?</h2>
                            <p class="lead mb-4">Create your account today and start analyzing your mobile data</p>
                            <div class="d-flex justify-content-center flex-wrap">
                                <a href="<?php echo base_url('auth/register'); ?>" class="btn btn-light btn-lg m-2">
                                    <i class="fas fa-user-plus mr-2"></i>Sign Up
                                </a>
                                <a href="<?php echo base_url('auth/login'); ?>" class="btn btn-outline-light btn-lg m-2">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                                </a>
                                <a href="<?php echo base_url('download'); ?>" class="btn btn-success btn-lg m-2">
                                    <i class="fas fa-download mr-2"></i>Download App
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php //include('footer_landing.php'); ?>