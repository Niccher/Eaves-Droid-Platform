    <!-- Hero Section -->
    <div class="bg-primary py-5 shadow-sm" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
        <div class="container text-center text-white">
            <h1 class="display-4 font-weight-bold">How Eaves Droid Works</h1>
            <p class="lead">From sign-up to AI-powered insights — your complete mobile data intelligence pipeline</p>
        </div>
    </div>

    <!-- Content Section -->
    <section class="content py-5">
        <div class="container">
            <!-- Process Steps -->
            <div class="row mb-5">
                <div class="col-12 text-center mb-5">
                    <h2 class="font-weight-light">Simple 4-Step Process</h2>
                    <p class="text-muted lead">Get started in minutes with our automated workflow</p>
                    <hr class="w-25 border-primary">
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="info-box shadow-sm h-100">
                        <span class="info-box-icon bg-primary">1</span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold">Create Account</span>
                            <span class="info-box-number small font-weight-normal">Sign up and receive your unique verification token.</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="info-box shadow-sm h-100">
                        <span class="info-box-icon bg-success">2</span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold text-success">Install Android Client</span>
                            <span class="info-box-number small font-weight-normal">Download APK, authenticate, and grant permissions.</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="info-box shadow-sm h-100">
                        <span class="info-box-icon bg-info">3</span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold text-info">Data Collection & Sync</span>
                            <span class="info-box-number small font-weight-normal">Background collection with encrypted Wi-Fi uploads.</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="info-box shadow-sm h-100">
                        <span class="info-box-icon bg-warning">4</span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold text-white">AI Analysis & Insights</span>
                            <span class="info-box-number small font-weight-normal text-white">PHP-ML algorithms generate reports and dashboards.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Steps -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card card-outline card-primary shadow-sm h-100">
                        <div class="card-header">
                            <h3 class="card-title text-bold"><i class="fas fa-user-plus mr-2 text-primary"></i>1. Create Account</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">Sign up for an Eaves Droid account using your email address. Upon registration, a unique verification token is generated and linked to your account — this token is used to authenticate your Android client and authorize data submissions.</p>
                            <div class="mt-2">
                                <span class="badge badge-primary">Instant Setup</span>
                                <span class="badge badge-success">Free Account</span>
                                <span class="badge badge-info">Verification Token</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card card-outline card-success shadow-sm h-100">
                        <div class="card-header">
                            <h3 class="card-title text-bold"><i class="fab fa-android mr-2 text-success"></i>2. Install Android Client</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">Download the Eaves Droid APK and install it on your Android device (Android 7.0+). Launch the app, enter your verification token to authenticate, and grant the required permissions (calls, SMS, contacts, location, files, and usage access) to enable full data collection.</p>
                            <div class="mt-2">
                                <span class="badge badge-info">Lightweight APK</span>
                                <span class="badge badge-warning">Android 7.0+</span>
                                <span class="badge badge-danger">Token Auth</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-header">
                            <h3 class="card-title text-bold"><i class="fas fa-database mr-2 text-info"></i>3. Data Collection & Sync</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">The Android client runs in the background collecting calls, SMS, contacts, locations, files, and activity data. All data is encrypted before upload and synced to your self-hosted backend, where Docker containers process and store it for analysis.</p>
                            <div class="mt-2">
                                <span class="badge badge-success">Encrypted Uploads</span>
                                <span class="badge badge-primary">Offline Queue</span>
                                <span class="badge badge-dark">Docker Backend</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card card-outline card-warning shadow-sm h-100">
                        <div class="card-header">
                            <h3 class="card-title text-bold"><i class="fas fa-brain mr-2 text-warning"></i>4. AI Analysis & Insights</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">PHP-ML runs KMeans and DBSCAN clustering plus TF-IDF and Z-Score analysis in the webapp, while the Python ML engine adds Isolation Forest, One-Class SVM, contact-graph outliers, activity prediction, phishing heuristics, and suspicious-file scanning. Results are compiled into a PDF intelligence report and displayed on an interactive dashboard with charts and tables.</p>
                            <div class="mt-2">
                                <span class="badge badge-danger">PHP-ML &amp; Python ML</span>
                                <span class="badge badge-info">Isolation Forest &amp; One-Class SVM</span>
                                <span class="badge badge-success">15-Section PDF</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Docker Note -->
            <div class="card bg-dark shadow-sm mt-4">
                <div class="card-body text-center text-white py-4">
                    <i class="fab fa-docker fa-2x mb-2"></i>
                    <p class="lead mb-1">Prefer to run your own instance? Deploy with Docker:</p>
                    <code class="bg-secondary text-white px-3 py-2 rounded d-inline-block">docker-compose up -d</code>
                </div>
            </div>

            <!-- Get Started -->
            <div class="card bg-primary shadow-sm mt-5 text-center p-5" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <h2 class="text-bold mb-3">Ready to Get Started?</h2>
                    <p class="lead mb-5">Create your account today and start analyzing your mobile data with precision.</p>
                    <div class="row justify-content-center">
                        <div class="col-md-3 mb-2">
                            <a href="<?= url_to('register') ?>" class="btn btn-warning btn-lg btn-block shadow text-bold">SIGN UP</a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= url_to('login') ?>" class="btn btn-outline-light btn-lg btn-block shadow-sm">LOG IN</a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= url_to('download') ?>" class="btn btn-success btn-lg btn-block shadow text-bold">DOWNLOAD APP</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


<?php //include('footer_landing.php'); ?>
