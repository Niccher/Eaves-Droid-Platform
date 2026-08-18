    <!-- Hero Section -->
    <div class="bg-primary py-5 shadow-sm" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
        <div class="container text-center text-white">
            <h1 class="display-4 font-weight-bold">About Eaves Droid</h1>
            <p class="lead">Unified platform for Android mobile data intelligence and analysis</p>
        </div>
    </div>

    <!-- Content Section -->
    <section class="content py-5">
        <div class="container">

            <!-- What is Eaves Droid -->
            <div class="row mb-5">
                <div class="col-12 text-center mb-4">
                    <h2 class="font-weight-light">What is Eaves Droid?</h2>
                    <p class="text-muted lead">Full-stack mobile data intelligence, from device to dashboard</p>
                    <hr class="w-25 border-primary">
                </div>
                <div class="col-12">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <p class="text-muted" style="font-size: 1.1rem; line-height: 1.8;">
                                        <strong class="text-primary">Eaves Droid</strong> is a comprehensive mobile data intelligence platform
                                        that collects Android device data via a native APK client, processes it with a
                                        <strong>two-engine ML stack</strong> (PHP-ML in the webapp, plus a dedicated Python ML
                                        engine), and presents actionable insights through an interactive web dashboard. The
                                        entire stack is fully containerized with <strong>Docker</strong> for seamless deployment.
                                    </p>
                                    <p class="text-muted" style="font-size: 1.1rem; line-height: 1.8;">
                                        From call logs and SMS messages to file metadata and app usage statistics —
                                        Eaves Droid ingests raw mobile data, applies clustering, classification, and
                                        anomaly detection models, and surfaces meaningful patterns through an intuitive
                                        interface built on <strong>CodeIgniter 4</strong> and <strong>AdminLTE</strong>.
                                    </p>
                                </div>
                                <div class="col-md-4 text-center d-flex align-items-center justify-content-center">
                                    <div class="p-4">
                                        <i class="fas fa-robot fa-5x text-primary mb-3"></i>
                                        <h5 class="text-bold">End-to-End Intelligence</h5>
                                        <p class="text-muted small mb-0">Collect → Process → Visualize</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Platform Architecture -->
            <div class="row mb-5">
                <div class="col-12 text-center mb-4">
                    <h2 class="font-weight-light">Platform Architecture</h2>
                    <p class="text-muted lead">Four pillars powering the Eaves Droid ecosystem</p>
                    <hr class="w-25 border-success">
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 card-outline card-primary shadow-sm text-center">
                        <div class="card-body">
                            <i class="fab fa-android fa-3x text-primary mb-3"></i>
                            <h5 class="text-bold">Android ClientController</h5>
                            <p class="text-muted small">Native APK data collector that extracts 60+ types of data from Android devices with secure, AES-encrypted sync.</p>
                            <span class="badge badge-primary">Java 17</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 card-outline card-success shadow-sm text-center">
                        <div class="card-body">
                            <i class="fas fa-tachometer-alt fa-3x text-success mb-3"></i>
                            <h5 class="text-bold">Web Dashboard</h5>
                            <p class="text-muted small">CodeIgniter 4 + AdminLTE interface providing real-time visualizations, user management, and export capabilities.</p>
                            <span class="badge badge-success">PHP 8.3 / CI4</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 card-outline card-info shadow-sm text-center">
                        <div class="card-body">
                            <i class="fas fa-brain fa-3x text-info mb-3"></i>
                            <h5 class="text-bold">ML Engine</h5>
                            <p class="text-muted small">Python FastAPI service running 7 anomaly detectors — Isolation Forest, One-Class SVM, PCA app scanning, contact-graph outliers, activity prediction, phishing heuristics, and suspicious-file scanning.</p>
                            <span class="badge badge-info">Python + scikit-learn</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 card-outline card-warning shadow-sm text-center">
                        <div class="card-body">
                            <i class="fab fa-docker fa-3x text-warning mb-3"></i>
                            <h5 class="text-bold">Docker Deployment</h5>
                            <p class="text-muted small">Fully containerized via docker-compose with Apache + PHP 8.3, MySQL 8.4, and the Python ML engine for reproducible production environments.</p>
                            <span class="badge badge-warning text-white">Apache + MySQL 8.4</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Capabilities -->
            <div class="row mb-5">
                <div class="col-12 text-center mb-4">
                    <h2 class="font-weight-light">Key Capabilities</h2>
                    <p class="text-muted lead">Comprehensive mobile data analysis at your fingertips</p>
                    <hr class="w-25 border-info">
                </div>

                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="info-box bg-light shadow-sm h-100">
                        <span class="info-box-icon bg-primary"><i class="fas fa-phone-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold">Call Log Analysis</span>
                            <span class="info-box-number small">Duration, frequency, contact patterns</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="info-box bg-light shadow-sm h-100">
                        <span class="info-box-icon bg-success"><i class="fas fa-sms"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold">SMS Correlation</span>
                            <span class="info-box-number small">Conversation threads, word frequency</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="info-box bg-light shadow-sm h-100">
                        <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold">Contact Network</span>
                            <span class="info-box-number small">Relationship mapping, group detection</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="info-box bg-light shadow-sm h-100">
                        <span class="info-box-icon bg-warning"><i class="fas fa-map-marker-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold">Location Timeline</span>
                            <span class="info-box-number small">Geo-tagged event sequences</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="info-box bg-light shadow-sm h-100">
                        <span class="info-box-icon bg-danger"><i class="fas fa-file-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold">File Categorization</span>
                            <span class="info-box-number small">Type distribution, storage analysis</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="info-box bg-light shadow-sm h-100">
                        <span class="info-box-icon bg-secondary"><i class="fas fa-chart-bar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold">App Usage Stats</span>
                            <span class="info-box-number small">Launch frequency, usage duration</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="info-box bg-light shadow-sm h-100">
                        <span class="info-box-icon bg-primary"><i class="fas fa-microchip"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold">Device Metrics</span>
                            <span class="info-box-number small">Hardware, OS, battery telemetry</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="info-box bg-light shadow-sm h-100">
                        <span class="info-box-icon bg-success"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-bold">Activity Patterns</span>
                            <span class="info-box-number small">Hourly/daily behavioral trends</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technology Stack -->
            <div class="row mb-5">
                <div class="col-12 text-center mb-4">
                    <h2 class="font-weight-light">Technology Stack</h2>
                    <p class="text-muted lead">Modern tools powering the Eaves Droid platform</p>
                    <hr class="w-25 border-warning">
                </div>

                <div class="col-12">
                    <div class="card card-outline card-warning shadow-sm">
                        <div class="card-body text-center py-4">
                            <span class="badge badge-primary px-3 py-2 m-1" style="font-size: 0.95rem;">PHP 8.3</span>
                            <span class="badge badge-success px-3 py-2 m-1" style="font-size: 0.95rem;">CodeIgniter 4</span>
                            <span class="badge badge-info px-3 py-2 m-1" style="font-size: 0.95rem;">MySQL 8.4</span>
                            <span class="badge badge-secondary px-3 py-2 m-1" style="font-size: 0.95rem;">Apache</span>
                            <span class="badge badge-warning text-white px-3 py-2 m-1" style="font-size: 0.95rem;">Docker</span>
                            <span class="badge badge-danger px-3 py-2 m-1" style="font-size: 0.95rem;">PHP-ML</span>
                            <span class="badge badge-dark px-3 py-2 m-1" style="font-size: 0.95rem;">Python / FastAPI</span>
                            <span class="badge badge-info px-3 py-2 m-1" style="font-size: 0.95rem;">scikit-learn</span>
                            <span class="badge badge-dark px-3 py-2 m-1" style="font-size: 0.95rem;">AdminLTE</span>
                            <span class="badge badge-primary px-3 py-2 m-1" style="font-size: 0.95rem;">Bootstrap 4</span>
                            <span class="badge badge-success px-3 py-2 m-1" style="font-size: 0.95rem;">FontAwesome</span>
                            <span class="badge badge-info px-3 py-2 m-1" style="font-size: 0.95rem;">Chart.js</span>
                            <span class="badge badge-warning text-white px-3 py-2 m-1" style="font-size: 0.95rem;">Android</span>
                            <span class="badge badge-secondary px-3 py-2 m-1" style="font-size: 0.95rem;">Dompdf</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Development Journey -->
            <div class="row">
                <div class="col-12 text-center mb-4">
                    <h2 class="font-weight-light">Development Journey</h2>
                    <p class="text-muted lead">Built with passion and precision</p>
                    <hr class="w-25 border-warning">
                </div>

                <div class="col-12">
                    <div class="card card-outline card-warning shadow-sm">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center mb-4 mb-md-0 border-right">
                                    <div class="bg-warning p-4 d-inline-flex rounded-circle mb-3 shadow-sm">
                                        <i class="fas fa-code fa-3x text-white"></i>
                                    </div>
                                    <h4 class="mb-1 text-bold">Solo Developer</h4>
                                    <p class="text-muted small">Founder & Full-Stack Architect</p>
                                </div>
                                <div class="col-md-9 pl-md-5">
                                    <h3 class="mb-3 text-bold">One Developer, Endless Possibilities</h3>
                                    <p class="text-muted">As the sole developer behind Eaves Droid, I've combined years of expertise in Android development, backend engineering, and machine learning to create a platform that balances technical power with an intuitive user experience.</p>

                                    <h5 class="mb-3 text-bold"><i class="fas fa-cogs mr-2 text-warning"></i>Core Competencies:</h5>
                                    <div class="mb-4">
                                        <span class="badge badge-primary px-3 py-2">Android Framework</span>
                                        <span class="badge badge-success px-3 py-2">Data Intelligence</span>
                                        <span class="badge badge-info px-3 py-2">System Security</span>
                                        <span class="badge badge-warning px-3 py-2 text-white">UI/UX Design</span>
                                        <span class="badge badge-danger px-3 py-2">Machine Learning</span>
                                    </div>

                                    <div class="callout callout-warning bg-light">
                                        <p class="mb-0 font-italic">
                                            "Every line of code is written with precision, ensuring that Eaves Droid delivers reliable intelligence while remaining accessible to all."
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card bg-primary shadow-sm text-white text-center py-4">
                        <div class="card-body">
                            <h3 class="font-weight-bold">Ready to explore your mobile data?</h3>
                            <p class="lead">Get started with Eaves Droid today.</p>
                            <a href="<?= url_to('register') ?>" class="btn btn-warning btn-lg px-5 shadow text-bold">Create Account</a>
                            <a href="<?= url_to('how-to') ?>" class="btn btn-outline-light btn-lg px-5 ml-2 shadow">Learn How</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
