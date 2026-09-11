    <!-- Hero Section -->
    <div class="jumbotron jumbotron-fluid bg-primary text-white shadow-sm mb-0" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="badge badge-warning badge-pill px-3 py-2 mb-3"><i class="fas fa-code-branch mr-1"></i> Open Source</span>
                    <h1 class="display-4 font-weight-bold mb-3">Eaves Droid</h1>
                    <p class="h4 font-weight-light mb-3">Open-Source Mobile Data Intelligence Platform</p>
                    <p class="lead mb-4">Collect, analyze, and visualize Android device data with AI-powered analytics. Uncover hidden patterns in calls, SMS, contacts, locations, and more — all self-hosted and privacy-first.</p>
                    <div class="mb-4">
                        <a href="<?= url_to('register') ?>" class="btn btn-warning btn-lg px-4 mr-3 shadow">
                            <i class="fas fa-rocket mr-2"></i>Get Started
                        </a>
                        <a href="<?= url_to('demo-login') ?>" class="btn btn-outline-warning btn-lg px-4 mr-3 shadow-sm" style="background-color: rgba(255,255,255,0.1); border-color: #ffc107; color: #ffc107;">
                            <i class="fas fa-magic mr-2"></i>Try Interactive Demo
                        </a>
                        <a href="<?= url_to('how-to') ?>" class="btn btn-outline-light btn-lg px-4 shadow-sm">
                            <i class="fas fa-play-circle mr-2"></i>How It Works
                        </a>
                    </div>
                    <div class="d-flex flex-wrap small opacity-75">
                        <span class="mr-3 mb-2"><i class="fas fa-check-circle mr-1 text-warning"></i> AI / ML Analysis</span>
                        <span class="mr-3 mb-2"><i class="fas fa-check-circle mr-1 text-warning"></i> Self-Hosted & Private</span>
                        <span class="mr-3 mb-2"><i class="fas fa-check-circle mr-1 text-warning"></i> Android APK Collector</span>
                        <span><i class="fas fa-check-circle mr-1 text-warning"></i> Docker Deploy</span>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="card card-outline card-warning shadow-lg text-dark">
                        <div class="card-header">
                            <h3 class="card-title text-bold"><i class="fas fa-chart-line mr-2"></i>Platform at a Glance</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <h4 class="text-primary font-weight-bold mb-0">60+</h4>
                                    <small class="text-muted">Data Extractors</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <h4 class="text-success font-weight-bold mb-0">3</h4>
                                    <small class="text-muted">Open-Source Repos</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-info font-weight-bold mb-0">17</h4>
                                    <small class="text-muted">Anomaly Detectors</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-warning font-weight-bold mb-0">24/7</h4>
                                    <small class="text-muted">Self-Hosted</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- What You Can Analyze -->
    <div class="content py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="font-weight-light mb-2"><i class="fas fa-database text-primary mr-2"></i>What You Can Analyze</h2>
                <p class="text-muted lead">Every data type your Android device generates — captured and correlated</p>
                <hr class="w-25 border-primary">
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 card-outline card-primary shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="mb-3"><i class="fas fa-phone-alt fa-3x text-primary"></i></div>
                            <h5 class="text-bold">Call Logs</h5>
                            <p class="text-muted small mb-0">Incoming, outgoing, missed calls with duration, timestamps, and frequency analysis.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 card-outline card-success shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="mb-3"><i class="fas fa-comment-dots fa-3x text-success"></i></div>
                            <h5 class="text-bold">SMS Messages</h5>
                            <p class="text-muted small mb-0">Sent and received messages with TF-IDF content analysis and contact clustering.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 card-outline card-info shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="mb-3"><i class="fas fa-address-book fa-3x text-info"></i></div>
                            <h5 class="text-bold">Contacts</h5>
                            <p class="text-muted small mb-0">Contact frequency maps, communication patterns, and relationship network graphs.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 card-outline card-warning shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="mb-3"><i class="fas fa-map-marker-alt fa-3x text-warning"></i></div>
                            <h5 class="text-bold">Locations</h5>
                            <p class="text-muted small mb-0">GPS coordinates with timeline visualization, geofence clustering, and movement patterns.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 card-outline card-danger shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="mb-3"><i class="fas fa-th-large fa-3x text-danger"></i></div>
                            <h5 class="text-bold">Installed Apps</h5>
                            <p class="text-muted small mb-0">Application inventory with categories, usage statistics, and permission analysis.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 card-outline card-secondary shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="mb-3"><i class="fas fa-folder-open fa-3x text-secondary"></i></div>
                            <h5 class="text-bold">Files &amp; Media</h5>
                            <p class="text-muted small mb-0">File metadata indexing, media library scanning, and storage usage breakdowns.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 card-outline card-cyan shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="mb-3"><i class="fas fa-history fa-3x text-cyan"></i></div>
                            <h5 class="text-bold">Activities</h5>
                            <p class="text-muted small mb-0">User activity timelines, app usage sessions, and behavioral pattern detection.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 card-outline card-navy shadow-sm text-center p-3">
                        <div class="card-body">
                            <div class="mb-3"><i class="fas fa-microchip fa-3x text-navy"></i></div>
                            <h5 class="text-bold">Device Metrics</h5>
                            <p class="text-muted small mb-0">Battery, network, sensor data, and hardware performance indicators.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Powered By: AI/ML Section -->
            <div class="bg-light p-4 p-md-5 rounded shadow-sm mt-4">
                <div class="text-center mb-5">
                    <h2 class="font-weight-light mb-2"><i class="fas fa-brain text-primary mr-2"></i>Powered By AI / ML</h2>
                    <p class="text-muted lead">A two-engine analysis stack — PHP-ML in the webapp, plus a dedicated Python ML engine</p>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-primary">
                            <div class="card-body">
                                <span class="badge badge-primary badge-pill float-right">Unsupervised</span>
                                <h5 class="text-bold text-primary"><i class="fas fa-project-diagram mr-2"></i>KMeans Clustering</h5>
                                <p class="text-muted small mb-0">Groups contacts and communication patterns into behavioral clusters (PHP-ML). Identifies who you talk to most and when, revealing natural social circles.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-success">
                            <div class="card-body">
                                <span class="badge badge-success badge-pill float-right">Anomaly</span>
                                <h5 class="text-bold text-success"><i class="fas fa-tree mr-2"></i>Isolation Forest</h5>
                                <p class="text-muted small mb-0">The Python ML engine flags multidimensional call anomalies — duration, hour, direction, and network combined — that single-threshold rules would miss.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-info">
                            <div class="card-body">
                                <span class="badge badge-info badge-pill float-right">Anomaly</span>
                                <h5 class="text-bold text-info"><i class="fas fa-microchip mr-2"></i>One-Class SVM</h5>
                                <p class="text-muted small mb-0">Models normal CPU, RAM, battery and radio states, then flags abnormal system behaviour — a hallmark of background malware or covert processes.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-warning">
                            <div class="card-body">
                                <span class="badge badge-warning badge-pill float-right">NLP</span>
                                <h5 class="text-bold text-warning"><i class="fas fa-file-alt mr-2"></i>TF-IDF Text Analysis</h5>
                                <p class="text-muted small mb-0">Extracts key terms and topics from SMS conversations and measures term importance to summarise what your communications are about.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-danger">
                            <div class="card-body">
                                <span class="badge badge-danger badge-pill float-right">Graph</span>
                                <h5 class="text-bold text-danger"><i class="fas fa-share-alt mr-2"></i>Contact Graph Outlier</h5>
                                <p class="text-muted small mb-0">Builds a relationship graph from your contacts and flags orphaned or isolated numbers that sit outside your normal social network.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-secondary">
                            <div class="card-body">
                                <span class="badge badge-secondary badge-pill float-right">Sequence</span>
                                <h5 class="text-bold text-secondary"><i class="fas fa-chart-line mr-2"></i>Activity Sequence Predictor</h5>
                                <p class="text-muted small mb-0">Learns your app-usage rhythms and flags activity at times that deviate from your normal daily pattern.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="<?= base_url('aboutus') ?>" class="btn btn-outline-primary btn-lg px-4 shadow-sm">
                        <i class="fas fa-brain mr-2"></i>See the Full ML Engine
                    </a>
                </div>
            </div>

            <!-- Remote Fleet Orchestration & Real-Time Controls -->
            <div class="mt-5">
                <div class="text-center mb-5">
                    <h2 class="font-weight-light mb-2"><i class="fas fa-satellite-dish text-primary mr-2"></i>Remote Device Orchestration</h2>
                    <p class="text-muted lead">Zero-latency command execution powered by Firebase Cloud Messaging (FCM)</p>
                    <hr class="w-25 border-primary">
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 card-outline card-danger shadow-sm text-center p-3">
                            <div class="card-body">
                                <div class="mb-3"><i class="fas fa-bell fa-3x text-danger"></i></div>
                                <h5 class="text-bold">Siren Alarm</h5>
                                <p class="text-muted small mb-0">Trigger loud audio sirens remotely to pinpoint misplaced devices or raise security alerts.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 card-outline card-warning shadow-sm text-center p-3">
                            <div class="card-body">
                                <div class="mb-3"><i class="fas fa-lock fa-3x text-warning"></i></div>
                                <h5 class="text-bold">Remote Screen Lock</h5>
                                <p class="text-muted small mb-0">Instantly lock active devices and enforce authentication during security quarantines.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 card-outline card-info shadow-sm text-center p-3">
                            <div class="card-body">
                                <div class="mb-3"><i class="fas fa-sync-alt fa-3x text-info"></i></div>
                                <h5 class="text-bold">On-Demand Sync</h5>
                                <p class="text-muted small mb-0">Dispatch instant telemetry sweep commands to pull updated GPS, battery, and call status.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 card-outline card-success shadow-sm text-center p-3">
                            <div class="card-body">
                                <div class="mb-3"><i class="fas fa-headset fa-3x text-success"></i></div>
                                <h5 class="text-bold">Live Support Chat</h5>
                                <p class="text-muted small mb-0">Direct real-time communication channel between fleet operators, users, and system administrators.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How It Works -->
            <div class="mt-5">
                <div class="text-center mb-5">
                    <h2 class="font-weight-light mb-2"><i class="fas fa-cogs text-primary mr-2"></i>How It Works</h2>
                    <p class="text-muted lead">From zero to insights in four straightforward steps</p>
                    <hr class="w-25 border-primary">
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0 bg-light">
                            <div class="card-body position-relative">
                                <div class="h1 text-primary font-weight-bold mb-3" style="opacity: 0.25; position: absolute; top: 8px; right: 16px;">01</div>
                                <div class="mb-3"><span class="btn btn-primary btn-lg rounded-circle shadow-sm" style="width: 60px; height: 60px; line-height: 44px;"><i class="fas fa-user-plus fa-lg"></i></span></div>
                                <h5 class="text-bold">Create Account</h5>
                                <p class="text-muted small mb-0">Register in seconds. Your data stays on your server — we never see it. Choose Docker or native PHP deployment.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0 bg-light">
                            <div class="card-body position-relative">
                                <div class="h1 text-success font-weight-bold mb-3" style="opacity: 0.25; position: absolute; top: 8px; right: 16px;">02</div>
                                <div class="mb-3"><span class="btn btn-success btn-lg rounded-circle shadow-sm" style="width: 60px; height: 60px; line-height: 44px;"><i class="fas fa-download fa-lg"></i></span></div>
                                <h5 class="text-bold">Install Android App</h5>
                                <p class="text-muted small mb-0">Download the Eaves Droid APK from your dashboard. Install on any Android device — no root required, minimal permissions.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0 bg-light">
                            <div class="card-body position-relative">
                                <div class="h1 text-info font-weight-bold mb-3" style="opacity: 0.25; position: absolute; top: 8px; right: 16px;">03</div>
                                <div class="mb-3"><span class="btn btn-info btn-lg rounded-circle shadow-sm" style="width: 60px; height: 60px; line-height: 44px;"><i class="fas fa-sync-alt fa-lg"></i></span></div>
                                <h5 class="text-bold">Sync Data</h5>
                                <p class="text-muted small mb-0">The app securely collects and uploads calls, SMS, contacts, locations, apps, and files to your Eaves Droid server.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0 bg-light">
                            <div class="card-body position-relative">
                                <div class="h1 text-warning font-weight-bold mb-3" style="opacity: 0.25; position: absolute; top: 8px; right: 16px;">04</div>
                                <div class="mb-3"><span class="btn btn-warning btn-lg rounded-circle shadow-sm" style="width: 60px; height: 60px; line-height: 44px;"><i class="fas fa-chart-pie fa-lg"></i></span></div>
                                <h5 class="text-bold">AI Analysis</h5>
                                <p class="text-muted small mb-0">KMeans and DBSCAN cluster your data, while the Python ML engine runs Isolation Forest, One-Class SVM, and more against your calls, SMS, contacts, apps, files, and activity.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="<?= url_to('how-to') ?>" class="btn btn-primary btn-lg px-5 shadow">
                        <i class="fas fa-info-circle mr-2"></i>Full Setup Guide
                    </a>
                </div>
            </div>

            <!-- Deployment Options -->
            <div class="bg-light p-4 p-md-5 rounded shadow-sm mt-5">
                <div class="text-center mb-5">
                    <h2 class="font-weight-light mb-2"><i class="fas fa-server text-primary mr-2"></i>Deployment Options</h2>
                    <p class="text-muted lead">Choose the deployment that fits your infrastructure</p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-5 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-primary">
                            <div class="card-header bg-primary text-white text-center">
                                <i class="fab fa-docker fa-3x mb-2"></i>
                                <h4 class="mb-0 text-bold">Docker</h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">One-command deployment with docker-compose. Includes the web app, PHP, MySQL, and the Python ML engine.</p>
                                <pre class="bg-dark text-light p-3 rounded small mb-0"><code>docker compose up --build -d</code></pre>
                                <hr>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li><i class="fas fa-check text-success mr-1"></i> Apache + PHP 8.3</li>
                                    <li><i class="fas fa-check text-success mr-1"></i> MySQL 8.4</li>
                                    <li><i class="fas fa-check text-success mr-1"></i> Python ML engine (FastAPI)</li>
                                    <li><i class="fas fa-check text-success mr-1"></i> phpMyAdmin</li>
                                    <li><i class="fas fa-check text-success mr-1"></i> Volume persistence</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-success">
                            <div class="card-header bg-success text-white text-center">
                                <i class="fas fa-terminal fa-3x mb-2"></i>
                                <h4 class="mb-0 text-bold">Native PHP</h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Deploy directly on any PHP-capable server. Minimal requirements, maximum compatibility.</p>
                                <pre class="bg-dark text-light p-3 rounded small mb-0"><code>composer install
php spark serve</code></pre>
                                <hr>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li><i class="fas fa-check text-success mr-1"></i> PHP 8.3+ required</li>
                                    <li><i class="fas fa-check text-success mr-1"></i> MySQL 8.4</li>
                                    <li><i class="fas fa-check text-success mr-1"></i> Apache / Nginx</li>
                                    <li><i class="fas fa-check text-success mr-1"></i> PHP-ML included</li>
                                    <li><i class="fas fa-check text-success mr-1"></i> No Docker dependencies</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="mt-5 text-center py-5 px-3 rounded shadow-sm" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                <h2 class="text-white font-weight-bold mb-3">Ready to Unlock Your Mobile Data?</h2>
                <p class="lead text-white mb-4 opacity-75">Start collecting, analyzing, and visualizing your Android device data today.</p>
                <div>
                    <a href="<?= url_to('register') ?>" class="btn btn-warning btn-lg px-5 shadow mr-3">
                        <i class="fas fa-rocket mr-2"></i>Get Started Free
                    </a>
                    <a href="<?= base_url('download') ?>" class="btn btn-outline-light btn-lg px-5 shadow-sm">
                        <i class="fas fa-download mr-2"></i>Download APK
                    </a>
                </div>
                <p class="text-white-50 small mt-4 mb-0">
                    <i class="fas fa-lock mr-1"></i> Fully self-hosted &bull;
                    <i class="fas fa-code mr-1"></i> Open source &bull;
                    <i class="fas fa-android mr-1"></i> Android + PHP + Python ML engine
                </p>
            </div>

        </div>
    </div>
