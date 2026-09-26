<!-- Hero Section -->
    <div class="bg-primary py-5 shadow-sm" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
        <div class="container text-center text-white">
            <h1 class="display-4 font-weight-bold">Privacy Policy</h1>
            <p class="lead">How we collect, use, and protect your data</p>
        </div>
    </div>

    <!-- Privacy Policy Section -->
    <section class="content py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <style>
                        .nav-pills .nav-link {
                            color: #007bff;
                            border: 1px solid transparent;
                        }
                        .nav-pills .nav-link:hover {
                            border-color: #007bff;
                        }
                        .nav-pills .nav-link.active,
                        .nav-pills .show > .nav-link {
                            background-color: #ffffff;
                            color: #007bff;
                            border: 1px solid #007bff;
                            font-weight: 700;
                        }
                    </style>
                    <!-- Privacy Navigation -->
                    <div class="card card-primary card-outline shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h3 class="card-title text-bold">Sections</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="pill" href="#introduction">
                                        <i class="fas fa-info-circle mr-2"></i> Introduction
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="pill" href="#information">
                                        <i class="fas fa-database mr-2"></i> Information We Collect
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="pill" href="#usage">
                                        <i class="fas fa-cogs mr-2"></i> How We Use Your Data
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="pill" href="#protection">
                                        <i class="fas fa-shield-alt mr-2"></i> Data Protection
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="pill" href="#control">
                                        <i class="fas fa-user-cog mr-2"></i> Your Control & Rights
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="pill" href="#contact">
                                        <i class="fas fa-envelope mr-2"></i> Contact Us
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="tab-content">
                        <!-- Introduction -->
                        <div class="tab-pane fade show active" id="introduction">
                            <div class="card card-outline card-primary shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title text-bold">Introduction</h3>
                                </div>
                                <div class="card-body">
                                    <div class="callout callout-warning shadow-sm">
                                        <h5 class="text-bold"><i class="fas fa-exclamation-triangle mr-2 text-warning"></i> Important: Review Carefully</h5>
                                        <p class="mb-0 small">This Privacy Policy explains how Eaves Droid collects, uses, stores, and protects your information when you use the platform at <strong><?= base_url() ?></strong>. By using our service, you consent to the practices described here.</p>
                                    </div>
                                    <p class="text-muted">Eaves Droid is a mobile data intelligence platform. You control your account and the device data you choose to upload, sync, and analyze. We are committed to transparency about what we do with that data.</p>
                                    <p class="text-muted">This policy applies to all visitors, accounts, and users of the platform. Please read it alongside our <a href="<?= base_url('faqs_terms') ?>">Terms of Service</a>.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Information We Collect -->
                        <div class="tab-pane fade" id="information">
                            <div class="card card-outline card-primary shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title text-bold">Information We Collect</h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">We collect information in three broad categories to deliver and improve our services.</p>

                                    <div class="row mt-4">
                                        <div class="col-md-6 mb-3">
                                            <div class="card card-outline card-info h-100">
                                                <div class="card-header"><h6 class="card-title text-bold"><i class="fas fa-user mr-2 text-info"></i>Account Information</h6></div>
                                                <div class="card-body">
                                                    <ul class="mb-0 small text-muted pl-3">
                                                        <li>Name, email address, and password</li>
                                                        <li>Registration and login timestamps</li>
                                                        <li>IP address and device identifiers</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card card-outline card-warning h-100">
                                                <div class="card-header"><h6 class="card-title text-bold"><i class="fas fa-mobile-alt mr-2 text-warning"></i>Device Data You Upload</h6></div>
                                                <div class="card-body">
                                                    <ul class="mb-0 small text-muted pl-3">
                                                        <li>Call logs and SMS messages</li>
                                                        <li>Contact lists and file metadata</li>
                                                        <li>App usage and device telemetry</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3 mx-auto">
                                            <div class="card card-outline card-secondary h-100">
                                                <div class="card-header"><h6 class="card-title text-bold"><i class="fas fa-cookie-bite mr-2 text-secondary"></i>Usage & Technical Data</h6></div>
                                                <div class="card-body">
                                                    <ul class="mb-0 small text-muted pl-3">
                                                        <li>Cookies and browser preferences</li>
                                                        <li>Page access and interaction logs</li>
                                                        <li>Diagnostic and performance metrics</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- How We Use Your Data -->
                        <div class="tab-pane fade" id="usage">
                            <div class="card card-outline card-primary shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title text-bold">How We Use Your Data</h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Your data is used solely to provide the service you asked for:</p>
                                    <ul class="small text-muted mb-3">
                                        <li>To authenticate your account and secure your login session.</li>
                                        <li>To process and analyze the mobile data you sync and generate your personalized dashboards.</li>
                                        <li>To send transactional and account-related notifications you opt into.</li>
                                        <li>To improve platform performance, security, and user experience.</li>
                                    </ul>

                                    <div class="callout callout-danger mt-4 shadow-sm">
                                        <h5 class="text-bold text-danger"><i class="fas fa-ban mr-2"></i> What We Do NOT Do</h5>
                                        <ul class="mb-0 small mt-2">
                                            <li>We do <b>not</b> sell or rent your personal data or uploaded information to third parties.</li>
                                            <li>We do <b>not</b> use your data for advertising or profiling.</li>
                                            <li>We do <b>not</b> share your data without your consent unless legally required.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Protection -->
                        <div class="tab-pane fade" id="protection">
                            <div class="card card-outline card-primary shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title text-bold">Data Protection</h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">We employ industry-standard safeguards to keep your information secure:</p>

                                    <div class="row mt-4">
                                        <div class="col-md-4 mb-3">
                                            <div class="card card-outline card-success h-100 text-center">
                                                <div class="card-body">
                                                    <i class="fas fa-lock fa-2x text-success mb-2"></i>
                                                    <h6 class="text-bold">Encryption</h6>
                                                    <p class="small text-muted mb-0">Passwords are hashed and data transferred over secure connections.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="card card-outline card-success h-100 text-center">
                                                <div class="card-body">
                                                    <i class="fas fa-user-shield fa-2x text-success mb-2"></i>
                                                    <h6 class="text-bold">Access Control</h6>
                                                    <p class="small text-muted mb-0">Your data is isolated per account and only you can access it.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="card card-outline card-success h-100 text-center">
                                                <div class="card-body">
                                                    <i class="fas fa-sync-alt fa-2x text-success mb-2"></i>
                                                    <h6 class="text-bold">Security Updates</h6>
                                                    <p class="small text-muted mb-0">We monitor and patch our platform to protect against vulnerabilities.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Your Control & Rights -->
                        <div class="tab-pane fade" id="control">
                            <div class="card card-outline card-primary shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title text-bold">Your Control & Rights</h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">You own your data. You can, at any time:</p>
                                    <ul class="small text-muted">
                                        <li><b>Access</b> and review your uploaded data from your dashboard.</li>
                                        <li><b>Correct</b> or update your account information.</li>
                                        <li><b>Export</b> your data for your own records.</li>
                                        <li><b>Delete</b> your account and associated data.</li>
                                        <li><b>Control</b> cookies and notification preferences.</li>
                                    </ul>

                                    <div class="callout callout-info mt-4 shadow-sm">
                                        <h5 class="text-bold"><i class="fas fa-cookie-bite mr-2 text-info"></i> Cookies</h5>
                                        <p class="mb-0 small">Like most websites, Eaves Droid uses cookies to store preferences and recognize returning visitors. You may disable cookies in your browser, though some features require them to function.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Us -->
                        <div class="tab-pane fade" id="contact">
                            <div class="card card-outline card-primary shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title text-bold">Contact Us</h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">If you have questions about this Privacy Policy or how we handle your data, reach out to us and we will be happy to help.</p>
                                    <div class="row mt-4">
                                        <div class="col-md-6 mb-3">
                                            <div class="info-box bg-light shadow-sm h-100">
                                                <span class="info-box-icon bg-primary"><i class="fas fa-envelope"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-bold">Contact Form</span>
                                                    <span class="info-box-number small"><a href="<?= base_url('contactus') ?>">Send us a message</a></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="info-box bg-light shadow-sm h-100">
                                                <span class="info-box-icon bg-info"><i class="fas fa-file-contract"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-bold">Related Policies</span>
                                                    <span class="info-box-number small"><a href="<?= base_url('faqs_terms') ?>">View Terms of Service</a></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last Updated -->
                    <div class="card bg-light shadow-sm mt-4 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                            <h3 class="text-bold">Your Privacy Matters</h3>
                            <p class="text-muted">Last updated: <?= date('F Y') ?>. We may update this policy from time to time — changes will be reflected on this page.</p>
                            <a href="<?= base_url('landing') ?>" class="btn btn-primary shadow-sm px-4">
                                <i class="fas fa-home mr-2"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>